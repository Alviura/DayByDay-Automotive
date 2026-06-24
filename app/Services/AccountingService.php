<?php

namespace App\Services;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalSource;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(private FinancialPeriodService $periods) {}

    /**
     * Post a system journal from an operational event.
     *
     * @param  array<int, array{account_id?: int, account_code?: string, debit?: float, credit?: float, description?: string, shop_id?: int, payment_method?: string, customer_account_id?: int, supplier_id?: int}>  $lines
     */
    public function post(
        string $eventType,
        Model $reference,
        array $lines,
        ?Carbon $entryDate = null,
        ?string $description = null,
        ?User $user = null
    ): JournalEntry {
        $user ??= auth()->user();
        $entryDate ??= now();
        $this->periods->assertDateOpen($entryDate);

        $idempotencyKey = $this->idempotencyKey($eventType, $reference);

        if ($existing = JournalEntry::where('idempotency_key', $idempotencyKey)->first()) {
            return $existing;
        }

        $normalized = $this->normalizeLines($lines);
        $this->assertBalanced($normalized);

        return DB::transaction(function () use ($eventType, $reference, $normalized, $entryDate, $description, $user, $idempotencyKey) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date' => ($entryDate ?? now())->toDateString(),
                'description' => $description ?? $this->defaultDescription($eventType, $reference),
                'source' => JournalSource::System,
                'event_type' => $eventType,
                'idempotency_key' => $idempotencyKey,
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
                'status' => JournalEntryStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user?->id,
                'created_by' => $user?->id,
            ]);

            $this->persistLines($entry, $normalized);

            return $entry->fresh(['lines.account']);
        });
    }

    public function postManualEntry(JournalEntry $entry, ?User $user = null): JournalEntry
    {
        $user ??= auth()->user();

        if (! $entry->isManual()) {
            throw new \InvalidArgumentException('Only manual journal entries can be posted this way.');
        }

        $entry->load('lines');

        if (! $entry->isBalanced()) {
            throw new \InvalidArgumentException('Journal entry is not balanced.');
        }

        $this->periods->assertDateOpen($entry->entry_date ?? now());

        return DB::transaction(function () use ($entry, $user) {
            $entry->update([
                'status' => JournalEntryStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user?->id,
            ]);

            return $entry->fresh(['lines.account', 'poster']);
        });
    }

    public function voidPostedEntry(JournalEntry $entry, ?string $reason = null, ?User $user = null): JournalEntry
    {
        $user ??= auth()->user();

        if (! $entry->canVoid()) {
            throw new \InvalidArgumentException('This journal entry cannot be voided.');
        }

        $entry->load('lines');

        return DB::transaction(function () use ($entry, $reason, $user) {
            $reversalLines = $entry->lines->map(fn (JournalLine $line) => [
                'chart_of_account_id' => $line->chart_of_account_id,
                'description' => 'Reversal: '.($line->description ?? $entry->description),
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
                'shop_id' => $line->shop_id,
                'payment_method' => $line->payment_method,
                'customer_account_id' => $line->customer_account_id,
                'supplier_id' => $line->supplier_id,
            ])->all();

            $reversal = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'entry_date' => now()->toDateString(),
                'description' => 'Void '.$entry->entry_number.($reason ? " — {$reason}" : ''),
                'source' => JournalSource::System,
                'event_type' => 'journal.voided',
                'idempotency_key' => 'journal.voided:'.$entry->id,
                'reference_type' => $entry->getMorphClass(),
                'reference_id' => $entry->id,
                'status' => JournalEntryStatus::Posted,
                'posted_at' => now(),
                'posted_by' => $user?->id,
                'created_by' => $user?->id,
                'reverses_entry_id' => $entry->id,
            ]);

            $this->persistLines($reversal, $reversalLines);

            $entry->update([
                'status' => JournalEntryStatus::Voided,
                'voided_by' => $user?->id,
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);

            return $entry->fresh(['lines.account', 'voidedBy']);
        });
    }

    public function findAccountByCode(string $code): ChartOfAccount
    {
        $account = ChartOfAccount::active()->where('code', $code)->first();

        if (! $account) {
            throw new \InvalidArgumentException("GL account {$code} is not configured.");
        }

        return $account;
    }

    public function findCashAccount(int $shopId, string $paymentMethod): ChartOfAccount
    {
        $shop = \App\Models\Shop::findOrFail($shopId);
        $code = config('finance.cash_account_prefix').'-'.$shop->code.'-'.strtoupper($paymentMethod);

        return $this->findAccountByCode($code);
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    public function prepareLines(array $lines): array
    {
        $normalized = $this->normalizeLines($lines);
        $this->assertBalanced($normalized);

        return $normalized;
    }

    public function idempotencyKey(string $eventType, Model $reference): string
    {
        return $eventType.':'.$reference->getMorphClass().':'.$reference->getKey();
    }

    /**
     * Post a reversing journal for a prior automated event (idempotent).
     */
    public function reverseByEvent(
        string $originalEventType,
        Model $reference,
        ?string $reversalEventType = null,
        ?User $user = null
    ): ?JournalEntry {
        $user ??= auth()->user();
        $reversalEventType ??= $originalEventType.'.reversed';
        $reversalKey = $this->idempotencyKey($reversalEventType, $reference);

        if ($existing = JournalEntry::where('idempotency_key', $reversalKey)->first()) {
            return $existing;
        }

        $original = JournalEntry::query()
            ->where('idempotency_key', $this->idempotencyKey($originalEventType, $reference))
            ->where('status', JournalEntryStatus::Posted)
            ->first();

        if (! $original) {
            return null;
        }

        $original->load('lines');

        $reversalLines = $original->lines->map(fn (JournalLine $line) => [
            'chart_of_account_id' => $line->chart_of_account_id,
            'description' => 'Reversal',
            'debit' => (float) $line->credit,
            'credit' => (float) $line->debit,
            'shop_id' => $line->shop_id,
            'payment_method' => $line->payment_method,
            'customer_account_id' => $line->customer_account_id,
            'supplier_id' => $line->supplier_id,
        ])->all();

        return $this->post(
            $reversalEventType,
            $reference,
            $reversalLines,
            now(),
            'Reversal of '.$original->entry_number,
            $user
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    private function normalizeLines(array $lines): array
    {
        return collect($lines)->map(function (array $line, int $index) {
            $accountId = $line['chart_of_account_id']
                ?? $line['account_id']
                ?? (isset($line['account_code']) ? $this->findAccountByCode($line['account_code'])->id : null);

            if (! $accountId) {
                throw new \InvalidArgumentException('Each journal line requires account_id or account_code.');
            }

            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit <= 0 && $credit <= 0) {
                throw new \InvalidArgumentException('Each journal line needs a debit or credit amount.');
            }

            if ($debit > 0 && $credit > 0) {
                throw new \InvalidArgumentException('A journal line cannot have both debit and credit.');
            }

            return [
                'chart_of_account_id' => $accountId,
                'description' => $line['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'shop_id' => $line['shop_id'] ?? null,
                'payment_method' => $line['payment_method'] ?? null,
                'customer_account_id' => $line['customer_account_id'] ?? null,
                'supplier_id' => $line['supplier_id'] ?? null,
                'sort_order' => $line['sort_order'] ?? $index,
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function persistLines(JournalEntry $entry, array $lines): void
    {
        foreach ($lines as $line) {
            $entry->lines()->create($line);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    private function assertBalanced(array $lines): void
    {
        $debits = round(collect($lines)->sum('debit'), 2);
        $credits = round(collect($lines)->sum('credit'), 2);

        if (abs($debits - $credits) >= 0.01) {
            throw new \InvalidArgumentException(
                'Journal entry is not balanced. Debits: '.number_format($debits, 2).' Credits: '.number_format($credits, 2)
            );
        }
    }

    private function defaultDescription(string $eventType, Model $reference): string
    {
        $ref = method_exists($reference, 'auditReferenceNumber')
            ? $reference->auditReferenceNumber()
            : (string) $reference->getKey();

        return str_replace('.', ' ', ucfirst($eventType)).($ref ? " — {$ref}" : '');
    }
}
