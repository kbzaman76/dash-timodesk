@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('App Name')</th>
                                    <th>@lang('Total Usage Time')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apps as $app)
                                    <tr>
                                        <td>
                                            <x-icons.app :name="$app->app_name" />
                                            <span class="ms-2">{{ $app->app_name }}</span>
                                        </td>
                                        <td><h5>{{ formatSecondsToHoursMinutes($app->totalSeconds) }}</h5></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($apps->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($apps) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
