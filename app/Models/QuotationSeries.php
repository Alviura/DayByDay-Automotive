<?php

namespace App\Models;

use App\Contracts\ApprovableDocument;
use App\Enums\ApprovalActionType;
use App\Enums\PurchaseType;
use App\Models\Concerns\Approvable;
use App\Models\Concerns\Auditable;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationSeries extends Model implements ApprovableDocument
{
    use Approvable, Auditable, SoftDeletes;

    protected $table = 'quotation_series';

    protected $fillable = [
        'series_number',
        'title',
        'description',
        'supplier_id',
        'currency',
        'exchange_rate',
        'purchase_type',
        'import_type',
        'cbm_rate',
        'status',
        'notes',
        'total_cost',
        'total_freight',
        'total_tax',
        'total_landing_cost',
        'total_purchase_price',
        'total_cbm',
        'total_transport_cost',
        'total_actual_cost',
        'total_expected_sales',
        'total_expected_margin',
        'created_by',
        'approved_by',
        'approved_at',
        'closed_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'cbm_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_freight' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_landing_cost' => 'decimal:2',
        'total_purchase_price' => 'decimal:2',
        'total_cbm' => 'decimal:4',
        'total_transport_cost' => 'decimal:2',
        'total_actual_cost' => 'decimal:2',
        'total_expected_sales' => 'decimal:2',
        'total_expected_margin' => 'decimal:2',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return static::nextNumber('PF-', 'series_number');
    }

    public static function generateTitle(Supplier $supplier, ?string $description = null): string
    {
        $date = now()->format('d').strtoupper(now()->format('M')).now()->format('Y');
        $title = "{$date} - {$supplier->name}";

        if ($description) {
            $title .= ' '.$description;
        }

        return $title;
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
        return $this->hasMany(QuotationItem::class);
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

    public function displayName(): string
    {
        return $this->title ?: $this->series_number;
    }

    public function purchaseTypeEnum(): PurchaseType
    {
        return PurchaseType::tryFrom($this->purchase_type ?? 'local') ?? PurchaseType::Local;
    }

    public function isLocal(): bool
    {
        return $this->purchaseTypeEnum()->isLocal();
    }

    public function isImport(): bool
    {
        return $this->purchaseTypeEnum()->isImport();
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isCalculated(): bool
    {
        return (float) $this->total_actual_cost > 0 && $this->items()->whereNotNull('unit_cost_arrival')->exists();
    }

    public function approvalTitle(): string
    {
        return 'Quotation Series '.$this->displayName();
    }

    public function approvalSummary(): string
    {
        $supplier = $this->supplier?->name ?? 'No supplier';
        $lines = $this->items()->count();

        return "{$supplier} — {$lines} line ".str('item')->plural($lines).', actual cost '.number_format((float) $this->total_actual_cost, 2).' KES';
    }

    public function approvalReference(): string
    {
        return $this->series_number;
    }

    public function approvalModuleKey(): string
    {
        return 'quotation-series';
    }

    public function auditModule(): string
    {
        return 'quotation-series';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->series_number;
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
        $this->update(['status' => 'order_draft']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'quotation_draft' => 'Quotation Draft',
            'order_draft' => 'Order Draft',
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

    public function canEditHeader(): bool
    {
        return in_array($this->status, ['quotation_draft', 'order_draft', 'draft', 'cost_analysis'], true);
    }

    /** @deprecated use canEditHeader */
    public function canEdit(): bool
    {
        return $this->canEditHeader();
    }

    public function canBulkAddItems(): bool
    {
        return $this->status === 'quotation_draft';
    }

    public function canProceedToOrder(): bool
    {
        return $this->status === 'quotation_draft' && $this->items()->exists();
    }

    public function canEditPrices(): bool
    {
        return $this->status === 'order_draft';
    }

    public function hasSavedPrices(): bool
    {
        if ($this->items->isEmpty()) {
            return false;
        }

        return $this->items->every(fn (QuotationItem $item) => $item->hasPrice());
    }

    public function canCalculate(): bool
    {
        return $this->status === 'order_draft'
            && $this->items()->exists()
            && $this->hasSavedPrices();
    }

    public function canConfirm(): bool
    {
        return $this->status === 'order_draft' && $this->isCalculated();
    }

    public function canGeneratePo(): bool
    {
        return $this->status === 'approved' && ! $this->purchaseOrders()->exists();
    }

    public function canMarkInTransit(): bool
    {
        return $this->status === 'po_generated' && $this->purchaseOrders()->exists();
    }

    public function canCloseSeries(): bool
    {
        return $this->status === 'received';
    }

    public function canExportQuotation(): bool
    {
        return $this->items()->exists();
    }
}
