<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\BillingManager;
use App\Models\Coupon;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\OrganizationDiscount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class ManageOrganizationController extends Controller {
    public function all() {
        $pageTitle     = 'All Organizations';
        $organizations = $this->getData();
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    public function suspend() {
        $pageTitle     = 'Suspend Organizations';
        $organizations = $this->getData('suspend');
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    public function paid() {
        $pageTitle     = 'Paid Organizations';
        $organizations = $this->getData('paid');
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    public function unpaid() {
        $pageTitle     = 'Unpaid Organizations';
        $organizations = $this->getData('unpaid');
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    private function getData($scope = null) {
        $query = Organization::searchable(['name'])
            ->withCount(['users' => function ($q) {
                $q->active();
            }]);
        if ($scope) {
            $query->$scope();
        }
        return $query->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function detail($id) {
        $organization = Organization::with('user')->findOrFail($id);
        $user         = $organization->user;
        $pageTitle    = 'Organization Detail - ' . $organization->name;
        $totalDeposit = Deposit::where('user_id', $user->id)->successful()->sum('amount');
        $totalUsers   = User::where('organization_id', $organization->id)->active()->count();
        $countries    = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $users        = User::active()->get();
        $widgets      = [
            'projects'               => $organization->projects()->count(),
            'total_users'            => $organization->users()->count(),
            'total_tasks'            => $organization->tasks()->count(),
            'screenshots'            => $organization->screenshots()->count(),
            'total_ss_size_in_bytes' => $organization->screenshots()->sum('size_in_bytes'),
        ];

        $discount          = OrganizationDiscount::with('coupon')->active()->where('organization_id', $organization->id)->latest()->first();
        $totalBillingUsers = BillingManager::totalBillingUsers($organization);
        $totalInvoice      = Invoice::where('organization_id', $organization->id)->count();
        return view('admin.organization.detail', compact('pageTitle', 'organization', 'widgets', 'user', 'totalDeposit', 'totalUsers', 'countries', 'users', 'totalBillingUsers', 'discount', 'totalInvoice'));
    }

    public function update(Request $request, $id) {
        $organization = Organization::findOrFail($id);
        $user         = $organization->user;

        $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray = (array) $countryData;
        $countries    = implode(',', array_keys($countryArray));

        $countryCode = $request->country;
        $country     = $countryData->$countryCode->country;
        $dialCode    = $countryData->$countryCode->dial_code;

        $request->validate([
            'mobile'                     => 'required|string|max:40',
            'country'                    => 'required|in:' . $countries,
            'name'                       => 'required|string|max:40',
            'address'                    => 'nullable|string|max:255',
            'organization_type'          => 'required|string|max:40',
            'hear_about_us'              => 'required|string|max:40',
            'no_suspend_till'            => 'nullable|date',
            'hear_about_us_source'       => 'required_if:hear_about_us,Other|max:255',
            'organization_type_describe' => 'required_if:organization_type,Other|max:255',
        ]);

        $exists = User::where('mobile', $request->mobile)->where('dial_code', $dialCode)->where('id', '!=', $user->id)->exists();
        if ($exists) {
            $notify[] = ['error', 'The mobile number already exists.'];
            return back()->withNotify($notify);
        }

        $organization->name                 = $request->name;
        $organization->org_type             = $request->organization_type;
        $organization->org_type_describe    = $request->org_type_describe;
        $organization->address              = $request->address;
        $organization->hear_about_us        = $request->hear_about_us;
        $organization->hear_about_us_source = $request->hear_about_us_source;
        $organization->no_suspend_till      = $request->no_suspend_till;
        $organization->save();

        $user->mobile       = $request->mobile;
        $user->country_name = $country;
        $user->dial_code    = $dialCode;
        $user->country_code = $countryCode;
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id) {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act'    => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $organization = Organization::findOrFail($id);
        $amount       = $request->amount;
        $trx          = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $organization->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark   = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', 'Balance added successfully'];

        } else {
            if ($amount > $organization->balance) {
                $notify[] = ['error', $organization->name . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $organization->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark   = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[]       = ['success', 'Balance subtracted successfully'];
        }

        $organization->save();

        $transaction->organization_id = $organization->id;
        $transaction->amount          = $amount;
        $transaction->post_balance    = $organization->balance;
        $transaction->charge          = 0;
        $transaction->trx             = $trx;
        $transaction->details         = $request->remark;
        $transaction->save();

        notify($organization->user, $notifyTemplate, [
            'trx'          => $trx,
            'amount'       => showAmount($amount, currencyFormat: false),
            'remark'       => $request->remark,
            'post_balance' => showAmount($organization->balance, currencyFormat: false),
        ]);

        return back()->withNotify($notify);
    }

    public function applyCoupon(Request $request) {
        $request->validate([
            'organization_id' => 'required|integer|exists:organizations,id',
            'coupon_code'     => 'required|string|exists:coupons,code',
        ], [
            'coupon_code.exists' => 'The coupon code you entered is invalid.',
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)->first();
        if (!$coupon) {
            $notify[] = ['error', 'The coupon code you entered is invalid.'];
            return back()->withNotify($notify)->withInput();
        }

        if ($coupon->status == Status::DISABLE) {
            $notify[] = ['error', 'The coupon is disabled.'];
            return back()->withNotify($notify);
        }

        if ($coupon->total_used >= $coupon->max_uses) {
            $notify[] = ['error', 'This coupon has reached its maximum usage limit'];
            return back()->withNotify($notify);
        }

        // disable old active coupon
        OrganizationDiscount::active()->where('organization_id', $request->organization_id)->update(['status' => Status::DISABLE]);

        $organizationDiscount                   = new OrganizationDiscount();
        $organizationDiscount->organization_id  = $request->organization_id;
        $organizationDiscount->coupon_id        = $coupon->id;
        $organizationDiscount->coupon_code      = $coupon->code;
        $organizationDiscount->discount_percent = $coupon->discount_percent;
        $organizationDiscount->discount_months  = $coupon->discount_months;
        $organizationDiscount->remaining_months = $coupon->discount_months;
        $organizationDiscount->save();

        $coupon->total_used += 1;
        $coupon->save();

        $notify[] = ['success', 'Coupon applied successfully'];
        return back()->withNotify($notify);
    }

}
