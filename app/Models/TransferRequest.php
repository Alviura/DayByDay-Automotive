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

class TransferRequest extends Model implements ApprovableDocument
{
    use Approvable, Auditable, SoftDeletes;

    protected $fillable = [
        'request_number',
        'type',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'TR-'.date('Y').'-';
        $last = static::withTrashed()
            ->where('request_number', 'like', $prefix.'%')
            ->orderByDesc('request_number')
            ->value('request_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
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
        return $this->hasMany(TransferRequestItem::class);
    }

    public function stockTransfer(): HasOne
    {
        return $this->hasOne(StockTransfer::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalTitle(): string
    {
        return 'Transfer '.$this->request_number;
    }

    public function approvalSummary(): string
    {
        $lines = $this->items()->count();

        return $this->routeLabel().' — '.$lines.' line '.str('item')->plural($lines);
    }

    public function approvalReference(): string
    {
        return $this->request_number;
    }

    public function approvalModuleKey(): string
    {
        return 'transfer';
    }

    public function auditModule(): string
    {
        return 'transfer';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->request_number;
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

        $this->load('items.product', 'source', 'destination');

        foreach ($this->items as $item) {
            $item->update(['approved_quantity' => $item->requested_quantity]);
        }

        app(TransferService::class)->reserveForRequest($this);

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
            default => ucfirst(str_replace('_', ' ', $this->type)),
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
            'dispatched' => 'In Transit',
            'completed' => 'Completed',
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
            'dispatched' => 'tr-badge tr-badge-indigo',
            'completed' => 'tr-badge tr-badge-green',
            'rejected' => 'tr-badge tr-badge-rose',
            'cancelled' => 'tr-badge tr-badge-slate',
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

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'returned'], true) && ! $this->hasOpenApproval();
    }

    public function canSubmit(): bool
    {
        return in_array($this->status, ['draft', 'returned'], true)
            && $this->items()->exists()
            && ! $this->hasOpenApproval();
    }

    public function canDispatch(): bool
    {
        return $this->status === 'approved' && ! $this->stockTransfer()->exists();
    }

    public function canReceive(): bool
    {
        return $this->stockTransfer
            && in_array($this->stockTransfer->status, ['dispatched', 'in_transit'], true);
    }
}
