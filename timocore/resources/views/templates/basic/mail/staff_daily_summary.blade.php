<div  align="center">
	<p style="margin-bottom:0; font-size: 20px; font-weight: bold;">Daily Work Summary for {{ $organization->name }}</p>
	<p style="margin-top:0;">{{ $reportDate->format('D, F j, Y'); }}</p>
</div>



<table width="100%" border="0" cellpadding="10px" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; border: 1px solid #e6e6e6; border-collapse: collapse;">
	<tr>
		<td align="center" width="33.33%">
			<p style="color:#797979; font-size: 14px; text-transform: uppercase;">Total Tasks</p>
			<p style="font-size: 32px; font-weight: bold; line-height: 24px; margin-top: 0; margin-bottom:15px ;">{{ $totalTasks }}</p>
		</td>
		<td align="center" width="33.33%">
			<p style="color:#797979; font-size: 14px; text-transform: uppercase;">Hours Worked</p>
			<p style="font-size: 32px; font-weight: bold; line-height: 24px; margin-top: 0; margin-bottom:15px ;">{{ formatSecondsToHoursMinutes($totalWorked) }}</p>

		</td>
		<td align="center" width="33.33%">
			<p style="color:#797979; font-size: 14px; text-transform: uppercase;">Activity</p>
			<p style="font-size: 32px; font-weight: bold; line-height: 24px; margin-top: 0; margin-bottom:15px ;">{{ $activityPercent }}%</p>
		</td>
	</tr>
</table>







<!-- ################BLOCK START-->


<div  align="center" style="margin-top:30px;">
	<p style="margin-bottom:0; font-size: 20px; font-weight: bold; text-transform: uppercase;">Top Projects</p>
</div>

<table width="100%" border="0" cellpadding="10px" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; border: 1px solid #e6e6e6; border-collapse: collapse;">


	<tr style="background-color:#9a9a9a;">
		<td align="left" width="50%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Project Name</p>
		</td>
		<td align="center" width="25%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Worked</p>
		</td>

		<td align="center" width="25%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Activity</p>


		</td>
	</tr>

<!-- --- loop -->
@foreach ($topProjects as $topProject)
<tr>
	<td style="border: 1px solid #e6e6e6;">{{ $topProject->project->title ?? 'N/A' }}</td>
	<td style="border: 1px solid #e6e6e6;" align="center">{{ formatSecondsToHoursMinutes($topProject->totalSeconds) }}</td>
	<td style="border: 1px solid #e6e6e6;" align="center">{{ $topProject->totalSeconds > 0 ? (int) (($topProject->totalActivity / $topProject->totalSeconds)) : 0 }}%</td>
</tr>
@endforeach
<!-- --- loop -->

</table>

<!-- ################BLOCK END -->





<!-- ################BLOCK START-->


<div  align="center" style="margin-top:30px;">
	<p style="margin-bottom:0; font-size: 20px; font-weight: bold; text-transform: uppercase;">Top Tasks</p>
</div>

<table width="100%" border="0" cellpadding="10px" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; border: 1px solid #e6e6e6; border-collapse: collapse;">


	<tr style="background-color:#9a9a9a;">
		<td align="left" width="50%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Task Name</p>
		</td>
		<td align="center" width="25%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Worked</p>
		</td>
		<td align="center" width="25%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Activity</p>


		</td>
	</tr>

<!-- --- loop -->
@foreach ($topTasks as $topTask)
<tr>
	<td style="border: 1px solid #e6e6e6;">{{ $topTask->task->title ?? 'N/A' }}</td>
	<td style="border: 1px solid #e6e6e6;" align="center">{{ formatSecondsToHoursMinutes($topTask->totalSeconds) }}</td>
	<td style="border: 1px solid #e6e6e6;" align="center">{{ $topTask->totalSeconds > 0 ? (int) ($topTask->totalActivity / $topTask->totalSeconds) : 0 }}%</td>
</tr>
@endforeach
<!-- --- loop -->

</table>

<!-- ################BLOCK END -->



<!-- ################BLOCK START-->


<div  align="center" style="margin-top:30px;">
	<p style="margin-bottom:0; font-size: 20px; font-weight: bold; text-transform: uppercase;">Top Apps</p>
</div>

<table width="100%" border="0" cellpadding="10px" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; border: 1px solid #e6e6e6; border-collapse: collapse;">


	<tr style="background-color:#9a9a9a;">
		<td align="left" width="70%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">App Name</p>
		</td>
		<td align="center" width="30%">
			<p class="mb-sm" style="color:#ffffff; font-size: 16px; line-height: 10px; font-weight: bold; text-transform: uppercase; margin: 0;">Worked</p>
		</td>


	</tr>

<!-- --- loop -->
@foreach ($topApps as $topApp)
<tr>
	<td style="border: 1px solid #e6e6e6;">{{ $topApp->app_name }}</td>
	<td style="border: 1px solid #e6e6e6;" align="center">{{ formatSecondsToHoursMinutes($topApp->totalSeconds) }}</td>
</tr>
@endforeach
<!-- --- loop -->

</table>

<!-- ################BLOCK END -->