<?php

use Carbon\Carbon;
use App\Lib\Captcha;
use App\Notify\Notify;
use App\Lib\ClientInfo;
use App\Lib\FileManager;
use App\Constants\Status;
use App\Models\Extension;
use App\Lib\S3FileManager;
use App\Lib\FTPFileManager;
use App\Models\AppModifier;
use App\Models\FileStorage;
use Illuminate\Support\Str;
use App\Models\GeneralSetting;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;


function systemDetails()
{
    $system['name'] = 'timodesk';
    $system['version'] = '1.1.5';
    $system['build_version'] = '5.1.19';
    return $system;
}

function slug($string)
{
    return Str::slug($string);
}

function verificationCode($length)
{
    if ($length == 0) {
        return 0;
    }

    $min = pow(10, $length - 1);
    $max = (int) ($min - 1) . '9';
    return random_int($min, $max);
}

function getNumber($length = 8)
{
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function activeTemplate($asset = false)
{
    $template = 'basic';
    if ($asset) {
        return 'assets/templates/' . $template . '/';
    }

    return 'templates.' . $template . '.';
}

function activeTemplateName()
{
    $template = 'basic';
    return $template;
}

function siteLogo($type = null)
{
    $name = $type ? "/logo_$type.png" : '/logo.png';
    return getImage(getFilePath('logoIcon') . $name);
}

function siteFavicon()
{
    return getImage(getFilePath('logoIcon') . '/favicon.png');
}

function loadReCaptcha()
{
    return Captcha::reCaptcha();
}

function verifyCaptcha()
{
    return Captcha::verify();
}

function loadExtension($key)
{
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}

function getTrx($length = 12)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2)
{
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false, $currencyFormat = true)
{
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        } else {
            $printAmount = rtrim($printAmount, '0');
        }
    }
    if ($currencyFormat) {
        if (gs('currency_format') == Status::CUR_BOTH) {
            return gs('cur_sym') . $printAmount . ' ' . __(gs('cur_text'));
        } elseif (gs('currency_format') == Status::CUR_TEXT) {
            return $printAmount . ' ' . __(gs('cur_text'));
        } else {
            return gs('cur_sym') . $printAmount;
        }
    }
    return $printAmount;
}

function removeElement($array, $value)
{
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet)
{
    return "https://api.qrserver.com/v1/create-qr-code/?data=$wallet&size=300x300&ecc=m";
}

function keyToTitle($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}

function titleToKey($text)
{
    return strtolower(str_replace(' ', '_', $text));
}

function strLimit($title = null, $length = 10)
{
    return Str::limit($title, $length);
}

function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}

function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}

function getTemplates()
{
    $param['purchasecode'] = env("PURCHASECODE");

    $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $param['website'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '' . $requestUri . ' - ' . env("APP_URL");
    return null;
}

function getPageSections($arr = false)
{
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}

function getImage($image, $avatar = false)
{
    $clean = '';
    if (file_exists($image) && is_file($image)) {
        return asset($image) . $clean;
    }
    if ($avatar) {
        return asset('assets/images/avatar.png');
    }
    return asset('assets/images/default.png');
}

function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true, $pushImage = null)
{
    $globalShortCodes = [
        'site_name' => gs('site_name'),
        'site_currency' => gs('cur_text'),
        'currency_symbol' => gs('cur_sym'),
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes = $shortCodes;
    $notify->user = $user;
    $notify->createLog = $createLog;
    $notify->pushImage = $pushImage;
    $notify->userColumn = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify->send();
}


function getPaginate($paginate = null)
{
    if (!$paginate) {
        $paginate = gs('paginate_number');
    }
    return $paginate;
}

function paginateLinks($data, $view = null)
{
    $view = 'partials.pagination';
    return $data->appends(request()->all())->links($view);
}

function menuActive($routeName, $type = null, $param = null)
{
    if ($type == 3) {
        $class = 'side-menu--open';
    } elseif ($type == 2) {
        $class = 'sidebar-submenu__open';
    } else {
        $class = 'active';
    }

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) {
                return $class;
            }
        }
    } elseif (request()->routeIs($routeName)) {
        $routeParam = array_values(isset(request()->route()->parameters) ? request()->route()->parameters : []);
        $firstParam = $routeParam[0] ?? null;
        if (is_string($firstParam) && is_string($param) && strcasecmp($firstParam, $param) === 0) {
            return $class;
        }
        return $class;
    }
}

function fileUploader($file, $location, $size = null, $old = null, $thumb = null, $filename = null)
{
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->filename = $filename;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function diffForHumans($date)
{
    return Carbon::parse($date)->diffForHumans();
}

function showDateTime($date, $format = 'Y-m-d h:i A')
{
    if (!$date) {
        return '-';
    }
    return Carbon::parse($date)->setTimezone(orgTimezone())->translatedFormat($format);
}

function urlPath($routeName, $routeParam = null)
{
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('user.home');
    $path = str_replace($basePath, '', $url);
    return $path;
}

function showMobileNumber($number)
{
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email)
{
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}

function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(isset($_SERVER['HTTP_FORWARDED']) ? $_SERVER['HTTP_FORWARDED'] : '', FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(isset($_SERVER['HTTP_FORWARDED_FOR']) ? $_SERVER['HTTP_FORWARDED_FOR'] : '', FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '', FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : '', FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : '', FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : '', FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}

function appendQuery($key, $value)
{
    return request()->fullUrlWithQuery([$key => $value]);
}

function dateSort($a, $b)
{
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr)
{
    usort($arr, "dateSort");
    return $arr;
}

function gs($key = null)
{
    $general = Cache::get('GeneralSetting');
    if (!$general) {
        $general = GeneralSetting::first();
        Cache::put('GeneralSetting', $general);
    }
    if ($key) {
        return isset($general->$key) ? $general->$key : null;
    }

    return $general;
}
function isImage($string)
{
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = pathinfo($string, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $allowedExtensions)) {
        return true;
    } else {
        return false;
    }
}

function isHtml($string)
{
    if (preg_match('/<.*?>/', $string)) {
        return true;
    } else {
        return false;
    }
}

function convertToReadableSize($size)
{
    preg_match('/^(\d+)([KMG])$/', $size, $matches);
    $size = (int) $matches[1];
    $unit = $matches[2];

    if ($unit == 'G') {
        return $size . 'GB';
    }

    if ($unit == 'M') {
        return $size . 'MB';
    }

    if ($unit == 'K') {
        return $size . 'KB';
    }

    return $size . $unit;
}

function buildResponse($remark, $status, $notify, $data = null)
{
    $response = [
        'remark' => $remark,
        'status' => $status,
    ];
    $message = [];
    if ($notify instanceof \Illuminate\Support\MessageBag) {
        $message['error'] = collect($notify)->map(function ($item) {
            return $item[0];
        })->values()->toArray();
    } else {
        $message = [
            $status => collect($notify)->map(function ($item) {
                if (is_string($item)) {
                    return $item;
                }
                if (count($item) > 1) {
                    return $item[1];
                }
                return $item[0];
            })->toArray(),
        ];
    }
    $response['message'] = $message;
    if ($data) {
        $response['data'] = $data;
    }
    return response()->json($response);
}

function responseSuccess($remark, $notify, $data = null)
{
    return buildResponse($remark, 'success', $notify, $data);
}

function responseError($remark, $notify, $data = null)
{
    return buildResponse($remark, 'error', $notify, $data);
}

function organizationTypes()
{
    return [
        "Technology / IT",
        "Professional Services",
        "Freelancers / Independent Contractors",
        "Marketing & Creative Agencies",
        "Finance & Accounting",
        "Construction & Engineering",
        "Education / Training",
        "Healthcare",
        "Manufacturing",
        "Logistics / Transportation",
        "Customer Support / Call Centers",
        "Retail & E-Commerce",
        "HR / Staffing",
        "Real Estate / Property Management",
        "Non-Profit",
        "Government",
        "Other"
    ];
}

function hearAboutUsOptions()
{
    return [
        "Google Search",
        "Facebook",
        "YouTube",
        "LinkedIn",
        "Instagram",
        "Twitter / X",
        "Reddit",
        "Blog or Article",
        "Friend or Colleague",
        "Referral",
        "Online Community / Forum",
        "Review Website",
        "Ads (Facebook/Google)",
        "Direct Visit",
        "Other"
    ];
}

function getBillingDate($date)
{
    if ($date->day >= 28) {
        return $date->copy()->addMonthNoOverflow()->startOfMonth();
    }

    return $date;
}
function formatSecondsToHoursMinutes($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return sprintf('%02d:%02d', $hours, $minutes);
}

function formatSecondsToHoursMinuteSeconds($seconds, $withHour = true)
{
    $seconds = (int) $seconds;
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    if ($withHour) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    } else {
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}

function formatSecondsToMinuteSeconds($seconds)
{
    $seconds = (int) $seconds;
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d', $minutes, $remainingSeconds);
}

function getActivityClass($percent)
{
    if ($percent < 30) {
        return 'bg--danger';
    } elseif ($percent < 60) {
        return 'bg--warning';
    } else {
        return 'bg--success';
    }
}

function eventsPerMinute($mouseCounts, $keyboardCounts, $seconds)
{
    $totalEvents = (int) ($mouseCounts ?? 0) + (int) ($keyboardCounts ?? 0);
    $minutes = ($seconds ?? 0) / 60;
    if ($minutes <= 0) {
        return 0;
    }
    return $totalEvents / $minutes;
}

function activityPercent($mouseCounts, $keyboardCounts, $seconds, $maxEventsPerMinute = 120)
{
    $epm = eventsPerMinute($mouseCounts, $keyboardCounts, $seconds);
    if ($maxEventsPerMinute <= 0) {
        return 0;
    }
    $percent = ($epm / $maxEventsPerMinute) * 100;
    $percent = max(0, min(100, $percent));

    return (int) round($percent);
}


function organizationId()
{
    return auth()->user()->organization_id;
}

function myOrganization()
{
    return auth()->user()->organization;
}

function s3FileUploader($storage, $file, $location = null, $size = null, $thumb = null, $filename = null, $isVerify = false)
{
    if (!$location) {
        $location = date('Y-m-d');
    }
    // Initialize S3FileManager with file and storageId
    $fileManager = new S3FileManager($storage, $file, $isVerify);

    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->thumb = $thumb;
    $fileManager->filename = $filename;

    // Upload file
    $fileManager->upload();

    return $fileManager->url();
}

function deleteS3File($oldFile, $storage)
{
    $fileManager = new S3FileManager($storage);
    $fileManager->httpDelete($oldFile);
}

/**
 * Upload file to FTP server
 *
 * @param mixed $file       Uploaded file or local path
 * @param string|null $location   Folder path on FTP
 * @param string|null $size       Resize size for images (e.g., "800x600")
 * @param int|null $storageId     Storage config ID from DB
 * @param string|null $thumb      Thumbnail size (e.g., "100x100")
 * @param string|null $filename   Optional filename to use
 * @param string|null $old        Old file to delete
 * @return string                 Path of uploaded file on FTP
 */
function ftpFileUploader($storage, $file, $location = null, $size = null, $thumb = null, $filename = null, $isVerify = false)
{
    if (!$location) {
        $location = date('Y/m/d'); // default folder
    }
    // Initialize FTPFileManager
    $fileManager = new FTPFileManager($storage, $file, $isVerify);

    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->thumb = $thumb;
    $fileManager->filename = $filename;

    // Upload file
    $fileManager->upload();

    return $fileManager->url();
}


function deleteFtpFile($oldFile, $storage)
{
    $fileManager = new FTPFileManager($storage);

    $fileManager->ftpDelete($oldFile);
}


function defaultTimeZone()
{
    return 'UTC';
}

function goBack($notify = null)
{
    if (!$notify) {
        return back();
    }

    return back()->withNotify($notify);
}

function formatSeconds($seconds, $versionTwo = false)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    if ($versionTwo) {
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
}

function screenshotUploader($file, $storeLocal = false, $organization = NULL, $uid = NULL)
{
    $organization = $organization ?? auth()->user()->organization;
    $storage = $organization->fileStorage ?? null;
    if (!$storage) {
        $storage = getStorage();
    }
    try {
        $uploadStatus = Status::YES;

        $location = $organization->uid . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . (auth()->user()->uid ?? $uid);

        $name = uploadToStorageServer($file, $location, $storage);

        // try to upload backup server only for admin storage failed
        if (!$name && $storage->organization_id == 0) {

            $storage = getStorage(Status::BACKUP_STORAGE);

            if ($storage) {
                $name = uploadToStorageServer($file, $location, $storage);
            }

            if (!$name && $storeLocal) {
                try {
                    $name = fileUploader($file, getFilePath('screenshots'));
                    $uploadStatus = Status::NO;
                } catch (\Exception $exp) {
                    $uploadStatus = Status::NO;
                }
            }
        } elseif (!$name) {
            $uploadStatus = Status::NO;
        }

        $storageId = $uploadStatus ? $storage->id : null;

        return [$name, $storageId, $uploadStatus];
    } catch (\Throwable $e) {
        return [null, null, null];
    }
}


// upload permanent image as like user image, organization logo and project icon
function uploadPermanentImage($file, $location)
{
    $storage = getStorage(Status::PERMANENT_STORAGE);

    try {

        $name = uploadToStorageServer($file, $location, $storage);

        if (!$name) {
            return [null, null];
        }

        return [$name, $storage->id];
    } catch (\Throwable $e) {
        return [null, null];
    }
}


function uploadToStorageServer($file, $location, $storage)
{

    if ($storage->storage_type == Status::FTP_STORAGE) {
        return ftpFileUploader($storage, $file, $location);
    } else {
        return s3FileUploader($storage, $file, $location);
    }
}

function deleteStorageFile($oldFile, $storageId)
{
    $storage = FileStorage::find($storageId);
    if ($storage->storage_type == Status::FTP_STORAGE) {
        deleteFtpFile($oldFile, $storage);
    } else {
        deleteS3File($oldFile, $storage);
    }
}


function templateImage($filePath)
{
    return asset(activeTemplate(true) . "images/" . $filePath);
}

function getUid($max = 6, $modelName = 'User', $columnName = 'uid', $type = 'numeric')
{
    if ($type == 'numeric') {
        $min = (int) ('1' . str_repeat('0', $max - 1));
        $maxNum = (int) str_repeat('9', $max);
    }

    $modelClass = "\\App\\Models\\{$modelName}";

    do {
        $uid = ($type == 'numeric') ? mt_rand($min, $maxNum) : strtolower(getTrx($max));
        $exists = false;

        if ($modelName && class_exists($modelClass) && $columnName) {
            $exists = $modelClass::where($columnName, $uid)->exists();
        }
    } while ($exists);

    return $uid;
}




// TODO
function testimonials()
{
    return [
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
        [
            'title' => 'They turned our concept for a time tracking and team management tool into a sleek product that our users adore.',
            'name' => 'Sarah Johnson',
            'designation' => 'Marketing Director',
            'image' => '/images/users/user.png',
            'logo' => '/images/thumbs/demologo.png',
        ],
    ];
}


function getSweetColors()
{
    $SWEET_COLORS = [
        "#FFE5EC",
        "#FFD7E2",
        "#FFCAD4",
        "#FFB3C6",
        "#FF9AA2",
        "#FFD6A5",
        "#FDD5B1",
        "#FBC4A6",
        "#F9AFAF",
        "#F7E3AF",
        "#FDFFB6",
        "#F6FDC3",
        "#FAF4B7",
        "#FFF9C4",
        "#FFF5BA",
        "#CAFFBF",
        "#D0F4DE",
        "#CFFFE5",
        "#D6F5E8",
        "#B8F2E6",
        "#E0FBFC",
        "#D4F8E8",
        "#C9F9E5",
        "#D3F9D8",
        "#CCF6C8",
        "#BDE0FE",
        "#C7E9FF",
        "#D6ECFF",
        "#D7E3FC",
        "#CCDBFD",
        "#A0C4FF",
        "#B3D6FF",
        "#C4DDFF",
        "#DAE6FF",
        "#E2EAFF",
        "#C8B6FF",
        "#D0C3FF",
        "#DCC9FF",
        "#E5D4FF",
        "#F1E1FF",
        "#FFC6FF",
        "#FFDEFA",
        "#FFE4FE",
        "#FCDFFF",
        "#F6D8FF",
        "#FFF1E6",
        "#FFEFE4",
        "#FFEAD9",
        "#FFE3CC",
        "#FDE2D6",
    ];

    $bg = $SWEET_COLORS[array_rand($SWEET_COLORS)];

    $hex = str_replace('#', '', $bg);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;

    $text = "#1a1a1a";
    if ($luminance < 0.6) {
        $text = "#ffffff";
    }

    return [
        'bg' => $bg,
        'text' => $text
    ];
}


function getActivity($tracks)
{
    $totalSeconds = $tracks->sum('time_in_seconds');
    if ($totalSeconds == 0) {
        return 0;
    }
    $totalActivity = $tracks->sum('overall_activity');
    return (float) $totalActivity / $totalSeconds;
}


function formatStorageSize($bytes, $precision = 2)
{
    if ($bytes <= 0) {
        return '0 Bytes';
    }

    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $base = floor(log($bytes, 1024));
    $size = round($bytes / pow(1024, $base), $precision);

    return $size . ' ' . $units[$base];
}


function formatNumberShort($number, $precision = 1)
{
    if ($number < 1000) {
        return number_format($number);
    }

    $units = ['', 'K', 'M', 'B', 'T'];
    $base = floor(log($number, 1000));
    $value = $number / pow(1000, $base);

    return number_format($value, $precision) . $units[$base];
}

function progressBarColor($percent)
{
    $value = (float) str_replace('%', '', $percent);

    return match (true) {
        $value < 25 => 'bg--danger',
        $value < 50 => 'bg--warning',
        $value < 75 => 'bg--info',
        default => 'bg--success',
    };
}

function unauthorized()
{
    $notify[] = 'Unauthorized request';
    return response()->json([
        'remark' => 'unauthenticated',
        'status' => 'error',
        'message' => ['error' => $notify]
    ]);
}

function getStorage($storageType = Status::ACTIVE_STORAGE)
{
    return FileStorage::where('organization_id', 0)->where('status', $storageType)->orderBy('id', 'asc')->first();
}

function toWebpFile(UploadedFile $file, $size = null, int $quality = 80)
{
    $manager = new ImageManager(new Driver());

    $img = $manager->read($file);

    if($size) {
        [$width, $height] = explode('x', strtolower($size));
        $img->resize($width, $height);
    }

    $tmpDir = storage_path('app/tmp');
    if (!is_dir($tmpDir)) {
        mkdir($tmpDir, 0775, true);
    }

    $tmpPath = $tmpDir . '/' . Str::uuid() . '.webp';
    $img->toWebp($quality)->save($tmpPath);

    return new UploadedFile(
        $tmpPath,
        basename($tmpPath),
        'image/webp',
        null,
        true
    );
}



function getApps($name)
{
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'apps.json';
    $apps = json_decode(file_get_contents($jsonUrl));

    if ($name) {
        return $apps->$name ?? 'default';
    }

    return $apps;
}

function orgTimezone()
{
    return auth()->user()?->organization?->timezone ?? 'UTC';
}

function getImageInfo($image)
{
    try {
        return [$storageId, $path] = explode('|', ($image ?? ""), 2);
    } catch (\Throwable $th) {
        //throw $th;
    }

    return [null, null];
}

function emptyImage($fileNameWithoutExt)
{
    return asset('assets/images/empty/' . $fileNameWithoutExt . '.webp');
}


function toTitle($str){
    return Str::title($str);
}


function isEditDisabled($member) {
    $auth = auth()->user();
    if($auth->id == $member->id || $member->has_organization) {
        return 'disabled';
    }
    return null;
}


function orgNow() {
    return now()->setTimeZone(orgTimezone());
}

function getAppModifiers() {
    return Cache::rememberForever('appModifiers', function () {
        return AppModifier::all();
    });
}

function appGroupName($appName) {
    $modifiers = getAppModifiers();
    $modifier = $modifiers->where('app_name', $appName)->first();
    return $modifier ? $modifier->group_name : $appName;
}

function last30Days($format = 'F d, Y') {
    $startDate = orgNow()->subDays(29)->startOfDay();
    $endDate   = orgNow()->endOfDay();

    $defaultDateRange = $startDate->format($format) . ' - ' . $endDate->format($format);
    $defaultLabel     = 'Last 30 Days';
    return ['defaultDateRange' => $defaultDateRange,  'defaultLabel' => $defaultLabel];
}


