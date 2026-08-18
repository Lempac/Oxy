<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'message_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
    ];

    protected $appends = [
        'url',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getUrlAttribute(): string
    {
        if (empty($this->path) || $this->path === '0') {
            return '';
        }

        return Storage::url($this->path);
    }

    protected static function booted(): void
    {
        static::deleted(function (MessageAttachment $attachment) {
            if ($attachment->path && Storage::exists($attachment->path)) {
                Storage::delete($attachment->path);
            }
        });
    }
}
