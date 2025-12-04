<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\CurlRequest;
use App\Lib\DailyReport;
use App\Lib\InvoiceManager;
use App\Lib\SendEmailLater;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Organization;
use App\Models\Screenshot;
use App\Models\SummaryMailQueue;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class CronController extends Controller
{
    public function cron()
    {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }
        $crons = $crons->get();
        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds((int) $cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }
        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }
        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }
    }

    public function generateInvoice()
    {
        try {
            $organizations  = Organization::where('next_invoice_date', '<=', now())->limit(50)->get();
            $invoiceManager = new InvoiceManager();

            foreach ($organizations as $organization) {
                $invoiceManager->generateInvoice($organization);
            }

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }


        CronJob::where('alias','generate_invoice')->update([
            'last_run'=>now()
        ]);

    }

    public function applyLateFee()
    {
        try {
            $invoices = Invoice::unpaid()->where('created_at', '<=', now()->subHours(Status::LATE_FEE_APPLY_HOURS))->where('late_fee', Status::NO)->with('organization.user')->get();

            $invoiceManager = new InvoiceManager();

            foreach ($invoices as $invoice) {
                $invoiceManager->applyLateFee($invoice);
            }

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }

        CronJob::where('alias','apply_late_fee')->update([
            'last_run'=>now()
        ]);
    }

    public function suspendOrganization()
    {
        try {
            $invoices = Invoice::unpaid()->where('is_suspend', Status::NO)->where('created_at', '<=', now()->subHours(Status::SUSPEND_HOURS))->with('organization.user')->get();

            foreach ($invoices as $invoice) {
                $invoice->is_suspend = Status::YES;
                $invoice->save();

                $organization             = $invoice->organization;
                $organization->is_suspend = Status::YES;
                $organization->save();

                notify($organization->user, 'ORGANIZATION_SUSPENDED', [
                    'organization_name' => $organization->name,
                    'invoice_due_from'  => showDateTime($invoice->created_at, 'Y-m-d'),
                ]);
            }

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }

        CronJob::where('alias','suspend_organization')->update([
            'last_run'=>now()
        ]);
    }

    public function sendEmail()
    {
        $notificationLogs = NotificationLog::where('is_send', Status::NO)->limit(50)->get();
        $sendEmail = new SendEmailLater();

        $config = gs('mail_config');

        foreach ($notificationLogs as $log) {
            if ($config->name == 'php') {
                $sendEmail->sendPhpMail($log);
            } else {
                $sendEmail->sendSmtpMail($log);
            }

            $log->is_send = Status::YES;
            $log->save();
        }

        CronJob::where('alias','send_email')->update([
            'last_run'=>now()
        ]);
    }

    public function summaryMailQueue()
    {
        $organizations = Organization::with('users:id,organization_id')->orderBy('last_summary_mail_check')->limit(20)->get();

        foreach ($organizations as $organization) {
            $organization->last_summary_mail_check = time();
            $organization->save();

            if (!$organization->timezone) {
                continue;
            }

            $currentTime = now()->setTimezone($organization->timezone)->format('Y-m-d');
            
            if($organization->last_summary_mail == $currentTime){
                continue;
            }
            
            $userIds               = $organization->users->pluck('id');
            $summaryMailQueueArray = [];

            foreach ($userIds as $userId) {
                $summaryMailQueueArray[] = [
                    'user_id'         => $userId,
                    'organization_id' => $organization->id,
                    'is_sent'         => Status::NO,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            if (count($summaryMailQueueArray) > 0) {
                SummaryMailQueue::insert($summaryMailQueueArray);
            }

            $organization->last_summary_mail = $currentTime;
            $organization->save();
        }

        CronJob::where('alias','summary_mail_queue')->update([
            'last_run'=>now()
        ]);
    }

    public function dailySummaryMail()
    {
        $dailyReport = new DailyReport();

        $summaryMailQueues = SummaryMailQueue::with('user', 'organization')->where('is_sent', Status::NO)->limit(30)->get();

        foreach ($summaryMailQueues as $summaryMailQueue) {
            if ($summaryMailQueue->user->role != Status::STAFF) {
                $dailyReport->generateDailyReportForOrganization($summaryMailQueue->user, $summaryMailQueue->organization);
            }

            $dailyReport->generateDailyReport($summaryMailQueue->user, $summaryMailQueue->organization);

            $summaryMailQueue->is_sent = Status::YES;
            $summaryMailQueue->save();
        }

        CronJob::where('alias','daily_summary_mail')->update([
            'last_run'=>now()
        ]);
    }

    public function uploadFailedScreenshot()
    {
        $failedImages = Screenshot::with('user')->where('uploaded', Status::NO)
            ->whereNotNull('src')
            ->orderBy('id')
            ->limit(100)
            ->get();
        foreach ($failedImages as $failedImage) {
            $src = $failedImage->src;
            try {
                $file = $this->imageConvertToRequestFile($src);

                if (!$file) {
                    continue;
                }

                [$storedName, $storageId, $uploadStatus] = screenshotUploader($file, organization: $failedImage->user->organization, uid: $failedImage->user->uid);

                    if(!$storedName) {
                        continue;
                    }

                    $failedImage->update([
                        'src'             => $storedName,
                        'file_storage_id' => $storageId,
                        'uploaded'        => $uploadStatus,
                    ]);

                // Remove local file after successful upload
                $oldFile = getFilePath('screenshots') . '/' . $src;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }

                // Remove temp uploaded file
                if (file_exists($file->getRealPath())) {
                    @unlink($file->getRealPath());
                }

                // Free memory
                unset($file);

                } catch (\Throwable $e) {
                    continue;
                }
            }


        CronJob::where('alias','upload_failed_screenshot')->update([
            'last_run'=>now()
        ]);
    }

    private function imageConvertToRequestFile($image)
    {
        $url = asset(getFilePath('screenshots') . '/' . $image);

        $response = Http::timeout(10)->get($url);

        if ($response->failed()) {
            return false;
        }

        $mime = $response->header('Content-Type');
        if (!str_starts_with($mime, 'image/')) {
            return false;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'url_img_');
        file_put_contents($tempPath, $response->body());

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        return new UploadedFile($tempPath, 'image.' . $extension, $mime, null, true);
    }

    public function emailTrack($trx)
    {
        $trx = explode('.', $trx)[0];

        $notificationLog = NotificationLog::where('trx_key', $trx)->first();
        if ($notificationLog) {
            $notificationLog->user_read += 1;
            $notificationLog->save();
        }

        $image = imagecreatetruecolor(1000, 1);
        $color = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $color);
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit;
    }

}
