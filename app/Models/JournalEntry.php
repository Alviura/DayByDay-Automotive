<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Enums\JournalEntryStatus;
use App\Enums\JournalSource;
use App\Models\Concerns\Approvable;
use App\Models\Concerns\Auditable;
use App\Services\AccountingService;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model implements ApprovableDocument
{
    use Approvable, Auditable;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'description',
        'source',
        'event_type',
        'idempotency_key',
        'reference_type',
        'reference_id',
        'status',
        'posted_at',
        'posted_by',
        'created_by',
        'voided_by',
        'voided_at',
        'void_reason',
        'reverses_entry_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'source' => JournalSource::class,
        'status' => JournalEntryStatus::class,
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'JE-'.date('Y').'-';
        $last = static::where('entry_number', 'like', $prefix.'%')
            ->orderByDesc('entry_number')
            ->value('entry_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('sort_order');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_entry_id');
    }

    public function scopePosted($query)
    {
        return $query->where('status', JournalEntryStatus::Posted);
    }

    public function isPosted(): bool
    {
        return $this->status === JournalEntryStatus::Posted;
    }

    public function isManual(): bool
    {
        return $this->source === JournalSource::Manual;
    }

    public function canEdit(): bool
    {
        return $this->isManual() && $this->status === JournalEntryStatus::Draft && ! $this->hasOpenApproval();
    }

    public function canSubmit(): bool
    {
        return $this->canEdit() && $this->lines()->exists();
    }

    public function canVoid(): bool
    {
        return $this->isPosted() && ! $this->reverses_entry_id;
    }

    public function totalDebits(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function totalCredits(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebits() - $this->totalCredits()) < 0.01;
    }

    public function approvalTitle(): string
    {
        return 'Manual Journal '.$this->entry_number;
    }

    public function approvalSummary(): string
    {
        return $this->description.' — KES '.number_format($this->totalDebits(), 2);
    }

    public function approvalReference(): string
    {
        return $this->entry_number;
    }

    public function approvalModuleKey(): string
    {
        return 'journal';
    }

    public function auditModule(): string
    {
        return 'journal';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->entry_number;
    }

    public function resolveApprovalApprover(): ?User
    {
        return User::permission('finance.approve')
            ->active()
            ->orderBy('id')
            ->first()
            ?? app(ApprovalService::class)->resolveDefaultApprover();
    }

    public function onApprovalApproved(Approval $approval): void
    {
        $actor = $approval->actions()
            ->where('action', ApprovalActionType::Approved)
            ->latest()
            ->first()
            ?->actor;

        app(AccountingService::class)->postManualEntry($this, $actor);
    }

    public function onApprovalRejected(Approval $approval): void
    {
        $this->update(['status' => JournalEntryStatus::Draft]);
    }

    public function onApprovalReturned(Approval $approval): void
    {
        $this->update(['status' => JournalEntryStatus::Draft]);
    }
}
