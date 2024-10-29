<?php

namespace App\Http\Controllers;

use App\Models\KanbanBoard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KanbanBoardController extends Controller
{
    public function index()
    {
        $boards = KanbanBoard::with('columns.tasks')->get();
        return Inertia::render('Kanban/Index', [
            'boards' => $boards,
        ]);
    }

    public function create()
    {
        return Inertia::render('Kanban/CreateBoard');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            //'bio' => 'nullable|string',
        ]);

        $board = KanbanBoard::create([
            'name' => $request->input('name'),
            //'bio' => $request->input('bio'),
        ]);

        $defaultColumns = ['To do', 'Doing', 'Done'];
        foreach ($defaultColumns as $position => $columnName) {
            $board->columns()->create([
                'name' => $columnName,
                'position' => $position,
            ]);
        }

        return redirect()->route('kanban.index')->with('success', 'Board created successfully.');
    }

    public function edit(KanbanBoard $board)
    {
        return Inertia::render('Kanban/EditBoard', [
            'board' => $board,
        ]);
    }

    public function update(Request $request, KanbanBoard $board)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            //'bio' => 'nullable|string',
        ]);

        $board->update([
            'name' => $request->input('name'),
            //'bio' => $request->input('bio'),
        ]);

        return redirect()->route('kanban.index')->with('Board updated successfully.');
    }

    public function destroy(KanbanBoard $board)
    {
        $board->delete();

        return redirect()->route('kanban.index')->with('Board deleted successfully.');
    }

    public function show(KanbanBoard $board)
    {
        $board->load('columns.tasks');
        return Inertia::render('Kanban/BoardDetail', [
            'board' => $board,
        ]);
    }
}
