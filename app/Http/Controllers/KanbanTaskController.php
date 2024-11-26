<?php

namespace App\Http\Controllers;

use App\Models\KanbanColumn;
use App\Models\KanbanTask;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KanbanTaskController extends Controller
{
    public function index(KanbanColumn $kanbanColumn)
    {
        $tasks = $kanbanColumn->tasks;

        return Inertia::render('Kanban/Tasks/Index', ['tasks' => $tasks, 'column' => $kanbanColumn]);
    }

    public function create(KanbanColumn $kanbanColumn)
    {
        return Inertia::render('Kanban/Tasks/Create', ['column' => $kanbanColumn]);
    }

    public function store(Request $request, KanbanColumn $kanbanColumn)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kanbanColumn->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('kanban_columns.show', $kanbanColumn);
    }

    public function show(KanbanTask $kanbanTask)
    {
        return Inertia::render('Kanban/Tasks/Show', ['task' => $kanbanTask]);
    }

    public function edit(KanbanTask $kanbanTask)
    {
        return Inertia::render('Kanban/Tasks/Edit', ['task' => $kanbanTask]);
    }

    public function update(Request $request, KanbanTask $kanbanTask)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kanbanTask->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('kanban_columns.tasks.show', $kanbanTask);
    }

    public function destroy(KanbanTask $kanbanTask)
    {
        $kanbanTask->delete();

        return redirect()->route('kanban_columns.show', $kanbanTask->kanban_column_id);
    }
}
