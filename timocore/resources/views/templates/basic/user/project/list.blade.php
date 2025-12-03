@extends('Template::layouts.master')
@section('content')
    @if (!blank($projects) || request('search'))
        <div class="table-wrapper w-100">
            <div class="table-filter">
                <div class="table-filter-left">
                    <x-user.search placeholder="Project or member" />
                </div>
                @role('manager|organizer')
                    <div class="table-filter-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn--md btn--secondary projectModalBtn">
                                <span class="icon">
                                    <x-icons.plus />
                                </span>
                                @lang('Add Project')
                            </button>
                        </div>
                    </div>
                @endrole
            </div>
            <div class="table-scroller">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>@lang('Title')</th>
                            @if (!auth()->user()->isStaff())
                                <th class="text-start">@lang('Assigned Members')</th>
                            @endif
                            <th>@lang('Total Tasks')</th>
                            <th>@lang('Time Tracked')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td>
                                    <x-user.project-thumb :project="$project" />
                                </td>
                                @if (!auth()->user()->isStaff())
                                    <td class="ps-0 text-start">
                                        <div class="d-flex flex-column">
                                            @forelse ($project->users()->limit(2)->get() as $projectUser)
                                                <x-user.table-cell :user="$projectUser" />
                                            @empty
                                                <span>@lang('No Members')</span>
                                            @endforelse

                                            @if ($project->users()->count() > 2)
                                                <a class="see-all-button" data-users="{{ json_encode($project->users) }}"
                                                    href="javascript:void(0)">+{{ $project->users()->count() - 2 }}
                                                    @lang('more...')</a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                                <td>{{ $project->tasks_count }}</td>
                                <td>{{ formatSeconds($project->tracks_sum_time_in_seconds, true) }}</td>
                                <td>
                                    <a href="{{ route('user.project.details', $project->uid) }}"
                                        class="btn btn--sm btn-outline--base">
                                        @lang('Details')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <x-user.no-data title="No project found" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($projects->hasPages())
            <div class="pagination-wrapper">
                {{ paginateLinks($projects) }}
            </div>
        @endif
    @else
        <div class="empty-project-wrapper text-center">
            <div class="empty-project-card">
                <img src="{{ asset('assets/images/empty/no-project.webp') }}" alt="@lang('No project illustration')"
                    class="empty-project-card__img">
                <h3 class="empty-project-card__title">@lang('You have no project yet')</h3>
                @role('manager|organizer')
                    <p class="empty-project-card__text">
                        @lang('Start by creating a project to organize work, invite teammates, and track progress from a single place.')
                    </p>
                    <button type="button" class="btn btn--base btn--md projectModalBtn">
                        <x-icons.plus />
                        @lang('Create your first project')
                    </button>
                @else
                    <p class="empty-project-card__text">@lang('Ask your organizer or manager to assign you to a project so you can start tracking.')</p>
                @endrole
            </div>
        </div>
    @endif


    <div class="modal custom--modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModal"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Add New Project')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.project.save') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title" class="form--label">@lang('Title')</label>
                            <input class="form--control md-style" type="text" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="title" class="form--label">@lang('Icon')</label>
                            <input type="file" accept=".jpg, .jpeg, .png" class="form--control md-style" name="icon">
                            <small class="text--base d-block">
                                <i class="las la-info-circle"></i> @lang('Icon will be resized to') {{ getFilesize('project') }}px
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="title" class="form--label">@lang('Description')</label>
                            <textarea class="form--control md-style project-description-input" type="text" name="description" data-limit="255"></textarea>
                            <small class="form-text text-muted text-end mt-1">
                                <span class="description-char-remaining">255</span> @lang('characters remaining')
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <div class="flex-between gap-2 mb-2">
                                <label for="user_ids" class="form--label mb-0">@lang('Assign Members')</label>
                                <div>
                                    <button type="button" class="btn btn--secondary btn--sm" id="addAll">
                                        Add All
                                    </button>
                                    <button type="button" class="btn btn--danger btn--sm" id="removeAll">
                                        Clear All
                                    </button>
                                </div>
                            </div>
                            <select multiple name="user_ids[]" id="user_ids" class="form--control sm-style select2 user_ids">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @disabled($user->status != Status::USER_ACTIVE || $user->ev != Status::VERIFIED)>
                                        {{ toTitle($user->fullname) }} @if($user->status == Status::USER_BAN) (Banned) @endif @if($user->status == Status::USER_PENDING) (Pending) @endif @if($user->status == Status::USER_REJECTED) (Rejected) @endif @if($user->ev == Status::UNVERIFIED) (Email Unverified) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md"
                            data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal custom--modal fade" id="usersModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('Project Members')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="project-member-view">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn--sm"
                        data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.projectModalBtn').on('click', function() {
                $('#projectModal').modal('show');
            });

            const defaultDescriptionLimit = 255;
            const $descriptionInputs = $('.project-description-input');

            function updateDescriptionCounter($textarea) {
                const limit = parseInt($textarea.data('limit'), 10) || defaultDescriptionLimit;
                let value = $textarea.val() || '';

                if (value.length > limit) {
                    value = value.substring(0, limit);
                    $textarea.val(value);
                }

                $textarea.closest('.form-group').find('.description-char-remaining').text(limit - value.length);
            }

            if ($descriptionInputs.length) {
                $descriptionInputs.on('input', function() {
                    updateDescriptionCounter($(this));
                });

                $descriptionInputs.each(function() {
                    updateDescriptionCounter($(this));
                });
            }



            $('.see-all-button').on('click', function(e) {
                e.preventDefault();
                const users = $(this).data('users');
                var html = '';

                var isStaff = {{ auth()->user()->isStaff() ? 'true' : 'false' }};

                users.map(function(user) {
                    const link = "{{ route('user.member.details', ':uid') }}".replace(':uid', user
                        .uid);
                    html +=
                        `<a ${!isStaff ? `href="${link}"` : ''} class="project-member-name">${user.fullname.toLowerCase().replace(/\b\w/g, char => char.toUpperCase())}</a>`;
                });
                $('#usersModal').find('.project-member-view').html(html);
                $('#usersModal').modal('show');
            });


            function updateButtons() {
                const total = $('.user_ids option').length;
                const selected = $('.user_ids').val()?.length || 0;

                if (selected > 0) {
                    $('#removeAll').removeClass('d-none');
                } else {
                    $('#removeAll').addClass('d-none');
                }

                if (selected === total && total > 0) {
                    $('#addAll').addClass('d-none');
                } else {
                    $('#addAll').removeClass('d-none');
                }
            }

             $('#addAll').on('click', function () {
                let allValues = [];
                $('.user_ids option').each(function () {
                    if (!$(this).prop('disabled')) {
                        allValues.push($(this).val());
                    }
                });

                $('.user_ids').val(allValues).trigger('change');
                updateButtons();
            });

            $('#removeAll').on('click', function () {
                $('.user_ids').val(null).trigger('change');
                updateButtons();
            });

            $('.user_ids').on('change', function () {
                updateButtons();
            });

            updateButtons();

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .empty-project-wrapper {
            display: flex;
            justify-content: center;
            padding: 80px 15px;
        }

        .empty-project-card {
            max-width: 520px;
        }

        .empty-project-card__img {
            max-width: 260px;
            width: 100%;
            margin: 0 auto 24px;
        }

        .empty-project-card__title {
            font-weight: 700;
            color: hsl(var(--heading-color));
            margin-bottom: 12px;
        }

        .empty-project-card__text {
            color: hsl(var(--body-color));
            margin-bottom: 24px;
        }

        .project-member-view {
            display: flex;
            align-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .project-member-name {
            padding: 4px 12px;
            border-radius: 4px;
            border: 1px solid hsl(var(--black) / .15);
            font-size: 0.75rem;
            color: hsl(var(--body-color));
            background-color: hsl(var(--section-bg));
            font-weight: 600;
        }

        .project-member-name:hover {
            background-color: hsl(var(--body-color) / .1);
            color: hsl(var(--heading-color));
        }
    </style>
@endpush
