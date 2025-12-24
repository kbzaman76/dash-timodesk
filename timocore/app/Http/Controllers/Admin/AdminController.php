<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Screenshot;
use App\Models\Track;
use App\Models\Transaction;
use App\Models\User;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller {

    public function dashboard() {
        $pageTitle = 'Dashboard';

        // User Info
        $widget['total_users']            = User::count();
        $widget['verified_users']         = User::active()->count();
        $widget['email_unverified_users'] = User::emailUnverified()->count();
        $widget['tracking_users']         = User::tracking()->count();

        $deposit['total_deposit_amount']   = Deposit::successful()->sum('amount');
        $deposit['total_deposit_pending']  = Deposit::pending()->count();
        $deposit['total_deposit_rejected'] = Deposit::rejected()->count();
        $deposit['total_deposit_charge']   = Deposit::successful()->sum('charge');

        $organization['total']        = Organization::count();
        $organization['totalPaid']    = Organization::paid()->count();
        $organization['totalUnpaid']  = Organization::unpaid()->count();
        $organization['totalSuspend'] = Organization::suspend()->count();

        $invoice['totalAmount']          = Invoice::sum('amount');
        $invoice['totalPaidAmount']      = Invoice::paid()->sum('amount');
        $invoice['totalUnpaidAmount']    = Invoice::unpaid()->sum('amount');
        $invoice['totalCancelledAmount'] = Invoice::cancelled()->sum('amount');

        $loggedHoursSummary   = $this->loggedHoursSummary();
        $screenshotsSummary   = $this->screenshotSummary();
        $organizationsSummary = $this->organizationSummary();

        return view('admin.dashboard', compact('pageTitle', 'widget', 'deposit', 'organization', 'invoice', 'loggedHoursSummary', 'screenshotsSummary', 'organizationsSummary'));
    }

    public function transactionReport(Request $request) {

        $diffInDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date));

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format  = $diffInDays > 30 ? '%M-%Y' : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = $this->getAllDates($request->start_date, $request->end_date);
        } else {
            $dates = $this->getAllMonths($request->start_date, $request->end_date);
        }

        $plusTransactions = Transaction::where('trx_type', '+')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $minusTransactions = Transaction::where('trx_type', '-')
            ->whereDate('created_at', '>=', $request->start_date)
            ->whereDate('created_at', '<=', $request->end_date)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'credits'    => getAmount($plusTransactions->where('created_on', $date)->first()?->amount ?? 0),
                'debits'     => getAmount($minusTransactions->where('created_on', $date)->first()?->amount ?? 0),
            ];
        }

        $data = collect($data);

        // Monthly Deposit Report Graph
        $report['created_on'] = $data->pluck('created_on');
        $report['data']       = [
            [
                'name' => 'Plus Transactions',
                'data' => $data->pluck('credits'),
            ],
            [
                'name' => 'Minus Transactions',
                'data' => $data->pluck('debits'),
            ],
        ];

        return response()->json($report);
    }

    private function getAllDates($startDate, $endDate) {
        $dates       = [];
        $currentDate = new \DateTime($startDate);
        $endDate     = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('d-F-Y');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    private function getAllMonths($startDate, $endDate) {
        if ($endDate > now()) {
            $endDate = now()->format('Y-m-d');
        }

        $startDate = new \DateTime($startDate);
        $endDate   = new \DateTime($endDate);

        $months = [];

        while ($startDate <= $endDate) {
            $months[] = $startDate->format('F-Y');
            $startDate->modify('+1 month');
        }

        return $months;
    }

    public function profile() {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request) {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password() {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request) {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications() {
        $notifications   = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        $hasUnread       = AdminNotification::where('is_read', Status::NO)->exists();
        $hasNotification = AdminNotification::exists();
        $pageTitle       = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }

    public function notificationRead($id) {
        $notification          = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function readAllNotification() {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES,
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification() {
        AdminNotification::truncate();
        $notify[] = ['success', 'Notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id) {
        AdminNotification::where('id', $id)->delete();
        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash) {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    private function organizationSummary($months = 12) {
        $endDate   = now()->endOfMonth();
        $startDate = now()->subMonths($months - 1)->startOfMonth(); // last 12 months including current

        // Aggregate organizations per month
        $organizationRows = Organization::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total_organizations")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $labels   = [];
        $values   = [];

        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $monthKey = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $values[] = (int) ($organizationRows[$monthKey]->total_organizations ?? 0);
            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function loggedHoursSummary() {
        return $this->summaryData('logged_hours');
    }

    private function screenshotSummary() {
        return $this->summaryData('screenshots');
    }

    private function summaryData($valuesType) {
        $endDate   = now()->endOfDay();
        $startDate = now()->subDays(29)->startOfDay();

        $trackRows = Track::whereBetween('started_at', [$startDate, $endDate])
            ->selectRaw('DATE(started_at) as day, SUM(time_in_seconds) as total_seconds')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $screenshotRows = Screenshot::whereBetween('taken_at', [$startDate, $endDate])
            ->selectRaw('
            DATE(taken_at) as day,
            COUNT(*) as total_screenshots,
            COALESCE(SUM(size_in_bytes),0) as total_size_bytes,
            COUNT(DISTINCT organization_id) as total_organizations
        ')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $trackUserRows = Track::whereBetween('started_at', [$startDate, $endDate])
            ->selectRaw('DATE(started_at) as day, COUNT(DISTINCT user_id) as total_track_users')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $labels   = [];
        $values   = [];
        $infoRows = [];

        $cursor = $startDate->copy();
        $end    = $endDate->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $dayKey = $cursor->toDateString();

            $labels[] = $cursor->format('M d');
            if ($valuesType == 'screenshots') {
                $values[] = (int) ($screenshotRows[$dayKey]->total_screenshots ?? 0);
            } else {
                $values[] = (int) formatSeconds($trackRows[$dayKey]->total_seconds ?? 0);
            }

            $sizeBytes  = (int) ($screenshotRows[$dayKey]->total_size_bytes ?? 0);
            $infoRows[] = [
                'date'                => $dayKey,
                'total_screenshots'   => formatNumberShort($screenshotRows[$dayKey]->total_screenshots ?? 0),
                'total_size_mb'       => formatStorageSize($sizeBytes ?? 0),
                'total_track_users'   => formatNumberShort($trackUserRows[$dayKey]->total_track_users ?? 0),
                'total_organizations' => formatNumberShort($screenshotRows[$dayKey]->total_organizations ?? 0),
                'logged_times'        => formatSeconds($trackRows[$dayKey]->total_seconds ?? 0),
            ];

            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'info'   => $infoRows,
        ];
    }

}
