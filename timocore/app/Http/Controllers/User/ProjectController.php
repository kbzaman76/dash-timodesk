<?php

namespace App\Http\Controllers\User;

use App\Enums\SystemEventType;
use App\Http\Controllers\Controller;
use App\Lib\Socket;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function list()
    {
        $pageTitle = __('Projects');

        $authUser = auth()->user();
        $userId = $authUser->id;

        $query = Project::query()
            ->with('users')
            ->where('organization_id', organizationId())
            ->when($authUser->isStaff(), function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereHas('users', function ($q2) use ($userId) {
                        $q2->where('users.id', $userId);
                    })
                        ->orWhereHas('tasks', function ($q2) use ($userId) {
                            $q2->where('tasks.user_id', $userId);
                        })
                        ->orWhereHas('tracks', function ($q2) use ($userId) {
                            $q2->where('tracks.user_id', $userId);
                        });
                });
            });

        if ($authUser->isStaff()) {
            $query
                ->withSum([
                    'tracks as tracks_sum_time_in_seconds' => function ($q) use ($userId) {
                        $q->where('tracks.user_id', $userId);
                    },
                ], 'time_in_seconds')
                ->withCount([
                    'tasks as tasks_count' => function ($q) use ($userId) {
                        $q->where('tasks.user_id', $userId);
                    },
                ])
                ->withMax([
                    'tracks as latest_track_at' => function ($q) use ($userId) {
                        $q->where('tracks.user_id', $userId);
                    },
                ], 'ended_at');
        } else {
            $query->withSum('tracks', 'time_in_seconds')
                ->withCount('tasks')
                ->withMax('tracks as latest_track_at', 'ended_at');
        }

        $projects = $query
            ->orderByDesc('latest_track_at')
            ->searchable(['title', 'users:fullname'])
            ->paginate(getPaginate());

        $users = User::where('organization_id', organizationId())
            ->orderBy('fullname')
            ->get();

        return view('Template::user.project.list', compact('pageTitle', 'projects', 'users'));
    }

    public function details($uid)
    {
        $authUser = auth()->user();
        $userId = $authUser->id;
        $project = Project::withCount(['users', 'tasks', 'tracks'])
            ->where('organization_id', organizationId())
            ->when($authUser->isStaff(), function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereHas('users', function ($q2) use ($userId) {
                        $q2->where('users.id', $userId);
                    })
                        ->orWhereHas('tasks', function ($q2) use ($userId) {
                            $q2->where('tasks.user_id', $userId);
                        })
                        ->orWhereHas('tracks', function ($q2) use ($userId) {
                            $q2->where('tracks.user_id', $userId);
                        });
                });
            })
            ->where('uid', $uid)
            ->withSum('tracks', 'overall')
            ->withSum('tracks', 'mouse_counts')
            ->withSum('tracks', 'keyboard_counts')
            ->withSum('tracks', 'time_in_seconds')
            ->with('users')
            ->firstOrFail();

        $pageTitle = __($project->title);
        $users = User::where('organization_id', organizationId())->orderBy('fullname')->get();
        $projectId = $project->id;

        $tasks = Task::where('project_id', $project->id)
            ->mine()
            ->with('users')
            ->withSum('tracks', 'time_in_seconds')
            ->orderBy('id', 'desc')
            ->get();

        $projectMembers = null;
        if (!$authUser->isStaff()) {
            $projectMembersQuery = $project->tracks()
                ->select('user_id')
                ->selectRaw('SUM(time_in_seconds) AS total_seconds')
                ->selectRaw('AVG(activity_percentage) AS avg_activity')
                ->with('user:id,uid,fullname,image');

            $projectMembers = $projectMembersQuery
                ->groupBy('user_id')
                ->orderByDesc('total_seconds')
                ->get()
                ->mapWithKeys(function ($track) use ($project) {
                    if (!$track->user) {
                        return [];
                    }

                    return [
                        $track->user_id => (object) [
                            'user' => $track->user,
                            'total_seconds' => (int) $track->total_seconds,
                            'avg_activity' => (float) ($track->avg_activity ?? 0),
                            'is_assigned' => $project->users->contains('id', $track->user_id),
                        ],
                    ];
                });

            $project->loadMissing('users:id,uid,fullname,image');

            foreach ($project->users as $assignedUser) {
                if ($authUser->isStaff() && $assignedUser->id !== $authUser->id) {
                    continue;
                }

                if (!isset($projectMembers[$assignedUser->id])) {
                    $projectMembers[$assignedUser->id] = (object) [
                        'user' => $assignedUser,
                        'total_seconds' => 0,
                        'avg_activity' => 0,
                        'is_assigned' => true,
                    ];
                } else {
                    $projectMembers[$assignedUser->id]->is_assigned = true;
                }
            }

            $projectMembers = $projectMembers
                ->sortByDesc('total_seconds')
                ->values();
        }

        $projectTasks = Task::where('project_id', $project->id)
            ->when($authUser->isStaff(), function ($q) use ($userId) {
                $q->whereHas('users', function ($uq) use ($userId) {
                    $uq->where('users.id', $userId);
                });
            })
            ->with('users')
            ->withSum(['tracks as total_seconds' => fn($q) => $q->where('project_id', $projectId)], 'time_in_seconds')
            ->orderBy('total_seconds', 'desc')
            ->get();

        $widget = [
            'worked_today' => $project->tracks()->mine()->whereBetweenOrg('started_at', now()->startOfDay(), now()->endOfDay())->sum('time_in_seconds'),
            'total_worked_time' => $project->tracks()->mine()->sum('time_in_seconds'),
            'activity_percentage' => getActivity($project->tracks()->mine()),
            'total_tasks' => $project->tasks()->mine()->count(),
        ];

        if (request()->ajax() && request()->view == 'members') {
            return view('Template::user.project.members', compact('projectMembers', 'project'))->render();
        }

        if (request()->ajax() && request()->view == 'tasks') {
            return view('Template::user.project.tasks', compact('projectTasks', 'project'))->render();
        }

        return view('Template::user.project.details', compact('pageTitle', 'projectMembers', 'projectTasks', 'users', 'project', 'tasks', 'widget'));
    }

    public function save(Request $request, $id = null)
    {
        $rules = [
            'icon' => ['nullable', 'image', new FileTypeValidate(['png', 'jpeg', 'jpg'])],
            'title' => ['required'],
            'user_ids' => ['nullable', 'array'],
            'description' => ['nullable', 'max:255'],
            'user_ids.*' => ['integer', 'exists:users,uid'],
        ];

        if (!$id) {
            $rules['title'][] = Rule::unique('projects', 'title')->where('organization_id', organizationId());
        } else {
            $rules['title'][] = Rule::unique('projects', 'title')->where('organization_id', organizationId())->ignore($id, 'uid');
        }

        $validated = $request->validate($rules);

        $orgUsers = User::active()->where('organization_id', organizationId())->pluck('uid')->all();

        if (!empty(array_diff($request->user_ids ?? [], $orgUsers))) {
            $notify[] = ['error', 'Invalid user to assign'];
            return back()->withNotify($notify);
        }

        return DB::transaction(function () use ($validated, $id, $request) {
            $project = $id ? Project::where('organization_id', organizationId())->where('uid', $id)->firstOrFail() : new Project();

            $project->organization_id = organizationId();
            $project->title = $validated['title'];
            $project->description = $request->input('description');
            $oldStorageId = $oldPath = null;

            if ($request->hasFile('icon')) {
                try {
                    $webpFile = toWebpFile($request->file('icon'), getFileSize('project'));

                    $organization = myOrganization();
                    $location = $organization->uid . '/project';

                    [$fileName, $storageId] = uploadPermanentImage($webpFile, $location);

                    if ($fileName || $storageId) {
                        [$oldStorageId, $oldPath] = getImageInfo($project->icon);

                        $image = $storageId . '|' . $fileName;
                        $project->icon = $image;
                    }

                } catch (\Exception $exp) {

                }
            }

            if (!$project->color) {
                $project->color = [
                    'bg' => getSweetColors()['bg'],
                    'text' => getSweetColors()['text'],
                ];
            }
            if (!$id) {
                $project->uid = getUid(30, 'Project', type: 'string');
            }
            $project->save();

            // delete old file
            if ($oldPath && $oldStorageId) {
                deleteStorageFile($oldPath, $oldStorageId);
            }

            $userIds = array_values(array_filter($validated['user_ids'] ?? [], fn($v) => !empty($v)));
            $userIds = User::where('organization_id', organizationId())->whereIn('uid', $userIds)->pluck('id')->all();
            $project->users()->sync($userIds);

            foreach ($userIds as $uId) {
                Socket::emit(
                    "user:" . $uId,
                    SystemEventType::PROJECT_ASSIGNED,
                    [
                        'title' => 'Project Assigned',
                        'body' => 'You have been assigned to project: ' . $project->title,
                        'project' => $project,
                        'projectId' => $project->id,
                    ]
                );
            }

            $notify[] = ['success', 'Project ' . ($id ? 'updated' : 'created') . ' successfully'];
            return goBack($notify);
        });
    }

    public function removeUser($projectId, $uid)
    {
        $project = Project::where('organization_id', organizationId())->where('uid', $projectId)->firstOrFail();
        $user = User::where('organization_id', organizationId())->where('uid', $uid)->firstOrFail();
        $project->users()->detach($user->id);

        $taskIds = Task::where('project_id', $project->id)->pluck('id');

        if ($taskIds->isNotEmpty()) {
            DB::table('task_user')
                ->whereIn('task_id', $taskIds)
                ->where('user_id', $user->id)
                ->delete();
        }

        Socket::emit(
            "user:" . $user->id,
            SystemEventType::PROJECT_UNASSIGNED,
            [
                'title' => 'Project Unassigned',
                'body' => 'You have been removed from project: ' . $project->title,
            ]
        );

        $notify[] = ['success', 'Member removed successfully'];
        return goBack($notify);
    }

    public function assignMember(Request $request, $projectId)
    {
        $request->validate([
            'member_ids.*' => ['integer'],
            'member_ids' => ['required', 'array'],
        ]);

        $orgUsers = User::active()->where('organization_id', organizationId())->pluck('uid')->all();

        if (!empty(array_diff($request->member_ids ?? [], $orgUsers))) {
            $notify[] = ['error', 'Invalid user to assign'];
            return back()->withNotify($notify);
        }

        $project = Project::where('organization_id', organizationId())->where('uid', $projectId)->firstOrFail();

        $userIds = User::where('organization_id', organizationId())->whereIn('uid', $request->member_ids)->pluck('id')->all();

        $project->users()->syncWithoutDetaching($userIds);

        foreach ($userIds as $uId) {
            Socket::emit(
                "user:" . $uId,
                SystemEventType::PROJECT_ASSIGNED,
                [
                    'title' => 'New Project Assigned',
                    'body' => 'You have been assigned to project: ' . $project->title,
                    'projectId' => $project->id,
                    'project' => $project
                ]
            );
        }

        $notify[] = ['success', 'New member assigned successfully'];
        return back()->withNotify($notify);
    }

    public function saveTask(Request $request, $projectId, $id = null)
    {
        $request->validate([
            'task_title'      => 'required|max:255',
            'task_user_ids'   => ['nullable', 'array'],
            'task_user_ids.*' => ['integer'],
        ]);

        $project = Project::where('organization_id', organizationId())
            ->where('uid', $projectId)
            ->firstOrFail();

        $assignedMembers = $project->users->pluck('uid')->all();
        $userUids = User::where('organization_id', organizationId())
            ->whereIn('uid', $request->task_user_ids ?? [])
            ->pluck('uid')
            ->all();

        if (!empty(array_diff($userUids, $assignedMembers))) {
            $notify[] = ['error', 'Invalid user to assign'];
            return goBack($notify);
        }

        if ($id) {
            $task = Task::where('organization_id', organizationId())
                ->where('project_id', $project->id)
                ->findOrFail($id);

            $task->title = $request->task_title;
            $task->save();

            $message = 'Task updated successfully';
        } else {
            $task                  = new Task();
            $task->project_id      = $project->id;
            $task->organization_id = organizationId();
            $task->title           = $request->task_title;
            $task->save();

            $message = 'Task created successfully';
        }

        $alreadyAssignedToTask = $task->users()->pluck('users.id')->all();
        $userIds = User::where('organization_id', organizationId())
            ->whereIn('uid', $userUids)
            ->pluck('id')
            ->all();

        $task->users()->sync($userIds);

        $addedUsers = array_diff($userIds, $alreadyAssignedToTask);
        foreach ($addedUsers as $uId) {
            Socket::emit(
                "user:" . $uId,
                SystemEventType::TASK_ASSIGNED,
                [
                    'title' => 'Task Assigned',
                    'body' => 'You have been assigned to task: ' . $task->title,
                    'taskId' => $task->id,
                    'taskName' => $task->title
                ]
            );
        }

        $removedUsers = array_diff($alreadyAssignedToTask, $userIds);
        foreach ($removedUsers as $uId) {
            Socket::emit(
                "user:" . $uId,
                SystemEventType::TASK_UNASSIGNED,
                [
                    'title' => 'Task Unassigned',
                    'body' => 'You have been unassigned from task: ' . $task->title,
                    'taskId' => $task->id,
                    'taskName' => $task->title
                ]
            );
        }

        $notify[] = ['success', $message];
        return goBack($notify);
    }

}
