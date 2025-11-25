<?php

namespace App\Traits;

use App\Lib\CurlRequest;
use App\Constants\Status;
use App\Models\Screenshot;
use App\Models\FileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

trait StorageManager {
    protected $userType;
    protected $user;
    protected $organization = null;

    public function list() {
        $pageTitle = "Storage Setting";
        $storages  = FileStorage::searchable(['name'])->withSum('screenshots', 'size_in_bytes')->where('organization_id', ($this->userType == 'user' ? $this->organization?->id : 0))->orderBy('id', 'desc')->paginate(getPaginate());

        if ($this->userType == 'admin') {
            return view('admin.storage.list', compact('pageTitle', 'storages'));
        }

        $fileStorageId = $this->organization->file_storage_id ?? 0;

        $screenshots = Screenshot::where('organization_id', organizationId());
        $storageUsed = $screenshots->sum('size_in_bytes');
        $totalScreenshot = $screenshots->count();
        $currentBillingScreenshot = $screenshots->whereBetween('taken_at', [$this->organization->currentBillingStartDate, now()])->count();

        return view('Template::user.storage.list', compact('pageTitle', 'storages', 'fileStorageId', 'storageUsed', 'totalScreenshot', 'currentBillingScreenshot'));
    }

    public function store(Request $request, $id = NULL) {
        $validation = [
            'storage_type' => 'required|in:' . Status::S3_STORAGE . ',' . Status::FTP_STORAGE,
            'name'         => 'required|string|max:255|unique:file_storages,name,' . $id . ',id',
        ];

        if ($request->storage_type == Status::S3_STORAGE) {
            $validation['access_key']  = 'required|string';
            $validation['secret_key']  = 'required|string';
            $validation['region']      = 'required|string';
            $validation['bucket_name'] = 'required|string';
            $validation['end_point']   = 'required|url';
            $validation['public_end_point']   = 'required|url';
        } else {
            $validation['domain']        = 'required|url';
            $validation['host']          = 'required|string';
            $validation['username']      = 'required|string';
            $validation['password']      = 'required|string';
            $validation['port']          = 'required|integer';
        }

        $request->validate($validation, [
            'domain.required' => 'The URL field is required.',
            'domain.url'      => 'The URL must be a valid URL.',

        ]);

        if ($id) {
            $storage = FileStorage::where('organization_id', ($this->userType == 'user' ? $this->organization?->id : 0))->findOrFail($id);

            $notify[] = ['success', 'Storage updated successfully'];
        } else {
            $storage                  = new FileStorage();
            $storage->organization_id = ($this->userType == 'user' ? $this->organization?->id : 0);

            $notify[] = ['success', 'Storage added successfully'];
        }
        if ($request->storage_type == Status::S3_STORAGE) {
            $config = [
                'access_key'  => $request->access_key,
                'secret_key'  => $request->secret_key,
                'region'      => $request->region,
                'bucket_name' => $request->bucket_name,
                'end_point'   => rtrim($request->end_point, '/'),
                'public_end_point'   => rtrim($request->public_end_point, '/'),
            ];
            $basePath = rtrim($request->public_end_point, '/');
        } else {
            $config = [
                'domain'        => rtrim($request->domain, '/'),
                'host'          => $request->host,
                'username'      => $request->username,
                'password'      => $request->password,
                'port'          => $request->port,
                'upload_folder' => $request->upload_folder,
            ];
            $basePath = rtrim($request->domain, '/');
        }
        $config['verify_code'] = getUid(30, NULL, NULL);
        $storage->storage_type = $request->storage_type;
        $storage->name         = $request->name;
        $storage->config       = $config;
        $storage->base_path    = $basePath;
        $storage->status       = Status::NO;
        $storage->verified     = Status::NO;
        $storage->error_message = NULL;
        $storage->save();

        if($storage->verified == Status::NO && $storage->error_message) {
            $notify[] = ['error', $storage->error_message];
        }

        // update organization storage id
        if($this->userType == 'user' && $this->organization->file_storage_id == $id) {
            $organization                  = $this->organization;
            $organization->file_storage_id = 0;
            $organization->save();
        }

        return back()->withNotify($notify);
    }


    public function verify($id) {
        $storage = FileStorage::where('organization_id', ($this->userType == 'user' ? $this->organization?->id : 0))->findOrFail($id);

        $data = [
            'id'          => $storage->id,
            'verify_code' => $storage->config->verify_code,
        ];
        $encryptedData = base64_encode(Crypt::encryptString(json_encode($data)));

        // Create temporary verification file
        $fileName = 'verify_' . $storage->id . '.txt';
        $filePath = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents($filePath, $encryptedData);

        if ($storage->storage_type == Status::FTP_STORAGE) {
            $uploadPath = ftpFileUploader($storage, $filePath, '/', isVerify: true);
            if ($uploadPath) {
                $this->verifyData($uploadPath, $storage);
            }
        } else {
            $uploadPath = s3FileUploader($storage, $filePath, "verify", isVerify: true);

            if ($uploadPath) {
                $this->verifyData($uploadPath, $storage);
            }
        }


        if($storage->verified) {
            $notify[] = ['success', 'Storage verified successfully'];
        } else {
            $notify[] = ['error', $storage->error_message ?? "Storage verified fail"];
        }

        return back()->withNotify($notify);
    }

    private function verifyData($uploadPath, $storage) {
        $fileUrl = $storage->base_path . '/' . $uploadPath;
        try {
            $response      = CurlRequest::curlContent($fileUrl);
            $decryptedData = json_decode(Crypt::decryptString(base64_decode($response)), true);

            if ($storage->id == $decryptedData['id'] && $storage->config->verify_code == $decryptedData['verify_code']) {
                $storage->verified      = Status::YES;
                $storage->error_message = NULL;
                $storage->save();

                if($storage->storage_type == Status::FTP_STORAGE) {
                    $relativePath = ltrim($uploadPath, '/');
                    deleteFtpFile($relativePath, $storage);
                } else {
                    $relativePath = ltrim(substr($uploadPath, strlen($storage->config->bucket_name)), '/');
                    deleteS3File($relativePath, $storage);
                }
            }
        } catch (\Throwable $th) {
            if($storage->storage_type == Status::FTP_STORAGE) {
                $storage->error_message = "File uploaded successfully, but verification failed.";
            } else {
                $storage->error_message = "File uploaded successfully, but verification failed because public access to this bucket is not enabled.";
            }
            $storage->save();
        }
    }
}
