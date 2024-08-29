<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

class BikerGeolocation extends Model
{
    use HasFactory, HasSpatial;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'location' => Point::class,
        ];
    }

    public function biker()
    {
        return $this->belongsTo(Biker::class);
    }
}
