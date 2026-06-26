<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ auth()->user()?->theme->value ?? 'oxy' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title data-inertia>{{ config('app.name', 'Oxy') }}</title>
        @inertiaHead

        <!-- Reverb Config for Runtime -->
        <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
        <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host') }}">
        <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port') }}">
        <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme') }}">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased">
        @inertia
        <div id="teleported"/>
    </body>
</html>
