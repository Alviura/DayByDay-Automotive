<?php

namespace App\Services;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalSource;
use App\Models\JournalEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    public function __construct(private AccountingService $accounting) {}

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createManualDraft(
        string $description,
        array $lines,
        ?Carbon $entryDate = null,
        ?User $user = null
    ): JournalEntry {
        $user ??= auth()->user();
        $entryDate ??= now();

        $normalized = $this->accounting->prepareLines($lines);

        return DB::transaction(function () use ($description, $normalized, $entryDate, $user) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date' => $entryDate->toDateString(),
                'description' => $description,
                'source' => JournalSource::Manual,
                'status' => JournalEntryStatus::Draft,
                'created_by' => $user?->id,
            ]);

            foreach ($normalized as $line) {
                $entry->lines()->create($line);
            }

            return $entry->fresh(['lines.account', 'creator']);
        });
    }

    public function submitForApproval(JournalEntry $entry, ?string $notes = null): JournalEntry
    {
        if (! $entry->canSubmit()) {
            throw new \InvalidArgumentException('This journal cannot be submitted for approval.');
        }

        if (! $entry->isBalanced()) {
            throw new \InvalidArgumentException('Journal entry must balance before submission.');
        }

        return DB::transaction(function () use ($entry, $notes) {
            $entry->update(['status' => JournalEntryStatus::PendingApproval]);
            $entry->requestApproval($notes);

            return $entry->fresh(['lines.account', 'approval']);
        });
    }

    public function void(JournalEntry $entry, ?string $reason = null, ?User $user = null): JournalEntry
    {
        return $this->accounting->voidPostedEntry($entry, $reason, $user);
    }
}
