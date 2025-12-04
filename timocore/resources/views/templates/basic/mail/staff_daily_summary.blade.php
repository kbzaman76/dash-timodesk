<div align="center">
    <p style="margin-bottom:0; font-size: 20px; font-weight: bold;">Daily Work Summary for {{ $organization->name }}</p>
    <p style="margin-top:0;">{{ $reportDate->format('D, F j, Y') }}</p>
</div>



<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;">
    <tr>
        <td align="center" style="">
            <!-- inner wrapper -->
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"
                style="width:100%; border:1px solid #e6e6e6;border-radius:2px;">
                <!--[if mso]>
          <tr>
            <td>
          <![endif]-->
                <tr>
                    <!-- Column 1 -->
                    <td class="stack" width="33.33%" valign="top"
                        style="width:33.33%; padding:10px; text-align:center; border-right:1px solid #e6e6e6; border-left:1px solid #e6e6e6;">
                        <div style="padding:0;">
                            <p style="color:#797979; font-size:14px; text-transform:uppercase; margin:0 0 10px;">Total
                                Task</p>
                            <p class="big-number"
                                style="font-size:32px; font-weight:700; line-height:24px; margin:0 0 15px;">
                                {{ $totalTasks }}</p>
                        </div>
                    </td>

                    <!-- Column 2 -->
                    <td class="stack" width="33.33%" valign="top"
                        style="width:33.33%; padding:10px; text-align:center; border-right:1px solid #e6e6e6; border-left:1px solid #e6e6e6;">
                        <div style="padding:0;">
                            <p style="color:#797979; font-size:14px; text-transform:uppercase; margin:0 0 10px;">Hours
                                Worked</p>
                            <p class="big-number"
                                style="font-size:32px; font-weight:700; line-height:24px; margin:0 0 15px;">
                                {{ formatSecondsToHoursMinutes($totalWorked) }}</p>
                        </div>
                    </td>

                    <!-- Column 3 -->
                    <td class="stack" width="33.33%" valign="top"
                        style="width:33.33%; padding:10px; text-align:center; border-right:1px solid #e6e6e6; border-left:1px solid #e6e6e6;">
                        <div style="padding:0;">
                            <p style="color:#797979; font-size:14px; text-transform:uppercase; margin:0 0 10px;">Average
                                Activity</p>
                            <p class="big-number"
                                style="font-size:32px; font-weight:700; line-height:24px; margin:0 0 15px;">
                                {{ $activityPercent }}%</p>
                        </div>
                    </td>
                </tr>

                <!--[if mso]>
            </td>
          </tr>
          <![endif]-->
            </table>
        </td>
    </tr>
</table>



<!-- ################BLOCK START-->
<div style="margin:30px 0px; border: 1px solid #e6e6e6;border-radius:6px;">
    <p
        style="margin-bottom:0; font-size: 20px; font-weight: bold; padding: 8px 16px; margin-top:0; margin-bottom:20px; border-bottom:1px solid #e6e6e6;">
        Top Projects</p>

    @php
        $totalProjectSeconds = $topProjects->sum('totalSeconds');
        $projectMultiplier = 80 / ($topProjects->max('totalSeconds') / $totalProjectSeconds);
    @endphp
    @foreach ($topProjects as $topProject)
        <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">

            <tr>
                <td class="stack" width="388px"
                    style="padding:5px 16px;vertical-align:middle; display:inline-block; box-sizing:border-box;">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                        <tr>
                            <td style="width:32px;height:32px;vertical-align:middle;">
                                @if ($topProject->project->icon_url)
                                    <img src="{{ $topProject->project->icon_url }}" width="32" height="32"
                                        alt="" style="border-radius:50%;display:block;">
                                @else
                                    <span width="32" height="32" alt=""
                                        style="width: 32px;height:32px; border-radius:50%;display:block; background: {{ $topProject->project->color ? $topProject->project->color->bg : getSweetColors()['bg'] }}; font-size: 14px; line-height: 32px; text-align: center;vertical-align:middle; color: {{ $topProject->project->color ? $topProject->project->color->text : getSweetColors()['text'] }}">{{ $topProject->project->title[0] }}</span>
                                @endif
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                                <p style="margin:0;font-size:16px;color:#030712;">
                                    {{ $topProject->project->title ?? 'N/A' }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- RIGHT SIDE / Totals -->
                <td class="stack" width="240px"
                    style="padding:5px 16px;vertical-align:middle;display:inline-block; box-sizing:border-box;">

                    <div>
                        <div style="width:48%; display: inline-block; text-align: left;">
                            @if ($topProject->totalSeconds > 60)
                                <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">
                                    {{ formatSecondsToHoursMinutes($topProject->totalSeconds) }}
                                    <span style="font-weight:400;font-size:13px;color:#797979;">Hours</span>
                                </p>
                            @else
                                <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">&lt; 1
                                    <span style="font-weight:400;font-size:13px;color:#797979;">Minute</span>
                                </p>
                            @endif

                        </div>
                        <div style="width:48%; display: inline-block; text-align: right;">
                            <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">
                                {{ $topProject->totalSeconds > 0 ? (int) ($topProject->totalActivity / $topProject->totalSeconds) : 0 }}%
                                <span style="font-weight:400;font-size:13px;color:#797979;">Activity</span>
                            </p>
                        </div>
                    </div>

                </td>
            </tr>

            <!-- PROGRESS BAR ROW -->
            <tr>
                <td colspan="2" style="padding:5px 16px 24px 16px;width:100%;">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                        <tr>
                            <td>
                                <div style="background:#e6e6e6;width:100%;height:6px;border-radius:4px;">
                                    <!-- Set the width to the activity percent -->
                                    <div
                                        style="background:#ff6900;width:{{ floor(($topProject->totalSeconds / $totalProjectSeconds) * $projectMultiplier) }}%;height:6px;border-radius:4px 0 0 4px;">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    @endforeach
</div>
<!-- ################BLOCK END -->





<!-- ################BLOCK START-->


<div style="margin:30px 0px; border: 1px solid #e6e6e6;border-radius:6px;">
    <p
        style="margin-bottom:0; font-size: 20px; font-weight: bold; padding: 8px 16px; margin-top:0; margin-bottom:20px; border-bottom:1px solid #e6e6e6;">
        Top Tasks</p>

    @php
        $totalTaskSeconds = $topTasks->sum('totalSeconds');
        $taskMultiplier = 80 / ($topTasks->max('totalSeconds') / $totalTaskSeconds);
    @endphp
    @foreach ($topTasks as $topTask)
        <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">

            <tr>
                <td class="stack" width="388px"
                    style="padding:5px 16px;vertical-align:middle; display:inline-block; box-sizing:border-box;">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                        <tr>
                            <td style="padding-left:0px;vertical-align:middle;">
                                <p style="margin:0;font-size:16px;color:#030712;">{{ $topTask->task->title ?? 'N/A' }}
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- RIGHT SIDE / Totals -->
                <td class="stack" width="240px"
                    style="padding:5px 16px;vertical-align:middle;display:inline-block; box-sizing:border-box;">

                    <div>
                        <div style="width:48%; display: inline-block; text-align: left;">
                          @if ($topTask->totalSeconds > 60)
                            <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">
                                {{ formatSecondsToHoursMinutes($topTask->totalSeconds) }}
                                <span style="font-weight:400;font-size:13px;color:#797979;">Hours</span>
                            </p>
                            @else
                                <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">&lt; 1
                                    <span style="font-weight:400;font-size:13px;color:#797979;">Minute</span>
                                </p>
                            @endif

                        </div>
                        <div style="width:48%; display: inline-block; text-align: right;">
                            <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">
                                {{ $topTask->totalSeconds > 0 ? (int) ($topTask->totalActivity / $topTask->totalSeconds) : 0 }}%
                                <span style="font-weight:400;font-size:13px;color:#797979;">Activity</span>
                            </p>
                        </div>
                    </div>

                </td>
            </tr>

            <!-- PROGRESS BAR ROW -->
            <tr>
                <td colspan="2" style="padding:5px 16px 24px 16px;width:100%;">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                        <tr>
                            <td>
                                <div style="background:#e6e6e6;width:100%;height:6px;border-radius:4px;">
                                    <!-- Set the width to the activity percent -->
                                    <div
                                        style="background:#ff6900;width:{{ floor(($topTask->totalSeconds / $totalTaskSeconds) * $taskMultiplier) }}%;height:6px;border-radius:4px 0 0 4px;">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    @endforeach
</div>

<!-- ################BLOCK END -->



<!-- ################BLOCK START-->

<div style="margin:30px 0px; border: 1px solid #e6e6e6;border-radius:6px;">
    <p
        style="margin-bottom:0; font-size: 20px; font-weight: bold; padding: 8px 16px; margin-top:0; margin-bottom:20px; border-bottom:1px solid #e6e6e6;">
        Top Apps</p>


    @php
        $totalAppSeconds = $topApps->sum('totalSeconds');
        $appMultiplier = 80 / ($topApps->max('totalSeconds') / $totalAppSeconds);
    @endphp
    @foreach ($topApps as $topApp)
        <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
            <tr>
                <td class="stack" width="388px"
                    style="padding:5px 16px;vertical-align:middle; display:inline-block; box-sizing:border-box;">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                        <tr>
                            <td style="width:32px;height:32px;vertical-align:middle;">
                                <img src="{{ asset('assets/images/apps/' . getApps($topApp->app_name) . '.png') }}"
                                    width="32" height="32" alt="" style="display:block;">
                            </td>
                            <td style="padding-left:12px;vertical-align:middle;">
                                <p style="margin:0;font-size:16px;color:#030712;">{{ $topApp->app_name }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- RIGHT SIDE / Totals -->
                <td class="stack" width="240px"
                    style="padding:5px 16px;vertical-align:middle;display:inline-block; box-sizing:border-box;">

                    <div>
                        <div style="width:100%; display: inline-block; text-align: left;">
                          @if ($topApp->totalSeconds > 60)
                            <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">
                                {{ formatSecondsToHoursMinutes($topApp->totalSeconds) }}
                                <span style="font-weight:400;font-size:13px;color:#797979;">Hours</span>
                            </p>
                            @else
                                <p style="margin:0;font-size:16px;font-weight:700;color:#030712;">&lt; 1
                                    <span style="font-weight:400;font-size:13px;color:#797979;">Minute</span>
                                </p>
                            @endif
                        </div>
                    </div>

                </td>
            </tr>

            <!-- PROGRESS BAR ROW -->
            <tr>
                <td colspan="2" style="padding:5px 16px 24px 16px;width:100%;">
                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
                        <tr>
                            <td>
                                <div style="background:#e6e6e6;width:100%;height:6px;border-radius:4px;">
                                    <!-- Set the width to the activity percent -->
                                    <div
                                        style="background:#ff6900;width:{{ floor(($topApp->totalSeconds / $totalAppSeconds) * $appMultiplier) }}%;height:6px;border-radius:4px 0 0 4px;">
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    @endforeach
</div>
<!-- ################BLOCK END -->
