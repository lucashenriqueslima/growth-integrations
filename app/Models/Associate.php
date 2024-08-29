<?php

namespace App\Models;

use App\Helpers\FormatHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Associate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper($value)
        );
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => FormatHelper::cpfOrCnpj($value)
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => FormatHelper::phone($value)
        );
    }

    public function car(): HasMany
    {
        return $this->hasMany(AssociateCar::class);
    }
}
