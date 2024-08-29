<?php

namespace App\Models\Locavibe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Eloquent\Model;

class LocavibeRenter extends Model
{
    use HasFactory, Notifiable;

    protected $connection = 'locavibe';
    protected $collection = 'renters';
    protected $guarded = [];
    
}
