<?php

namespace App\Http\Controllers;

use App\Enums\ApprovalActionType;
use App\Enums\ApprovalStatus;
use App\Exceptions\ApprovalException;
use App\Http\Requests\ApprovalActionRequest;
use App\Models\Approval;
use App\Models\ApprovalDemonstration;
use App\Models\QuotationSeries;
use App\Models\ReturnRecord;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
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
            ->with(['requester', 'currentApprover', 'approvable', 'actions.actor'])
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

        $modules = collect(config('approvals.modules', []))
            ->reject(fn ($meta) => ($meta['legacy'] ?? false) && ($meta['pipeline'] ?? true) === false)
            ->all();

        $pipeline = $this->buildPipeline($request, $filter);

        return view('approvals.index', compact('approvals', 'stats', 'filter', 'modules', 'pipeline'));
    }

    public function show(Approval $approval): View
    {
        $approval->load(['requester', 'currentApprover', 'actions.actor']);

        $approval->loadMorph('approvable', [
            StockTransfer::class => ['items.product.unit', 'source', 'destination', 'creator', 'transferRequest'],
            StockAdjustment::class => ['items.product', 'location', 'creator'],
            QuotationSeries::class => ['items.product', 'supplier'],
            ReturnRecord::class => ['items.product', 'sale', 'supplier', 'shop', 'warehouse'],
            ApprovalDemonstration::class => [],
        ]);

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

    /**
     * @return list<array{key: string, label: string, icon: string, count: int}>
     */
    private function buildPipeline(Request $request, string $filter): array
    {
        $pendingByModule = $this->approvalService->pendingCountByModule(
            $filter === 'mine' ? auth()->user() : null
        );

        $steps = [
            ['key' => '', 'label' => 'All modules', 'icon' => 'fa-layer-group', 'count' => array_sum($pendingByModule)],
        ];

        foreach (config('approvals.modules', []) as $key => $meta) {
            if (! ($meta['pipeline'] ?? true)) {
                continue;
            }

            if (! isset(config('approvals.module_models')[$key])) {
                continue;
            }

            $steps[] = [
                'key' => $key,
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'count' => $pendingByModule[$key] ?? 0,
            ];
        }

        return $steps;
    }
}
