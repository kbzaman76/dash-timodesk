<?php

namespace App\Http\Controllers\Api;
use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\IdleTime;
use App\Models\Screenshot;
use App\Models\Task;
use App\Models\Track;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrackController extends Controller
{
    private function handleScreenshots($files, $request, $track = null)
    {
        $metas = $request->input('screenshots_meta', []);

        foreach ($files as $idx => $file) {
            $sizeInBytes = (int) ($file?->getSize() ?? 0);

            if ($sizeInBytes === 0) {
                try {
                    $sizeInBytes = filesize($file->getRealPath());
                } catch (\Throwable $e) {
                    $sizeInBytes = 0;
                }
            }

            try {
                [$storedName, $storageId, $uploadStatus] = screenshotUploader($file, true);
            } catch (\Throwable $e) {
                continue;
            }

            $meta = $metas[$idx] ?? [];
            $takenAt = $meta['taken_at'] ? Carbon::parse($meta['taken_at']) : null;

            $user = auth()->user();

            $screenshot = new Screenshot();
            $screenshot->project_id = $request->project_id ?? $track?->project?->id ?? null;
            $screenshot->task_id = $request->task_id ?? $track?->task?->id ?? null;
            $screenshot->taken_at = $takenAt;
            $screenshot->user_id = $user->id;
            $screenshot->organization_id = $user->organization_id;
            $screenshot->src = $storedName;
            $screenshot->file_storage_id = $storageId;
            $screenshot->size_in_bytes = $sizeInBytes;
            $screenshot->uploaded = $uploadStatus;
            $screenshot->save();
        }
    }

    public function uploadScreenshot(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'project_id' => 'required',
            'screenshots' => ['nullable', 'array'],
            'screenshots.*' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'webp', 'png'])],
            'screenshots_meta' => ['nullable', 'array'],
            'screenshots_meta.*.taken_at' => ['nullable', 'date']
        ]);

        $this->handleScreenshots($request->file('screenshots', []), $request);

        return responseSuccess('screenshot_uploaded', ['Screenshot uploaded successfully']);
    }

    public function storeTrack(Request $request)
    {
        $data = $this->validateStoreTrack($request);

        // we will let to know the client time has been inserted
        // but ignoring to db insertion
        if (
            $data['time_in_seconds'] <= 0 ||
            // auth()->user()->tracking_status == Status::NO ||
            (!@$data['project_id'] && !@$data['task_id']) // silently ignore it
        ) {
            $notify[] = ['success', 'Data inserted'];
            return responseSuccess(Track::STORED_TRACK, $notify, [
                'todaysTime' => auth()->user()->tracks()->whereDateOrg('started_at')->sum('time_in_seconds')
            ]);
        }

        $userId = auth()->id();

        $appsPayload = isset($data['apps']) ? json_decode($data['apps'], true) : null;
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($appsPayload))
            $appsPayload = null;

        $startedAt = $data['started_at'] ? Carbon::parse($data['started_at']) : null;
        $endedAt = $data['ended_at'] ? Carbon::parse($data['ended_at']) : null;

        $seconds = $startedAt && $startedAt
            ? $startedAt->diffInSeconds($endedAt)
            : 0;

        $timeInSeconds = $seconds;

        if ($request->subtract_seconds && is_numeric($request->subtract_seconds)) {
            $timeInSeconds = max(0, $timeInSeconds - (int) $request->subtract_seconds);
        }

        $mouseCounts = min($data['mouse_counts'] ?? 0, 300);
        $keyboardCounts = min($data['keyboard_counts'] ?? 0, 300);
            $overallToStore  = $data['overall'] ?? ($keyboardCounts + $mouseCounts - (min($keyboardCounts, $mouseCounts)));
        $activityPct = $timeInSeconds > 0 ? (int) round(($overallToStore / $timeInSeconds) * 100) : 0;

        $files = $request->file('screenshots', []);
        $metas = $request->input('screenshots_meta', []);


        try {
            $track = DB::transaction(function () use ($data, $userId, $startedAt, $endedAt, $timeInSeconds, $overallToStore, $mouseCounts, $keyboardCounts, $activityPct, $appsPayload, $request) {
                $track = new Track();
                $track->organization_id = auth()->user()->organization_id;
                $track->user_id = $userId;
                $track->project_id = $data['project_id'];
                $track->task_id = isset($data['task_id']) ? (int) $data['task_id'] : 0;
                $track->started_at = $startedAt;
                $track->ended_at = $endedAt;
                $track->time_in_seconds = $timeInSeconds;
                $track->overall = $overallToStore;
                $track->activity_percentage = $activityPct;
                $track->overall_activity = $activityPct * $timeInSeconds;
                $track->keyboard_counts = $keyboardCounts;
                $track->mouse_counts = $mouseCounts;
                $track->save();

                $this->insertAppData($track, $appsPayload, (int) $request->subtract_seconds);

                Screenshot::where('user_id', $track->user_id)
                    ->where('task_id', $track->task_id)
                    ->whereBetween('taken_at', [$track->started_at, $track->ended_at])
                    ->update(['track_id' => $track->id]);

                if (isset($data['task_id'])) {
                    Task::where('id', $data['task_id'])
                        ->update([
                            'spent_time' => DB::raw("COALESCE(spent_time, 0) + $timeInSeconds")
                        ]);

                }

                return $track;
            });
        } catch (\Throwable $e) {
            logger()->error($e);
        }

        foreach ($files as $idx => $file) {
            if (
                !$this->validToUploadSS(
                    $data['idle_times'] ?? [],
                    isset($metas[$idx]['taken_at']) ? Carbon::parse($metas[$idx]['taken_at']) : null
                )
            ) {
                continue;
            }

            $sizeInBytes = (int) ($file?->getSize() ?? 0);
            if ($sizeInBytes === 0) {
                try {
                    $sizeInBytes = filesize($file->getRealPath());
                } catch (\Throwable $e) {
                    $sizeInBytes = 0;
                }
            }

            try {
                [$storedName, $storageId, $uploadStatus] = screenshotUploader($file, true);
            } catch (\Throwable $e) {
                continue;
            }

            $meta = $metas[$idx] ?? [];
            $takenAt = $meta['taken_at'] ? Carbon::parse($meta['taken_at']) : null;
            $shotProj = $meta['project_id'] ?? $track->project_id;
            $shotTask = $meta['task_id'] ?? $track->task_id;

            $track->screenshots()->create([
                'size_in_bytes' => $sizeInBytes,
                'user_id' => $userId,
                'organization_id' => auth()->user()->organization_id,
                'project_id' => $shotProj,
                'task_id' => $shotTask,
                'src' => $storedName,
                'taken_at' => $takenAt,
                'file_storage_id' => $storageId,
                'uploaded' => $uploadStatus,
            ]);
        }


        $notify[] = ['success', 'Track created successfully'];
        return responseSuccess(Track::STORED_TRACK, $notify, [
            'todaysTime' => auth()->user()->tracks()->whereDateOrg('started_at')->sum('time_in_seconds'),
            'track' => @$track
        ]);
    }


    public function updateTrack($id, Request $request)
    {
        $track = Track::find($id);
        if (!$track) {
            $notify[] = ['error', 'No track found'];
            return responseError(Track::NO_TRACK_FOUND, $notify);
        }

        $validator = Validator::make($request->all(), ['ended_at' => 'required']);
        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $track->ended_at = $request->ended_at;
        $track->save();

        $notify[] = ['success', 'Track updated'];
        return responseSuccess(Track::UPDATED_TRACK, $notify);
    }

    public function storeIdleTrack(Request $request)
    {
        $user = auth()->user();
        $idleTime = new IdleTime();
        $idleTime->project_id = $request->project_id ?? 0;
        $idleTime->user_id = $user->id;
        $idleTime->task_id = $request->task_id;
        $idleTime->resumed = $request->resumed;
        $idleTime->idle_started_at = $request->idle_started_at ? Carbon::parse($request->idle_started_at)->timezone(defaultTimeZone()) : null;
        $idleTime->ended_at = now();

        $start = Carbon::parse($request->idle_started_at)->timezone(defaultTimeZone());
        $end = Carbon::now()->timezone(defaultTimeZone());
        $idleTime->time_in_seconds = $start->diffInSeconds($end);

        $idleTime->save();

        return responseSuccess('idle_time_stored', [['Idle time stored']], $idleTime);
    }

    private function insertAppData($track, $appsPayload, $idleTime = 0)
    {
        $appsData = [];

        if ($appsPayload && is_iterable($appsPayload)) {
            $appsData = [];
            $now = now();

            $grouped = collect($appsPayload)
                ->groupBy(function ($item) {
                    return appGroupName($item['appName']);
                })
                ->map(function ($items, $groupName) {
                    return [
                        'app_name'      => $groupName,
                        'total_seconds' => $items->sum('seconds'),
                    ];
                });


            foreach($grouped as $group) {
                $appsData[] = [
                    'app_name' => $group['app_name'],
                    'session_time' => $group['total_seconds'],
                    'track_id' => $track->id,
                    'org_id' => organizationId(),
                    'user_id' => auth()->id(),
                    'project_id' => $track->project_id,
                    'task_id' => $track->task_id ?? 0,
                    'started_at' => $track->started_at ?? 0,
                    'ended_at' => $track->ended_at ?? 0,
                    'created_at' => $now
                ];
            }

            if ($idleTime > 0) {
                for ($i = count($appsData) - 1; $i >= 0 && $idleTime > 0; $i--) {
                    $sessionTime = $appsData[$i]['session_time'];

                    if ($sessionTime <= 0) {
                        continue;
                    }

                    if ($sessionTime <= $idleTime) {
                        $idleTime -= $sessionTime;
                        $appsData[$i]['session_time'] = 0;
                    } else {
                        $appsData[$i]['session_time'] -= $idleTime;
                        $idleTime = 0;
                    }
                }
            }

            DB::table('apps')->insert($appsData);
        }
    }

    private function validateStoreTrack($request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['nullable'],
            'task_id' => ['nullable', 'integer'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date'],
            'time_in_seconds' => ['nullable', 'numeric'],
            'tracked' => ['nullable', 'numeric'],
            'overall' => ['nullable', 'numeric'],
            'keyboard_counts' => ['nullable'],
            'mouse_counts' => ['nullable'],
            'apps' => ['nullable', 'string'],
            'activity_minutes' => ['nullable', 'string'],
            'idle_times' => ['nullable', 'string'],
            'screenshots' => ['nullable', 'array'],
            'screenshots.*' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'webp', 'png'])],
            'screenshots_meta' => ['nullable', 'array'],
            'screenshots_meta.*.taken_at' => ['nullable', 'date'],
            'screenshots_meta.*.project_id' => ['nullable', 'integer'],
            'screenshots_meta.*.task_id' => ['nullable', 'integer'],
        ]);

        return $validator->validated();
    }

    private function validToUploadSS($idleTimes = [], $takenAt)
    {
        if (!$takenAt) {
            return true;
        }

        $takenAt = Carbon::parse($takenAt);

        $idleTimes = is_string($idleTimes)
            ? json_decode($idleTimes, true) ?? []
            : $idleTimes;

        foreach ($idleTimes as $idleTime) {
            if (empty($idleTime['idle_started_at']) || empty($idleTime['idle_ended_at'])) {
                continue;
            }

            $idleTimeStart = Carbon::parse($idleTime['idle_started_at']);
            $idleEndedAt = Carbon::parse($idleTime['idle_ended_at']);

            if ($idleTimeStart->lte($takenAt) && $idleEndedAt->gte($takenAt)) {
                return false;
            }
        }

        return true;
    }
}
