<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanTask extends Model
{
    protected $table = 'kanban_tasks';

    protected $fillable = ['title', 'description', 'kanban_column_id', 'position'];
    protected $dates = ['created_at', 'updated_at'];

    /**
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(KanbanColumn::class, 'kanban_column_id');
    }

    /**
     */
    public function board()
    {
        return $this->hasOneThrough(KanbanBoard::class, KanbanColumn::class, 'id', 'id', 'kanban_column_id', 'kanban_board_id');
    }

    /**
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
