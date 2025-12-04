<?php

namespace App\Http\Controllers\Gateway;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\InvoiceManager;
use App\Models\AdminNotification;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function quickDeposit(Request $request)
    {
        $request->validate([
            'amount'      => ['required', 'numeric', 'gte:10'],
            'payment_via' => ['required', 'in:card,bkash'],
        ]);

        $amount = (float) $request->amount;

        $methodMap = [
            'card'  => ['code' => 114, 'currency' => 'USD'],
            'bkash' => ['code' => 126, 'currency' => 'BDT'],
        ];

        $selectedMethod = $methodMap[$request->payment_via] ?? null;

        if (!$selectedMethod) {
            $notify[] = ['error', __('Invalid payment selection')];
            return back()->withNotify($notify);
        }

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $selectedMethod['code'])->where('currency', $selectedMethod['currency'])->first();

        if (!$gate) {
            $notify[] = ['error', __('Selected payment method is temporarily unavailable')];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $amount || $gate->max_amount < $amount) {
            $notify[] = ['error', __('Please try with a lower amount')];
            return back()->withNotify($notify);
        }

        $charge      = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
        $charge      = 0;
        $payable     = $amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $user = auth()->user();

        $deposit                  = new Deposit();
        $deposit->user_id         = $user->id;
        $deposit->organization_id = $user->organization_id;
        $deposit->invoice_id      = 0;
        $deposit->method_code     = $gate->method_code;
        $deposit->method_currency = strtoupper($gate->currency);
        $deposit->amount          = $amount;
        $deposit->charge          = $charge;
        $deposit->rate            = $gate->rate;
        $deposit->final_amount    = $finalAmount;
        $deposit->btc_amount      = 0;
        $deposit->btc_wallet      = "";
        $deposit->trx             = getTrx();
        $deposit->success_url     = route('user.deposit.history');
        $deposit->failed_url      = route('user.deposit.history');
        $deposit->save();

        session()->put('Track', $deposit->trx);

        return to_route('user.deposit.confirm');
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount'     => 'required|numeric|gt:0',
            'gateway'    => 'required',
            'currency'   => 'required',
            'invoice_id' => 'required|integer',
        ]);

        if ($request->invoice_id) {
            Invoice::unpaid()->where('organization_id', auth()->user()->organization_id)->findOrFail($request->invoice_id);
        }

        $user = auth()->user();
        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();
        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $charge      = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $payable     = $request->amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data                  = new Deposit();
        $data->user_id         = $user->id;
        $data->organization_id = $user->organization_id;
        $data->invoice_id      = $request->invoice_id;
        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $request->amount;
        $data->charge          = $charge;
        $data->rate            = $gate->rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = getTrx();
        $data->success_url     = $request->invoice_id ? route('user.invoice.list') : route('user.deposit.history');
        $data->failed_url      = route('user.deposit.history');
        $data->save();
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function appDepositConfirm($hash)
    {
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            abort(404);
        }
        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);
        auth()->login($user);
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function depositConfirm()
    {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);

        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (isset($data->session)) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }

    public static function userDataUpdate($deposit)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user = $deposit->user;

            $organization = Organization::find($deposit->organization_id);
            $organization->balance += $deposit->amount;
            $organization->save();

            $methodName = $deposit->methodName();

            $transaction                  = new Transaction();
            $transaction->organization_id = $deposit->organization_id;
            $transaction->amount          = $deposit->amount;
            $transaction->post_balance    = $organization->balance;
            $transaction->charge          = $deposit->charge;
            $transaction->trx_type        = '+';
            $transaction->details         = 'Paid Via ' . $methodName;
            $transaction->trx             = $deposit->trx;
            $transaction->remark          = 'deposit';
            $transaction->save();

            $userNotification            = new UserNotification();
            $userNotification->user_id   = $user->id;
            $userNotification->title     = 'Deposit | Deposit successful via ' . $methodName;
            $userNotification->click_url = urlPath('user.deposit.history');
            $userNotification->save();

            if ($deposit->invoice_id) {
                $invoice = Invoice::unpaid()->find($deposit->invoice_id);
                if ($invoice) {
                    $invoiceManage = new InvoiceManager();
                    $invoiceManage->invoicePayViaBalance($invoice, $organization);
                }
            }

            $firstDeposit = Deposit::successful()->where('organization_id', $organization->id)->count();
            $referrerOrganization = $organization->referrer;

            if ($firstDeposit == 1 && $referrerOrganization) {
                $invoiceManage = new InvoiceManager();
                $invoiceManage->depositBonus($referrerOrganization, $organization, $deposit->trx);
            }

            notify($user, 'DEPOSIT_COMPLETE', [
                'method_name'     => $methodName,
                'method_currency' => $deposit->method_currency,
                'method_amount'   => showAmount($deposit->final_amount, currencyFormat: false),
                'amount'          => showAmount($deposit->amount, currencyFormat: false),
                'charge'          => showAmount($deposit->charge, currencyFormat: false),
                'rate'            => showAmount($deposit->rate, currencyFormat: false),
                'trx'             => $deposit->trx,
                'post_balance'    => showAmount($organization->balance),
            ]);
        }
    }

}
