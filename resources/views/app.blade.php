<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Warna latar disetel di sini juga agar tidak ada kedipan
             sebelum app.css termuat. Sejak arah A+B (23 Agustus 2026)
             seluruh aplikasi berlatar putih — dulu krem. --}}
        <style>
            html {
                background-color: #ffffff;
            }
        </style>

        <meta name="theme-color" content="#FFFFFF">

        {{-- Ikon tab browser.

             Halaman publik memakai lencana usaha yang sedang dibuka —
             kartu anak usaha membuka tab baru, dan pengunjung yang
             melihat beberapa unit sekaligus perlu tahu tab mana milik
             siapa tanpa membaca judulnya yang terpotong.

             DITENTUKAN DI SINI, bukan lewat <Head> Inertia. Sempat
             dicoba lewat React, tapi hasilnya DUA <link rel="icon"> di
             HTML awal: satu bawaan dari Blade, satu dari React. Inertia
             memang menggantinya setelah JavaScript jalan, tapi browser
             sudah membaca yang pertama lebih dulu — dan ikon sempat
             berkedip berganti.

             Karena `favicon_url` sudah ada di props halaman, Blade bisa
             membacanya langsung dan menuliskan SATU tag yang benar sejak
             respons pertama. --}}
        @php
            $favicon = data_get($page, 'props.business.favicon_url')
                ?? data_get($page, 'props.parent.favicon_url');
            $heroImage = data_get($page, 'props.business.cover_image_url')
                ?? data_get($page, 'props.parent.cover_image_url')
                ?? (data_get($page, 'component') === 'home'
                    ? data_get($page, 'props.parent.logo_url')
                    : null);
        @endphp

        {{-- Logo induk dapat menjadi elemen LCP di desktop. Props sudah
             tersedia pada HTML awal, jadi browser tidak perlu menunggu
             React selesai hydration untuk menemukan permintaan gambarnya. --}}
        @if ($heroImage)
            <link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">
        @endif

        @if ($favicon)
            <link rel="icon" type="image/png" href="{{ $favicon }}">
        @else
            <link rel="icon" href="/favicon.ico">
        @endif

        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
