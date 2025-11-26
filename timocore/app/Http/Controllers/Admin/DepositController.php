<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Http\Controllers\Controller;

class DepositController extends Controller
{

    public function successful($userId = null)
    {
        $pageTitle = 'Successful Deposits';
        $deposits = $this->depositData('successful');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function initiated($userId = null)
    {
        $pageTitle = 'Initiated Deposits';
        $deposits = $this->depositData('initiated');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function deposit($organizationId = null)
    {
        $pageTitle = 'Deposit History';
        $deposits = $this->depositData($scope = null,organizationId:$organizationId);
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    protected function depositData($scope = null,$organizationId = null)
    {
        if ($scope) {
            $deposits = Deposit::$scope()->with(['user', 'gateway']);
        }else{
            $deposits = Deposit::with(['user', 'gateway']);
        }

        if ($organizationId) {
            $deposits = $deposits->where('organization_id',$organizationId);
        }

        $deposits = $deposits->searchable(['trx','user:fullname'])->dateFilter();

        $request = request();

        if ($request->method) {
            $method = Gateway::where('alias',$request->method)->firstOrFail();
            $deposits = $deposits->where('method_code',$method->code);
        }
        return $deposits->orderBy('id','desc')->paginate(getPaginate());
    }

    public function details($id)
    {
        $deposit = Deposit::where('id', $id)->with(['user', 'gateway'])->firstOrFail();
        $pageTitle = $deposit->user->fullname.' requested ' . showAmount($deposit->amount);
        $details = ($deposit->detail != null) ? json_encode($deposit->detail) : null;
        return view('admin.deposit.detail', compact('pageTitle', 'deposit','details'));
    }
}
