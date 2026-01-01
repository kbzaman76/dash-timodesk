<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ gs()->siteName(__($pageTitle)) }}</title>
    @stack('seo')

    <link rel="shortcut icon" type="image/png" href="{{siteFavicon()}}">
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">

    @stack('style-lib')

    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/main.css') }}?v=1.1.1">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/custom.css') }}">

    @stack('style')

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5B584FWP');</script>
    <!-- End Google Tag Manager -->


</head>
<body>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5B584FWP" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!--==================== Overlay Start ====================-->
    <div class="body-overlay"></div>
    <!--==================== Overlay End ====================-->

    <!--==================== Sidebar Overlay End ====================-->
    <div class="sidebar-overlay"></div>
    <!--==================== Sidebar Overlay End ====================-->

    <!-- ==================== Scroll to Top End Here ==================== -->
    <!-- <a class="scroll-top"><i class="fas fa-angle-up"></i></a> -->
    <!-- ==================== Scroll to Top End Here ==================== -->

    @stack('fbComment')

    @yield('main')

    <!-- Optional JavaScript -->
    <script src="{{ asset('assets/global/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/sticky-sidebar.js') }}"></script>

    @stack('script-lib')
    @pushIf(auth()->check() && (auth()->user()->isOrganizer() || auth()->user()->isManager()), 'script')
        <script>
            var TOKEN_ROUTE = "{{ route('user.token') }}";
        </script>
        <script src="{{ asset(activeTemplate(true) . 'js/socket.io.min.js') }}"></script>
        <script src="{{ asset(activeTemplate(true) . 'js/socket.js') }}"></script>
    @endpushif
    <script src="{{ asset(activeTemplate(true) . 'js/main.js') }}?v=1.1.1"></script>

    @php echo loadExtension('tawk-chat') @endphp

    @include('partials.notify')

    @if (gs('pn'))
        @include('partials.push_script')
    @endif
    @stack('script')



    <script>
        (function($) {
            "use strict";


            var inputElements = $('[type=text],[type=password],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input, select, textarea'), function(i, element) {
                var elementType = $(element);
                if (elementType.attr('type') != 'checkbox') {
                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').addClass('required');
                    }
                }
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                '[title], [data-title], [data-bs-title]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });


            let disableSubmission = false;
            $('.disableSubmission').on('submit', function(e) {
                if (disableSubmission) {
                    e.preventDefault()
                } else {
                    disableSubmission = true;
                }
            });

        })(jQuery);
    </script>

</body>

</html>
