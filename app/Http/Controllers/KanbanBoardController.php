<?php

namespace App\Http\Controllers;

use App\Models\KanbanBoard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KanbanBoardController extends Controller
{
    public function index()
    {
        $boards = KanbanBoard::all();
        return Inertia::render('Kanban/Index', ['boards' => $boards]);
    }

    public function create()
    {
        return Inertia::render('Kanban/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        KanbanBoard::create(['name' => $request->name]);

        return redirect()->route('kanban_boards.index');
    }

    public function show(KanbanBoard $kanbanBoard)
    {
        $kanbanBoard->load('columns.tasks');
        return Inertia::render('Kanban/Show', ['board' => $kanbanBoard]);
    }

    public function edit(KanbanBoard $kanbanBoard)
    {
        return Inertia::render('Kanban/Edit', ['board' => $kanbanBoard]);
    }

    public function update(Request $request, KanbanBoard $kanbanBoard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $kanbanBoard->update(['name' => $request->name]);

        return redirect()->route('kanban_boards.show', $kanbanBoard);
    }

    public function destroy(KanbanBoard $kanbanBoard)
    {
        $kanbanBoard->delete();
        return redirect()->route('kanban_boards.index');
    }
}
