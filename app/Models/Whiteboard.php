<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Whiteboard extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'state'];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
