<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Task;
use App\Models\UserTask;
use Illuminate\Http\Request;


class TaskController extends Controller
{
    public function tasks()
    {
        $pageTitle   = 'Tasks';
        $tasks     = Task::whereHas('lottery', function ($query) {
            $query->whereDate('draw_date', '>', now());
        })->paginate(getPaginate(10));
        return view('Template::user.task.index', compact('pageTitle', 'tasks'));
    }

    public function showTask(Task $task)
    {
        $pageTitle   = 'Task';
        $page        = Page::where('tempname', activeTemplate())->where('slug', 'tasks')->first();
        $submitted  = UserTask::where('user_id', auth()->id())
            ->where('task_id', $task->id)
            ->first();
        return view('Template::user.task.show', compact('pageTitle', 'task', 'submitted'));
    }

    public function submitTask(Request $request, Task $task)
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,png|max:4096',
        ]);

        if ($request->hasFile('proof')) {
            try {
                $path = fileUploader($request->file('proof'), getFilePath('taskProof'));
            } catch (\Exception $exp) {
                dd($exp);
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }
        // $path = $request->file('proof')->store('proofs', 'public');

        $request->user()->tasks()->attach($task->id, [
            'status' => 'pending',
            'proof' => $path,
        ]);

        $notify[] = ['success', 'Task has been sent for verification'];
        return back()->withNotify($notify);
    }

    public function submittedTasks()
    {
        $pageTitle   = 'Submitted Tasks';
        $userTasks = UserTask::where('user_id', auth()->id())
            ->where('status', '!=', 'pending')
            ->paginate(getPaginate(10));
        return view('Template::user.task.submitted', compact('pageTitle', 'userTasks'));
    }
}
