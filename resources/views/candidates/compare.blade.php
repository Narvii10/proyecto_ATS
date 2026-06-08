@extends('layouts.app')
@section('title', 'Comparar candidatos')

@section('content')
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease forwards; }
</style>

<div class="p-8 space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start gap-3">
        <div class="w-1 h-10 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full mt-0.5 shrink-0"></div>
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-2xl font-bold text-gray-900">Comparar Candidatos</h2>
                @if($candidates->isNotEmpty())
                <span class="px-2 py-0.5 bg-purple-50 text-purple-600 text-xs font-mono font-semibold rounded-md border border-purple-100">
                    {{ $candidates->count() }} seleccionados
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-0.5">Selecciona una vacante y hasta 4 candidatos para comparar lado a lado.</p>
        </div>
    </div>

    {{-- Selector form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Configurar comparación</h3>
            </div>
        </div>
        <form method="GET" action="{{ route('compare.index') }}" class="p-6 space-y-5">

            {{-- Vacante --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Vacante</label>
                <div class="relative w-full sm:w-96">
                    <select name="vacancy_id" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 bg-white cursor-pointer transition-colors appearance-none pr-10">
                        <option value="">— Selecciona una vacante —</option>
                        @foreach($vacancies as $v)
                        <option value="{{ $v->id }}" @selected($vacancy?->id == $v->id)>{{ $v->title }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            {{-- Candidatos checkboxes --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    Candidatos
                    <span class="normal-case font-normal text-gray-400 ml-1">(máx. 4)</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 max-h-52 overflow-y-auto pr-1">
                    @foreach($allCandidates as $c)
                    @php $checked = $candidates->contains('id', $c->id); @endphp
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border cursor-pointer transition-all duration-150
                                  {{ $checked
                                     ? 'border-purple-300 bg-purple-50 shadow-sm shadow-purple-100'
                                     : 'border-gray-200 hover:border-purple-200 hover:bg-purple-50/30' }}">
                        <input type="checkbox" name="candidates[]" value="{{ $c->id }}"
                               @checked($checked)
                               class="rounded text-purple-600 focus:ring-purple-400 shrink-0"
                               onchange="limitCheckboxes(this, 4)">
                        @php $ini = collect(explode(' ', trim($c->name ?? '')))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join(''); @endphp
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ $ini ?: '?' }}
                        </div>
                        <span class="text-xs font-medium text-gray-700 truncate">{{ $c->name ?? 'Sin nombre' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-purple-500/25 transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Comparar
            </button>
        </form>
    </div>

    {{-- Comparison table --}}
    @if($candidates->isNotEmpty() && $vacancy)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
            <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900">Comparación — {{ $vacancy->title }}</h3>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-36">Categoría</th>
                    @foreach($candidates as $c)
                    @php $initials = collect(explode(' ', trim($c->name ?? '')))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join(''); @endphp
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                {{ $initials ?: '?' }}
                            </div>
                            <span class="text-gray-800 font-semibold text-xs">{{ Str::words($c->name ?? 'Sin nombre', 2, '') }}</span>
                            <a href="{{ route('candidates.show', $c) }}"
                               class="text-xs text-purple-500 hover:text-purple-700 hover:underline font-normal transition-colors">Ver perfil →</a>
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">

                {{-- Score total --}}
                <tr class="bg-blue-50/40">
                    <td class="px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wide">Score Total</td>
                    @foreach($candidates as $c)
                    @php $r = $results[$c->id] ?? null; $sc = round($r?->total_score ?? 0, 1);
                    $scColor = $sc >= 70 ? 'text-emerald-600' : ($sc >= 40 ? 'text-amber-500' : 'text-red-500');
                    $scBar   = $sc >= 70 ? 'from-emerald-500 to-teal-500' : ($sc >= 40 ? 'from-amber-500 to-orange-500' : 'from-red-400 to-rose-500');
                    @endphp
                    <td class="px-5 py-4 text-center">
                        <span class="text-2xl font-bold {{ $scColor }}">{{ $sc }}%</span>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full mt-1.5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r {{ $scBar }}"
                                 style="width:{{ min($sc,100) }}%"></div>
                        </div>
                    </td>
                    @endforeach
                </tr>

                @php
                $rows = [
                    'skills_score'        => 'Skills',
                    'languages_score'     => 'Lenguajes',
                    'experience_score'    => 'Experiencia',
                    'education_score'     => 'Educación',
                    'certifications_score'=> 'Certificaciones',
                ];
                @endphp

                @foreach($rows as $field => $label)
                @php
                    $scores = $candidates->map(fn($c) => $results[$c->id]?->$field ?? 0);
                    $maxSc  = $scores->max();
                @endphp
                <tr class="hover:bg-blue-50/20 transition-colors duration-150">
                    <td class="px-5 py-3.5 text-xs font-medium text-gray-600">{{ $label }}</td>
                    @foreach($candidates as $c)
                    @php $sc = round($results[$c->id]?->$field ?? 0, 0); $isBest = $sc == $maxSc && $maxSc > 0; @endphp
                    <td class="px-5 py-3.5 text-center">
                        <span class="text-sm font-bold {{ $isBest ? 'text-emerald-600' : 'text-gray-500' }}">
                            {{ $sc }}%{{ $isBest ? ' ★' : '' }}
                        </span>
                        <div class="w-full h-1 bg-gray-100 rounded-full mt-1 overflow-hidden">
                            <div class="h-full rounded-full {{ $isBest ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gray-200' }}"
                                 style="width:{{ min($sc,100) }}%"></div>
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach

                {{-- Coincidencias --}}
                <tr class="hover:bg-blue-50/20 transition-colors duration-150">
                    <td class="px-5 py-4 text-xs font-medium text-gray-600 align-top pt-4">Coincidencias</td>
                    @foreach($candidates as $c)
                    @php $matched = array_unique($results[$c->id]?->matched ?? []); @endphp
                    <td class="px-5 py-4 align-top">
                        @if(empty($matched))
                            <span class="text-xs text-gray-400">—</span>
                        @else
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($matched, 0, 6) as $m)
                            @php $clean = preg_replace('/^(Skill|Language|Lang|Cert):\s*/i', '', $m); @endphp
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-md text-xs font-medium">{{ $clean }}</span>
                            @endforeach
                            @if(count($matched) > 6)
                            <span class="text-xs text-gray-400 self-center">+{{ count($matched)-6 }}</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    @endforeach
                </tr>

                {{-- Faltantes --}}
                <tr class="hover:bg-blue-50/20 transition-colors duration-150">
                    <td class="px-5 py-4 text-xs font-medium text-gray-600 align-top pt-4">Faltantes</td>
                    @foreach($candidates as $c)
                    @php $missing = array_unique($results[$c->id]?->missing ?? []); @endphp
                    <td class="px-5 py-4 align-top">
                        @if(empty($missing))
                            <span class="text-xs text-emerald-600 font-semibold">Completo ✓</span>
                        @else
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($missing, 0, 6) as $m)
                            @php $clean = preg_replace('/^(Skill|Language|Lang|Cert):\s*/i', '', $m); @endphp
                            <span class="px-2 py-0.5 bg-red-50 text-red-600 border border-red-100 rounded-md text-xs font-medium">{{ $clean }}</span>
                            @endforeach
                            @if(count($missing) > 6)
                            <span class="text-xs text-gray-400 self-center">+{{ count($missing)-6 }}</span>
                            @endif
                        </div>
                        @endif
                    </td>
                    @endforeach
                </tr>

                {{-- Evaluación IA --}}
                <tr class="hover:bg-blue-50/20 transition-colors duration-150">
                    <td class="px-5 py-4 text-xs font-medium text-gray-600 align-top pt-4">Evaluación IA</td>
                    @foreach($candidates as $c)
                    <td class="px-5 py-4 align-top">
                        @if($c->ai_assessment)
                        @php
                            $assColor = str_contains($c->ai_assessment, 'fuerte')  ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                      : (str_contains($c->ai_assessment, 'Buen')   ? 'bg-blue-50 text-blue-700 border-blue-200'
                                      : (str_contains($c->ai_assessment, 'Necesita') ? 'bg-amber-50 text-amber-700 border-amber-200'
                                      : 'bg-red-50 text-red-700 border-red-200'));
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $assColor }}">
                            {{ $c->ai_assessment }}
                        </span>
                        @else
                        <span class="text-xs text-gray-400">Sin análisis IA</span>
                        @endif
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>
        </div>
    </div>

    {{-- Bar chart --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100">
            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
            <h3 class="text-sm font-semibold text-gray-900">Comparación Visual por Categoría</h3>
        </div>
        <div class="p-6" style="height:300px;">
            <canvas id="compareChart"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
    new Chart(document.getElementById('compareChart'), {
        type: 'bar',
        data: {
            labels: @json($candidates->pluck('name')->toArray()),
            datasets: [
                { label: 'Skills',        data: @json($candidates->map(fn($c) => round(min($results[$c->id]?->skills_score ?? 0, 100), 1))->values()), backgroundColor: '#6366f1cc', borderColor: '#6366f1', borderWidth: 1, borderRadius: 5 },
                { label: 'Lenguajes',     data: @json($candidates->map(fn($c) => round($results[$c->id]?->languages_score ?? 0, 1))->values()),         backgroundColor: '#10b981cc', borderColor: '#10b981', borderWidth: 1, borderRadius: 5 },
                { label: 'Experiencia',   data: @json($candidates->map(fn($c) => round($results[$c->id]?->experience_score ?? 0, 1))->values()),         backgroundColor: '#f59e0bcc', borderColor: '#f59e0b', borderWidth: 1, borderRadius: 5 },
                { label: 'Educación',     data: @json($candidates->map(fn($c) => round($results[$c->id]?->education_score ?? 0, 1))->values()),           backgroundColor: '#ef4444cc', borderColor: '#ef4444', borderWidth: 1, borderRadius: 5 },
                { label: 'Certs',         data: @json($candidates->map(fn($c) => round($results[$c->id]?->certifications_score ?? 0, 1))->values()),      backgroundColor: '#8b5cf6cc', borderColor: '#8b5cf6', borderWidth: 1, borderRadius: 5 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { min: 0, max: 100, ticks: { stepSize: 25, font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } },
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 16 } },
                tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8, titleFont: { size: 12 }, bodyFont: { size: 11 } },
            },
        },
    });
    </script>
    @endpush
    @endif

</div>

<script>
function limitCheckboxes(el, max) {
    const all = document.querySelectorAll('input[name="candidates[]"]:checked');
    if (all.length > max) {
        el.checked = false;
        alert(`Máximo ${max} candidatos para comparar.`);
    }
}
</script>
@endsection
