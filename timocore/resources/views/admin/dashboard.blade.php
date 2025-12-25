@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4 mb-3">
        <div class="col-xxl-3 col-sm-6">

            <x-widget style="6" link="{{ route('admin.organization.all') }}" icon="la la-building"
                title="Total Organization" value="{{ $organization['total'] }}" bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.organization.paid') }}" icon="las la-hand-holding-usd"
                title="Paid Organizations" value="{{ $organization['totalPaid'] }}" bg="success" />
        </div><!-- dashboard-w1 end -->
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.organization.unpaid') }}" icon="las la-wallet"
                title="Unpaid Organizations" value="{{ $organization['totalUnpaid'] }}" bg="info" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.organization.suspend') }}" icon="las la-ban"
                title="Suspend Organizations" value="{{ $organization['totalSuspend'] }}" bg="danger" />
        </div>
    </div>


    <div class="row gy-4 mb-3">
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Deposits')</h5>
                    <div class="widget-card-wrapper">

                        <div class="widget-card bg--success">
                            <a href="{{ route('admin.deposit.list') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($deposit['total_deposit_amount']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Deposited')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $deposit['total_deposit_pending'] }}</h6>
                                    <p class="widget-card-title">@lang('Pending Deposits')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $deposit['total_deposit_rejected'] }}</h6>
                                    <p class="widget-card-title">@lang('Rejected Deposits')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a href="{{ route('admin.deposit.list') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($deposit['total_deposit_charge']) }}</h6>
                                    <p class="widget-card-title">@lang('Deposited Charge')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Invoice')</h5>
                    <div class="widget-card-wrapper">

                        <div class="widget-card bg--info">
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($invoice['totalAmount']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--success">
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($invoice['totalPaidAmount']) }}</h6>
                                    <p class="widget-card-title">@lang('Paid Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($invoice['totalUnpaidAmount']) }}</h6>
                                    <p class="widget-card-title">@lang('Unpaid Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-file-circle-xmark"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($invoice['totalCancelledAmount']) }}</h6>
                                    <p class="widget-card-title">@lang('Cancelled Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-3">

        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.all') }}" icon="las la-users" title="Total Users"
                value="{{ $widget['total_users'] }}" bg="primary" />
        </div><!-- dashboard-w1 end -->
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.active') }}" icon="las la-user-check"
                title="Active Users" value="{{ $widget['verified_users'] }}" bg="success" />
        </div><!-- dashboard-w1 end -->
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.email.unverified') }}" icon="lar la-envelope"
                title="Email Unverified Users" value="{{ $widget['email_unverified_users'] }}" bg="danger" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="javascript:void(0)" icon="las la-clock" title="Total Tracking Users"
                value="{{ $widget['tracking_users'] }}" bg="info" />
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h5 class="card-title">@lang('Logged Hours')</h5>
                    </div>
                    <div id="loggedHoursArea"> </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h5 class="card-title">@lang('Screenshots')</h5>
                    </div>
                    <div id="screenshotsArea"> </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h5 class="card-title">@lang('Organizations')</h5>
                    </div>
                    <div id="joinOrganizationsArea"> </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.cron.index') }}" class="btn btn-outline--primary btn-sm">
        <i class="las la-server"></i>@lang('Cron Setup')
    </a>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
@endpush


@push('script')
    <script>
        "use strict";

        // Generic function to render a bar chart
        function renderBarChart(selector, labels, values, info = [], seriesName, barColor = '#FF6B00') {
            const options = {
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: seriesName,
                    data: values
                }],
                xaxis: {
                    categories: labels,
                    labels: {
                        rotate: -45
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4,
                        backgroundBarColors: ['#e0e0e0'],
                        backgroundBarOpacity: 1,
                        backgroundBarRadius: 4,
                        backgroundBarHeight: '100%',
                    }
                },
                colors: [barColor],
                dataLabels: {
                    enabled: false
                },
                tooltip: info.length > 0 ?{
                    shared: false,
                    custom: function({
                        seriesIndex,
                        dataPointIndex
                    }) {
                        const dayInfo = info[dataPointIndex] || {};
                        return `<div style="padding:10px;">
                            <strong>${labels[dataPointIndex]}</strong><br>
                            Track Users: <span class="text-dark" style="font-weight: 500">${dayInfo.total_track_users}</span><br>
                            Tracked Hours: <span class="text-dark" style="font-weight: 500">${dayInfo.logged_times}</span><br>
                            Organizations: <span class="text-dark" style="font-weight: 500">${dayInfo.total_organizations}</span><br>
                            Screenshots: <span class="text-dark" style="font-weight: 500">${dayInfo.total_screenshots}</span><br>
                            Storage Used: <span class="text-dark" style="font-weight: 500">${dayInfo.total_size_mb}</span><br>
                        </div>`;
                    }
                } : {}
            };

            const chart = new ApexCharts(document.querySelector(selector), options);
            chart.render();
        }

        // Render charts
        renderBarChart("#loggedHoursArea", @json($loggedHoursSummary['labels']), @json($loggedHoursSummary['values']),
            @json($loggedHoursSummary['info'] ?? []), 'Logged Hours');
        renderBarChart("#screenshotsArea", @json($screenshotsSummary['labels']), @json($screenshotsSummary['values']),
            @json($screenshotsSummary['info'] ?? []), 'Screenshots', '#7264f3');
        renderBarChart("#joinOrganizationsArea", @json($organizationsSummary['labels']), @json($organizationsSummary['values']),[], 'Joined Organizations', '#40ef52');
    </script>
@endpush
@push('style')
    <style>
        .apexcharts-menu {
            min-width: 120px !important;
        }
    </style>
@endpush
