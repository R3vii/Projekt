<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="System zarządzania zadaniami Kanban HIKIXMORI">
    <title>@yield('title', 'Logowanie') — HIKIXMORI Kanban</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 400: '#7a93ff', 500: '#5a6dff', 600: '#4b5ae8', 700: '#3d48cc' }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { backdrop-filter: blur(12px); background: rgba(17,17,34,0.7); }
        .gradient-bg {
            background: radial-gradient(ellipse at 20% 50%, rgba(90,109,255,0.15) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(139,92,246,0.12) 0%, transparent 60%),
                        #0a0a1a;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-white font-bold text-xl tracking-wide">HIKIXMORI</p>
                    <p class="text-gray-400 text-xs">System Zarządzania Kanban</p>
                </div>
            </div>
        </div>

        <!-- Card -->
        <div class="glass rounded-2xl border border-gray-700/50 p-8 shadow-2xl">
            @yield('content')
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-xs mt-6">
            © {{ date('Y') }} HIKIXMORI. Projekt zaliczeniowy — Aplikacje Internetowe.
        </p>
    </div>
</body>
</html>
