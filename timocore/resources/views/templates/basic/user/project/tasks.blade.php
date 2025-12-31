<div class="table-scroller">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>@lang('Task')</th>
                @role('manager|organizer')
                <th class="text-start">@lang('Members')</th>
                @endrole
                <th>@lang('Total Worked')</th>
                @role('manager|organizer')
                    <th>@lang('Action')</th>
                @endrole
            </tr>
        </thead>
        <tbody>
            @php
                $maxSeconds = $projectTasks->max('total_seconds');
            @endphp
            @forelse ($projectTasks as $projectTask)
                @php
                    $progressPercent = $maxSeconds > 0 ? ($projectTask->total_seconds / $maxSeconds) * 100 : 0;
                @endphp

                <tr class="task-row {{ $loop->iteration > 10 ? 'd-none' : '' }}">
                    <td>{{ $projectTask->title }}</td>
                    @role('manager|organizer')
                    <td class="text-start">
                        @forelse ($projectTask->users()->limit(2)->get() as $projectUser)
                            <x-user.table-cell :user="$projectUser" />
                            <br />
                        @empty

                            <span class="no__members icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M29.2379 27.8239C29.5243 27.2732 29.7 26.6572 29.7 26C29.7 25.81 29.69 25.62 29.67 25.43C29.4 22.02 26.67 19.29 23.26 19.02C23.07 19 22.88 18.99 22.69 18.99H20.4041L17.8365 16.4225C18.5437 16.2384 19.2133 15.9608 19.82 15.58C21.92 14.3 23.33 11.98 23.33 9.33002C23.33 5.28998 20.04 2 16 2C13.35 2 11.03 3.40997 9.76001 5.52002C9.37311 6.12616 9.09753 6.7951 8.91559 7.50153L3.70703 2.29297C3.31641 1.90234 2.68359 1.90234 2.29297 2.29297C1.90234 2.68359 1.90234 3.31641 2.29297 3.70703L28.293 29.707C28.4883 29.9023 28.7441 30 29 30C29.2559 30 29.5117 29.9023 29.707 29.707C30.0977 29.3164 30.0977 28.6836 29.707 28.293L29.2379 27.8239Z"
                                        fill="currentColor" />
                                    <path
                                        d="M9.31 18.99C5.45001 18.99 2.29999 22.13 2.29999 26C2.29999 28.21 4.09003 30 6.29999 30H25.76L14.75 18.99H9.31Z"
                                        fill="currentColor" />
                                </svg>

                                @lang('No Members')
                            </span>
                        @endforelse

                        @if ($projectTask->users()->count() > 2)
                            <a class="see-all-button ms-2" data-users="{{ json_encode($projectTask->users) }}"
                                href="javascript:void(0)">+{{ $projectTask->users()->count() - 2 }}
                                @lang('more...')</a>
                        @endif
                    </td>
                    @endrole
                    <td>
                        <div class="flex-align gap-2">
                            <div class="progress sm-style mx-auto">
                                <div class="progress-bar bg--base" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <div class="w-100">{{ formatSeconds($projectTask->total_seconds) }}</div>
                        </div>
                    </td>
                    @role('manager|organizer')
                        <td>
                            <button
                                data-action="{{ route('user.project.task.save', ['projectId' => $project->uid, 'id' => $projectTask->id]) }}"
                                data-title="{{ $projectTask->title }}" data-users="{{ json_encode($projectTask->users) }}"
                                class="btn btn--sm btn--secondary editTaskBtn" title="Edit Task">
                                <x-icons.edit />
                            </button>
                        </td>
                    @endrole
                </tr>
                @if ($loop->iteration > 10 && $loop->iteration == 11)
                    <tr class="count-rows">
                        <td colspan="4" class="text-center">
                            <a href="javascript:void(0)" id="viewAllTask">+{{ $projectTasks->count() - 10 }} more...</a>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="100%">
                        <x-user.no-data title="No tasks found" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


<div class="modal fade custom--modal" id="taskUsersModal" tabindex="-1" aria-labelledby="taskUsersModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title" id="taskUsersModalLabel">@lang('Task Members')</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="project-member-view"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--sm btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

@pushOnce('script')
    <script>
        $(document).on('click', '.see-all-button', function(e) {
            e.preventDefault();
            const users = JSON.parse($(this).attr('data-users'));
            let html = '';
            const modal = $('#taskUsersModal');
            users.map(function(user) {
                const link = "{{ route('user.member.details', ':uid') }}".replace(':uid', user.uid);
                html += `
                    <a class="project-member-name" href="${link}">
                        ${user.fullname.toLowerCase().replace(/\b\w/g, char => char.toUpperCase())}
                    </a>
                `;
            });
            modal.find('.project-member-view').html(html);
            modal.modal('show');
        });

        $(document).on('click', '.editTaskBtn', function() {
            const users = JSON.parse($(this).attr('data-users'));
            const title = $(this).attr('data-title');
            const action = $(this).attr('data-action');

            const modal = $(document).find('#taskModal');
            modal.find('.modal-title').text('{{ __('Edit Task') }}');
            modal.find('form').attr('action', action);
            modal.find('[name="task_title"]').val(title);
            const ids = users.map(u => u.uid);
            modal.find('.task_user_ids').val(ids).trigger('change');
            modal.modal('show');
        });

        $('#viewAllTask').on('click', function() {
            $(this).closest('tr').remove();
            $('.task-row').removeClass('d-none');
        });
    </script>
@endpushonce


@push('style')
    <style>
        .no__members {
            display: flex;
            align-content: center;
            gap: 5px;
        }

        .no__members svg {
            width: 20px;
            height: 20px;
            color: hsl(var(--black) / .2)
        }

        .task-info-table {
            width: 100%;
            min-width: fit-content;
        }

        .task-info-table th {
            font-size: 0.75rem !important;
            padding: 6px !important;
            padding-bottom: 0px !important;
            background-color: transparent !important;
        }

        .task-info-table td {
            font-size: 0.75rem !important;
            padding: 6px 0px !important;
        }

        .task-info-table .badge {
            width: fit-content;
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
