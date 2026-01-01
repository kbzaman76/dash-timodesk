@extends('Template::layouts.master')
@section('content')
    @if (!blank($members))
        <div class="table-wrapper w-100">
            <div class="table-scroller">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>@lang('Email')</th>
                            <th>@lang('Role')</th>
                            <th>@lang('Invited At')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->roleText }}</td>
                                <td>{{ showDateTime($member->created_at) }}</td>
                                <td>
                                    @php
                                        echo $member->statusBadge;
                                    @endphp
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn--sm btn-outline--danger btn--md notify-delete-btn confirmationBtn"
                                        data-title="Invitation Remove Confirmation" data-question="@lang('Are you sure to remove the invitation?')"
                                        data-action="{{ route('user.member.invitation.delete', $member->id) }}"><i
                                            class="las la-trash me-0"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($members->hasPages())
            <div class="pagination-wrapper">
                {{ paginateLinks($members) }}
            </div>
        @endif
    @else
        <div class="empty-invitation-wrapper text-center">
            <div class="empty-invitation-card">
                <img src="{{ emptyImage('no-data') }}" alt="@lang('No invitations illustration')"
                    class="empty-invitation-card__img">
                <h3 class="empty-invitation-card__title">@lang('No pending invitations')</h3>
                <p class="empty-invitation-card__text">
                    @lang('When it’s time to scale or onboard new talent, send a fresh invite and continue building stronger teams.')
                </p>
            </div>
        </div>
    @endif

    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        .empty-invitation-wrapper {
            display: flex;
            justify-content: center;
            padding: 80px 15px;
        }

        .empty-invitation-card {
            max-width: 520px;
            padding: 48px 32px;
        }

        .empty-invitation-card__img {
            max-width: 260px;
            width: 100%;
            margin: 0 auto 24px;
        }

        .empty-invitation-card__title {
            font-weight: 700;
            color: hsl(var(--heading-color));
            margin-bottom: 12px;
        }

        .empty-invitation-card__text {
            color: hsl(var(--body-color));
            margin-bottom: 24px;
        }
    </style>
@endpush

@push('breadcrumb')
    <div class="nav nav-tabs add-tab-nav">
        <a href="{{ route('user.member.list') }}" class="nav-link {{ menuActive('user.member.list') }}"> @lang('Members')
        </a>
        <a href="{{ route('user.member.pending') }}" class="nav-link {{ menuActive('user.member.pending') }}">
            @lang('Pending Email Invitations')
            @if ($totalPendingMembers)
                <span class="members-count">{{ $totalPendingMembers }}</span>
            @endif
        </a>
        <a href="{{ route('user.member.online') }}" class="nav-link {{ menuActive('user.member.online') }}">@lang('Online Members') </a>
    </div>

@endpush
