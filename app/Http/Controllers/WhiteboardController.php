<?php

namespace App\Http\Controllers;

use App\Models\Whiteboard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WhiteboardController extends Controller
{
    public function index()
    {
        $whiteboards = Whiteboard::all();

        return Inertia::render('Whiteboard/Index', [
            'whiteboards' => $whiteboards,
        ]);
    }

    public function create()
    {
        return Inertia::render('Whiteboard/CreateBoard');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Whiteboard::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('whiteboard.index')->with('success', 'Whiteboard created successfully.');
    }

    public function edit(Whiteboard $whiteboard)
    {
        return Inertia::render('Whiteboard/EditBoard', [
            'whiteboard' => $whiteboard,
        ]);
    }

    public function update(Request $request, Whiteboard $whiteboard)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $whiteboard->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('whiteboard.index')->with('success', 'Whiteboard updated successfully.');
    }

    public function destroy(Whiteboard $whiteboard)
    {
        $whiteboard->delete();

        return redirect()->route('whiteboard.index')->with('success', 'Whiteboard deleted successfully.');
    }

    public function show(Whiteboard $whiteboard)
    {
        return Inertia::render('Whiteboard/WhiteboardBoard', [
            'whiteboard' => $whiteboard,
        ]);
    }

    public function saveState(Request $request, Whiteboard $whiteboard)
    {
        $request->validate([
            'state' => 'required|string',
        ]);

        $whiteboard->update([
            'state' => $request->input('state'),
        ]);

        return response()->json(['success' => true]);
    }
}
