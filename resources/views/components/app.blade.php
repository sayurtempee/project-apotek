<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | {{ $project }}</title>
</head>

<body class="min-h-screen bg-gray-50">
    <div class="bg-white/80">
        {{--  @if (!request()->routeIs('login', 'register'))
            @include('layouts.sidebar')
        @endif  --}}
        <div
            class="@yield('bodyClass', 'mt-16 p-6 font-sans')
                   {{ request()->routeIs('cart.index', 'login', 'order.invoice', 'home') ? '' : 'lg:ml-64' }}">
            @yield('content')
        </div>
    </div>
</body>

</html>
