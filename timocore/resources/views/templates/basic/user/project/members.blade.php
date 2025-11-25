<div class="table-scroller">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>@lang('Member')</th>
                <th>@lang('Total Worked')</th>
                <th>@lang('Activity Percentage')</th>
                @role('manager|organizer')
                <th>@lang('Action')</th>
                @endrole
            </tr>
        </thead>
        <tbody>
            @php
                $maxSeconds = $widget['total_worked_time'];
            @endphp
            @forelse ($projectMembers as $projectUser)
                <tr class="member-row {{ $loop->iteration > 10 ? 'd-none' : '' }}" data-name="{{ toTitle($projectUser->user->fullname) }}">
                    <td>
                        <x-user.table-cell :user="$projectUser->user" />
                    </td>
                    <td>
                         @php
                            $progressPercent = $maxSeconds > 0 ? ($projectUser->total_seconds / $maxSeconds) * 100 : 0;
                        @endphp
                        <div class="flex-align gap-2">
                            <div class="progress sm-style mx-auto">
                                <div class="progress-bar bg--base" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <div class="w-100">{{ formatSeconds($projectUser->total_seconds) }}</div>
                        </div>

                    </td>
                    <td>
                        <div class="flex-align gap-2">

                            <div class="progress sm-style mx-auto">
                                <div class="progress-bar bg--base" style="width: {{ number_format($projectUser->avg_activity ?? 0, 2) }}%"></div>
                            </div>
                            <div class="w-100">{{ number_format($projectUser->avg_activity ?? 0, 2) }}%</div>
                        </div>
                    </td>
                    @role('manager|organizer')
                    <td>
                            @if ($projectUser->is_assigned)
                                <button type="button"
                                    class="btn btn-outline--danger btn--xsm confirmationBtn member-confirmation-btn"
                                    data-question="@lang('Are you sure you want to unassign this member from the project?')"
                                    data-action="{{ route('user.project.member.remove', [$project->id, $projectUser->user->id]) }}"
                                    data-mode="unassign"
                                    title="@lang('Unassign Member')">@lang('Unassign')</button>
                            @else
                                <button type="button"
                                    class="btn btn-outline--success btn--xsm confirmationBtn member-confirmation-btn"
                                    data-question="@lang('Are you sure you want to assign this member to the project?')"
                                    data-action="{{ route('user.project.assign.member', $project->id) }}"
                                    data-member-id="{{ $projectUser->user->id }}"
                                    data-mode="assign"
                                    title="@lang('Assign Member')">@lang('Assign')</button>
                            @endif
                    </td>
                    @endrole
                </tr>
                @if($loop->iteration > 10 && $loop->iteration == 11)
                    <tr class="count-rows">
                        <td colspan="4" class="text-center">
                            <a href="javascript:void(0)" id="viewAllMember">+{{ $projectMembers->count() - 10 }} more...</a>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="100%">
                        <x-user.no-data title="No members found" />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('style')
    <style>
        .progress.sm-style {
            height: 4px;
            width: 120px;
            max-width: 120px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('#viewAllMember').on('click', function(){
                $(this).closest('tr').remove();
                $('.member-row').removeClass('d-none');
            });

            $(document).on('click', '.member-confirmation-btn', function () {
                const modal = $('#confirmationModal');
                if (!modal.length) {
                    return;
                }

                const mode = $(this).data('mode');
                const memberInputSelector = '.member-confirm-input';

                if (mode === 'assign') {
                    let memberInput = modal.find(memberInputSelector);
                    if (!memberInput.length) {
                        memberInput = $('<input>', {
                            type: 'hidden',
                            name: 'member_ids[]',
                            class: 'member-confirm-input',
                        });
                        modal.find('form').append(memberInput);
                    }
                    memberInput.val($(this).data('member-id'));
                } else {
                    modal.find(memberInputSelector).remove();
                }
            });

            $(document).on('hidden.bs.modal', '#confirmationModal', function () {
                $(this).find('.member-confirm-input').remove();
            });

        })(jQuery);
    </script>
@endpush
