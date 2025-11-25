<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\FileStorage;
use App\Traits\StorageManager;

class StorageController extends Controller {

    use StorageManager;

    public function __construct() {
        parent::__construct();
        $this->userType     = 'user';
        $this->user         = auth()->user();
        $this->organization = auth()->user()->organization;
    }

    public function activate($id) {
        $storage = FileStorage::where('organization_id', $this->organization->id)->findOrFail($id);
        if($storage->verified == Status::NO) {
            $notify[] = ['error', 'The storage has not been verified yet.'];
            return back()->withNotify($notify);
        }

        // update organization
        $organization                  = $this->organization;
        $organization->file_storage_id = $storage->id;
        $organization->save();

        $notify[] = ['success', 'Storage activated successfully'];
        return back()->withNotify($notify);
    }


    public function deactivate($id) {
        $organization                  = $this->organization;
        $organization->file_storage_id = 0;
        $organization->save();

        $notify[] = ['success', 'Storage deactivated successfully'];
        $notify[] = ['info', gs('site_name').' storage activated successfully'];
        return back()->withNotify($notify);
    }

}
