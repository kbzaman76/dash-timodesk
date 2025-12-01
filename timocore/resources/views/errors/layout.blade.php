<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @stack('title')
    </title>
    
    <meta property="og:image" content="https://timodesk.com/assets/images/seo.png">

    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1180">
    <meta property="og:image:height" content="600">
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/errors/css/main.css') }}">
</head>

<body>
    <div class="error">
        <div class="container">
            @yield('content')
        </div>
    </div>
</body>

</html>



@php
    if (isset($exception)) {
        $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 'N/A';
        $url    = request()->fullUrl();
        $ip     = getRealIP();
        $logLine = "[" . now() . "] Status: {$status}, URL: {$url}, IP: {$ip}" . PHP_EOL;
        $filePath = 'assets/error_logs/'.date('Y-m-d').'.txt';
        file_put_contents($filePath, $logLine, FILE_APPEND);
    }
@endphp
