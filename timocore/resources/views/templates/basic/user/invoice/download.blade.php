<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TimoDesk - Invoice#{{ $invoice->invoice_number }}</title>
  {{-- <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet"> --}}

  <style>
    /* INVOICE LAYOUT START */
    *{
      padding: 0;
      margin: 0;
    }
    @page {
      size: 8.27in 11.7in;

    }
    header {
      position: fixed;
      top: 0px;
      left: 0px;
      right: 0px;
      background-image: url({{asset('assets/images/invoice/letterhead.png')}});
      background-repeat: no-repeat;
      width:100%;
      height:100%;
      background-size: cover;
      z-index: -999;
    }
    body {
      margin: 1.3in 0.5in 0.75in 0.5in;
    }
    .invoice-page-content-area {
      position: relative;
      width: 7.27in;
    }
/* INVOICE LAYOUT END */

/* INVOICE TOP START */
.invoice-page-content-area .invoice-inner .invnum-large {
  position: fixed;
  top: 170px;
  right: 150px;
}
.large-invoice-number{
  font-size: 80px;
  line-height: 80px;
  font-weight: 800;
  font-family: "Inter", sans-serif;
  color: #030712;
}

.invoice-date{
  font-size: 40px;
  line-height: 40px;
  font-weight: 600;
  font-family: "Inter", sans-serif;
  color: #030712;
  text-align: center;
}

.invoice-page-content-area .invoice-inner .top-content {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 60px;
  margin: 60px 0;
}
.invoice-page-content-area .invoice-inner .top-content .left-content {
  display: inline-block;
  border-right: 10px solid #ff6900;
  padding-right: 90px;
}
.invoice-page-content-area .invoice-inner .top-content .right-content {
  display: inline-block;
  float: right;
  width: 1.6in;
  text-align: center;
}
.invoice-page-content-area .invoice-inner .top-content .subtitle {
  font-size: 50px;
  line-height: 50px;
  font-weight: 600;
  font-family: "Urbanist", sans-serif;
  color: #030712;
}
.clientdata .name{
  font-family: "Inter", sans-serif;
  font-size: 70px;
  line-height: 90px;
  font-weight: 800;
  color: #ff6900;
}
.clientdata .address{
  font-family: "Inter", sans-serif;
  font-size: 40px;
  line-height: 40px;
  font-weight: 500;
  color: #030712;
}

.badge-middle{
  margin-top: 70px;
}

/* INVOICE TOP END */


/* BADGE/STATUS START */
.badge {
  font-family: "Urbanist", sans-serif;
  font-size: 60px;
  line-height: 60px;
  font-weight: 700;
  padding: 10px 30px !important;
  border-radius: 15px !important;
  display: inline-block;
  text-transform: capitalize;
}
.badge--success {
  background-color: rgba(40, 199, 111, 0.15);
  border: 6px solid #28c76f;
  color: #28c76f;
}
.badge--danger {
  background-color: rgba(234, 84, 85, 0.15);
  border: 6px solid #ea5455;
  color: #ea5455;
}
.badge--base {
  background-color: rgba(0, 135, 255, 0.15);
  border: 6px solid #0087ff;
  color: #0087ff;
}
/* BADGE/STATUS END */

.invoice-page-content-area .invoice-inner .bottom-content .body-content {
  padding: 90px 0 0 0;
}

/* TABLE DESIGN START */

.invoice-page-content-area .invoice-inner .bottom-content .body-content table {
  font-family: "Inter", sans-serif;
}


.invoice-page-content-area .invoice-inner .bottom-content .body-content table thead tr th {
  background-color: #030712;
  border: none;
  font-size: 46px;
  font-family: "Urbanist", sans-serif;
  font-weight: 600;
  color: #ffffff;
  padding: 12px 0;
  line-height: 46px;
}


.invoice-page-content-area .invoice-inner .bottom-content .body-content table tbody tr {
  background-color: rgb(255, 255, 255,0.05);
}

.invoice-page-content-area .invoice-inner .bottom-content .body-content table tbody tr:nth-of-type(even) {
  background-color: rgba(0, 0, 0, 0.05);
}


.invoice-page-content-area .invoice-inner .bottom-content .body-content table tbody tr td {
  font-size: 46px;
  border: none;
  padding: 30px 30px;
  line-height: 40px;
  font-weight: 400;
  font-family: "Inter", sans-serif;
}

.tablefootertotal {
  background-color: #030712;
  border: none;
  font-size: 46px !important;
  font-family: "Urbanist", sans-serif;
  font-weight: 800 !important;
  color: #ffffff;
  padding: 18px 30px 18px 0 !important;
  line-height: 48px;
  text-align: right;"
}

/* TABLE DESIGN END */


.fw-600{
  font-weight: 600 !important;
}

</style>
</head>

<body>
  <header></header>

  <div class="invoice-page-content-area">
    <div class="invoice-inner">



      <div class="invnum-large">
        <h1 class="large-invoice-number">INVOICE #{{$invoice->invoice_number}}</h1>
        <h4 class="invoice-date">INVOICE DATE : {{showDateTime($invoice->created_at, 'Y-m-d')}}</h4>
      </div>

      <div class="top-content">
        <div class="left-content" style="width:4.8in">
          <span class="subtitle">INVOICE TO</span><br>
          <div class="clientdata">
            <span class="name">{{$invoice->organization->name}}</span><br>
            <span class="address">{{$invoice->organization->address}}</span>
          </div>
        </div>
        <div class="right-content">
          <div class="badge-middle">
            @php echo $invoice->status_badge @endphp
          </div>
        </div>
      </div>


      <div class="bottom-content">
        <div class="body-content">

          <table class="table table-default" style="width: 100%;">
            <thead>
              <tr>
                <th style="text-align: center; width: 150px;">Sl#</th>
                <th style="text-align: center;">Description</th>
                <th style="text-align: center; width: 390px;">Amount</th>
              </tr>
            </thead>
            <tbody>

              @foreach ($invoice->invoiceItems as $item)
              <tr>
                <td class="fw-600">{{$loop->iteration}}</td>
                <td>{!! nl2br(@$item->details) !!}</td>
                <td class="fw-600" style="text-align: right; margin: 12px 60px; font-weight:500;">{{number_format($item->amount, 2, '.', '')}}</td>
              </tr>
              @endforeach

              <tr>
                <td colspan="2" class="tablefootertotal">Total</td>
                <td class="tablefootertotal">{{showAmount($invoice->amount)}}</td>
              </tr>

            </tbody>
          </table>

        </div>
      </div>

    </div>
  </div>
</body>
</html>
