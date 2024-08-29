<?php

namespace App\Models\Ileva;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IlevaBill extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_finan_boleto';

    protected $guarded = [];
    
    public function ilevaPerson(): BelongsTo
    {
        return $this->belongsTo(IlevaAssociatePerson::class, 'id_pessoa');
    }
    
}
