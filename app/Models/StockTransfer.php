<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Models\Concerns\Approvable;
use App\Models\Concerns\Auditable;
use App\Services\ApprovalService;
use App\Services\TransferService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model implements ApprovableDocument
{
    use Approvable, Auditable, SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'transfer_request_id',
        'type',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'dispatched_by',
        'dispatched_at',
        'received_by',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'ST-'.date('Y').'-';
        $last = static::withTrashed()
            ->where('transfer_number', 'like', $prefix.'%')
            ->orderByDesc('transfer_number')
            ->value('transfer_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function transferRequest(): BelongsTo
    {
        return $this->belongsTo(TransferRequest::class);
    }

    public function linkedRequest(): HasOne
    {
        return $this->hasOne(TransferRequest::class, 'stock_transfer_id');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function destination(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approvalTitle(): string
    {
        return 'Stock Transfer '.$this->transfer_number;
    }

    public function approvalSummary(): string
    {
        $lines = $this->items()->count();

        return $this->routeLabel().' — '.$lines.' line '.str('item')->plural($lines);
    }

    public function approvalReference(): string
    {
        return $this->transfer_number;
    }

    public function approvalModuleKey(): string
    {
        return 'transfer';
    }

    public function auditModule(): string
    {
        return 'stock_transfer';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->transfer_number;
    }

    public function resolveApprovalApprover(): ?User
    {
        return User::permission('transfers.approve')
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

        $this->load('items.product', 'source', 'destination');

        app(TransferService::class)->reserveForTransfer($this);

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
        $this->update(['status' => 'returned']);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'warehouse_to_shop' => 'Warehouse → Shop',
            'inter_shop' => 'Shop → Shop',
            'shop_to_warehouse' => 'Shop → Warehouse',
            default => ucfirst(str_replace('_', ' ', (string) $this->type)),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'returned' => 'Returned',
            'dispatched', 'in_transit' => 'In Transit',
            'received', 'closed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft', 'returned' => 'tr-badge tr-badge-slate',
            'pending' => 'tr-badge tr-badge-amber',
            'approved' => 'tr-badge tr-badge-blue',
            'dispatched', 'in_transit' => 'tr-badge tr-badge-indigo',
            'received', 'closed' => 'tr-badge tr-badge-green',
            'rejected', 'cancelled' => 'tr-badge tr-badge-rose',
            default => 'tr-badge tr-badge-slate',
        };
    }

    public function isWarehouseSource(): bool
    {
        return $this->source instanceof Warehouse;
    }

    public function isWarehouseDestination(): bool
    {
        return $this->destination instanceof Warehouse;
    }

    public function routeLabel(): string
    {
        return $this->sourceLabel().' → '.$this->destinationLabel();
    }

    public function sourceLabel(): string
    {
        $source = $this->source;

        if (! $source) {
            return 'Unknown source';
        }

        return ($source instanceof Warehouse ? 'WH' : 'Shop').': '.$source->name;
    }

    public function destinationLabel(): string
    {
        $destination = $this->destination;

        if (! $destination) {
            return 'Unknown destination';
        }

        return ($destination instanceof Warehouse ? 'WH' : 'Shop').': '.$destination->name;
    }

    public function canSubmit(): bool
    {
        return in_array($this->status, ['draft', 'returned'], true)
            && $this->items()->exists()
            && ! $this->hasOpenApproval();
    }

    public function canDispatch(): bool
    {
        return $this->status === 'approved'
            && (float) $this->items()->sum('dispatched_quantity') < 0.001;
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['dispatched', 'in_transit'], true);
    }

    public function isInTransit(): bool
    {
        return in_array($this->status, ['dispatched', 'in_transit'], true);
    }
}
