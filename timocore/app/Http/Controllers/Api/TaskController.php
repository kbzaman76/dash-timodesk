<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Task;

class TaskController extends Controller
{
    public function myTasks() {
        $user = auth()->user();

        $tasks = $user->tasks()
            ->with('project:id,title')
            ->withMax([
                'tracks as last_started_at' => fn ($q) => $q->where('user_id', $user->id),
            ], 'started_at')
            ->withSum([
                'tracks as total_work_time' => fn ($q) => $q->where('user_id', $user->id),
            ], 'time_in_seconds')
            ->orderByDesc('last_started_at')
            ->get();

        return responseSuccess('my_tasks', [], [
            'tasks' => $tasks,
        ]);
    }
    
    public function saveTask(Request $request, $id = null)
    {
        $request->validate([
            'title' => 'required',
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        $user = auth()->user();
        $projectId = $request->project_id;

        $isAssigned = DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isAssigned) {
            return responseError('validation_error', [['You are not assigned to this project']]);
        }

        $task                  = new Task();
        $task->title           = $request->title;
        $task->project_id      = $request->project_id;
        $task->user_id         = $user->id;
        $task->organization_id = $user->organization_id;
        $task->save();

        $task->users()->syncWithoutDetaching([$user->id]);

        return responseSuccess('task_created', [['Task created successfully']], ['task' => $task]);
    }
}
