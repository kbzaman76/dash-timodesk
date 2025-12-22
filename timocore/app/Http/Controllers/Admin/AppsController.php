<?php

namespace App\Http\Controllers\Admin;

use App\Models\App;
use App\Models\AppModifier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AppsController extends Controller {

    public function index() {
        $pageTitle = 'Apps';
        $apps      = AppModifier::selectRaw('
                group_name,
                COUNT(*) as total_app_count,
                GROUP_CONCAT(app_name SEPARATOR " | ") as apps,
                GROUP_CONCAT(id SEPARATOR " | ") as apps_id
            ')
            ->groupBy('group_name')
            ->orderBy('group_name')
            ->paginate(getPaginate());

        return view('admin.apps.index', compact('apps', 'pageTitle'));
    }

    public function create() {
        $pageTitle = 'Add New App Group';
        $appGroups = AppModifier::groupBy('group_name')->select('group_name')->pluck('group_name')->toArray();
        $apps      = App::whereNotIn('app_name', $appGroups)->orderBy('app_name')->groupBy('app_name')->get();
        return view('admin.apps.add', compact('pageTitle', 'apps'));
    }

    public function store(Request $request) {
        $request->validate(
            [
                'app_group_name' => 'required|string|max:255',
                'app_names'      => 'required|array|min:1',
                'app_names.*'    => 'required|string|unique:app_modifiers,app_name',
            ],
            [
                'app_names.*.unique' => $request->app_names[0] . ' app cannot be added because it is already assigned to an app group.',

            ]
        );

        $groupName = trim($request->app_group_name);
        $appNames  = $request->app_names;

        $appsDatas = [];
        foreach ($appNames as $appName) {
            $appsDatas[] = [
                'group_name' => $groupName,
                'app_name'   => $appName,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        AppModifier::insert($appsDatas);

        $this->updateCache();

        $notify[] = ['success', 'App group created successfully.'];
        return back()->withNotify($notify);
    }

    public function update(Request $request) {
        $request->validate(
            [
                'app_group_name' => 'required|string|max:255',
                'app_names'      => 'required|array|min:1',
                'app_names.*'    => 'required|string',
            ]
        );

        $groupName = trim($request->app_group_name);
        $appNames  = $request->app_names;

        AppModifier::where('group_name', $groupName)->delete();

        $appsDatas = [];
        foreach ($appNames as $appName) {
            $appsDatas[] = [
                'group_name' => $groupName,
                'app_name'   => $appName,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        AppModifier::insert($appsDatas);

        $this->updateCache();

        $notify[] = ['success', 'App group updated successfully.'];
        return back()->withNotify($notify);
    }


    public function updateApps($groupName) {
        $appsModifiers = AppModifier::where('group_name', $groupName)->select('group_name', 'app_name')->get();
        $appGroupName = $appsModifiers->first()->group_name;
        $appsNames = $appsModifiers->pluck('app_name')->toArray();

        App::whereIn('app_name', $appsNames)->update(['app_name' => $appGroupName]);

        $notify[] = ['success', 'Apps updated successfully.'];
        return back()->withNotify($notify);
    }

    private function updateCache() {
        // Clear old cache
        Cache::forget('appModifiers');

        // Set updated cache
        Cache::forever('appModifiers', AppModifier::all());
    }
}
