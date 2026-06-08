<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ATS') — ATS Compiler</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --background: #ffffff;
            --foreground: oklch(0.145 0 0);
            --card: #ffffff;
            --primary: #030213;
            --muted: #ececf0;
            --muted-foreground: #717182;
            --accent: #e9ebef;
            --border: rgba(0,0,0,0.1);
            --input-background: #f3f3f5;
            --radius: 0.625rem;
            --sidebar: oklch(0.985 0 0);
            --sidebar-border: oklch(0.922 0 0);
        }
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gray-50">

<div class="flex h-screen overflow-hidden">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto">

        {{-- Flash messages --}}
        @if(session('success') || session('warning') || session('error'))
        <div id="flash-msg" class="mx-8 mt-6">
            @if(session('success'))
            <div class="flex items-center gap-3 px-5 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('warning'))
            <div class="flex items-center gap-3 px-5 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                {{ session('warning') }}
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 px-5 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
            </div>
            @endif
        </div>
        <script>setTimeout(() => { const el = document.getElementById('flash-msg'); if(el) el.style.display='none'; }, 5000);</script>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
