@extends('Template::layouts.master')
@section('content')
    <div class="table-wrapper w-100">
        <div class="table-scroller">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>@lang('Trx')</th>
                        <th>@lang('Transacted')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Post Balance')</th>
                        <th>@lang('Detail')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td>
                                <strong>{{ $trx->trx }}</strong>
                            </td>

                            <td>
                                {{ showDateTime($trx->created_at) }}<br>{{ diffForHumans($trx->created_at) }}
                            </td>

                            <td>
                                <span
                                    class="fw-bold @if ($trx->trx_type == '+') text--success @else text--danger @endif">
                                    {{ $trx->trx_type }} {{ showAmount($trx->amount) }}
                                </span>
                            </td>

                            <td>
                                {{ showAmount($trx->post_balance) }}
                            </td>


                            <td>{{ __($trx->details) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-muted text-center" colspan="100%">
                                <x-user.no-data title="No transaction history found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($transactions->hasPages())
        <div class="pagination-wrapper">
            {{ paginateLinks($transactions) }}
        </div>
    @endif
@endsection

@push('style')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush
