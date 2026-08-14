<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-theme="{{ auth()->user()?->light_theme->value ?? 'oxy' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title data-inertia>{{ config('app.name', 'Oxy') }}</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Reverb Config for Runtime (Browser Echo WebSockets) -->
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.key') }}">
    <meta name="reverb-host" content="{{ env('VITE_REVERB_HOST', env('REVERB_HOST', 'localhost')) }}">
    <meta name="reverb-port" content="{{ env('VITE_REVERB_PORT', env('REVERB_PORT', 443)) }}">
    <meta name="reverb-scheme" content="{{ env('VITE_REVERB_SCHEME', env('REVERB_SCHEME', 'https')) }}">
    <meta name="yjs-ws-url" content="{{ env('VITE_YJS_WS_URL', 'ws://localhost:1234') }}">
    <!-- Fonts -->
    {{--    <link rel="preconnect" href="https://fonts.bunny.net">--}}
    {{--    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>--}}

    <!-- Scripts -->
    @vite(['resources/js/app.ts'])
    <x-inertia::head/>
</head>
<body class="font-sans antialiased">
<x-inertia::app/>
<div id="teleported"/>
</body>
</html>
