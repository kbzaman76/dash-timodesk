@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Active Member')</th>
                                    <th>@lang('Next Invoice Date')</th>
                                    <th>@lang('Is Suspend')</th>
                                    <th>@lang('No Suspend Till')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($organizations as $organization)
                                    <tr>
                                        <td>
                                            {{ __($organization->name) }}
                                        </td>
                                        <td>
                                            {{ $organization->users_count }}
                                        </td>

                                        <td>
                                            {{ showDateTime($organization->next_invoice_date) }}
                                        </td>

                                        <td>
                                            @php
                                                echo $organization->suspendBadge;
                                            @endphp
                                        </td>

                                        <td>
                                            {{ $organization->no_suspend ? showDateTime($organization->no_suspend) : '--' }}

                                        </td>

                                        <td>
                                            <div class="button--group">
                                            <a href="{{ route('admin.organization.detail', $organization->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="las la-desktop"></i> @lang('Details')
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($organizations->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($organizations) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


@push('breadcrumb-plugins')
    <x-search-form placeholder="Name" />
@endpush
