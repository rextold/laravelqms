<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="QMS">
    <meta name="msapplication-TileColor" content="#667eea">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    
    <title>@yield('title', 'Queue Management System')</title>
    <script src="{{ asset('assets/js/tailwind/tailwind-3.4.17.js') }}"></script>
    <script src="{{ asset('assets/js/jquery/jquery-3.6.0.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <script>
        // Global AJAX setup for CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* The rest style here */
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50" data-organization-code="{{ request()->route('organization_code') ?? (auth()->user() && auth()->user()->organization ? auth()->user()->organization->organization_code : '') }}">
    @if(auth()->check() && !request()->is('monitor*') && !request()->is('kiosk*'))
    <!-- The rest of HTML and scripts here -->
    @endif   
    <!-- Authentication Error Modal -->
    @include('components.auth-error-modal')
   @stack('scripts')
</body>
</html>