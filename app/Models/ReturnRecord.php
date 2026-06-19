<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Models\Concerns\Approvable;
use App\Models\Concerns\Auditable;
use App\Services\ApprovalService;
use App\Services\ReturnService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnRecord extends Model implements ApprovableDocument
{
    use Approvable, Auditable, SoftDeletes;

    protected $table = 'returns';

    protected $fillable = [
        'return_number',
        'type',
        'sale_id',
        'supplier_id',
        'shop_id',
        'warehouse_id',
        'reason',
        'status',
        'refund_amount',
        'processed_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public static function generateNumber(string $type = 'customer'): string
    {
        $prefix = ($type === 'supplier' ? 'SRT' : 'CRT').'-'.date('Y').'-';
        $last = static::withTrashed()
            ->where('return_number', 'like', $prefix.'%')
            ->orderByDesc('return_number')
            ->value('return_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalTitle(): string
    {
        return ucfirst($this->type).' Return '.$this->return_number;
    }

    public function approvalSummary(): string
    {
        $lines = $this->items()->count();
        $context = $this->type === 'customer'
            ? ($this->sale?->receipt_number ?? 'No sale')
            : ($this->supplier?->name ?? 'No supplier');

        return "{$context} — {$lines} line ".str('item')->plural($lines);
    }

    public function approvalReference(): string
    {
        return $this->return_number;
    }

    public function approvalModuleKey(): string
    {
        return 'return';
    }

    public function auditModule(): string
    {
        return 'return';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->return_number;
    }

    public function resolveApprovalApprover(): ?User
    {
        return app(ApprovalService::class)->resolveDefaultApprover();
    }

    public function onApprovalApproved(Approval $approval): void
    {
        $actor = $approval->actions()
            ->where('action', ApprovalActionType::Approved)
            ->latest()
            ->first()
            ?->actor;

        app(ReturnService::class)->process($this, $actor);

        $this->update([
            'approved_by' => $actor?->id,
            'approved_at' => now(),
        ]);
    }

    public function onApprovalRejected(Approval $approval): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function onApprovalReturned(Approval $approval): void
    {
        $this->update(['status' => 'pending']);
    }

    public function typeLabel(): string
    {
        return $this->type === 'customer' ? 'Customer Return' : 'Supplier Return';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
            default => ucfirst($this->status),
        };
    }

    public function canEdit(): bool
    {
        return $this->status === 'pending' && ! $this->hasOpenApproval();
    }

    public function canSubmit(): bool
    {
        return $this->status === 'pending'
            && $this->items()->exists()
            && ! $this->hasOpenApproval();
    }

    public function canDelete(): bool
    {
        return $this->canEdit();
    }
}
