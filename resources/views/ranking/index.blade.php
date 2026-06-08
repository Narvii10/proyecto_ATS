@extends('layouts.app')
@section('title', 'Ranking')

@section('content')
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease forwards; }
</style>

<div class="p-8 space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div class="flex items-start gap-3">
            <div class="w-1 h-10 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full mt-0.5 shrink-0"></div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-2xl font-bold text-gray-900">Ranking de Candidatos</h2>
                    @if($vacancy)
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-mono font-semibold rounded-md border border-blue-100">
                        {{ $results->count() }}
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Candidatos ordenados por compatibilidad</p>
            </div>
        </div>
        @if($vacancy)
        <a href="{{ route('reports.ranking', $vacancy) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium hover:bg-emerald-50 transition-colors duration-150 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Exportar PDF
        </a>
        @endif
    </div>

    {{-- Vacancy selector --}}
    @if($vacancies->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 py-4">
        <form method="GET" action="{{ route('ranking.index') }}" class="flex items-center gap-4">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide shrink-0">Vacante</label>
            <div class="flex-1 relative">
                <select name="vacancy_id" onchange="this.form.submit()"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white cursor-pointer transition-colors appearance-none pr-10">
                    @foreach($vacancies as $v)
                    <option value="{{ $v->id }}" {{ $vacancy?->id === $v->id ? 'selected' : '' }}>
                        {{ $v->title }} ({{ $v->compatibility_results_count }} candidatos)
                    </option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </form>
    </div>
    @endif

    @if($vacancy)

    {{-- Top 3 podium cards --}}
    @if($results->count() >= 1)
    @php
        $top3 = $results->take(3);
        $medals = [
            0 => [
                'grad'       => 'from-yellow-400 to-amber-500',
                'bg'         => 'bg-yellow-50',
                'border'     => 'border-yellow-200',
                'badgeBg'    => 'bg-yellow-400',
                'scoreColor' => 'text-yellow-500',
                'tagBg'      => 'bg-yellow-100 border-yellow-200 text-yellow-700',
                'topLine'    => 'from-yellow-400 to-amber-500',
                'label'      => '#1',
            ],
            1 => [
                'grad'       => 'from-slate-400 to-gray-500',
                'bg'         => 'bg-gray-50',
                'border'     => 'border-gray-200',
                'badgeBg'    => 'bg-gray-400',
                'scoreColor' => 'text-blue-600',
                'tagBg'      => 'bg-gray-100 border-gray-200 text-gray-600',
                'topLine'    => 'from-slate-400 to-gray-500',
                'label'      => '#2',
            ],
            2 => [
                'grad'       => 'from-orange-400 to-amber-600',
                'bg'         => 'bg-orange-50',
                'border'     => 'border-orange-200',
                'badgeBg'    => 'bg-orange-400',
                'scoreColor' => 'text-orange-500',
                'tagBg'      => 'bg-orange-100 border-orange-200 text-orange-700',
                'topLine'    => 'from-orange-400 to-amber-600',
                'label'      => '#3',
            ],
        ];
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-{{ min($top3->count(), 3) }} gap-5">
        @foreach($top3 as $i => $result)
        @php
            $m        = $medals[$i];
            $score    = round($result->total_score, 1);
            $skills   = array_slice(array_map(fn($s) => preg_replace('/^(Skill|Language|Lang|Cert):\s*/i', '', $s), (array)($result->matched ?? [])), 0, 3);
            $position = $result->candidate->ai_category ?? '—';
            $name     = $result->candidate->name ?? 'Sin nombre';
            $initials = collect(explode(' ', trim($name)))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
        @endphp
        <div class="bg-white rounded-2xl border {{ $m['border'] }} shadow-sm overflow-hidden flex flex-col">
            {{-- Top gradient accent --}}
            <div class="h-1 bg-gradient-to-r {{ $m['topLine'] }}"></div>

            {{-- Score header band --}}
            <div class="{{ $m['bg'] }} px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $m['grad'] }} text-white text-sm font-bold flex items-center justify-center shadow-sm">
                            {{ $m['label'] }}
                        </span>
                        <svg class="w-5 h-5 {{ $m['scoreColor'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                        </svg>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold {{ $m['scoreColor'] }}">{{ $score }}%</p>
                        <p class="text-xs text-gray-400 mt-0.5 font-medium">Match</p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-5 py-4 space-y-3 flex-1">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm">
                        {{ $initials ?: '?' }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $name }}</p>
                        <p class="text-xs text-gray-500">{{ $position }}</p>
                    </div>
                </div>

                @if(!empty($skills))
                <div class="flex flex-wrap gap-1.5">
                    @foreach($skills as $skill)
                    <span class="px-2.5 py-0.5 {{ $m['tagBg'] }} border rounded-md text-xs font-medium">
                        {{ $skill }}
                    </span>
                    @endforeach
                </div>
                @endif

                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                    </svg>
                    <span>Experiencia: {{ round($result->experience_score, 0) }}%</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Full ranking table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Ranking Completo</h3>
            </div>
            <span class="text-xs text-gray-400">Todos los candidatos evaluados</span>
        </div>

        @if($results->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">Sin candidatos para esta vacante todavía.</p>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Ranking</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Candidato</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Posición</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Habilidades</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Experiencia</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Match</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($results as $idx => $result)
                @php
                    $score    = round($result->total_score, 1);
                    $name     = $result->candidate->name ?? 'Sin nombre';
                    $initials = collect(explode(' ', trim($name)))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
                    $position = $result->candidate->ai_category ?? '—';
                    $allSkills   = array_map(fn($s) => preg_replace('/^(Skill|Language|Lang|Cert):\s*/i', '', $s), (array)($result->matched ?? []));
                    $skills      = array_slice($allSkills, 0, 2);
                    $extraSkills = count($allSkills) - 2;

                    [$rankBg, $rankText, $showTrophy] = match(true) {
                        $idx === 0 => ['bg-yellow-100', 'text-yellow-700', true],
                        $idx === 1 => ['bg-gray-100',   'text-gray-600',   true],
                        $idx === 2 => ['bg-orange-100', 'text-orange-600', true],
                        default    => ['bg-gray-50',    'text-gray-500',   false],
                    };

                    $barColor = $score >= 80 ? 'from-emerald-500 to-teal-500'
                              : ($score >= 60 ? 'from-blue-500 to-indigo-500'
                              : ($score >= 40 ? 'from-amber-500 to-orange-500'
                              : 'from-red-400 to-rose-500'));
                @endphp
                <tr class="hover:bg-blue-50/30 transition-colors duration-150">

                    {{-- Rank --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg {{ $rankBg }} {{ $rankText }} flex items-center justify-center text-xs font-bold">
                                #{{ $idx + 1 }}
                            </span>
                            @if($showTrophy)
                            <svg class="w-4 h-4 {{ $rankText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                            </svg>
                            @endif
                        </div>
                    </td>

                    {{-- Candidato --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs shrink-0 shadow-sm">
                                {{ $initials ?: '?' }}
                            </div>
                            <a href="{{ route('candidates.show', $result->candidate) }}"
                               class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                {{ $name }}
                            </a>
                        </div>
                    </td>

                    {{-- Posición --}}
                    <td class="px-5 py-4">
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-md">{{ $position }}</span>
                    </td>

                    {{-- Habilidades --}}
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($skills as $skill)
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-md text-xs font-medium">
                                {{ $skill }}
                            </span>
                            @endforeach
                            @if($extraSkills > 0)
                            <span class="px-2 py-0.5 bg-gray-50 text-gray-500 rounded-md text-xs border border-gray-100">
                                +{{ $extraSkills }}
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- Experiencia --}}
                    <td class="px-5 py-4">
                        <span class="text-sm font-mono text-gray-600">{{ round($result->experience_score, 0) }}%</span>
                    </td>

                    {{-- Match --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5 min-w-[120px]">
                            <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full"
                                     style="width: {{ min($score, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-900 font-mono tabular-nums shrink-0">{{ $score }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-24 text-center">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 01-2.25 2.25h-12a2.25 2.25 0 01-2.25-2.25V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-1.5a2.251 2.251 0 00-2.15 1.586"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Sin vacantes creadas todavía.</p>
        <a href="{{ route('vacancies.index') }}" class="text-sm text-blue-600 hover:underline cursor-pointer">Crea una primero →</a>
    </div>
    @endif

</div>
@endsection
