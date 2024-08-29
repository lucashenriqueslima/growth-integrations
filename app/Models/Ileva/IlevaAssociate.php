<?php

namespace App\Models\Ileva;

use App\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

Class IlevaAssociate extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_asc_associado';
    protected $guarded = [];

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => FormatHelper::cpfOrCnpj($value) 
        );
    }

    public function ilevaVehicle(): HasOne
    {
        return $this->hasOne(IlevaAssociateVehicle::class, 'id_associado');
    }
    
    public function ilevaPerson(): BelongsTo
    {
        return $this->belongsTo(IlevaAssociatePerson::class, 'id_pessoa', 'id')->orderBy('nome');
    }
}
