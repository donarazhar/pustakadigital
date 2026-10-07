<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Pembaca Buku Interaktif' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- PWA Web App Manifest & Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Pustaka Digital">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Reader Styles & Offline Manager -->
    <link rel="stylesheet" href="{{ asset('css/reader.css') }}?v={{ file_exists(public_path('css/reader.css')) ? filemtime(public_path('css/reader.css')) : time() }}">
    <script src="{{ asset('js/offline-manager.js') }}"></script>
    
    <!-- StPageFlip Library for 3D Interactive Flipbook -->
    <script src="{{ asset('js/page-flip.browser.min.js') }}"></script>
    <script>
        // Fallback to CDN if local script is missing or blocked
        if (typeof St === 'undefined' || typeof St.PageFlip === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"><\/script>');
        }
    </script>

    <!-- PDF.js Library for 3D PDF Flipbook Rendering -->
    <script src="{{ asset('js/pdf.min.js') }}"></script>
    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdf.worker.min.js') }}";
        }
    </script>

    @livewireStyles
</head>
<body class="reader-mode">
    {{ $slot }}

    @livewireScripts
</body>
</html>
