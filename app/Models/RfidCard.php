<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidCard extends Model
{
    protected $fillable = ['uid', 'nama', 'status'];

    public function logs(): HasMany
    {
        return $this->hasMany(RfidLog::class);
    }
}
