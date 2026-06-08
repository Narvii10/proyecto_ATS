@extends('layouts.app')
@section('title', $candidate->name ?? 'Candidato')

@section('content')
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease forwards; }
</style>

<div class="p-8 space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3 min-w-0">
            <div class="w-1 h-10 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full mt-0.5 shrink-0"></div>
            <div class="min-w-0">
                <h2 class="text-2xl font-bold text-gray-900 truncate">{{ $candidate->name ?? 'Sin nombre' }}</h2>
                <p class="text-sm text-gray-400 font-mono mt-0.5">{{ $candidate->email ?? '—' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 flex-wrap justify-end">
            <form method="POST" action="{{ route('candidates.reanalyze', $candidate) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-sm font-medium hover:bg-indigo-100 transition-colors duration-150 cursor-pointer border border-indigo-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Re-analizar IA
                </button>
            </form>
            @if($latestCv)
            <button onclick="openCVModal()"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-medium hover:bg-blue-100 transition-colors duration-150 cursor-pointer border border-blue-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ver CV
            </button>
            <a href="{{ route('reports.candidate', $candidate) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-medium hover:bg-emerald-100 transition-colors duration-150 cursor-pointer border border-emerald-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar PDF
            </a>
            @endif
            <form method="POST" action="{{ route('candidates.destroy', $candidate) }}"
                  onsubmit="return confirm('¿Eliminar candidato?')">
                @csrf @method('DELETE')
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-xl text-sm font-medium hover:bg-red-100 transition-colors duration-150 cursor-pointer border border-red-100">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    {{-- Main 2-col grid --}}
    <div class="grid grid-cols-3 gap-6">

        {{-- LEFT COLUMN --}}
        <div class="col-span-2 space-y-6">

            {{-- Profile card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-blue-500 to-purple-600"></div>
                <div class="p-6">
                    <div class="flex items-start gap-5">
                        @php
                            $initials = collect(explode(' ', trim($candidate->name ?? '')))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
                            $bestResult = $candidate->compatibilityResults->sortByDesc('total_score')->first();
                            $bestScore  = round($bestResult?->total_score ?? 0, 1);
                        @endphp
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shrink-0 shadow-lg shadow-blue-500/20">
                            {{ $initials ?: '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xl font-bold text-gray-900">{{ $candidate->name ?? 'Sin nombre' }}</h3>
                            <p class="text-gray-400 text-sm mt-0.5 font-mono">{{ $candidate->email ?? '—' }}</p>
                            <div class="flex items-center gap-4 mt-2.5 text-sm text-gray-500">
                                @if($candidate->phone)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $candidate->phone }}
                                </span>
                                @endif
                                @if($candidate->age)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/>
                                    </svg>
                                    {{ $candidate->age }} años
                                </span>
                                @endif
                            </div>
                            @if($candidate->ai_category)
                            <span class="inline-flex mt-3 px-3 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100">
                                {{ $candidate->ai_category }}
                            </span>
                            @endif
                        </div>
                    </div>

                    @if($bestScore > 0)
                    @php $isAutoRecommended = !session('highlight_vacancy'); @endphp
                    <div class="mt-5 p-4 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl border border-blue-100/60">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-700">Compatibilidad General</span>
                                @if($isAutoRecommended)
                                <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-md font-semibold border border-purple-200">Auto</span>
                                @endif
                            </div>
                            <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{{ $bestScore }}%</span>
                        </div>
                        <div class="w-full h-2 bg-white/80 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-700"
                                 style="width: {{ min($bestScore, 100) }}%"></div>
                        </div>
                        @if($bestResult?->vacancy)
                        <p class="text-xs text-gray-500 mt-1.5">
                            {{ $isAutoRecommended ? 'Mejor match:' : 'vs.' }} {{ $bestResult->vacancy->title }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- AI Assessment --}}
            @if($candidate->ai_summary)
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 p-6">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-indigo-800">Evaluación IA</h3>
                </div>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $candidate->ai_summary }}</p>
                @if($candidate->ai_strengths || $candidate->ai_weaknesses)
                <div class="mt-4 grid grid-cols-2 gap-4">
                    @if($candidate->ai_strengths)
                    <div>
                        <p class="text-xs font-bold text-emerald-700 mb-2 uppercase tracking-wide">Fortalezas</p>
                        <ul class="space-y-1.5">
                            @foreach($candidate->ai_strengths as $s)
                            <li class="flex items-start gap-2 text-xs text-gray-600">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mt-1 shrink-0"></span>
                                {{ $s }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if($candidate->ai_weaknesses)
                    <div>
                        <p class="text-xs font-bold text-red-600 mb-2 uppercase tracking-wide">Áreas de mejora</p>
                        <ul class="space-y-1.5">
                            @foreach($candidate->ai_weaknesses as $w)
                            <li class="flex items-start gap-2 text-xs text-gray-600">
                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full mt-1 shrink-0"></span>
                                {{ $w }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

            @if($latestCv)
            @php
                $parsed  = $latestCv->parsed_content ?? [];
                $rawData = $parsed['raw_data'] ?? [];

                $skills = collect($parsed['skills'] ?? $rawData['skills'] ?? []);
                if ($skills->isEmpty()) {
                    $skills = $latestCv->lexicalTokens->where('type', 'TOKEN_SKILL')->pluck('value')->unique()->values();
                }

                $languages = collect($parsed['languages'] ?? $rawData['languages'] ?? []);
                $langFromTokens = $latestCv->lexicalTokens->where('type', 'TOKEN_LANGUAGE')->pluck('value')->unique()->values();

                $certifications = collect($parsed['certifications'] ?? $rawData['certifications'] ?? []);
                if ($certifications->isEmpty()) {
                    $certifications = $latestCv->lexicalTokens->where('type', 'TOKEN_CERTIFICATION')->pluck('value')->unique()->values();
                }

                $experience = collect($parsed['experience'] ?? $rawData['experience'] ?? []);

                if ($experience->isEmpty()) {
                    $rawLines  = $parsed['lines'] ?? $rawData['lines'] ?? [];
                    $dateRange = '/(\d{4})\s*[-–]\s*(\d{4}|[Pp]resente|[Pp]resent|[Aa]ctual)/';
                    $extracted = [];

                    foreach ($rawLines as $i => $line) {
                        if (!preg_match($dateRange, $line, $dm)) continue;
                        $period     = $dm[0];
                        $companyRaw = trim(preg_replace('/' . preg_quote($period, '/') . '[,\s]*/', '', $line));
                        $companyRaw = rtrim($companyRaw, ', ');
                        $titleLine  = '';
                        for ($j = $i + 1; $j <= $i + 3 && $j < count($rawLines); $j++) {
                            $next = trim($rawLines[$j]);
                            if ($next && !str_starts_with($next, '•') && !str_starts_with($next, '-') && !preg_match($dateRange, $next)) {
                                $titleLine = $next;
                                break;
                            }
                        }
                        $descParts = [];
                        $startDesc = $titleLine ? $j + 1 : $i + 1;
                        for ($k = $startDesc; $k <= $startDesc + 4 && $k < count($rawLines); $k++) {
                            $dl = trim($rawLines[$k] ?? '');
                            if ($dl && (str_starts_with($dl, '•') || str_starts_with($dl, '-'))) {
                                $descParts[] = ltrim($dl, '•- ');
                            } elseif ($dl === '' || preg_match($dateRange, $dl)) {
                                break;
                            }
                        }
                        if ($companyRaw || $titleLine) {
                            $extracted[] = [
                                'title'       => $titleLine ?: $companyRaw,
                                'company'     => $titleLine ? $companyRaw : '',
                                'period'      => $period,
                                'description' => implode(' · ', array_slice($descParts, 0, 2)),
                            ];
                        }
                    }
                    $experience = collect($extracted);
                }

                $expYearsToken = $latestCv->lexicalTokens->firstWhere('type', 'TOKEN_EXPERIENCE_YEARS');
            @endphp

            {{-- Skills --}}
            @if($skills->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-900">Habilidades Técnicas</h3>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($skills as $skill)
                    <span class="px-3 py-1.5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 text-blue-700 rounded-lg text-xs font-semibold cursor-default hover:border-blue-300 transition-colors">
                        {{ is_array($skill) ? ($skill['name'] ?? $skill[0] ?? '') : $skill }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Languages --}}
            @if($languages->isNotEmpty() || $langFromTokens->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                    <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-900">Lenguajes de Programación</h3>
                </div>
                @if($languages->isNotEmpty())
                <div class="space-y-3">
                    @foreach($languages as $lang)
                    @php
                        $langLevels = ['básico'=>30,'beginner'=>30,'intermedio'=>60,'intermediate'=>60,'avanzado'=>85,'advanced'=>85,'nativo'=>100,'native'=>100,'fluido'=>90,'fluent'=>90,'professional'=>80];
                        $langName  = is_array($lang) ? ($lang['name'] ?? $lang[0] ?? $lang) : $lang;
                        $langLevel = is_array($lang) ? ($lang['level'] ?? '') : '';
                        $pct       = $langLevels[strtolower($langLevel)] ?? 70;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-semibold text-gray-700">{{ $langName }}</span>
                            <span class="text-xs font-bold text-purple-600 font-mono">{{ $pct }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full transition-all duration-700"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="flex flex-wrap gap-2">
                    @foreach($langFromTokens as $lang)
                    <span class="px-3 py-1.5 bg-purple-50 border border-purple-100 text-purple-700 rounded-lg text-xs font-semibold">{{ $lang }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            {{-- Experience --}}
            @if($experience->isNotEmpty() || $expYearsToken)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-sm font-bold text-gray-900">Experiencia Laboral</h3>
                    </div>
                    @if($expYearsToken)
                    <span class="text-xs px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg font-semibold border border-amber-100">
                        {{ $expYearsToken->value }} año(s)
                    </span>
                    @endif
                </div>

                @if($experience->isEmpty() && $expYearsToken)
                <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-amber-800">El CV menciona {{ $expYearsToken->value }} año(s) de experiencia pero no se encontraron entradas individuales.</span>
                </div>
                @else
                <div class="space-y-4">
                    @foreach($experience as $exp)
                    @php
                        $expTitle   = is_array($exp) ? ($exp['title']   ?? $exp['position'] ?? $exp['role'] ?? '') : (string)$exp;
                        $expCompany = is_array($exp) ? ($exp['company']  ?? $exp['employer'] ?? '') : '';
                        $expPeriod  = is_array($exp) ? ($exp['period']   ?? $exp['years']    ?? $exp['duration'] ?? $exp['dates'] ?? '') : '';
                        $expDesc    = is_array($exp) ? ($exp['description'] ?? $exp['summary'] ?? '') : '';
                    @endphp
                    @if($expTitle)
                    <div class="border-l-2 border-amber-300 pl-4 py-1">
                        <p class="text-sm font-bold text-gray-900">{{ $expTitle }}</p>
                        @if($expCompany || $expPeriod)
                        <p class="text-xs text-gray-500 mt-0.5 font-mono">
                            {{ $expCompany }}@if($expCompany && $expPeriod) · @endif{{ $expPeriod }}
                        </p>
                        @endif
                        @if($expDesc)
                        <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">{{ Str::limit($expDesc, 160) }}</p>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endif
            @endif

            {{-- Token Graph --}}
            @if($latestCv && $latestCv->lexicalTokens->isNotEmpty())
            @php
                $graphTokenMeta = [
                    'TOKEN_NAME'             => ['label' => 'Nombre',        'color' => '#3B82F6'],
                    'TOKEN_EMAIL'            => ['label' => 'Correo',        'color' => '#0EA5E9'],
                    'TOKEN_PHONE'            => ['label' => 'Teléfono',      'color' => '#06B6D4'],
                    'TOKEN_AGE'              => ['label' => 'Edad',          'color' => '#14B8A6'],
                    'TOKEN_UNIVERSITY'       => ['label' => 'Universidad',   'color' => '#22C55E'],
                    'TOKEN_CAREER'           => ['label' => 'Carrera',       'color' => '#10B981'],
                    'TOKEN_SKILL'            => ['label' => 'Habilidad',     'color' => '#A855F7'],
                    'TOKEN_LANGUAGE'         => ['label' => 'Lenguaje',      'color' => '#6366F1'],
                    'TOKEN_EXPERIENCE_YEARS' => ['label' => 'Años Exp.',     'color' => '#F59E0B'],
                    'TOKEN_CERTIFICATION'    => ['label' => 'Certificación', 'color' => '#F97316'],
                    'TOKEN_DATE'             => ['label' => 'Fecha',         'color' => '#EF4444'],
                ];
                $graphGroups = $latestCv->lexicalTokens->groupBy('type');
                $treeData    = [
                    'name'  => $candidate->name ?? 'CV',
                    'type'  => 'root',
                    'color' => '#6366F1',
                    'children' => [],
                ];
                foreach ($graphGroups as $type => $tokens) {
                    $meta    = $graphTokenMeta[$type] ?? ['label' => $type, 'color' => '#6B7280'];
                    $values  = $tokens->unique('value')->take(5);
                    $extra   = max(0, $tokens->unique('value')->count() - 5);
                    $children = [];
                    foreach ($values as $tok) {
                        $children[] = ['name' => $tok->value, 'type' => 'value', 'color' => $meta['color'], 'children' => []];
                    }
                    if ($extra > 0) {
                        $children[] = ['name' => "+{$extra} más", 'type' => 'extra', 'color' => '#9CA3AF', 'children' => []];
                    }
                    $treeData['children'][] = [
                        'name'     => $meta['label'],
                        'type'     => 'category',
                        'color'    => $meta['color'],
                        'count'    => $tokens->count(),
                        'children' => $children,
                    ];
                }
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <h3 class="text-sm font-bold text-gray-900">Grafo de Tokens</h3>
                    </div>
                    <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">Clic en nodo para expandir</span>
                </div>
                <div id="token-graph-container" class="overflow-x-auto bg-gray-50/50" style="min-height:420px;"></div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
            <script>
            (function () {
                const raw = @json($treeData);
                const container = document.getElementById('token-graph-container');
                const fullW = Math.max(container.offsetWidth || 800, 700);
                const fullH = 420;
                const margin = { top: 20, right: 220, bottom: 20, left: 120 };
                const W = fullW - margin.left - margin.right;
                const H = fullH - margin.top  - margin.bottom;

                const svg = d3.select('#token-graph-container')
                    .append('svg')
                    .attr('width', fullW)
                    .attr('height', fullH)
                    .style('font-family', 'Inter, system-ui, sans-serif');

                const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

                svg.append('defs').append('marker')
                    .attr('id', 'arrow')
                    .attr('viewBox', '0 -4 8 8')
                    .attr('refX', 8).attr('refY', 0)
                    .attr('markerWidth', 5).attr('markerHeight', 5)
                    .attr('orient', 'auto')
                    .append('path')
                    .attr('d', 'M0,-4L8,0L0,4')
                    .attr('fill', '#CBD5E1');

                const treeLayout = d3.tree().size([H, W]);
                let root = d3.hierarchy(raw);

                root.children && root.children.forEach(cat => {
                    if (cat.children) { cat._children = cat.children; cat.children = null; }
                });

                let nodeId = 0;

                function update(source) {
                    const treeData = treeLayout(root);
                    const nodes = treeData.descendants();
                    const links = treeData.links();

                    const link = g.selectAll('path.link').data(links, d => d.target.data.name + d.target.depth);
                    const linkEnter = link.enter().insert('path', 'g')
                        .attr('class', 'link').attr('fill', 'none')
                        .attr('stroke', '#CBD5E1').attr('stroke-width', 1.5)
                        .attr('marker-end', 'url(#arrow)')
                        .attr('d', () => {
                            const o = { x: source.x0 ?? source.x, y: source.y0 ?? source.y };
                            return d3.linkHorizontal()({ source: [o.y, o.x], target: [o.y, o.x] });
                        });

                    linkEnter.merge(link).transition().duration(350)
                        .attr('d', d => d3.linkHorizontal()({ source: [d.source.y, d.source.x], target: [d.target.y, d.target.x] }));

                    link.exit().transition().duration(350)
                        .attr('d', () => {
                            const o = { x: source.x, y: source.y };
                            return d3.linkHorizontal()({ source: [o.y, o.x], target: [o.y, o.x] });
                        }).remove();

                    const node = g.selectAll('g.node').data(nodes, d => d.id || (d.id = ++nodeId));
                    const nodeEnter = node.enter().append('g')
                        .attr('class', 'node')
                        .attr('transform', () => `translate(${source.y0 ?? source.y},${source.x0 ?? source.x})`)
                        .style('cursor', d => (d.children || d._children) ? 'pointer' : 'default')
                        .on('click', (_, d) => {
                            if (d.children) { d._children = d.children; d.children = null; }
                            else if (d._children) { d.children = d._children; d._children = null; }
                            update(d);
                        });

                    const defs = svg.select('defs');
                    if (defs.select('#glow').empty()) {
                        const filter = defs.append('filter').attr('id', 'glow');
                        filter.append('feGaussianBlur').attr('stdDeviation', '3').attr('result', 'coloredBlur');
                        const merge = filter.append('feMerge');
                        merge.append('feMergeNode').attr('in', 'coloredBlur');
                        merge.append('feMergeNode').attr('in', 'SourceGraphic');
                    }

                    nodeEnter.append('circle')
                        .attr('r', d => d.depth === 0 ? 26 : d.depth === 1 ? 20 : 13)
                        .attr('fill', d => d.data.color + '30').attr('stroke', 'none');

                    nodeEnter.append('circle').attr('r', 0)
                        .attr('fill', d => d.depth === 0 ? d.data.color : d.depth === 1 ? '#ffffff' : d.data.color + '20')
                        .attr('stroke', d => d.data.color)
                        .attr('stroke-width', d => d.depth === 0 ? 0 : 2)
                        .style('filter', d => d.depth === 0 ? 'url(#glow)' : 'none')
                        .transition().duration(350)
                        .attr('r', d => d.depth === 0 ? 22 : d.depth === 1 ? 16 : 10);

                    nodeEnter.append('circle').attr('class', 'indicator')
                        .attr('cx', d => d.depth === 0 ? 22 : 16).attr('cy', 0).attr('r', 4)
                        .attr('fill', d => d._children ? d.data.color : 'transparent').attr('stroke', 'none');

                    nodeEnter.append('text').attr('class', 'inner-label')
                        .attr('text-anchor', 'middle').attr('dominant-baseline', 'middle')
                        .attr('font-size', d => d.depth === 0 ? '9px' : '8px')
                        .attr('font-weight', 'bold')
                        .attr('fill', d => d.depth === 0 ? '#fff' : d.data.color)
                        .attr('pointer-events', 'none')
                        .text(d => {
                            if (d.depth === 0) return 'CV';
                            if (d.depth === 1) return d.data.name.substring(0, 3).toUpperCase();
                            return '';
                        });

                    nodeEnter.append('text').attr('class', 'outer-label')
                        .attr('x', d => (d.children || d._children) ? -26 : 14)
                        .attr('dy', '0.32em')
                        .attr('text-anchor', d => (d.children || d._children) ? 'end' : 'start')
                        .attr('font-size', d => d.depth === 1 ? '11px' : '10px')
                        .attr('font-weight', d => d.depth <= 1 ? '600' : '400')
                        .attr('fill', d => d.depth === 0 ? '#4F46E5' : d.depth === 1 ? '#1F2937' : '#6B7280')
                        .attr('pointer-events', 'none')
                        .text(d => {
                            if (d.depth === 0) return d.data.name.split(' ').slice(0,2).join(' ');
                            if (d.depth === 1) return `${d.data.name} (${d.data.count})`;
                            const v = d.data.name;
                            return v.length > 22 ? v.substring(0, 21) + '…' : v;
                        });

                    const nodeUpdate = nodeEnter.merge(node);
                    nodeUpdate.transition().duration(350).attr('transform', d => `translate(${d.y},${d.x})`);
                    nodeUpdate.select('circle.indicator').attr('fill', d => d._children ? d.data.color : 'transparent');

                    node.exit().transition().duration(350)
                        .attr('transform', `translate(${source.y},${source.x})`).style('opacity', 0).remove();

                    nodes.forEach(d => { d.x0 = d.x; d.y0 = d.y; });
                }

                root.x0 = H / 2; root.y0 = 0;
                update(root);
            })();
            </script>
            @endif

            {{-- Symbol Table --}}
            @if($latestCv && $latestCv->lexicalTokens->isNotEmpty())
            @php
                $tokenMeta = [
                    'TOKEN_NAME'             => ['label' => 'Nombre',        'color' => 'text-blue-300 bg-blue-500/10 border-blue-500/25'],
                    'TOKEN_EMAIL'            => ['label' => 'Correo',        'color' => 'text-sky-300 bg-sky-500/10 border-sky-500/25'],
                    'TOKEN_PHONE'            => ['label' => 'Teléfono',      'color' => 'text-cyan-300 bg-cyan-500/10 border-cyan-500/25'],
                    'TOKEN_AGE'              => ['label' => 'Edad',          'color' => 'text-teal-300 bg-teal-500/10 border-teal-500/25'],
                    'TOKEN_UNIVERSITY'       => ['label' => 'Universidad',   'color' => 'text-emerald-300 bg-emerald-500/10 border-emerald-500/25'],
                    'TOKEN_CAREER'           => ['label' => 'Carrera',       'color' => 'text-green-300 bg-green-500/10 border-green-500/25'],
                    'TOKEN_SKILL'            => ['label' => 'Habilidad',     'color' => 'text-purple-300 bg-purple-500/10 border-purple-500/25'],
                    'TOKEN_LANGUAGE'         => ['label' => 'Lenguaje',      'color' => 'text-indigo-300 bg-indigo-500/10 border-indigo-500/25'],
                    'TOKEN_EXPERIENCE_YEARS' => ['label' => 'Años Exp.',     'color' => 'text-amber-300 bg-amber-500/10 border-amber-500/25'],
                    'TOKEN_CERTIFICATION'    => ['label' => 'Certificación', 'color' => 'text-orange-300 bg-orange-500/10 border-orange-500/25'],
                    'TOKEN_DATE'             => ['label' => 'Fecha',         'color' => 'text-rose-300 bg-rose-500/10 border-rose-500/25'],
                ];
                $groupedTokens = $latestCv->lexicalTokens->groupBy('type');
                $totalTokens   = $latestCv->lexicalTokens->count();
            @endphp
            <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-200/60">
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3.5 bg-gray-900 border-b border-gray-700/60">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/90"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/90"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/90"></div>
                        </div>
                        <div class="w-px h-4 bg-gray-600"></div>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 3v18"/>
                        </svg>
                        <span class="text-sm font-mono text-gray-300 font-medium">symbol_table.dat</span>
                    </div>
                    <div class="flex items-center gap-2 px-2.5 py-1 bg-purple-500/10 rounded-md border border-purple-500/20">
                        <span class="text-xs text-purple-400 font-mono font-semibold">{{ $totalTokens }} tokens</span>
                    </div>
                </div>

                {{-- Token type summary --}}
                <div class="px-5 py-3 bg-gray-900/95 border-b border-gray-700/40 flex flex-wrap gap-1.5">
                    @foreach($groupedTokens as $type => $tokens)
                    @php $meta = $tokenMeta[$type] ?? ['label' => $type, 'color' => 'text-gray-400 bg-gray-500/10 border-gray-500/25']; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-mono font-medium border {{ $meta['color'] }}">
                        {{ $meta['label'] }} <span class="font-bold">{{ $tokens->count() }}</span>
                    </span>
                    @endforeach
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto bg-[#0d1117]">
                    <table class="w-full text-sm font-mono">
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th class="px-4 py-2.5 text-left text-xs text-gray-600 w-10">#</th>
                                <th class="px-4 py-2.5 text-left text-xs text-gray-600">Tipo</th>
                                <th class="px-4 py-2.5 text-left text-xs text-gray-600">Valor</th>
                                <th class="px-4 py-2.5 text-left text-xs text-gray-600 w-16">Línea</th>
                                <th class="px-4 py-2.5 text-left text-xs text-gray-600 w-16">Pos.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestCv->lexicalTokens as $i => $token)
                            @php $meta = $tokenMeta[$token->type] ?? ['label' => $token->type, 'color' => 'text-gray-400 bg-gray-500/10 border-gray-500/25']; @endphp
                            <tr class="border-b border-gray-800/50 hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $i + 1 }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium border {{ $meta['color'] }}">
                                        {{ $meta['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-xs text-slate-300 max-w-[220px] truncate" title="{{ $token->value }}">
                                    {{ $token->value }}
                                </td>
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $token->line > 0 ? $token->line : '—' }}</td>
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $token->position }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Status bar --}}
                <div class="flex items-center justify-between px-5 py-2 bg-indigo-600 text-white text-xs font-mono">
                    <span>Symbol Table</span>
                    <div class="flex items-center gap-4 opacity-80">
                        <span>{{ $groupedTokens->count() }} tipos</span>
                        <span>{{ $totalTokens }} tokens</span>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-5">

            {{-- Compatibility --}}
            @php
                $highlightVacancyId = session('highlight_vacancy');
                $byScore = $candidate->compatibilityResults->sortByDesc('total_score');
                $autoMode = !$highlightVacancyId;

                if ($autoMode) {
                    $primaryResult = $byScore->first();
                    $otherResults  = $byScore->skip(1)->values();
                } else {
                    $primaryResult = $byScore->firstWhere('vacancy_id', $highlightVacancyId) ?? $byScore->first();
                    $otherResults  = $byScore->filter(fn($r) => $r->vacancy_id != $primaryResult?->vacancy_id)->values();
                }
            @endphp

            @if($primaryResult)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-1 bg-gradient-to-r {{ $autoMode ? 'from-purple-500 to-blue-500' : 'from-blue-500 to-indigo-500' }}"></div>
                <div class="px-5 pt-5 pb-4">
                    <div class="flex items-center gap-2 mb-3">
                        @if($autoMode)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 text-purple-700 rounded-lg text-xs font-semibold border border-purple-100">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Recomendación del sistema
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100">
                            Vacante seleccionada
                        </span>
                        @endif
                    </div>

                    @php $primaryScore = round($primaryResult->total_score, 1); @endphp
                    <div class="{{ $autoMode ? 'p-4 bg-gradient-to-br from-purple-50 to-blue-50 rounded-xl border border-purple-100/60' : 'p-4 bg-blue-50/60 rounded-xl border border-blue-100/60' }}">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <p class="text-sm font-bold text-gray-900 leading-tight">{{ $primaryResult->vacancy->title ?? 'Vacante' }}</p>
                            <span class="text-xl font-bold {{ $autoMode ? 'text-purple-700' : 'text-blue-700' }} shrink-0">{{ $primaryScore }}%</span>
                        </div>
                        <div class="w-full h-2 bg-white/80 rounded-full overflow-hidden mb-3">
                            <div class="h-full bg-gradient-to-r {{ $autoMode ? 'from-purple-500 to-blue-500' : 'from-blue-500 to-indigo-500' }} rounded-full"
                                 style="width: {{ min($primaryScore, 100) }}%"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs text-gray-500 font-mono">
                            <span>Habilidades: <span class="font-bold text-gray-700">{{ number_format(min($primaryResult->skills_score, 100), 0) }}%</span></span>
                            <span>Lenguajes: <span class="font-bold text-gray-700">{{ number_format($primaryResult->languages_score, 0) }}%</span></span>
                            <span>Experiencia: <span class="font-bold text-gray-700">{{ number_format($primaryResult->experience_score, 0) }}%</span></span>
                            <span>Educación: <span class="font-bold text-gray-700">{{ number_format($primaryResult->education_score, 0) }}%</span></span>
                        </div>
                    </div>
                </div>

                @if($otherResults->isNotEmpty())
                <div class="border-t border-gray-100 px-5 py-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">También compatible con</p>
                    <div class="space-y-3">
                        @foreach($otherResults as $result)
                        @php $sc = round($result->total_score, 1); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 truncate max-w-[65%]">{{ $result->vacancy->title ?? 'Vacante' }}</span>
                                <span class="text-xs font-bold text-gray-700 font-mono">{{ $sc }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-400 to-purple-400 rounded-full"
                                     style="width: {{ min($sc, 100) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-semibold text-gray-700 mb-1">Compatibilidad</p>
                <p class="text-xs text-gray-400">Sin vacantes todavía.</p>
            </div>
            @endif

            {{-- Requirements vs Profile --}}
            @if($candidate->compatibilityResults->isNotEmpty())
            @php
                $topResult     = $primaryResult;
                $cleanLabel    = fn($s) => preg_replace('/^(Skill|Language|Lang|Cert):\s*/i', '', $s);
                $matchedUnique = array_values(array_unique($topResult->matched ?? []));
                $missingUnique = array_values(array_unique($topResult->missing ?? []));
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <p class="text-sm font-bold text-gray-900 mb-0.5">Requisitos vs Perfil</p>
                <p class="text-xs text-gray-400 mb-4 truncate">{{ $topResult->vacancy->title ?? '' }}</p>

                @if(!empty($matchedUnique))
                <div class="mb-4">
                    <p class="text-xs font-bold text-emerald-700 mb-2 uppercase tracking-wide">Encontrados ({{ count($matchedUnique) }})</p>
                    <ul class="space-y-1.5">
                        @foreach($matchedUnique as $item)
                        <li class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full shrink-0"></span>
                            {{ $cleanLabel($item) }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(!empty($missingUnique))
                <div>
                    <p class="text-xs font-bold text-red-600 mb-2 uppercase tracking-wide">Faltantes ({{ count($missingUnique) }})</p>
                    <ul class="space-y-1.5">
                        @foreach($missingUnique as $item)
                        <li class="flex items-center gap-2 text-xs text-gray-600">
                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full shrink-0"></span>
                            {{ $cleanLabel($item) }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif

            {{-- Certifications --}}
            @if($latestCv && isset($certifications) && $certifications->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-900">Certificaciones</h3>
                </div>
                <div class="space-y-2">
                    @foreach($certifications as $cert)
                    <div class="flex items-center gap-3 p-3 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100/60">
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-800">{{ is_array($cert) ? ($cert['name'] ?? $cert[0] ?? $cert) : $cert }}</p>
                            @if(is_array($cert) && isset($cert['issuer']))
                            <p class="text-xs text-gray-400 mt-0.5">{{ $cert['issuer'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- CV Quality --}}
            @if($latestCv)
            @php
                $lexCount    = $latestCv->lexicalErrors->count();
                $synCount    = $latestCv->syntacticErrors->count();
                $semErrors   = $latestCv->semanticErrors;
                $semCritical = $semErrors->where('severity','error')->count();
                $semWarnings = $semErrors->where('severity','warning')->count();
                $totalErrors = $lexCount + $synCount + $semErrors->count();
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $totalErrors === 0 ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                        <h3 class="text-sm font-bold text-gray-900">Calidad del CV</h3>
                    </div>
                    @if($totalErrors === 0)
                    <span class="text-xs px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg font-semibold border border-emerald-100">Sin problemas</span>
                    @else
                    <span class="text-xs px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg font-semibold border border-amber-100">{{ $totalErrors }} aviso(s)</span>
                    @endif
                </div>
                @if($totalErrors === 0)
                <p class="text-xs text-gray-400">El CV fue procesado correctamente.</p>
                @else
                <div class="space-y-2">
                    @if($lexCount > 0)
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <span class="text-xs font-medium text-blue-700">Tokens no reconocidos</span>
                        <span class="text-sm font-bold text-blue-700 font-mono">{{ $lexCount }}</span>
                    </div>
                    @endif
                    @if($synCount > 0)
                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-xl border border-orange-100">
                        <span class="text-xs font-medium text-orange-700">Formato irregular</span>
                        <span class="text-sm font-bold text-orange-700 font-mono">{{ $synCount }}</span>
                    </div>
                    @endif
                    @if($semCritical > 0 || $semWarnings > 0)
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-xl border border-red-100">
                        <span class="text-xs font-medium text-red-700">Datos incompletos</span>
                        <span class="text-sm font-bold text-red-700 font-mono">{{ $semCritical + $semWarnings }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- Recruiter notes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="text-sm font-bold text-gray-900">Notas del Reclutador</h3>
                </div>

                <form method="POST" action="{{ route('notes.store', $candidate) }}" class="mb-4">
                    @csrf
                    <textarea name="content" rows="3" placeholder="Agregar nota interna..."
                              class="w-full text-sm px-3 py-2.5 border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-colors bg-gray-50/50 placeholder-gray-400"></textarea>
                    <button type="submit"
                            class="mt-2 inline-flex items-center gap-2 px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Guardar nota
                    </button>
                </form>

                @if($candidate->notes->isEmpty())
                <p class="text-xs text-gray-400 italic">Sin notas todavía.</p>
                @else
                <ul class="space-y-2.5">
                    @foreach($candidate->notes as $note)
                    <li class="flex items-start gap-3 p-3 bg-indigo-50/60 rounded-xl border border-indigo-100/50">
                        <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 leading-relaxed">{{ $note->content }}</p>
                            <p class="text-xs text-gray-400 font-mono mt-1">{{ $note->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <form method="POST" action="{{ route('notes.destroy', [$candidate, $note]) }}">
                            @csrf @method('DELETE')
                            <button class="text-gray-300 hover:text-red-400 transition-colors cursor-pointer p-0.5 rounded hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            {{-- CV Document info --}}
            @if($latestCv)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    <h3 class="text-sm font-bold text-gray-900">Documento CV</h3>
                </div>
                <dl class="space-y-2.5">
                    <div class="flex justify-between items-center">
                        <dt class="text-xs text-gray-400">Archivo</dt>
                        <dd class="font-semibold text-gray-700 text-xs truncate max-w-[55%] font-mono">{{ $latestCv->original_filename }}</dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-xs text-gray-400">Formato</dt>
                        <dd><span class="uppercase font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md text-xs font-bold">{{ $latestCv->format }}</span></dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-xs text-gray-400">Estado</dt>
                        @php
                            $stMap  = ['completed'=>['Analizado','bg-emerald-50 text-emerald-700 border-emerald-200'],'processing'=>['Procesando','bg-amber-50 text-amber-700 border-amber-200'],'pending'=>['Pendiente','bg-amber-50 text-amber-700 border-amber-200'],'failed'=>['Error','bg-red-50 text-red-700 border-red-200']];
                            [$stLabel, $stClass] = $stMap[$latestCv->processing_status ?? 'pending'] ?? ['—','bg-gray-50 text-gray-500 border-gray-200'];
                        @endphp
                        <dd><span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $stClass }}">{{ $stLabel }}</span></dd>
                    </div>
                    <div class="flex justify-between items-center">
                        <dt class="text-xs text-gray-400">Subido</dt>
                        <dd class="text-xs text-gray-600 font-mono">{{ $latestCv->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
            @endif

        </div>
    </div>
</div>

@if($latestCv)
{{-- CV Viewer Modal --}}
<div id="cvModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCVModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl flex flex-col" style="width:90vw;max-width:900px;height:88vh;">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center border border-blue-100">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $latestCv->original_filename }}</p>
                    <p class="text-xs text-gray-400 font-mono uppercase">{{ $latestCv->format }} · {{ $latestCv->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('candidates.cv', $candidate) }}" download="{{ $latestCv->original_filename }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold hover:bg-gray-200 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar
                </a>
                <button onclick="closeCVModal()" class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-hidden rounded-b-2xl">
            @if($latestCv->format === 'pdf')
            <iframe src="{{ route('candidates.cv', $candidate) }}" class="w-full h-full border-0 rounded-b-2xl" type="application/pdf"></iframe>
            @else
            <div class="h-full overflow-auto p-6 bg-gray-950 rounded-b-2xl">
                <pre class="text-xs text-green-400 font-mono leading-relaxed whitespace-pre-wrap break-words">{{ $latestCv->raw_content }}</pre>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCVModal()  { document.getElementById('cvModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeCVModal() { document.getElementById('cvModal').classList.add('hidden');    document.body.style.overflow = ''; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCVModal(); });
</script>
@endpush
@endif

@endsection
