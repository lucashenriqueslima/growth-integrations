<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ThirdPartyCar extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParty::class);
    }

    public function expertiseFiles(): MorphToMany
    {
        return $this->morphToMany(ExpertiseFile::class, 'fileable');
    }

    public function expetiseFormInputs(): MorphToMany
    {
        return $this->morphToMany(ExpertiseFormInput::class, 'inputable');
    }
}
