<?php

namespace App\Models;

use App\Enums\AuvoDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuvoCustomer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'auvo_department' => AuvoDepartment::class,
        ];
    }
}
