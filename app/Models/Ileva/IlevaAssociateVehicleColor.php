<?php

namespace App\Models\Ileva;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IlevaAssociateVehicleColor extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_asc_veiculo_cor';
    protected $guarded = [];

    public function ilevaVehicle(): BelongsTo
    {
        return $this->belongsTo(IlevaAssociateVehicle::class, 'id', 'id_cor');
    }

}
