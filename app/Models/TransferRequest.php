<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRequest extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'request_number',
        'type',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'status',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'stock_transfer_id',
        'notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'TQR-'.date('Y').'-';
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

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function auditModule(): string
    {
        return 'transfer_request';
    }

    public function auditReferenceNumber(): ?string
    {
        return $this->request_number;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'warehouse_to_shop' => 'Request from Warehouse',
            'inter_shop' => 'Request from Shop',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Awaiting Review',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'fulfilled' => 'Fulfilled',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft' => 'tr-badge tr-badge-slate',
            'submitted' => 'tr-badge tr-badge-amber',
            'accepted' => 'tr-badge tr-badge-blue',
            'fulfilled' => 'tr-badge tr-badge-green',
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
        return $this->status === 'draft' && $this->items()->exists();
    }

    public function canReview(): bool
    {
        return $this->status === 'submitted';
    }
}
