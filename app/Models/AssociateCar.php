<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociateCar extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function associate(): BelongsTo
    {
        return $this->belongsTo(Associate::class);
    }
}
