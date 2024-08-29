<?php

namespace App\Models\Ileva;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IlevaFipeModel extends Model
{
    use HasFactory;

    protected $connection = 'ileva';
    protected $table = 'hbrd_adm_fipe_modelos';
    protected $guarded = [];
}
