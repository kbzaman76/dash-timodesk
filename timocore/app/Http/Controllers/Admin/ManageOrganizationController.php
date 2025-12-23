<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Lib\BillingManager;

class ManageOrganizationController extends Controller
{
    public function all()
    {
        $pageTitle     = 'All Organizations';
        $organizations = $this->getData();
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    public function suspend()
    {
        $pageTitle     = 'Suspend Organizations';
        $organizations = $this->getData('suspend');
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }


    public function paid()
    {
        $pageTitle     = 'Paid Organizations';
        $organizations = $this->getData('paid');
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    public function unpaid()
    {
        $pageTitle     = 'Unpaid Organizations';
        $organizations = $this->getData('unpaid');
        return view('admin.organization.list', compact('pageTitle', 'organizations'));
    }

    private function getData($scope = null) {
        $query = Organization::searchable(['name'])
        ->withCount(['users' => function ($q) {
            $q->active();
        }]);
        if($scope) {
            $query->$scope();
        }
        return $query->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function detail($id)
    {
        $organization     = Organization::with('user')->findOrFail($id);
        $user             = $organization->user;
        $pageTitle        = 'Organization Detail - ' . $organization->name;
        $totalDeposit     = Deposit::where('user_id', $user->id)->successful()->sum('amount');
        $totalUsers       = User::where('organization_id', $organization->id)->active()->count();
        $countries        = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $users            = User::active()->get();
        $widgets = [
            'projects' => $organization->projects()->count(),
            'total_users' => $organization->users()->count(),
            'total_tasks' => $organization->tasks()->count(),
            'screenshots' => $organization->screenshots()->count(),
            'total_ss_size_in_bytes' => $organization->screenshots()->sum('size_in_bytes')
        ];
        $totalBillingUsers = BillingManager::totalBillingUsers($organization);
        return view('admin.organization.detail', compact('pageTitle', 'organization', 'widgets', 'user', 'totalDeposit', 'totalUsers', 'countries', 'users', 'totalBillingUsers'));
    }

    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);
        $user         = $organization->user;

        $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray = (array) $countryData;
        $countries    = implode(',', array_keys($countryArray));

        $countryCode = $request->country;
        $country     = $countryData->$countryCode->country;
        $dialCode    = $countryData->$countryCode->dial_code;

        $request->validate([
            'fullname'          => 'required|string|max:40',
            'email'             => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile'            => 'required|string|max:40',
            'country'           => 'required|in:' . $countries,
            'name'              => 'required|string|max:40',
            'organization_type' => 'required|string|max:40',
            'hear_about_us'     => 'required|string|max:40',
            'no_suspend_till'   => 'nullable|date',
        ]);

        $exists = User::where('mobile', $request->mobile)->where('dial_code', $dialCode)->where('id', '!=', $user->id)->exists();
        if ($exists) {
            $notify[] = ['error', 'The mobile number already exists.'];
            return back()->withNotify($notify);
        }

        $organization->name                 = $request->name;
        $organization->org_type             = $request->organization_type;
        $organization->org_type_describe    = $request->org_type_describe;
        $organization->hear_about_us        = $request->hear_about_us;
        $organization->hear_about_us_source = $request->hear_about_us_source;
        $organization->no_suspend_till      = $request->no_suspend_till;
        $organization->save();

        $user->mobile       = $request->mobile;
        $user->fullname     = $request->fullname;
        $user->email        = $request->email;
        $user->country_name = $country;
        $user->dial_code    = $dialCode;
        $user->country_code = $countryCode;
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id)
    {
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

}
