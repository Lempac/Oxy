<?php

namespace App\Http\Controllers;

use App\Models\KanbanBoard;
use App\Models\KanbanColumn;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KanbanColumnController extends Controller
{
    public function index(KanbanBoard $kanbanBoard)
    {
        $columns = $kanbanBoard->columns;
        return Inertia::render('Kanban/Columns/Index', ['columns' => $columns, 'board' => $kanbanBoard]);
    }

    public function create(KanbanBoard $kanbanBoard)
    {
        return Inertia::render('Kanban/Columns/Create', ['board' => $kanbanBoard]);
    }

    public function store(Request $request, KanbanBoard $kanbanBoard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $kanbanBoard->columns()->create(['name' => $request->name]);

        return redirect()->route('kanban_boards.show', $kanbanBoard);
    }

    public function show(KanbanColumn $kanbanColumn)
    {
        $kanbanColumn->load('tasks');
        return Inertia::render('Kanban/Columns/Show', ['column' => $kanbanColumn]);
    }

    public function edit(KanbanColumn $kanbanColumn)
    {
        return Inertia::render('Kanban/Columns/Edit', ['column' => $kanbanColumn]);
    }

    public function update(Request $request, KanbanColumn $kanbanColumn)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $kanbanColumn->update(['name' => $request->name]);

        return redirect()->route('kanban_boards.columns.show', $kanbanColumn);
    }

    public function destroy(KanbanColumn $kanbanColumn)
    {
        $kanbanColumn->delete();
        return redirect()->route('kanban_boards.show', $kanbanColumn->kanban_board_id);
    }
}
