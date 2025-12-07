

Here is your summary of the new members who joined {{ $organization->name }} today. This digest keeps you informed about how your team is growing. To view member details, assigned roles, and projects, please visit your TimoDesk dashboard.

<br><br>

<div  align="center">
<p style="margin-bottom:0; font-size: 20px; font-weight: bold;">New Member Summary for {{ $organization->name }}</p>
<p style="margin-top:0;">{{ $reportDate->format('D, F j, Y'); }}</p>
</div>






<!-- ################BLOCK START-->

<div style="margin:30px 0px; border: 1px solid #e6e6e6;border-radius:6px;">
<p style="margin-bottom:0; font-size: 20px; font-weight: bold; padding: 8px 16px; margin-top:0; border-bottom:1px solid #e6e6e6;"> Members Joined: <span style="text-align:right; display: inline-block; width: ;">{{ $members->count() }}</span></p>

@foreach($members as $member)

<!-- MEMBER BLOCK -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
  
  <tr>
    <td class="stack" width="100%" style="padding:12px 16px;vertical-align:middle; display:inline-block; box-sizing:border-box; border-bottom:1px solid #e6e6e6;">
      <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
        <tr>
          <td style="width:32px;height:32px;vertical-align:middle;">
            <img src="{{ $member->image_url }}" width="32" height="32" alt="" style="border-radius:50%;display:block;">
          </td>
          <td style="padding-left:12px;vertical-align:middle;">
            <p style="margin:0;font-size:18px;color:#030712;">{{ $member->fullname }}</p>
          </td>
        </tr>
      </table>
    </td>

  </tr>

</table>
<!-- END MEMBER BLOCK -->

@endforeach
</div>



<!-- ################BLOCK END -->