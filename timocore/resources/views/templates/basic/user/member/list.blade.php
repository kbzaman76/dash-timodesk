@extends('Template::layouts.master')
@section('content')
    <div class="table-wrapper w-100">
        <div class="table-filter">
            <div class="table-filter-left">
                <x-user.search />
            </div>
            <div class="table-filter-right">
                <div class="btn-group">
                    <button type="button" class="btn btn--md btn--secondary registrationBtn">
                        <span class="icon">
                            <x-icons.plus />
                        </span>
                        @lang('Add Member')
                    </button>
                    <a href="#offcanvasRight" data-bs-toggle="offcanvas" class="btn btn--md btn--secondary">
                        <span class="icon">
                            <x-icons.filter />

                        </span>
                        @lang('Filter')
                    </a>
                </div>
            </div>
        </div>
        <div class="table-scroller">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>@lang('Member')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Tracking Status')</th>
                        <th>@lang('Role')</th>
                        <th>@lang('Projects')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>
                                <div class="member-auth">
                                    <x-user.table-cell :user="$member" />
                                </div>
                            </td>
                            <td>
                                @php
                                    echo $member->statusBadge;
                                @endphp
                            </td>
                            <td>
                                @php
                                    echo $member->trackingStatusBadge;
                                @endphp
                            </td>
                            <td>{{ $member->roleText }}</td>
                            <td>{{ $member->projects->count() }}</td>
                            <td>
                                <div class="dropdown action-dropdown">
                                    <button class="btn btn--sm btn--secondary justify-content-center" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-item">
                                            <a class="dropdown-link"
                                                href="{{ route('user.member.details', $member->uid) }}">@lang('Details')</a>
                                        </li>
                                        @if ($member->status != Status::USER_PENDING && $member->ev == Status::VERIFIED)
                                            @if ($member->tracking_status)
                                                <li class="dropdown-item">
                                                    <a href="javascript:void(0)"
                                                        class="dropdown-link w-100 text-start confirmationBtn trackingStatusConfirm {{ $member->status == Status::USER_BAN ? 'disabled' : '' }}"
                                                        data-title="@lang('Tracking Disable Confirmation')" data-question="@lang('Are you sure you want to disable?')"
                                                        data-status-value="{{ Status::NO }}"
                                                        data-action="{{ route('user.member.tracking.status', $member->id) }}">
                                                        @lang('Disable Tracking')
                                                    </a>
                                                </li>
                                            @else
                                                <li class="dropdown-item">
                                                    <a href="javascript:void(0)"
                                                        class="dropdown-link w-100 text-start confirmationBtn trackingStatusConfirm {{ $member->status == Status::USER_BAN ? 'disabled' : '' }}"
                                                        data-title="@lang('Tracking Enable Confirmation')" data-question="@lang('Are you sure you want to enable?')"
                                                        data-status-value="{{ Status::YES }}"
                                                        data-action="{{ route('user.member.tracking.status', $member->id) }}">
                                                        @lang('Enable Tracking')
                                                    </a>
                                                </li>
                                            @endif
                                        @endif
                                        @if ($member->status == Status::USER_ACTIVE)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0)"
                                                    class="dropdown-link confirmationBtn {{ isEditDisabled($member) }}"
                                                    data-title="Member Disable Confirmation"
                                                    data-question="@lang('Are you sure to disable the member?')"
                                                    data-action="{{ route('user.member.status', $member->id) }}">@lang('Disable Member')</a>
                                            </li>
                                        @elseif ($member->status == Status::USER_PENDING)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0)" class="dropdown-link confirmationBtn"
                                                    data-title="Member Enable Confirmation"
                                                    data-question="@lang('Are you sure to approve the member?')"
                                                    data-action="{{ route('user.member.status.approve', $member->id) }}">@lang('Approve Member')</a>
                                            </li>
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0)" class="dropdown-link confirmationBtn"
                                                    data-title="Member Rejection Confirmation"
                                                    data-question="@lang('Are you sure to reject the member?')"
                                                    data-action="{{ route('user.member.status.reject', $member->id) }}">@lang('Reject Member')</a>
                                            </li>
                                        @else
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0)"
                                                    class="dropdown-link confirmationBtn {{ isEditDisabled($member) }}"
                                                    data-title="Member Enable Confirmation"
                                                    data-question="@lang('Are you sure to enable the member?')"
                                                    data-action="{{ route('user.member.status', $member->id) }}">@lang('Enable Member')</a>
                                            </li>
                                        @endif
                                        <li class="dropdown-item">
                                            <a href="{{ route('user.activity.screenshot.index', $member->uid) }}"
                                                class="dropdown-link">@lang('Screenshots')</a>
                                        </li>
                                        <li class="dropdown-item">
                                            <a href="{{ route('user.time.weekly.worklog', $member->uid) }}"
                                                class="dropdown-link">@lang('Weekly Worklog')</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="100%">
                                <x-user.no-data title="No members found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($members->hasPages())
        <div class="pagination-wrapper">
            {{ paginateLinks($members) }}
        </div>
    @endif




    {{-- add member form form --}}
    <div class="modal member__modal fade custom--modal" id="registrationModal" tabindex="-1"
        aria-labelledby="registrationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">@lang('Add Or Invite Member')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')">
                        <i class="las la-times"></i>
                    </button>
                </div>

                @if (auth()->user()->ev)
                    <div class="member__modal__list">
                        <div class="nav nav-tabs add-tab-nav custom--tab-bar">
                            <button class="nav-link add-member-link active sendInvite">@lang('Send Invitation')</button>
                            <button class="nav-link add-member-link accountCreate">@lang('Create Account')</button>
                        </div>
                    </div>

                    <div id="invitationSection" class="d-none">
                        <form action="{{ route('user.member.invitation.send') }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <h6>@lang('Invite Via Email')</h6>
                                <div id="emailSection" class="mt-3">
                                    <div class="row gy-3">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="invite_email" class="form--label">@lang('Email')</label>
                                                <input type="email" name="email[]" id="invite_email"
                                                    class="form--control md-style checkUser" data-classname="inviteEmail"
                                                    required>
                                                <small class="text--danger inviteEmail"></small>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="role" class="form--label">@lang('Role')</label>
                                                <select class="form--control sm-style select2" id="role"
                                                    name="role[]" data-minimum-results-for-search="-1" required>
                                                    <option value="{{ Status::ORGANIZER }}">@lang('Organizer')</option>
                                                    <option value="{{ Status::MANAGER }}"> @lang('Manager')</option>
                                                    <option selected value="{{ Status::STAFF }}">@lang('Staff')
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn--sm btn--secondary mt-3" id="addMoreEmail">
                                    <span class="icon">
                                        <x-icons.plus />
                                    </span>
                                    @lang('Invite Another')
                                </button>
                            </div>

                            <div class="modal-footer justify-content-between">
                                <div id="invitationLinkSeciton" class="d-none">
                                    <button class="btn btn--md btn--primary" type="button"
                                        id="showInviteLink">@lang('Or Invite Via Link')</button>
                                </div>
                                <div>
                                    <button type="button" class="btn btn--dark btn--md"
                                        data-bs-dismiss="modal">@lang('Cancel')</button>
                                    <button type="submit" class="btn btn--base btn--md">@lang('Send')</button>
                                </div>
                            </div>
                            <div id="invitelinkInputSection" class="d-none p-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form--label mb-0">@lang('Invitation Link')</label>
                                    <button type="button" class="btn btn-outline--base btn--md generateLink">
                                        @lang('Regenerate')
                                    </button>
                                </div>
                                <div class="input-group input--group copy-input-box my-3">
                                    <input type="text"
                                        value="{{ $organization->invitation_code ? route('user.invitation.join', $organization->invitation_code) : null }}"
                                        class="form-control md-style invitationLink" required readonly>

                                    <button type="button" class="text--dark pe-3" id="copyLink">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20"
                                            height="20" color="currentColor" fill="none">
                                            <path
                                                d="M9 15C9 12.1716 9 10.7574 9.87868 9.87868C10.7574 9 12.1716 9 15 9L16 9C18.8284 9 20.2426 9 21.1213 9.87868C22 10.7574 22 12.1716 22 15V16C22 18.8284 22 20.2426 21.1213 21.1213C20.2426 22 18.8284 22 16 22H15C12.1716 22 10.7574 22 9.87868 21.1213C9 20.2426 9 18.8284 9 16L9 15Z"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                            <path
                                                d="M16.9999 9C16.9975 6.04291 16.9528 4.51121 16.092 3.46243C15.9258 3.25989 15.7401 3.07418 15.5376 2.90796C14.4312 2 12.7875 2 9.5 2C6.21252 2 4.56878 2 3.46243 2.90796C3.25989 3.07417 3.07418 3.25989 2.90796 3.46243C2 4.56878 2 6.21252 2 9.5C2 12.7875 2 14.4312 2.90796 15.5376C3.07417 15.7401 3.25989 15.9258 3.46243 16.092C4.51121 16.9528 6.04291 16.9975 9 16.9999"
                                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round"></path>
                                        </svg>
                                        <span class="copyText">@lang('Copy')</span>
                                    </button>

                                </div>
                                <p class="small text--primary">
                                    <i class="las la-info-circle"></i>
                                    @lang('Generate a shareable link anyone can use to join. New users who sign up with this link will be placed in Pending status — they’ll need admin approval before they can access projects or data.')
                                </p>
                            </div>
                        </form>
                    </div>

                    <div id="accountCreateSection" class="d-none">
                        <form action="{{ route('user.member.registration') }}" method="POST">
                            @csrf
                            <div class="row gy-3 modal-body">
                                <div class="col-md-6">
                                    <input type="text" style="display:none">
                                    <input type="password" style="display:none">

                                    <div class="form-group">
                                        <label for="fullname" class="form--label">@lang('Name')</label>
                                        <input type="text" name="fullname" id="fullname"
                                            class="form--control md-style" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form--label">@lang('Email')</label>
                                        <input type="email" name="email" id="email"
                                            class="form--control md-style checkUser" data-classname="registerEmail"
                                            required autocomplete="off" autofill="off" autocomplete="new-password">
                                        <small class="text--danger registerEmail"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="filter-member-rol" class="form--label">@lang('Role')</label>
                                        <select class="form--control sm-style select2" id="filter-member-rol"
                                            name="role" data-minimum-results-for-search="-1" required>
                                            <option value="0">@lang('Select One')</option>
                                            <option value="{{ Status::ORGANIZER }}">@lang('Organizer')</option>
                                            <option value="{{ Status::MANAGER }}">@lang('Manager')</option>
                                            <option selected value="{{ Status::STAFF }}">@lang('Staff')</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="form--label">@lang('Password')</label>
                                        <div class="position-relative">
                                            <input id="password" type="password" name="password"
                                                class="form--control md-style" required />
                                            <span class="password-show-hide toggle-password"
                                                id="#password">@lang('Show')</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn--dark btn--md"
                                    data-bs-dismiss="modal">@lang('Cancel')</button>
                                <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="modal-body">
                        @include('Template::partials.email_verify_alert')
                    </div>
                @endif
            </div>
        </div>
    </div>


    {{-- filter canvas --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel"
        aria-modal="true" role="dialog">
        <div class="offcanvas-header">
            <h6 id="offcanvasRightLabel">@lang('Filter Members')</h6>
            <button type="button" class="btn--close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="las la-times"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <form action="">
                <div class="form-group">
                    <label for="filter-member-project" class="form--label">@lang('Project')</label>
                    <select class="select2 sm-style" id="filter-member-project" name="project">
                        <option value="">@lang('All')</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected($project->id == request('project'))>
                                {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-member-status" class="form--label">@lang('Status')</label>
                    <select class="select2 sm-style" id="filter-member-status" name="status"
                        data-minimum-results-for-search="-1">
                        <option value="">@lang('All')</option>
                        <option value="{{ Status::USER_ACTIVE }}" @selected(Status::USER_ACTIVE == request('status'))>@lang('Enable')</option>
                        <option value="{{ Status::USER_BAN }}" @selected(Status::USER_BAN == request('status') && request('status') != '')>@lang('Disable')</option>
                        <option value="{{ Status::USER_PENDING }}" @selected(Status::USER_PENDING == request('status'))>@lang('Pending')
                        <option value="{{ Status::USER_REJECTED }}" @selected(Status::USER_REJECTED == request('status'))>@lang('Reject')
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-member-role" class="form--label">@lang('Role')</label>
                    <select class="select2 sm-style" id="filter-member-role" name="role"
                        data-minimum-results-for-search="-1">
                        <option value="">@lang('All')</option>
                        <option value="{{ Status::ORGANIZER }}" @selected(Status::ORGANIZER == request('role'))>
                            @lang('Organizer')</option>
                        <option value="{{ Status::MANAGER }}" @selected(Status::MANAGER == request('role'))>
                            @lang('Manager')</option>
                        <option value="{{ Status::STAFF }}" @selected(Status::STAFF == request('role'))>
                            @lang('Staff')</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-member-time" class="form--label">
                        @lang('Time Tracking Status')
                    </label>
                    <select class="select2 sm-style" id="filter-member-time" name="tracking_status"
                        data-minimum-results-for-search="-1">
                        <option value="">@lang('All')</option>
                        <option value="{{ Status::YES }}" @selected(Status::YES == request('tracking_status'))>@lang('Enable')</option>
                        <option value="{{ Status::NO }}" @selected(Status::NO == request('tracking_status') && request('tracking_status') != '')>@lang('Disable')</option>
                    </select>
                </div>
                <div class="text-end">
                    <button class="btn btn--md btn--base px-5" type="submit">
                        @lang('Apply Filter')
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb')
    <div class="nav nav-tabs add-tab-nav">
        <a href="{{ route('user.member.list') }}" class="nav-link {{ menuActive('user.member.list') }}">
            @lang('Members') </a>
        <a href="{{ route('user.member.pending') }}" class="nav-link {{ menuActive('user.member.pending') }}">
            @lang('Pending Email Invitations')
            @if ($totalPendingMembers)
                <span class="members-count">{{ $totalPendingMembers }}</span>
            @endif
        </a>
    </div>
@endpush

@push('style')
    <style>
        .member__modal {
            .modal-body {
                padding-top: 0;
            }

            .form-group {
                margin-bottom: 0;
            }
        }

        .member__modal__list {
            padding: 24px 16px;
        }

        span.remveInviteEmail {
            color: hsl(var(--danger) / 0.8);
            cursor: pointer;
            font-size: 20px;
            margin-top: 27px;
        }

        input.form-control.md-style.invitationLink {
            border: 0;
            box-shadow: 0 0 0;
        }

        .add-tab-nav.custom--tab-bar.nav-tabs {
            border-bottom: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px;
            background: #f1f1f1;
            border-radius: 999px;
        }

        .add-tab-nav.custom--tab-bar .nav-link {
            border: none;
            border-radius: 999px;
            background: transparent;
            color: #9ca3af;
            padding: 0.5rem 1.4rem;
            font-weight: 500;
            font-size: 0.9rem;
            position: relative;
            transition:
                background-color 0.18s ease,
                color 0.18s ease,
                box-shadow 0.18s ease,
                transform 0.12s ease;
            outline: none;
        }

        .add-tab-nav.custom--tab-bar .nav-link:hover {
            background: rgba(148, 163, 184, 0.16);
        }

        .add-tab-nav.custom--tab-bar .nav-link:hover {
            color: #000000 !important;
        }

        .add-tab-nav.custom--tab-bar .nav-link.active:hover {
            color: #f9fafb !important;
        }

        .add-tab-nav.custom--tab-bar .nav-link.active {
            color: #f9fafb;
            background: hsl(var(--base));
            transform: translateY(-1px);
        }

        .add-tab-nav.custom--tab-bar .nav-link.active::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: inherit;
            pointer-events: none;
            opacity: 0.7;
        }

        @media (max-width: 575.98px) {
            .add__email_row {
                border-top: 1px solid hsl(var(--black)/.04);
                margin-top: 15px;
            }
            .add-tab-nav.custom--tab-bar.nav-tabs {
                width: 100%;
                justify-content: space-between;
                padding-inline: 6px;
            }

            .add-tab-nav.custom--tab-bar .nav-link {
                flex: 1 1 0;
                text-align: center;
                padding-inline: 0.4rem;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            let registrationModal = $('#registrationModal');
            let accountCreateSection = $('#accountCreateSection');
            let invitationSection = $('#invitationSection');
            let invitationLinkSeciton = $('#invitationLinkSeciton');
            let invitelinkInputSection = $('#invitelinkInputSection');
            let sendInviteBtn = $('.sendInvite');
            let accountCreateBtn = $('.accountCreate');
            const confirmationModal = $('#confirmationModal');
            const confirmationForm = confirmationModal.find('form');

            $('.add-member-link').on('click', function() {
                $('.add-member-link').removeClass('active');
                $(this).addClass('active');
            });

            $('.registrationBtn').on('click', function() {
                registrationModal.modal('show');
                accountCreateSection.addClass('d-none');
                invitationSection.removeClass('d-none');
                invitationLinkSeciton.removeClass('d-none');
                invitelinkInputSection.addClass('d-none');
                $('.add-member-link').removeClass('active');
                sendInviteBtn.addClass('active');
            });

            $('#showInviteLink').on('click', function() {
                $(this).toggleClass('shown');

                if ($(this).hasClass('shown')) {
                    $(this).text("@lang('Hide Invite Link')");
                } else {
                    $(this).text("@lang('Or Invite Via Link')");
                }

                invitelinkInputSection.toggleClass('d-none');
            });

            accountCreateBtn.on('click', function() {
                accountCreateSection.removeClass('d-none');
                invitationSection.addClass('d-none');

                let role = $('#role');

                if (!role.parent().hasClass("select2-wrapper")) {
                    role.wrap('<div class="select2-wrapper"></div>');
                }

                var config = {
                    dropdownParent: role.closest(".select2-wrapper"),
                };
                role.select2(config);
            });

            sendInviteBtn.on('click', function() {
                accountCreateSection.addClass('d-none');
                invitationSection.removeClass('d-none');
            });

            $(document).on('click', '.trackingStatusConfirm', function() {
                const statusValue = $(this).data('statusValue');
                confirmationForm.find('input[name="tracking_status"]').remove();
                if (typeof statusValue !== 'undefined') {
                    confirmationForm.append(
                        $('<input>', {
                            type: 'hidden',
                            name: 'tracking_status',
                            value: statusValue
                        })
                    );
                }
            });



            // generate link
            $('.generateLink').on('click', function() {
                let linkBtn = $(this);
                linkBtn.removeClass('btn-outline--base').addClass('btn--secondary').text("@lang('Generating…')")
                    .prop('disabled', true);

                $.ajax({
                    type: "post",
                    url: "{{ route('user.member.generate.invitation.link') }}",
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        notify('success', '@lang('Link generated successfully.')')
                        $('.invitationLink').val(data.invitationLink);
                        linkBtn.removeClass('btn--secondary').addClass('btn--base').text(
                            "@lang('Generated')").prop('disabled', false);

                        setTimeout(() => {
                            linkBtn.removeClass('btn--base').addClass('btn-outline--base')
                                .text("@lang('Regenerate')");
                        }, 1500);
                    }
                });
            });


            let inviteCount = 0;
            $('#addMoreEmail').on('click', function() {
                ++inviteCount;
                let inviteHtml = `<div class="pt-3 add__email_row">
                        <div class="row gy-3">
                            <div class="col-sm-6">
                            <div class="form-group">
                                <label for="invite_email${inviteCount}" class="form--label">@lang('Email')</label>
                                <input type="email" name="email[]" id="invite_email${inviteCount}" class="form--control md-style checkUser" data-classname="inviteEmail${inviteCount}" required>
                                <small class="text--danger inviteEmail${inviteCount}"></small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex gap-2 align-items-center justify-content-between">
                                <div class="form-group w-100">
                                    <label for="role${inviteCount}" class="form--label">@lang('Role')</label>
                                    <select class="form--control sm-style select2" id="role${inviteCount}" name="role[]"
                                        data-minimum-results-for-search="-1" required>
                                        <option value="{{ Status::ORGANIZER }}"> @lang('Organizer')</option>
                                        <option value="{{ Status::MANAGER }}"> @lang('Manager')</option>
                                        <option selected value="{{ Status::STAFF }}">@lang('Staff')</option>
                                    </select>
                                </div>
                                <span class="remveInviteEmail">
                                    <i class="las la-times"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    </div>`;
                $('#emailSection').append(inviteHtml);
                $('.select2').each(function() {
                    const $select = $(this);

                    if (!$select.parent().hasClass('select2-wrapper')) {
                        $select.wrap('<div class="select2-wrapper"></div>');
                    }

                    $select.select2({
                        dropdownParent: $select.closest('.select2-wrapper')
                    });
                });
                addRequired();
            });

            $(document).on('click', '.remveInviteEmail', function() {
                $(this).closest('.row').remove();
            });

            $(document).on('focusout', '.checkUser', function(e) {
                var email = $(this).val();
                var className = $(this).data('classname');
                checkExist(email, className)
            });

            function checkExist(email, className) {
                var data = {
                    email,
                    _token: '{{ csrf_token() }}'
                }
                var url = "{{ route('user.member.checkUser') }}";
                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $(`.${className}`).text(`${response.field} already exist`);
                    } else {
                        $(`.${className}`).text('');
                    }
                });
            }

            function addRequired() {
                $.each($('input, select, textarea'), function(i, element) {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').first().addClass('required');
                    }

                });
            }


            $(document).on('click', '#copyLink', function() {
                var copyText = document.getElementsByClassName("invitationLink");
                copyText = copyText[0];
                copyText.select();
                copyText.setSelectionRange(0, 99999);

                /*For mobile devices*/
                document.execCommand("copy");
                $('.copyText').text('Copied');
                setTimeout(() => {
                    $('.copyText').text('Copy');
                }, 2000);
            });

        })(jQuery);
    </script>
@endpush
