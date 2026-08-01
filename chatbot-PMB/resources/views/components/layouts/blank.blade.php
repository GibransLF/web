<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    @include('partials.head')
</head>
<body class="h-full bg-white dark:bg-zinc-900 font-sans antialiased text-zinc-900 dark:text-zinc-100">
    {{ $slot }}

    @fluxScripts
</body>
</html>
