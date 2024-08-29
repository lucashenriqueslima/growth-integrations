<?php

namespace App\Models\Ileva;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IlevaAssociateModelBrand extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_asc_veiculo_marca';
    protected $guarded = [];

    public function ilevaModel(): BelongsTo
    {
        return $this->belongsTo(IlevaAssociateVehicleModel::class, 'id_marca');
    }
}
