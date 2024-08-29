<?php

namespace App\Models\Ileva;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IlevaAssociateVehicleModel extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_asc_veiculo_modelo';
    protected $guarded = [];
    
    public function ilevaBrand(): HasOne
    {
        return $this->hasOne(IlevaAssociateModelBrand::class, 'id', 'id_marca');
    }
    public function ilevaVehicle(): BelongsTo
    {
        return $this->belongsTo(IlevaAssociateVehicle::class, 'id_modelo');
    }
    
}
