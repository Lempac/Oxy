<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    protected $table = 'kanban_columns';

    protected $fillable = ['name', 'kanban_board_id', 'position'];

    /**
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }

    /**
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(KanbanTask::class, 'kanban_column_id')->orderBy('position');
    }
}