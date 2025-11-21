<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'LinkForge') }}</title>
        <meta name="description" content="LinkForge - Raccourcisseur de liens puissant et analyses détaillées pour vos campagnes marketing.">
        <meta name="keywords" content="raccourcisseur de liens, link shortener, analytics, marketing, tracking, liens personnalisés">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ config('app.url') }}">
        <meta property="og:title" content="LinkForge - Maîtrisez vos liens">
        <meta property="og:description" content="Raccourcissez, partagez et suivez vos liens avec LinkForge.">
        <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ config('app.url') }}">
        <meta property="twitter:title" content="LinkForge - Maîtrisez vos liens">
        <meta property="twitter:description" content="Raccourcissez, partagez et suivez vos liens avec LinkForge.">
        <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
