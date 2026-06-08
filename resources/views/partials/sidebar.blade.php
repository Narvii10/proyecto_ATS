<aside class="w-64 h-screen flex flex-col shrink-0 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 shadow-xl shadow-slate-900/30">

    {{-- Logo --}}
    <div class="px-5 pt-6 pb-5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-400 to-violet-500 flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/30">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-white leading-tight tracking-tight">ATS Compiler</h1>
                <p class="text-[10px] text-slate-400 font-medium leading-tight mt-0.5">Sistema de Análisis de CVs</p>
            </div>
        </div>

        {{-- Divider --}}
        <div class="mt-5 h-px bg-gradient-to-r from-transparent via-slate-600 to-transparent"></div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 overflow-y-auto space-y-0.5 pb-4">

        {{-- Main --}}
        <p class="px-3 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Principal</p>

        @php
            $main = [
                ['route' => 'dashboard',        'label' => 'Dashboard',   'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>'],
                ['route' => 'candidates.index', 'label' => 'Candidatos', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>'],
                ['route' => 'vacancies.index', 'label' => 'Vacantes',   'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>'],
            ];
        @endphp

        @foreach($main as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 text-sm font-medium relative
                      {{ $active ? 'bg-white/10 text-white shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' }}">
                @if($active)
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-blue-400 rounded-r-full"></div>
                @endif
                <svg class="w-4.5 h-4.5 shrink-0 w-[18px] h-[18px] {{ $active ? 'text-blue-400' : 'text-slate-500 group-hover:text-slate-300' }}"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    {!! $item['svg'] !!}
                </svg>
                <span>{{ $item['label'] }}</span>
                @if($active)
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                @endif
            </a>
        @endforeach

        {{-- Análisis --}}
        <p class="px-3 pt-5 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Análisis</p>

        @php
            $analysis = [
                ['route' => 'ranking.index', 'label' => 'Ranking',   'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>'],
                ['route' => 'compare.index', 'label' => 'Comparar',  'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>'],
                ['route' => 'ast.index',     'label' => 'AST Viewer', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z"/>'],
                ['route' => 'errors.index',  'label' => 'Errores',   'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>'],
            ];
        @endphp

        @foreach($analysis as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 text-sm font-medium relative
                      {{ $active ? 'bg-white/10 text-white shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' }}">
                @if($active)
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-violet-400 rounded-r-full"></div>
                @endif
                <svg class="w-[18px] h-[18px] shrink-0 {{ $active ? 'text-violet-400' : 'text-slate-500 group-hover:text-slate-300' }}"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    {!! $item['svg'] !!}
                </svg>
                <span>{{ $item['label'] }}</span>
                @if($active)
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-violet-400"></div>
                @endif
            </a>
        @endforeach

        {{-- Sistema --}}
        <p class="px-3 pt-5 pb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Sistema</p>

        @php $settingsActive = request()->routeIs('settings.index'); @endphp
        <a href="{{ route('settings.index') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 text-sm font-medium relative
                  {{ $settingsActive ? 'bg-white/10 text-white shadow-sm' : 'text-slate-400 hover:bg-white/5 hover:text-slate-200' }}">
            @if($settingsActive)
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-slate-400 rounded-r-full"></div>
            @endif
            <svg class="w-[18px] h-[18px] shrink-0 {{ $settingsActive ? 'text-slate-300' : 'text-slate-500 group-hover:text-slate-300' }}"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Configuración</span>
            @if($settingsActive)
            <div class="ml-auto w-1.5 h-1.5 rounded-full bg-slate-400"></div>
            @endif
        </a>
    </nav>

    {{-- Upload CTA --}}
    <div class="px-4 pb-4">
        <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent mb-4"></div>
        <a href="{{ route('candidates.upload') }}"
           class="flex items-center justify-center gap-2.5 w-full px-4 py-3 rounded-xl font-semibold text-sm
                  bg-slate-700 text-slate-200 hover:bg-slate-600 hover:text-white
                  transition-all duration-200 active:scale-95">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            Subir CV
        </a>
    </div>

</aside>
