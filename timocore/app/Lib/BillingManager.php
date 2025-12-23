<?php

namespace App\Lib;

use Carbon\Carbon;
use App\Models\User;
use App\Constants\Status;
use App\Models\BillingUser;
use Illuminate\Support\Facades\Cookie;

class BillingManager {
    public static function nextInvoiceDate($organization = null) {
        $organization = $organization ?: myOrganization();
        return now()->parse($organization->next_invoice_date);
    }

    public static function trialInfo($organization = null) {
        $organization     = $organization ?: myOrganization();
        $trialTotalDays   = Status::FREE_TRIAL_DURATION;
        $trialEnd         = $organization->trial_end_at ? Carbon::parse($organization->trial_end_at) : null;
        $trialActive      = $trialEnd && now()->lt($trialEnd);
        $trialDaysLeft    = 0;
        $trialPercentLeft = 0;
        if ($trialActive) {
            $trialDaysLeft    = max(0, number_format((float) now()->diffInDays($trialEnd) + 1, 0));
            $trialPercentLeft = $trialTotalDays > 0 ? round(($trialDaysLeft / $trialTotalDays) * 100) : 0;
        }
        return [
            'trialActive'       => $trialActive,
            'trialEnd'          => $trialEnd ? $trialEnd->toDateString() : null,
            'trialDaysLeft'    => $trialDaysLeft,
            'trialPercentLeft' => $trialPercentLeft,
        ];
    }

    public static function storeBillingUser() {
        $user         = auth()->user();
        $organization = myOrganization();
        $cookieName   = 'billing_user_' . $user->id . '_' . $organization->id;

        if (!Cookie::has($cookieName) && !self::trialInfo($organization)['trialActive']) {

            $billingUser = new BillingUser();
            $billingUser->user_id = $user->id;
            $billingUser->organization_id = $organization->id;
            $billingUser->save();

            $minutes              = now()->diffInMinutes($organization->next_invoice_date, false);
            if($minutes <= 0) {
                $minutes = now()->diffInMinutes(now()->parse($organization->next_invoice_date)->copy()->addMonth(), false);
            }
            Cookie::queue($cookieName, $billingUser->id, $minutes);
        }
    }

    public static function totalBillingUsers($organization = null) {
        $organization = $organization ?: myOrganization();
        $nextInvoiceDate = now()->parse($organization->next_invoice_date);

        $trackMembers = User::where('organization_id', $organization->id)
            ->whereHas('tracks', function ($track) use ($nextInvoiceDate) {
                $track->whereBetween('ended_at', [$nextInvoiceDate->copy()->subMonth(), $nextInvoiceDate->copy()]);
            });
        $trackMembersCount = $trackMembers->count();

        $loginUserWithoutTrack = User::whereNotIn('id', $trackMembers->pluck('id')->toArray())
            ->where('organization_id', $organization->id)
            ->whereHas('billingUsers', function ($track) use ($nextInvoiceDate) {
                $track->whereBetween('created_at', [$nextInvoiceDate->copy()->subMonth(), $nextInvoiceDate->copy()]);
            })->count();

        return $trackMembersCount + $loginUserWithoutTrack;
    }


    public static function billUserIds($organization = null) {
        $organization = $organization ?: myOrganization();
        $nextInvoiceDate = now()->parse($organization->next_invoice_date);

        $trackMembers = User::where('organization_id', $organization->id)
            ->whereHas('tracks', function ($track) use ($nextInvoiceDate) {
                $track->whereBetween('ended_at', [$nextInvoiceDate->copy()->subMonth(), $nextInvoiceDate->copy()]);
            });
        $trackMemberIds = $trackMembers->pluck('id')->toArray();

        $loginUserWithoutTrackIds = User::whereNotIn('id', $trackMembers->pluck('id')->toArray())
            ->where('organization_id', $organization->id)
            ->whereHas('billingUsers', function ($track) use ($nextInvoiceDate) {
                $track->whereBetween('created_at', [$nextInvoiceDate->copy()->subMonth(), $nextInvoiceDate->copy()]);
            })->pluck('id')->toArray();

        return array_merge($trackMemberIds, $loginUserWithoutTrackIds);
    }
}
