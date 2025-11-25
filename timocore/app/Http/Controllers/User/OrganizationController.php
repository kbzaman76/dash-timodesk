<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Organization;
use App\Models\OrganizationDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function userOrganization()
    {
        $user = auth()->user();

        if ($user->organization_id) {
            return to_route('user.home');
        }

        $mobileCode = @$_SERVER['HTTP_CF_IPCOUNTRY'] ?? getIpInfo()['code'][0];
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $pageTitle = 'Add Your Organization';
        return view('Template::user.user_organization', compact('pageTitle', 'user', 'countries', 'mobileCode'));
    }

    public function userOrganizationSubmit(Request $request)
    {
        $user = auth()->user();

        if ($user->organization_id) {
            return to_route('user.home');
        }

        $validOrganizationTypes  = array_values(organizationTypes());
        $validHearAboutUsOptions = array_values(hearAboutUsOptions());

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'organization_name'          => 'required|string|max:255',
            'country_code'               => 'required|in:' . $countryCodes,
            'country'                    => 'required|in:' . $countries,
            'mobile_code'                => 'required|in:' . $mobileCodes,
            'mobile'                     => ['required', 'regex:/^([0-9]*)$/', Rule::unique('users')->where('dial_code', $request->mobile_code)],
            'organization_type'          => ['required', Rule::in($validOrganizationTypes)],
            'organization_type_describe' => 'required_if:organization_type,Other|max:255',
            'hear_about_us'              => ['required', Rule::in($validHearAboutUsOptions)],
            'hear_about_us_source'       => 'required_if:hear_about_us,Other|max:255',
            'coupon'                     => 'nullable|string',
        ]);

        $coupon = null;

        if ($request->coupon) {
            $coupon = Coupon::active()->where('code', $request->coupon)->first();
            if (!$coupon) {
                $notify[] = ['error', 'The coupon code you entered is invalid.'];
                return back()->withNotify($notify)->withInput();
            }

            if ($coupon->total_used >= $coupon->max_uses) {
                $notify[] = ['error', 'Coupon expired'];
                return back()->withNotify($notify);
            }
        }

        $user               = auth()->user();
        $user->country_code = $request->country_code;
        $user->mobile       = $request->mobile;
        $user->country_name = isset($request->country) ? $request->country : '';
        $user->dial_code    = $request->mobile_code;

        $referralCode = Cookie::get('referral_code');
        $referrer     = null;
        if ($referralCode) {
            $referrer = Organization::where('referral_code', $referralCode)->active()->first();
        }

        $referralCode = $this->getReferralCode();
        $ipInfo = getIpInfo();
        $timezone = @$ipInfo['timezone'][0] ?? 'UTC';

        $organization                       = new Organization();
        $organization->user_id              = $user->id;
        $organization->referred_by          = $referrer ? $referrer->id : 0;
        $organization->referral_code        = $referralCode;
        $organization->name                 = $request->organization_name;
        $organization->org_type             = $request->organization_type;
        $organization->org_type_describe    = $request->organization_type_describe;
        $organization->hear_about_us        = $request->hear_about_us;
        $organization->hear_about_us_source = $request->hear_about_us_source;
        $organization->trial_end_at         = now()->addDays(Status::FREE_TRIAL_DURATION);
        $organization->next_invoice_date    = getBillingDate($organization->trial_end_at->copy()->addMonth());
        $organization->billing_day          = $organization->next_invoice_date->copy()->day;
        $organization->invitation_code      = strtolower(getTrx(35));
        $organization->uid                  = getUid(modelName: 'Organization');
        $organization->timezone             = $timezone;
        $organization->save();

        $user->organization_id = $organization->id;
        $user->save();

        if ($coupon) {
            $organizationDiscount                   = new OrganizationDiscount();
            $organizationDiscount->organization_id  = $organization->id;
            $organizationDiscount->coupon_id        = $coupon->id;
            $organizationDiscount->coupon_code      = $coupon->code;
            $organizationDiscount->discount_percent = $coupon->discount_percent;
            $organizationDiscount->discount_months  = $coupon->discount_months;
            $organizationDiscount->remaining_months = $coupon->discount_months;
            $organizationDiscount->save();

            $coupon->total_used += 1;
            $coupon->save();
        }

        return to_route('user.home');
    }

    private function getReferralCode()
    {
        $referralCode = strtolower(getTrx(6));
        $exists       = Organization::where('referral_code', $referralCode)->exists();
        if ($exists) {
            $this->getReferralCode();
        }
        return $referralCode;
    }

    public function checkCoupon(Request $request)
    {
        $coupon = Coupon::active()->where('code', $request->coupon)->first();

        if (!$coupon) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid Coupon',
            ]);
        }

        $discountAmount = gs('price_per_user') * $coupon->discount_percent / 100;

        return response()->json([
            'status'  => 'success',
            'message' => 'Coupon applied. You get ' . showAmount($discountAmount) . ' discount per user.',
        ]);

    }
}
