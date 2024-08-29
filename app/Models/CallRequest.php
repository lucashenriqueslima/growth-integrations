<?php

namespace App\Models;

use App\Enums\CallRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallRequest extends Model
{
    use HasFactory;


    protected function casts(): array
    {
        return [
            'status' => CallRequestStatus::class,
        ];
    }
    protected $guarded = [];

    public function biker(): BelongsTo
    {
        return $this->belongsTo(Biker::class);
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }
}
