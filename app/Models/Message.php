<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use phpDocumentor\Reflection\Types\Nullable;

class Message extends Model
{
    use HasFactory;

    /**
     * @var ?Closure
     * @returns User
     */
    public ?Closure $sender;
    protected $fillable = [
        'type',
        'mdata',
        'user_id',
        'channel_id',
    ];

    public function channel() : BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
