<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Lib\CurlRequest;
use App\Lib\DailyReport;
use App\Lib\InvoiceManager;
use App\Lib\SendEmailLater;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\EngagementEmail;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Organization;
use App\Models\Screenshot;
use App\Models\SummaryMailQueue;
use App\Models\Track;
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
                $dailyReport->generateRecentlyMemberAddedReport($summaryMailQueue->user, $summaryMailQueue->organization);
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


    public function engagementEmails()
    {

        $engagements = [
            'welcome', 
            'member_invite',
            'opportunity_missing',
            'unlock_full_potential',
            'refer_earn',
            'come_back_14',
            'trial_end',
            'screenshot_100',
            'screenshot_1000',
            'screenshot_10000',
            'screenshot_100000',
            'screenshot_1000000',
            'hour_100',
            'hour_1000',
            'hour_10000',
            'hour_100000',
            'hour_1000000',
        ];

        $organizations = Organization::orderBy('last_summary_mail_check')->limit(20)->get();

        foreach ($organizations as $organization) {

            $orgSent = EngagementEmail::where('organization_id',$organization->id)->pluck('alias')->toArray();
            $orgEngagements = array_diff_key(
                $engagements,
                array_flip($orgSent)
            );

            $screenshotCount = $organization->screenshots()->count();
            $hourCount = $organization->tracks()->sum('time_in_seconds');


            foreach($orgEngagements as $alias){

                if($alias == 'welcome'){
                    if($organization->user->ev){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'member_invite'){
                    if($organization->users()->count() > 1){
                        $this->sendEngagement($organization, $alias, 0);
                    }elseif($organization->user->ev && $organization->users()->count() == 1 && abs(now()->diffInSeconds($organization->created_at)) > 3600){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'opportunity_missing'){
                    if($organization->users()->count() > 1){
                        $this->sendEngagement($organization, $alias, 0);
                    }elseif($organization->user->ev && $organization->users()->count() == 1 && abs(now()->diffInSeconds($organization->created_at)) > 3*24*3600){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'unlock_full_potential'){
                    if(abs(now()->diffInSeconds(optional($organization->users->skip(1)->first())->created_at)) > 3*24*3600){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'refer_earn'){
                    if(abs(now()->diffInSeconds(optional($organization->deposits->where('status',1)->first())->created_at)) > 2*24*3600){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'come_back_14'){
                    $trackCount = Track::where('organization_id', $organization->id)->where('created_at', '>=', now()->subDays(14))->count();
                    if($trackCount == 0 && abs(now()->diffInSeconds($organization->created_at)) > 14*24*3600){
                        $this->sendEngagement($organization, $alias);
                    }            
                }

                if($alias == 'trial_end'){
                    $trialEnd         = $organization->trial_end_at ? Carbon::parse($organization->trial_end_at) : null;
                    $trialActive      = $trialEnd && now()->lt($trialEnd);
                    if (!$trialActive) {
                        $this->sendEngagement($organization, $alias);
                    }
                }


                if($alias == 'screenshot_100'){
                    if($screenshotCount > 100){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'screenshot_1000'){
                    if($screenshotCount > 1000){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'screenshot_10000'){
                    if($screenshotCount > 10000){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'screenshot_100000'){
                    if($screenshotCount > 100000){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'screenshot_1000000'){
                    if($screenshotCount > 1000000){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'hour_100'){
                    if($hourCount > 3600*100){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'hour_1000'){
                    if($hourCount > 3600*1000){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'hour_10000'){
                    if($hourCount > 3600*10000){
                        $this->sendEngagement($organization, $alias);
                    }
                }

                if($alias == 'hour_100000'){
                    if($hourCount > 3600*100000){
                        $this->sendEngagement($organization, $alias);
                    }
                }
                
                if($alias == 'hour_1000000'){
                    if($hourCount > 3600*1000000){
                        $this->sendEngagement($organization, $alias);
                    }
                }


            }
        } 
    }


    private function sendEngagement($organization, $alias, $is_need = 1)
    {
        $newEngament = new EngagementEmail();
        $newEngament->organization_id = $organization->id;
        $newEngament->alias = $alias;
        $newEngament->is_need = $is_need;
        $newEngament->save();

        if($is_need){

            $expAlice = explode('_', $alias);
            if($expAlice[0] == 'hour'){
                // Hour Email Template with Variable $expAlice[1]
            }elseif($expAlice[0] == 'screenshot'){
                // Screenshot Email Template with Variable $expAlice[1]
            }else{
                // Direct Email Template
            }

        //#################### send the email to all organizers of this organization

        }

    }

}
