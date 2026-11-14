<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'notify_kendala',
        'notify_permintaan',
        'per_page',
    ];

    protected $casts = [
        'notify_kendala'    => 'boolean',
        'notify_permintaan' => 'boolean',
        'per_page'          => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
