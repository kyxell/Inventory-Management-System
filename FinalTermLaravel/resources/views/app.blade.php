@php($isStockApp = request()->is('stock*'))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#212529">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title @unless($isStockApp) inertia @endunless>{{ $isStockApp ? 'Streetfood Stock Management' : config('app.name', 'Laravel') }}</title>

        @unless($isStockApp)
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @else
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        @endunless

        @routes
        @viteReactRefresh
        @if($isStockApp)
            @vite('resources/js/app.jsx')
        @else
            @vite(['resources/js/inertia.jsx', "resources/js/Pages/{$page['component']}.jsx"])
            @inertiaHead
        @endif
    </head>
    <body @unless($isStockApp) class="font-sans antialiased" @endunless>
        @if($isStockApp)
            <div id="root"></div>
        @else
            @inertia
        @endif
    </body>
</html>
