<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\InvoiceManager;
use App\Models\Deposit;
use App\Models\GatewayCurrency;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function invoiceList()
    {
        $pageTitle = 'Invoice List';

        $invoices = Invoice::where('organization_id', organizationId());

        if(request()->status == 'unpaid'){
            $invoices = $invoices->unpaid();
        }

        $invoices = $invoices->orderBy('id', 'desc')->paginate(getPaginate());

        $bkashGateway = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', 126)->first();

        return view('Template::user.invoice.list', compact('pageTitle', 'invoices', 'bkashGateway'));
    }

    public function invoicePay(Request $request)
    {
        $request->validate([
            'invoice_id'  => 'required|integer',
            'payment_via' => 'required|in:main_balance,card,bkash',
        ]);

        $user         = auth()->user();
        $organization = $user->organization;

        $invoice = Invoice::unpaid()->where('organization_id', $user->organization_id)->findOrFail($request->invoice_id);


        if ($request->payment_via == 'main_balance') {
            if ($invoice->amount > $organization->balance) {
                $notify[] = ['error','You don\'t have sufficient balance to pay the invoice.'];
                return redirect()->back()->withNotify($notify);
            }

            $invoiceManager = new InvoiceManager();
            $invoiceManager->invoicePayViaBalance($invoice, $organization);

            $notify[] = ['success', 'Invoice has been paid successfully'];
            return back()->withNotify($notify);
        }

        $methodCode = 0;
        $currency = 'USD';

        if ($request->payment_via == 'card') {
            $methodCode =114;
        }

        if ($request->payment_via == 'bkash') {
            $methodCode =126;
            $currency = 'BDT';
        }

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $methodCode)->where('currency', $currency)->first();
        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        // $charge      = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        // $payable     = $request->amount + $charge;
        $finalAmount = $invoice->amount * $gate->rate;


        $data                  = new Deposit();
        $data->user_id         = $user->id;
        $data->organization_id = $user->organization_id;
        $data->invoice_id      = $invoice->id;
        $data->method_code     = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount          = $invoice->amount;
        $data->charge          = 0;
        $data->rate            = $gate->rate;
        $data->final_amount    = $finalAmount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = getTrx();
        $data->success_url     = route('user.invoice.list');
        $data->failed_url      = route('user.invoice.list');
        $data->save();
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');

    }

    public function invoiceDownload($invoiceNumber)
    {
        $pageTitle = 'Download Invoice';

        $invoice = Invoice::with('invoiceItems')->where('organization_id', organizationId())->where('invoice_number', $invoiceNumber)->where('status', 'paid')->firstOrFail();

        $pdf = Pdf::loadView('Template::user.invoice.download', compact('invoice', 'pageTitle'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi'              => 300,
            'isRemoteEnabled'  => true,
            'chroot'           => realpath(base_path('..')),
            'fontDir'   => storage_path('fonts'),
            'fontCache' => storage_path('fonts'),
        ]);

        return $pdf->download('Invoice-' . $invoice->invoice_number .'.pdf');
    }

}
