<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrganizationDiscount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;

class InvoiceManager
{

    public function generateInvoice($organization)
    {
        $nextInvoiceDate = now()->parse($organization->next_invoice_date);

        $pricePerUser = gs('price_per_user');
        $activeMembersCount = BillingManager::totalBillingUsers($organization);
        $totalAmount  = $activeMembersCount * $pricePerUser;

        if ($totalAmount > 0) {
            $invoice                      = new Invoice();
            $invoice->invoice_number      = $this->getInvoiceNumber();
            $invoice->organization_id     = $organization->id;
            $invoice->amount              = $totalAmount;
            $invoice->save();

            $invoiceItem             = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->amount     = $totalAmount;
            $invoiceItem->details    = 'Members monthly bill ' . $activeMembersCount . ' members x ' . showAmount($pricePerUser);
            $invoiceItem->save();

            $organizationDiscount = OrganizationDiscount::active()->where('organization_id', $organization->id)->latest()->first();

            if ($organizationDiscount) {
                $discountAmount = $totalAmount * $organizationDiscount->discount_percent / 100;

                $invoiceItem             = new InvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->amount     = -$discountAmount;
                $invoiceItem->details    = $organizationDiscount->discount_percent . ' % Discount for coupon ' . $organizationDiscount->coupon_code;
                $invoiceItem->save();

                $invoice->amount -= $discountAmount;
                $invoice->save();

                if ($organizationDiscount->discount_months != -1) {
                    $organizationDiscount->remaining_months -= 1;
                    if ($organizationDiscount->remaining_months <= 0) {
                        $organizationDiscount->status = Status::DISABLE;
                    }
                    $organizationDiscount->save();
                }
            }

            $user = $organization->user;

            $userNotification            = new UserNotification();
            $userNotification->user_id   = $user->id;
            $userNotification->title     = 'Invoice | New invoice generated';
            $userNotification->click_url = urlPath('user.invoice.list');
            $userNotification->save();

            $string = '';

            foreach ($invoice->invoiceItems as $invoiceItem) {
                $string .= $invoiceItem->details . ' : ' . showAmount($invoiceItem->amount) . '<br/>';
            }

            notify($user, 'INVOICE_GENERATED', [
                'organization_name' => $organization->name,
                'amount'            => showAmount($invoice->amount),
                'invoice_number'    => $invoice->invoice_number,
                'invoice_date'      => showDateTime(now()),
                'active_members'    => $activeMembersCount,
                'invoice_body'      => $string,
            ]);

            if ($organization->balance >= $invoice->amount) {
                $this->invoicePayViaBalance($invoice, $organization);
            }
        }

        $organization->next_invoice_date = $nextInvoiceDate->copy()->addMonth();
        $organization->save();
    }

    public function invoicePayViaBalance($invoice, $organization = null, $user = null)
    {
        $organization = $organization ?? $invoice->organization;
        $user         = $user ?? $organization->user;

        if ($invoice->amount > 0) {
            $organization->balance -= $invoice->amount;
            $organization->save();

            $trx                          = getTrx();
            $transaction                  = new Transaction();
            $transaction->organization_id = $organization->id;
            $transaction->amount          = $invoice->amount;
            $transaction->post_balance    = $organization->balance;
            $transaction->trx_type        = '-';
            $transaction->details         = 'Payment for Invoice # ' . $invoice->invoice_number;
            $transaction->trx             = $trx;
            $transaction->remark          = 'invoice_pay';
            $transaction->save();
        }

        $invoice->status = Status::INVOICE_PAID;
        $invoice->save();

        $userNotification            = new UserNotification();
        $userNotification->user_id   = $user->id;
        $userNotification->title     = 'Invoice | Invoice paid';
        $userNotification->click_url = urlPath('user.invoice.list');
        $userNotification->save();

        $string = '';

        foreach ($invoice->invoiceItems as $invoiceItem) {
            $string .= $invoiceItem->details . ' : ' . showAmount($invoiceItem->amount) . '<br/>';
        }

        notify($user, 'INVOICE_PAID', [
            'organization_name' => $organization->name,
            'amount'            => showAmount($invoice->amount),
            'invoice_number'    => $invoice->invoice_number,
            'invoice_body'      => $string,
            'paid_at'           => showDateTime(now()),
        ]);

        if ($organization->is_suspend == Status::YES) {
            $anyUnpaidInvoice = Invoice::unpaid()->where('organization_id', $organization->id)->exists();

            if (!$anyUnpaidInvoice) {
                $organization->is_suspend = Status::NO;
                $organization->save();

                notify($organization->user, 'ORGANIZATION_ACTIVE', [
                    'organization_name' => $organization->name,
                ]);
            }
        }
    }

    public function depositBonus($referrerOrganization, $organization, $trx)
    {
        $commission = gs('referral_commission');
        $referrerOrganization->balance += $commission;
        $referrerOrganization->save();

        $transaction                  = new Transaction();
        $transaction->organization_id = $referrerOrganization->id;
        $transaction->amount          = $commission;
        $transaction->post_balance    = $referrerOrganization->balance;
        $transaction->trx_type        = '+';
        $transaction->details         = 'Referral credit from ' . $organization->name;
        $transaction->trx             = $trx;
        $transaction->remark          = 'referral_credit';
        $transaction->save();

        $userNotification            = new UserNotification();
        $userNotification->user_id   = $referrerOrganization->user_id;
        $userNotification->title     = 'Commission | Referral commission received';
        $userNotification->click_url = urlPath('user.transactions');
        $userNotification->save();

        notify($referrerOrganization->user, 'REFERRAL_COMMISSION', [
            'organization_name' => $organization->name,
            'amount'            => showAmount($commission),
            'post_balance'      => showAmount($referrerOrganization->balance),
            'trx'               => $transaction->trx,
        ]);
    }

    public function applyLateFee($invoice)
    {
        $invoiceAmount = $invoice->amount;
        $lateFee       = $invoiceAmount * Status::LATE_FEE_PERCENTAGE / 100;

        $invoiceItem             = new InvoiceItem();
        $invoiceItem->invoice_id = $invoice->id;
        $invoiceItem->amount     = $lateFee;
        $invoiceItem->details    = 'Late fee applied (' . Status::LATE_FEE_PERCENTAGE . '%) at ' . showDateTime(now(), 'Y-m-d');
        $invoiceItem->save();

        $invoice->amount   = $invoice->amount + $lateFee;
        $invoice->late_fee = Status::YES;
        $invoice->save();

        $userNotification            = new UserNotification();
        $userNotification->user_id   = $invoice->organization->user_id;
        $userNotification->title     = 'Invoice | Late fee applied';
        $userNotification->click_url = urlPath('user.invoice.list');
        $userNotification->save();

        notify($invoice->organization->user, 'INVOICE_LATE_FEE', [
            'organization_name' => $invoice->organization->name,
            'amount'            => showAmount($invoiceAmount),
            'invoice_number'    => $invoice->invoice_number,
            'late_fee_amount'   => showAmount($lateFee),
            'late_fee_percent'  => showAmount(Status::LATE_FEE_PERCENTAGE, currencyFormat: false),
        ]);
    }

    private function getInvoiceNumber()
    {
        do {
            $invoiceNumber = mt_rand(100000, 999999);
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}
