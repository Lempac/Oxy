<?php

namespace App\Models;

use App\Events\Messages\MessageCreated;
use App\Events\Messages\MessageDeleted;
use App\Events\Messages\MessageEdited;
use Closure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

//    protected $dispatchesEvents = [
//        'created' => MessageCreated::class,
//        'updated' => MessageEdited::class,
//        'deleted' => MessageDeleted::class,
//    ];

    public function channel() : BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
