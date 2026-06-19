<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Models\Concerns\Approvable;
use App\Models\Concerns\Auditable;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementFolder extends Model implements ApprovableDocument
{
    use Approvable, Auditable, SoftDeletes;

    protected $fillable = [
        'folder_number',
        'supplier_id',
        'currency',
        'exchange_rate',
        'import_type',
        'status',
        'notes',
        'total_cost',
        'total_freight',
        'total_tax',
        'total_landing_cost',
        'created_by',
        'approved_by',
        'approved_at',
        'closed_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'total_cost' => 'decimal:2',
        'total_freight' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_landing_cost' => 'decimal:2',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return static::nextNumber('PF-', 'folder_number');
    }

    protected static function nextNumber(string $prefix, string $column): string
    {
        $yearPrefix = $prefix.date('Y').'-';
        $last = static::withTrashed()
            ->where($column, 'like', $yearPrefix.'%')
            ->orderByDesc($column)
            ->value($column);

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $yearPrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceiptNotes(): HasMany
    {
        return $this->hasMany(GoodsReceiptNote::class);
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
        return 'Procurement '.$this->folder_number;
    }

    public function approvalSummary(): string
    {
        $supplier = $this->supplier?->name ?? 'No supplier';
        $lines = $this->items()->count();

        return "{$supplier} — {$lines} line ".str('item')->plural($lines).', landing cost '.number_format((float) $this->total_landing_cost, 2).' '.$this->currency;
    }

    public function approvalReference(): string
    {
        return $this->folder_number;
    }

    public function approvalModuleKey(): string
    {
        return 'procurement';
    }

    public function auditModule(): string
    {
        return 'procurement';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->folder_number;
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

        $this->update([
            'status' => 'approved',
            'approved_by' => $actor?->id,
            'approved_at' => now(),
        ]);
    }

    public function onApprovalRejected(Approval $approval): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function onApprovalReturned(Approval $approval): void
    {
        $this->update(['status' => 'cost_analysis']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'cost_analysis' => 'Cost Analysis',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'po_generated' => 'PO Generated',
            'in_transit' => 'In Transit',
            'received' => 'Received',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'cost_analysis'], true) && ! $this->hasOpenApproval();
    }

    public function canSubmit(): bool
    {
        return $this->status === 'cost_analysis'
            && $this->items()->exists()
            && ! $this->hasOpenApproval();
    }

    public function canGeneratePo(): bool
    {
        return $this->status === 'approved' && ! $this->purchaseOrders()->exists();
    }
}
