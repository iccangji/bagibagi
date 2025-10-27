<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::latest()->paginate(10);
        $pageTitle = 'Tasks';
        return view('admin.tasks.index', compact('pageTitle', 'tasks'));
    }

    public function create()
    {
        $pageTitle = 'Insert Tasks';
        return view('admin.tasks.add', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'reward_points' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        Task::create($request->all());
        $notify[] = ['success', 'Task has been created'];
        return redirect()->route('admin.tasks.index')->withNotify($notify);
    }

    public function edit(Task $task)
    {
        $pageTitle = 'Update Tasks';
        return view('admin.tasks.add', compact('task', 'pageTitle'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'reward_points' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $task->update($request->all());

        $notify[] = ['success', 'Tasks has been updated'];
        return back()->withNotify($notify);
    }

    public function destroy(Task $task)
    {
        try {
            $task->delete();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        $notify[] = ['success', 'Task has been deleted'];
        return back()->withNotify($notify);
    }
}
