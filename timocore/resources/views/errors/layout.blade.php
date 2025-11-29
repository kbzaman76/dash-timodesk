<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @stack('title')
    </title>
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
