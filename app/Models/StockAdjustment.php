<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Models\Concerns\Approvable;
use App\Models\Concerns\Auditable;
use App\Services\ApprovalService;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model implements ApprovableDocument
{
    use Approvable, Auditable, SoftDeletes;

    protected $fillable = [
        'adjustment_number',
        'location_type',
        'location_id',
        'reason',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'ADJ-'.date('Y').'-';
        $last = static::withTrashed()
            ->where('adjustment_number', 'like', $prefix.'%')
            ->orderByDesc('adjustment_number')
            ->value('adjustment_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalTitle(): string
    {
        return 'Stock Adjustment '.$this->adjustment_number;
    }

    public function approvalSummary(): string
    {
        $location = $this->locationLabel();
        $itemCount = $this->items()->count();
        $reason = str_replace('_', ' ', ucfirst($this->reason));

        return "{$reason} at {$location} — {$itemCount} line ".str('item')->plural($itemCount).'.';
    }

    public function approvalReference(): string
    {
        return $this->adjustment_number;
    }

    public function approvalModuleKey(): string
    {
        return 'adjustment';
    }

    public function auditModule(): string
    {
        return 'adjustment';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->adjustment_number;
    }

    public function resolveApprovalApprover(): ?User
    {
        return User::permission('inventory.adjust.approve')
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

        app(InventoryService::class)->postAdjustment($this, $actor);

        $this->update([
            'status' => 'approved',
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
        $this->update(['status' => 'draft']);
    }

    public function locationLabel(): string
    {
        $location = $this->location;

        if (! $location) {
            return 'Unknown location';
        }

        $type = $location instanceof Warehouse ? 'Warehouse' : 'Shop';

        return $type.': '.$location->name;
    }

    public function reasonLabel(): string
    {
        return match ($this->reason) {
            'damaged' => 'Damaged stock',
            'lost' => 'Lost / missing',
            'count_variance' => 'Count variance',
            'correction' => 'Correction',
            default => 'Other',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'approved' => 'Approved & Posted',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    public function canSubmit(): bool
    {
        return $this->status === 'draft' && ! $this->hasOpenApproval();
    }

    public function canEdit(): bool
    {
        return $this->status === 'draft' && ! $this->hasOpenApproval();
    }
}
