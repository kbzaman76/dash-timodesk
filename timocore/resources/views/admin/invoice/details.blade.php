<div class="invoice-details">
    <div class="invoice-details__header">
        <ul class="invoice-details-info">
            <li class="invoice-details-info__item">
                <span class="label">Invoice No</span>
                <span class="value">#{{ $invoice->invoice_number }}</span>
            </li>
            <li class="invoice-details-info__item">
                <span class="label">Invoice Date</span>
                <span class="value">{{ showDateTime($invoice->created_at, 'Y-m-d') }}</span>
            </li>
            <li class="invoice-details-info__item">
                <span class="label">Invoice Status</span>
                <span class="value">@php echo $invoice->status_badge @endphp</span>
            </li>
        </ul>
        <div class="invoice-details-to">
            <span class="label">Invoice To:</span>
            <ul class="list">
                <li class="list-item">
                    {{ $invoice->organization->name }}
                </li>
                @if($invoice->organization->address)
                <li class="list-item">
                    {{ $invoice->organization->address }}
                </li>
                @endif
            </ul>
        </div>
    </div>
    <div class="invoice-details__body">
        <table class="invoice-details__table">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->invoiceItems as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{!! nl2br(@$item->details) !!}</td>
                        <td>{{ showAmount($item->amount) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="100%">
                        <div class="total">
                            <span class="label">Total :</span>
                            <span class="value">{{ showAmount($invoice->amount) }}</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
