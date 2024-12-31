<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-cloak x-data="{}" :class="$store.darkMode.on ? 'dark' : ''">
    <head>
        <meta charset="utf-8">

        <meta name="application-name" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite('resources/css/app.css')
    </head>

    <body class="antialiased dark:bg-black">

        <div class="max-w-7xl mx-auto px-4">

            <x-navbar />

            {{ $slot }}
        </div>

        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')

    </body>
</html>
