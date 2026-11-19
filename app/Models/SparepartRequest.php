<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SparepartRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'sparepart_id',
        'nama_sparepart',
        'qty',
        'priority_level',
        'expected_delivery_date',
        'vendor_name',
        'maintenance_id',
        'requested_by',
        'alasan',
        'status',
        'catatan_admin',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'qty'                    => 'integer',
        'expected_delivery_date' => 'date',
        'reviewed_at'            => 'datetime',
    ];

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'sparepart_request_id');
    }

    public function latestPurchase(): HasOne
    {
        return $this->hasOne(Purchase::class, 'sparepart_request_id')->latestOfMany();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
