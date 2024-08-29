<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ThirdParty extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function car(): HasOne
    {
        return $this->hasOne(ThirdPartyCar::class);
    }
}
