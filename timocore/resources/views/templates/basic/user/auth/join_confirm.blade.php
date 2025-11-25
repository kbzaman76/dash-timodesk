@extends('Template::layouts.frontend')

@section('content')
    <div class="join-confirm">
        <img class="join-confirm__thumb" src="{{ templateImage('success-circle.png') }}" alt="">
        <h4 class="join-confirm__title">
            @lang('Account Created Successfully!')
        </h4>
        <p class="join-confirm__msg">
            @lang('Your account has been created through a team invitation at')
            <span class="text--base">{{ __($organization->name) }}</span>
            @lang('Please wait while') <strong>@lang('your account is reviewed and approved by the administrator.')</strong> @lang('You’ll receive an email notification once your account has been approved.')
        </p>
    </div>
@endsection


@push('style')
    <style>
        .join-confirm {

            max-width: 536px;
            width: 100%;
            border-radius: 40px;
            background-color: hsl(var(--white));
            padding: 40px;
        }

        @media screen and (max-width: 767px) {
            .join-confirm {
                padding: 32px;
                border-radius: 32px;
            }
        }

        @media screen and (max-width: 575px) {
            .join-confirm {
                padding: 24px;
                border-radius: 24px;
            }
        }

        .join-confirm__thumb {
            --size: 200px;
            width: var(--size);
            height: var(--size);
            display: block;
            object-fit: cover;
            margin-inline: auto;
            margin-bottom: 16px;
        }

        @media screen and (max-width: 1199px) {
            .join-confirm__thumb {
                --size: 180px;
            }
        }

        @media screen and (max-width: 575px) {
            .join-confirm__thumb {
                --size: 150px;
            }
        }

        .join-confirm__title {
            color: hsl(var(--black));
            text-align: center;
            margin-bottom: 16px;
        }

        .join-confirm__msg {
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            line-height: 150%;
            color: hsl(var(--black)/0.7);
        }


        .join-confirm__msg a {
            font-weight: 700;
        }

        .join-confirm__msg strong {
            font-weight: 700;
        }

        .join-confirm__msg a:hover,
        .join-confirm__msg a:focus {
            outline: none;
            box-shadow: none;
        }

        .join-confirm__note {
            width: fit-content;
            text-align: center;
            font-size: 1.125rem;
            font-weight: 700;
            text-align: center;
            margin-top: 24px;
            margin-inline: auto;
            color: hsl(var(--base));
        }
    </style>
@endpush
