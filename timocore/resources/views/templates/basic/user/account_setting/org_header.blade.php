<div class="nav nav-tabs add-tab-nav">
    @role('organizer')
    <a href="{{ route('user.account.setting.organization') }}"
        class="nav-link {{ menuActive('user.account.setting.organization') }}">@lang('Organization Setting') </a>
    <a href="{{ route('user.account.setting.referral') }}"
        class="nav-link {{ menuActive('user.account.setting.referral') }}">@lang('Referral Option') </a>
    @endrole
    <a href="{{ route('user.account.setting.profile') }}"
        class="nav-link {{ menuActive('user.account.setting.profile') }}">@lang('Profile') </a>
    <a href="{{ route('user.account.setting.change.password') }}"
        class="nav-link {{ menuActive('user.account.setting.change.password') }}">@lang('Change Password') </a>
</div>

@push('style')
    <style>
        .organization__info {
            display: grid;
            grid-template-columns: 155px 1fr;
            gap: 40px;

            @media (max-width: 622px) {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        .organization__info__item {
            position: relative;
        }

        .organization-box {
            position: relative;
            width: 155px;
            height: auto;
        }

        .organization-logo {
            border: 1px solid hsl(var(--black) / 0.1);
            border-radius: 10px;
            background-color: hsl(var(--base)/.08);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;

        }

        .upload__box {
            cursor: pointer;
            position: absolute;
            bottom: -15px;
            right: -13px;
            background: hsl(var(--base));
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            color: hsl(var(--white));

            svg {
                max-height: 50px;
                display: block;
                margin: 0 auto;
            }

            .upload__text {
                display: block;
                padding: 20px 0;
                font-size: 0.895rem;
            }
        }

        .organization__list {
            padding: 8px 0;
            font-size: 0.875rem;
            display: flex;

            span {
                padding: 0 20px;
            }
        }

        .organization__list div:nth-child(1) {
            min-width: 174px;

            @media (max-width: 991.98px) {
                min-width: 130px;
            }
        }

        .copy-input-box {
            align-items: center;

            .form--control[readonly] {
                background: none !important;
                color: hsl(var(--body-color)/.8) !important;
            }

            &:has(.form--control[readonly]) {
                box-shadow: none !important;
                background: hsl(var(--white) / 0.1) !important;
            }

            .copyText {
                font-size: .875rem;
                font-weight: 500;
                color: hsl(var(--body-color)/.9) !important;
            }
        }

        .dropdown-menu {
            --bs-dropdown-link-active-bg: hsl(var(--base));

            .dropdown-item {
                border-radius: 4px;
            }
        }

        .referral__input {
            align-items: center;
        }

        .referral__btn {
            height: 35px;
            margin-right: 4px;
            padding: 5px 9px;
            border-radius: 6px !important;
            font-size: 0.875rem !important;
        }
    </style>
@endpush
