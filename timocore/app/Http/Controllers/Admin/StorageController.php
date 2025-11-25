<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Models\FileStorage;
use App\Traits\StorageManager;
use App\Http\Controllers\Controller;

class StorageController extends Controller {

    use StorageManager;

    public function __construct() {
        parent::__construct();
        $this->userType     = 'admin';
        $this->user         = auth()->user();
        $this->organization = NULL;
    }

    public function status($id) {
        $storage = FileStorage::where('organization_id', 0)->findOrFail($id);
        if ($storage->status == Status::ENABLE) {
            $storage->status = Status::DISABLE;
            $storage->save();
        } else {
            if($storage->verified) {
                $storage->status = Status::ENABLE;
                $storage->save();
            } else {
                $notify[] = ['error', 'The storage has not been verified yet.'];
                return back()->withNotify($notify);
            }
        }

        $notify[] = ['success', 'Storage status updated successfully'];
        return back()->withNotify($notify);
    }


    public function backup($id) {
        $sotrage = FileStorage::where('organization_id', 0)->verified()->findOrFail($id);
        $sotrage->status = Status::BACKUP_STORAGE;
        $sotrage->save();

        FileStorage::where('organization_id', 0)->where('status', Status::BACKUP_STORAGE)->where('id', '!=', $id)->update(['status' => Status::ACTIVE_STORAGE]);

        $notify[] = ['success', 'Backup storage set successfully'];
        return back()->withNotify($notify);
    }

    public function permanent($id) {
        $sotrage = FileStorage::where('organization_id', 0)->verified()->findOrFail($id);
        $sotrage->status = Status::PERMANENT_STORAGE;
        $sotrage->save();

        FileStorage::where('organization_id', 0)->where('status', Status::PERMANENT_STORAGE)->where('id', '!=', $id)->update(['status' => Status::ACTIVE_STORAGE]);

        $notify[] = ['success', 'Permanent storage set successfully'];
        return back()->withNotify($notify);
    }

}
