@php
    $text = isset($register) ? 'Register' : 'Login';
    $socials = [
        'google'   => ['status' => gs('socialite_credentials')->google->status ?? null, 'icon' => 'google.svg', 'label' => 'Google'],
        'facebook' => ['status' => gs('socialite_credentials')->facebook->status ?? null, 'icon' => 'facebook.svg', 'label' => 'Facebook'],
        'linkedin' => ['status' => gs('socialite_credentials')->linkedin->status ?? null, 'icon' => 'linkdin.svg', 'label' => 'Linkedin'],
    ];
@endphp

@php
    // Check if at least one social login is enabled
    $hasSocialLogin = collect($socials)->contains(fn($s) => $s['status'] == Status::ENABLE);
@endphp

@if($hasSocialLogin)
<div class="social-auth">
    <ul class="social-auth__list">
        @foreach($socials as $key => $social)
            @if($social['status'] == Status::ENABLE)
                <li class="social-auth__item mb-3">
                    <a href="{{ route('user.social.login', $key) }}" class="social-auth__link">
                        <img src="{{ asset(activeTemplate(true)."images/{$social['icon']}") }}" alt="{{ $social['label'] }}">
                        @lang("$text with {$social['label']}")
                    </a>
                </li>
            @endif
        @endforeach
    </ul>

    <div class="social-auth-title">
        <span>@lang('Or Login With')</span>
    </div>
</div>
@endif
