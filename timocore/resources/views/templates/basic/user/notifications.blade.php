@extends('Template::layouts.master')
@section('content')
    <div class="row gy-4 justify-content-center">
        <div class="col-xxl-10">
            <div class="notifications">
                <div class="notifications__header">
                    @if ($hasUnread)
                        <a href="{{ route('user.notifications.read.all') }}" class="btn btn--sm btn-outline--primary">
                            <i class="las la-check"></i>
                            @lang('Mark All As Read')
                        </a>
                    @endif
                    @if ($hasNotification)
                        <button class="btn btn--sm btn-outline--danger confirmationBtn"
                            data-action="{{ route('user.notifications.delete.all') }}"
                            data-title="Notification Delete Confirmation" data-question="@lang('Are you sure to delete all notifications?')">
                            <i class="las la-trash"></i>
                            @lang('Delete All Notification')
                        </button>
                    @endif
                </div>
                <div class="notifications__body">
                    @forelse($notifications as $notification)
                        <ul class="notifications-list">
                            <li class="notifications-list-item  @if ($notification->is_read == Status::NO) unread @endif">
                                <div class="notifications-list-item__icon">
                                    <i class="fa-regular fa-bell"></i>
                                </div>
                                <div class="notifications-list-item__content">
                                    <div class="notifications-list-item__top">
                                        <h6 class="notifications-list-item__title">
                                            <a href="{{ route('user.notification.read', $notification->id) }}">
                                                {{ toTitle($notification->sender?->fullname ?? null) }}
                                            </a>
                                        </h6>
                                        <span class="notifications-list-item__date">
                                            <i class="las la-clock"></i>
                                            {{ diffForHumans($notification->created_at) }}
                                        </span>
                                    </div>

                                    <p class="notifications-list-item__desc">
                                        {{ __($notification->title) }}
                                    </p>
                                </div>
                                <button type="button" class="notifications-list-item__delete confirmationBtn"
                                    data-title="Notification Delete Confirmation" data-question="@lang('Are you sure to delete the notification?')"
                                    data-action="{{ route('user.notifications.delete.single', $notification->id) }}">
                                    <i class="las la-trash me-0"></i>
                                </button>
                            </li>
                        </ul>
                    @empty
                        <div class="empty-notification-list text-center">
                            <div class="empty-invitation-card mx-auto">
                                <img src="{{ emptyImage('no-data') }}" alt="no notifications"
                                    class="empty-invitation-card__img">
                                <h3 class="empty-invitation-card__title">No Notifications</h3>
                                <p class="empty-invitation-card__text">
                                    You're all caught up! There are no new notifications at the moment.
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="notifications__footer">
                    @if ($notifications->hasPages())
                        <div class="pagination-wrapper mt-3">
                            {{ paginateLinks($notifications) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection


@push('style')
    <style>
        .notifications {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .notifications__header {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .notifications__body {}

        .notifications-list {
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid hsl(var(--border-color));
            background-color: hsl(var(--white));
            box-shadow: 0px 3px 5px hsl(var(--black) / 0.05)
        }

        .notifications-list-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            position: relative;
            padding: 12px;
            transition: .2s ease;
        }


        .notifications-list-item:not(:last-child) {
            padding-bottom: 16px;
            border-bottom: 1px solid hsl(var(--border-color))
        }

        .notifications-list-item__icon {
            --size: 40px;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: hsl(var(--white));
            background-color: hsl(var(--base));
            flex-shrink: 0;
            font-size: calc(var(--size) * 0.4);
        }

        @media screen and (max-width: 575px) {
            .notifications-list-item__icon {
                --size: 32px;
            }
        }

        .notifications-list-item__content {
            flex-grow: 1;
        }

        .notifications-list-item__top {
            display: flex;
            flex-wrap: wrap-reverse;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .notifications-list-item__title {
            font-size: 1rem;
            font-weight: 600;
            color: hsl(var(--black)/0.8);
        }

        @media screen and (max-width: 575px) {
            .notifications-list-item__title {
                font-size: 0.875rem;
            }
        }


        .notifications-list-item__title a::before {
            content: '';
            width: 100%;
            height: 100%;
            position: absolute;
            inset: 0;
        }

        .notifications-list-item__desc {
            max-width: 1000px;
            font-size: 0.875rem;
            color: hsl(var(--black)/0.6);
        }


        @media screen and (max-width: 575px) {
            .notifications-list-item__desc {
                font-size: 0.75rem;
            }
        }

        .notifications-list-item__date {
            font-size: 0.75rem;
            color: hhsl(var(--black)/0.5);
        }

        .notifications-list-item__date i {
            font-size: 1.1em;
        }

        .notifications-list-item__delete {
            --size: 24px;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: calc(var(--size) * 0.5);
            color: hsl(var(--base));
            border: 1px solid hsl(var(--base));
            flex-shrink: 0;
            z-index: 1;
            transition: .3s ease;
        }

        .notifications-list-item__delete:hover,
        .notifications-list-item__delete:focus {
            color: hsl(var(--white));
            background-color: hsl(var(--base));
        }

        .notifications-list-item:hover {
            background-color: hsl(var(--black)/0.025);
        }

        .notifications-list-item:hover .notifications-list-item__title a {
            color: hsl(var(--heading-color))
        }

        .notifications-list-item:hover .notifications-list-item__title a {
            color: hsl(var(--heading-color))
        }

        .notifications-list-item.unread {
            background-color: hsl(var(--base)/0.05);
            border-bottom-color: hsl(var(--base)/0.1);
        }

        .notifications-list-item.unread .notifications-list-item__title a {
            color: hsl(var(--base))
        }
    </style>
@endpush
