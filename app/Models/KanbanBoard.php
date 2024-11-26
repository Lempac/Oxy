<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    protected $table = 'kanban_boards';

    protected $fillable = ['name', 'bio'];

    public function columns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class, 'kanban_board_id');
    }
}
