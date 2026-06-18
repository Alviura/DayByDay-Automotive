<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalActionType;
use App\Enums\ApprovalStatus;
use App\Exceptions\ApprovalException;
use App\Http\Requests\ApprovalActionRequest;
use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalService $approvalService)
    {
        $this->middleware('permission:approvals.act');
    }

    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'mine');

        $approvals = Approval::query()
            ->with(['requester', 'currentApprover', 'approvable'])
            ->search($request->search)
            ->when($filter === 'mine', function ($q) {
                $user = auth()->user();

                if ($user->hasRole(config('approvals.default_approver_role'))) {
                    $q->pending();
                } else {
                    $q->pending()->forApprover($user);
                }
            })
            ->when($filter === 'requested', fn ($q) => $q->where('requested_by', auth()->id()))
            ->when($filter === 'pending', fn ($q) => $q->pending())
            ->when($filter === 'completed', fn ($q) => $q->whereIn('status', [
                ApprovalStatus::Approved,
                ApprovalStatus::Rejected,
            ]))
            ->when($filter === 'returned', fn ($q) => $q->where('status', ApprovalStatus::Returned))
            ->when($request->module, function ($q) use ($request) {
                $model = config('approvals.module_models.'.$request->module);
                if ($model) {
                    $q->where('approvable_type', $model);
                }
            })
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest())
            ->when($request->sort === 'module', fn ($q) => $q->orderBy('approvable_type')->orderByDesc('id'))
            ->when(! in_array($request->sort, ['oldest', 'module'], true), fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending_mine' => $this->approvalService->pendingCountFor(auth()->user()),
            'pending_all' => Approval::pending()->count(),
            'completed' => Approval::whereIn('status', [ApprovalStatus::Approved, ApprovalStatus::Rejected])->count(),
            'returned' => Approval::where('status', ApprovalStatus::Returned)->count(),
        ];

        $modules = config('approvals.modules', []);

        return view('approvals.index', compact('approvals', 'stats', 'filter', 'modules'));
    }

    public function show(Approval $approval): View
    {
        $approval->load(['requester', 'currentApprover', 'approvable', 'actions.actor']);

        $canAct = $this->approvalService->canAct($approval, auth()->user());

        return view('approvals.show', compact('approval', 'canAct'));
    }

    public function act(ApprovalActionRequest $request, Approval $approval): RedirectResponse
    {
        try {
            $action = ApprovalActionType::from($request->action);

            match ($action) {
                ApprovalActionType::Approved => $this->approvalService->approve($approval, auth()->user(), $request->comments),
                ApprovalActionType::Rejected => $this->approvalService->reject($approval, auth()->user(), $request->comments),
                ApprovalActionType::Returned => $this->approvalService->returnForRevision($approval, auth()->user(), $request->comments),
                default => throw new ApprovalException('Unsupported approval action.'),
            };

            return redirect()
                ->route('approvals.show', $approval)
                ->with('status', 'Approval '.$action->label().' successfully.');
        } catch (ApprovalException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
