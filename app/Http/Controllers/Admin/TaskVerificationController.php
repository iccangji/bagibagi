<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskVerificationController extends Controller
{
    public function index()
    {
        $pageTitle = 'Tasks Submissions';
        $tasks = UserTask::with('task', 'user')
            ->where('status', 'pending')
            ->paginate(getPaginate(10));

        return view('admin.tasks.submissions.index', compact('tasks', 'pageTitle'));
    }

    /**
     * Verifikasi tugas user
     */
    public function verify(Request $request, UserTask $userTask)
    {
        $request->validate([
            'action' => 'required|in:verified,rejected',
        ]);

        try {
            if ($userTask->status !== 'pending') {
                return back()->with('error', 'Task already ' . $userTask->status);
            }

            $userTask->status = $request->action;
            $userTask->verified_at = now();
            $userTask->save();

            // Jika diverifikasi, tambahkan poin ke user dan buat transaksi
            if ($request->action === 'verified') {
                $user = $userTask->user;
                $rewardPoints = $userTask->task->reward_points;

                $user->balance += $rewardPoints;
                $user->save();

                DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'amount' => $rewardPoints,
                    'charge' => 0,
                    'post_balance' => $user->balance,
                    'trx_type' => '+',
                    'trx' => 'TASK-' . strtoupper(uniqid()),
                    'details' => 'Reward from task: ' . $userTask->task->title,
                    'remark' => 'task',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $notify[] = ['success', 'Task has been ' . $request->action];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
}
