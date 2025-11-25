<div class="email-verification-wrapper text-center">
    <div class="icon-area">
        <i class="fa-solid fa-envelope-circle-check"></i>
    </div>
    <div class="content-area">
        <h4>@lang('Verify Your Email to Continue')</h4>
        <p class="mb-4">
            @lang('A confirmed email address is required before you can add or invite team members. This keeps your workspace secure and ensures invitations reach the right people.')
        </p>
    </div>
    <a href="{{ route('user.send.email.ver.link') }}" class="btn btn--base">
        @lang('Sent Verification Email')
    </a>
</div>

@push('style')
    <style>
        .email-verification-wrapper .icon-area i {
            font-size: 70px;
            width: 100px;
            color: hsl(var(--base));
            margin-bottom: 20px;
        }

        .email-verification-wrapper .content-area h4 {
            font-size: 30px;
            margin-bottom: 22px;
        }
    </style>
@endpush
