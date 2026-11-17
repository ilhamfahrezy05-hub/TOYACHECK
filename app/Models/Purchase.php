<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'kode_pembelian',
        'user_id',
        'vendor_id',
        'vendor_name',
        'sparepart_request_id',
        'tanggal_pembelian',
        'waktu_pembelian',
        'total_harga',
        'bukti_pembayaran',
        'catatan',
        'status_pembelian',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'total_harga'       => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function sparepartRequest(): BelongsTo
    {
        return $this->belongsTo(SparepartRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
