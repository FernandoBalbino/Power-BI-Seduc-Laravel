@props(['title' => 'SEDUC BI'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-seduc-page text-seduc-body">
        <div class="min-h-screen">
            <x-sidebar />

            <main class="ml-64 min-h-screen bg-seduc-page px-6 py-6">
                <x-topbar :title="$title" />

                <div class="space-y-5">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
    </body>
</html>
