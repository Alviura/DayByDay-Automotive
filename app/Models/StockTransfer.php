<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transfer_number',
        'transfer_request_id',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'status',
        'dispatched_by',
        'dispatched_at',
        'received_by',
        'received_at',
        'notes',
    ];

    protected $casts = [
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

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'dispatched' => 'Dispatched',
            'in_transit' => 'In Transit',
            'received' => 'Received',
            'closed' => 'Closed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function routeLabel(): string
    {
        $source = $this->source;
        $destination = $this->destination;

        $sourceLabel = $source ? (($source instanceof Warehouse ? 'WH' : 'Shop').': '.$source->name) : '?';
        $destLabel = $destination ? (($destination instanceof Warehouse ? 'WH' : 'Shop').': '.$destination->name) : '?';

        return "{$sourceLabel} → {$destLabel}";
    }

    public function canReceive(): bool
    {
        return in_array($this->status, ['dispatched', 'in_transit'], true);
    }
}
