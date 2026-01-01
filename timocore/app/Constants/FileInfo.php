<?php

namespace App\Constants;

class FileInfo
{

    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    |
    | This class basically contain the path of files and size of images.
    | All information are stored as an array. Developer will be able to access
    | this info as method and property using FileManager class.
    |
    */

    public function fileInfo(){
        $data['verify'] = [
            'path'      =>'assets/verify'
        ];
        $data['default'] = [
            'path'      => 'assets/images/default.png',
        ];
        $data['logoIcon'] = [
            'path'      => 'assets/images/logo_icon',
        ];
        $data['favicon'] = [
            'size'      => '128x128',
        ];
        $data['extensions'] = [
            'path'      => 'assets/images/extensions',
            'size'      => '36x36',
        ];
        $data['adminProfile'] = [
            'path'      =>'assets/admin/images/profile',
            'size'      =>'400x400',
        ];
        $data['maintenance'] = [
            'path'      =>'assets/images/maintenance',
            'size'      =>'660x325',
        ];
        $data['gateway'] = [
            'path' => 'assets/images/gateway',
            'size' => ''
        ];
        $data['screenshots'] = [
            'path'      => 'assets/images/screenshots',
        ];
        $data['organization'] = [
            'path'      => 'assets/organization',
            'size' => '512x512'
        ];
        $data['userProfile'] = [
            'path'      =>'assets/images/user/profile',
            'size'      =>'256x256',
        ];
        $data['project'] = [
            'path'      =>'assets/images/project',
            'size'      =>'128x128',
        ];
        $data['apps'] = [
            'path'      =>'assets/images/apps',
            'size'      =>'32x32',
        ];
        return $data;
	}

}
