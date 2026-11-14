<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'inspection_item_id',
        'item',
        'criteria',
        'kondisi',
        'foto_dokumentasi',
    ];

    protected $casts = [
        'kondisi' => 'boolean',
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function inspectionItem()
    {
        return $this->belongsTo(InspectionItem::class);
    }
}