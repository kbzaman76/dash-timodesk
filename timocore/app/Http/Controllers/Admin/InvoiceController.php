<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function index($id = 0) {
        $pageTitle = 'Invoices';
        $invoices = Invoice::searchable(['organization:name', 'invoice_number'])->when($id, function ($query) use ($id) {
            $query->where('organization_id', $id);
        })->orderBy('id','desc')->paginate(getPaginate());

        return view('admin.invoice.list', compact('pageTitle', 'invoices'));
    }

    public function details(Request $request) {
        $invoice = Invoice::findOrFail($request->id);
        $html = view('admin.invoice.details', compact('invoice'))->render();
        return responseSuccess('invoice_details', 'Invoice details data', ['html' => $html]);
    }
}
