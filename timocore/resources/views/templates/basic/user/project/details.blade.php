@extends('Template::layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="flex-between gap-3 mb-4">
                <x-user.project-thumb :show_desc="true" :project="$project" />
                @role('manager|organizer')
                    <button data-title="{{ $project->title }}" data-description="{{ $project->description }}"
                        data-action="{{ route('user.project.save', $project->uid) }}" data-user-ids='@json($project->users->pluck('id'))'
                        class="btn btn--base btn--md editProjectBtn">
                        <x-icons.edit /> Edit
                    </button>
                @endrole
            </div>

            <div class="row gy-4 mb-4">
                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-card">
                        <div class="widget-card__body">
                            <div class="widget-card__wrapper">
                                <div class="widget-card__icon">
                                    <x-icons.calendar-v2 />
                                </div>
                                <p class="widget-card__count totalTime">{{ formatSeconds($widget['worked_today'], true) }}
                                </p>
                            </div>
                            <p class="widget-card__title">@lang('Today’s Logged Hours')</p>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-card">
                        <div class="widget-card__body">
                            <div class="widget-card__wrapper">
                                <div class="widget-card__icon">
                                    <x-icons.calendar-v2 />
                                </div>
                                <p class="widget-card__count totalTime">
                                    {{ formatSeconds($widget['total_worked_time'], true) }}
                                </p>
                            </div>
                            <p class="widget-card__title">@lang('Total Logged Hours')</p>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-card">
                        <div class="widget-card__body">
                            <div class="widget-card__wrapper">
                                <div class="widget-card__icon">
                                    <x-icons.percent />
                                </div>
                                <p class="widget-card__count">{{ number_format($widget['activity_percentage'], 2) }}%</p>
                            </div>
                            <p class="widget-card__title">@lang('Average Activity')</p>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <div class="widget-card">
                        <div class="widget-card__body">
                            <div class="widget-card__wrapper">
                                <div class="widget-card__icon">
                                    <x-icons.project />
                                </div>
                                <p class="widget-card__count">{{ $widget['total_tasks'] }}</p>
                            </div>
                            <p class="widget-card__title">@lang('Total Tasks')</p>
                        </div>
                    </div>
                </div>
            </div>
            @role('manager|organizer')
                <div class="table-wrapper mb-4">
                    <div class="table-filter justify-content-between">
                        <h6>@lang('Members')</h6>
                        <button class="btn btn--sm btn--secondary addMemberBtn">
                            <x-icons.plus />
                            @lang('Assign Members')
                        </button>
                    </div>
                    <div id="membersWrap" class="table-scroller">
                        @include('Template::user.project.members')
                    </div>
                </div>
            @endrole

            <div class="table-wrapper">
                <div class="table-filter justify-content-between">
                    <h6>@lang('Tasks')</h6>
                    @role('manager|organizer')
                        <button class="btn btn--sm btn--secondary taskModalBtn">
                            <x-icons.plus />
                            @lang('Add Task')
                        </button>
                    @endrole
                </div>
                <div id="tasksWrap" class="table-scroller">
                    @include('Template::user.project.tasks')
                </div>
            </div>
        </div>
    </div>

    {{-- Edit modal --}}
    <div class="modal custom--modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModal"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edit Project')</h5>
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
                            <label for="description" class="form--label">@lang('Description')</label>
                            <textarea name="description" class="form--control md-style project-description-input" data-limit="255"></textarea>
                            <small class="form-text text-muted text-end mt-1">
                                <span class="description-char-remaining">255</span> @lang('characters remaining')
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between mb-2">
                                <label for="user_ids" class="form--label mb-0">@lang('Assign Members')</label>
                                <div>
                                    <button type="button" class="btn btn--secondary btn--sm addAll">
                                        Add All
                                    </button>
                                    <button type="button" class="btn btn-outline--base btn--sm removeAll">Clear</button>
                                </div>
                            </div>
                            <select multiple name="user_ids[]" id="user_ids"
                                class="form--control sm-style select2 user_ids">
                                @php
                                    $selected = collect(
                                        old('user_ids', isset($project) ? $project->users->pluck('id')->all() : []),
                                    )
                                        ->map(fn($v) => (int) $v)
                                        ->all();
                                @endphp
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(in_array($user->id, $selected)) @disabled($user->status != Status::USER_ACTIVE || $user->ev != Status::VERIFIED)>
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

    <div class="modal fade custom--modal" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">
                        @lang('Add New Task')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" action="{{ route('user.project.task.save', ['projectId' => $project->id]) }}">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label class="form--label">@lang('Task Title')</label>
                            <input type="text" name="task_title" class="form--control md-style" maxlength="255"
                                required />
                        </div>
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between mb-2">
                                <label for="user_ids" class="form--label mb-0">@lang('Assign Members')</label>
                                <div>
                                    <button type="button" class="btn btn--secondary btn--sm addAll">
                                        Add All
                                    </button>
                                    <button type="button" class="btn btn-outline--base btn--sm removeAll">Clear</button>
                                </div>
                            </div>
                            <select multiple name="task_user_ids[]"
                                class="task_user_ids form--control sm-style select2 user_ids">
                                @foreach ($users->whereIn('id', $selected) as $user)
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

    @role('manager|organizer')
        <div class="modal fade custom--modal" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addMemberModalLabel">
                            @lang('Assign New Member')
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form method="post" action="{{ route('user.project.assign.member', ['projectId' => $project->id]) }}">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group mb-0">
                                <div class="d-flex justify-content-between mb-2">
                                    <label for="user_ids" class="form--label mb-0">@lang('Assign Members')</label>
                                    <div>
                                        <button type="button" class="btn btn--secondary btn--sm addAll">
                                            Add All
                                        </button>
                                        <button type="button" class="btn btn-outline--base btn--sm removeAll">Clear</button>
                                    </div>
                                </div>
                                <select multiple name="member_ids[]"
                                    class="member_ids form--control sm-style select2 user_ids" required>
                                    @foreach ($users->whereNotIn('id', $selected) as $user)
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
    @endrole

    <x-confirmation-modal />
@endsection


@push('script-lib')
    <script src="{{ asset(activeTemplate(true) . 'js/echarts.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/chart.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('#user_ids').select2({
                width: '100%',
                dropdownParent: $('#projectModal')
            });

            const defaultDescriptionLimit = 255;
            const $descriptionInputs = $('.project-description-input');

            function updateDescriptionCounter($textarea) {
                if (!$textarea.length) return;
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

            $('.editProjectBtn').on('click', function() {
                const title = $(this).data('title');
                const description = $(this).data('description');
                const action = $(this).data('action');

                const $modal = $('#projectModal');
                const $form = $modal.find('form');

                $form.attr('action', action);
                $form.find('[name="title"]').val(title);
                const $descriptionField = $form.find('[name="description"]');
                $descriptionField.val(description || '');
                updateDescriptionCounter($descriptionField);

                $modal.modal('show');
            });


            $('.taskModalBtn').on('click', function() {
                const modal = $('#taskModal');
                modal.find('.modal-title').text('{{ __('Add New Task') }}');
                modal.find('[name="task_title"]').val('');
                modal.find('.task_user_ids').val(null);
                modal.modal('show');
            });

            $('.addMemberBtn').on('click', function() {
                const modal = $('#addMemberModal');
                modal.find('.member_ids').val("").change();
                modal.modal('show');
            });


            function updateButtons(formGroup) {
                const select = formGroup.find('.user_ids');
                const total = select.find('option').length;
                const selected = select.val()?.length || 0;


                if (selected > 0) {
                    formGroup.find('.removeAll').removeClass('d-none');
                } else {
                    formGroup.find('.removeAll').addClass('d-none');
                }



                if (selected === total && total > 0) {
                    formGroup.find('.addAll').addClass('d-none');
                } else {
                    formGroup.find('.addAll').removeClass('d-none');
                }
            }

            $('.addAll').on('click', function() {

                const formGroup = $(this).closest('.form-group');
                const select = formGroup.find('.user_ids');

                let allValues = [];
                select.find('option').each(function() {
                    if (!$(this).prop('disabled')) {
                        allValues.push($(this).val());
                    }
                });

                select.val(allValues).trigger('change');
                updateButtons(formGroup);
            });



            $('.removeAll').on('click', function() {
                const formGroup = $(this).closest('.form-group');
                const select = formGroup.find('.user_ids');

                select.val(null).trigger('change');
                updateButtons(formGroup);
            });


            $('.user_ids').on('change', function() {
                const formGroup = $(this).closest('.form-group');
                updateButtons(formGroup);
            });


        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .project-content .fw-medium.text--base.fs-14 {
            font-weight: 700;
            color: hsl(var(--heading-color)) !important;
        }
    </style>
@endpush
