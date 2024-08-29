<?php

namespace App\Models\Ileva;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IlevaAssociatePerson extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_asc_pessoa';

    protected $guarded = [];

    public function ilevaAssociate(): HasOne
    {
        return $this->hasOne(IlevaAssociate::class, 'id_pessoa');
    }

    public function ilevaBills(): HasMany 
    {
        return $this->hasMany(IlevaBill::class, 'id_pessoa');
    }
    
}
