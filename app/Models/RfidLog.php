<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidLog extends Model
{
    protected $fillable = ['rfid_card_id', 'uid', 'status', 'scanned_at'];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(RfidCard::class, 'rfid_card_id');
    }
}
