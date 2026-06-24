<?php

namespace App\Http\Controllers;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalSource;
use App\Http\Requests\StoreManualJournalRequest;
use App\Http\Requests\VoidJournalEntryRequest;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\JournalEntryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(private JournalEntryService $journals)
    {
        $this->middleware('permission:finance.view')->only(['index', 'show']);
        $this->middleware('permission:finance.journal')->only(['create', 'store', 'submit']);
        $this->middleware('permission:finance.manage')->only(['void']);
    }

    public function index(Request $request): View
    {
        $entries = JournalEntry::query()
            ->with(['creator', 'poster'])
            ->withCount('lines')
            ->withSum('lines as total_debit', 'debit')
            ->when($request->source, fn ($q) => $q->where('source', $request->source))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('entry_number', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            }))
            ->when($request->sort === 'oldest', fn ($q) => $q->oldest('entry_date'))
            ->when($request->sort !== 'oldest', fn ($q) => $q->latest('entry_date'))
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => JournalEntry::count(),
            'posted' => JournalEntry::where('status', JournalEntryStatus::Posted)->count(),
            'draft' => JournalEntry::where('status', JournalEntryStatus::Draft)->count(),
            'pending' => JournalEntry::where('status', JournalEntryStatus::PendingApproval)->count(),
            'voided' => JournalEntry::where('status', JournalEntryStatus::Voided)->count(),
            'manual' => JournalEntry::where('source', JournalSource::Manual)->count(),
            'posted_amount' => (float) JournalLine::query()
                ->whereHas('journalEntry', fn ($q) => $q->where('status', JournalEntryStatus::Posted))
                ->sum('debit'),
        ];

        $pipeline = [
            ['key' => '', 'label' => 'All', 'icon' => 'fa-list', 'count' => $stats['total']],
            ['key' => 'posted', 'label' => 'Posted', 'icon' => 'fa-circle-check', 'count' => $stats['posted']],
            ['key' => 'pending_approval', 'label' => 'Pending', 'icon' => 'fa-hourglass-half', 'count' => $stats['pending']],
            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fa-pen', 'count' => $stats['draft']],
            ['key' => 'voided', 'label' => 'Voided', 'icon' => 'fa-ban', 'count' => $stats['voided']],
        ];

        return view('finance.journal-entries.index', compact('entries', 'stats', 'pipeline'));
    }

    public function create(): View
    {
        $accounts = ChartOfAccount::active()->orderBy('code')->get(['id', 'code', 'name', 'account_type']);

        return view('finance.journal-entries.create', compact('accounts'));
    }

    public function store(StoreManualJournalRequest $request): RedirectResponse
    {
        try {
            $entry = $this->journals->createManualDraft(
                $request->description,
                $request->lines,
                Carbon::parse($request->entry_date)
            );

            if ($request->boolean('submit_for_approval')) {
                $this->journals->submitForApproval($entry, $request->approval_notes);

                return redirect()->route('journal-entries.show', $entry)
                    ->with('status', 'Journal submitted for approval.');
            }

            return redirect()->route('journal-entries.show', $entry)
                ->with('status', 'Manual journal draft saved.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account', 'creator', 'poster', 'voidedBy', 'reference', 'approval.requester', 'approval.currentApprover']);

        return view('finance.journal-entries.show', compact('journalEntry'));
    }

    public function submit(Request $request, JournalEntry $journalEntry): RedirectResponse
    {
        try {
            $this->journals->submitForApproval($journalEntry, $request->input('approval_notes'));

            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('status', 'Journal submitted for approval.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function void(VoidJournalEntryRequest $request, JournalEntry $journalEntry): RedirectResponse
    {
        try {
            $this->journals->void($journalEntry, $request->void_reason);

            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('status', 'Journal voided and reversal posted.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
