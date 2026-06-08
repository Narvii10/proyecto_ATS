@extends('layouts.app')

@section('title', $vacancy->title)
@section('header', 'Ranking — ' . $vacancy->title)

@section('header-actions')
    <a href="{{ route('compare.index', ['vacancy_id' => $vacancy->id]) }}"
       class="px-4 py-2 border border-purple-200 text-purple-700 rounded-lg text-sm font-medium hover:bg-purple-50 transition">
        Comparar candidatos
    </a>
    <a href="{{ route('reports.ranking.csv', $vacancy) }}"
       class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium hover:bg-emerald-200 transition">
        Exportar CSV
    </a>
    <a href="{{ route('reports.ranking', $vacancy) }}"
       class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
        Exportar PDF
    </a>
    <a href="{{ route('vacancies.edit', $vacancy) }}"
       class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
        Editar
    </a>
@endsection

@section('content')

@php
$stageConfig = [
    'aplicado'   => ['label' => 'Aplicado',     'color' => 'blue'],
    'revision'   => ['label' => 'En Revisión',  'color' => 'yellow'],
    'entrevista' => ['label' => 'Entrevista',   'color' => 'purple'],
    'oferta'     => ['label' => 'Oferta',       'color' => 'orange'],
    'contratado' => ['label' => 'Contratado',   'color' => 'green'],
    'rechazado'  => ['label' => 'Rechazado',    'color' => 'red'],
];
@endphp

<div class="grid grid-cols-4 gap-6">

    {{-- Requirements sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-xs font-semibold text-gray-500 uppercase mb-3">Requisitos</h2>
            <dl class="space-y-3 text-xs">
                <div>
                    <dt class="text-gray-400 mb-1">Experiencia</dt>
                    <dd class="font-semibold">{{ $vacancy->required_years_experience }} año(s)</dd>
                </div>
                <div>
                    <dt class="text-gray-400 mb-1">Educación</dt>
                    <dd class="font-semibold capitalize">{{ $vacancy->required_education_level }}</dd>
                </div>
                @if(!empty($vacancy->required_skills))
                <div>
                    <dt class="text-gray-400 mb-1">Habilidades</dt>
                    <dd class="flex flex-wrap gap-1">
                        @foreach($vacancy->required_skills as $s)
                            <span class="px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded text-xs">{{ $s }}</span>
                        @endforeach
                    </dd>
                </div>
                @endif
                @if(!empty($vacancy->required_languages))
                <div>
                    <dt class="text-gray-400 mb-1">Lenguajes</dt>
                    <dd class="flex flex-wrap gap-1">
                        @foreach($vacancy->required_languages as $l)
                            <span class="px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded text-xs">{{ $l }}</span>
                        @endforeach
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Pipeline counter --}}
        @if($results->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-xs font-semibold text-gray-500 uppercase mb-3">Pipeline</h2>
            @php
                $stageCounts = $results->groupBy('pipeline_stage')->map->count();
            @endphp
            <div class="space-y-2">
                @foreach($stageConfig as $key => $cfg)
                @php $count = $stageCounts[$key] ?? 0; @endphp
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">{{ $cfg['label'] }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full
                        bg-{{ $cfg['color'] }}-100 text-{{ $cfg['color'] }}-700">
                        {{ $count }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Main content --}}
    <div class="col-span-3 space-y-6">

        {{-- Skills Gap Radar Chart --}}
        @if($results->isNotEmpty())
        @php
            $top5 = $results->take(5);
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800">Skills Gap — Top {{ $top5->count() }} Candidatos</h3>
                <span class="text-xs text-gray-400">Radar de compatibilidad por categoría</span>
            </div>
            <div class="relative" style="height:260px;">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
        @endif

        {{-- Ranking table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @if($results->isEmpty())
                <div class="py-16 text-center text-gray-400">
                    <p class="text-sm">Ningún candidato evaluado para esta vacante todavía.</p>
                    <a href="{{ route('candidates.upload') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">
                        Subir un CV
                    </a>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">#</th>
                            <th class="px-4 py-3 text-left">Candidato</th>
                            <th class="px-4 py-3 text-center">Total</th>
                            <th class="px-4 py-3 text-center">Skills</th>
                            <th class="px-4 py-3 text-center">Langs</th>
                            <th class="px-4 py-3 text-center">Exp</th>
                            <th class="px-4 py-3 text-center">Edu</th>
                            <th class="px-4 py-3 text-center">Certs</th>
                            <th class="px-4 py-3 text-center">Pipeline</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($results as $i => $r)
                        @php
                            $stage     = $r->pipeline_stage ?? 'aplicado';
                            $stageCfg  = $stageConfig[$stage] ?? ['label' => $stage, 'color' => 'gray'];
                        @endphp
                        <tr class="{{ $i === 0 ? 'bg-amber-50' : 'hover:bg-gray-50' }} transition">
                            <td class="px-4 py-3 text-center font-bold {{ $i === 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $r->candidate?->name ?? '—' }}
                                @if($i === 0)
                                    <span class="ml-1 text-xs text-amber-600">★ Top</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold {{ $r->total_score >= 70 ? 'text-green-600' : ($r->total_score >= 40 ? 'text-amber-600' : 'text-red-500') }}">
                                    {{ number_format($r->total_score, 1) }}%
                                </span>
                            </td>
                            @foreach([$r->skills_score, $r->languages_score, $r->experience_score, $r->education_score, $r->certifications_score] as $score)
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ number_format($score, 0) }}%</td>
                            @endforeach
                            <td class="px-4 py-3 text-center">
                                <select data-result-id="{{ $r->id }}"
                                        onchange="updateStage(this)"
                                        class="pipeline-select text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none
                                               bg-{{ $stageCfg['color'] }}-50 text-{{ $stageCfg['color'] }}-700
                                               border-{{ $stageCfg['color'] }}-200">
                                    @foreach($stageConfig as $key => $cfg)
                                    <option value="{{ $key }}" @selected($stage === $key)>{{ $cfg['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($r->candidate)
                                    <a href="{{ route('candidates.show', $r->candidate) }}"
                                       class="text-indigo-600 hover:underline text-xs">Ver</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@if($results->isNotEmpty())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// ── Radar Chart ──────────────────────────────────────────────────────────
const top5 = @json($results->take(5)->values());
const colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6'];

new Chart(document.getElementById('radarChart'), {
    type: 'radar',
    data: {
        labels: ['Skills', 'Lenguajes', 'Experiencia', 'Educación', 'Certs'],
        datasets: top5.map((r, i) => ({
            label: r.candidate?.name ?? `Candidato ${i+1}`,
            data: [
                Math.min(r.skills_score, 100),
                r.languages_score,
                r.experience_score,
                r.education_score,
                r.certifications_score,
            ],
            backgroundColor: colors[i] + '22',
            borderColor: colors[i],
            borderWidth: 2,
            pointBackgroundColor: colors[i],
            pointRadius: 4,
        })),
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                min: 0, max: 100,
                ticks: { stepSize: 25, font: { size: 10 } },
                pointLabels: { font: { size: 11, weight: '600' } },
                grid: { color: '#e5e7eb' },
            },
        },
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } },
        },
    },
});

// ── Pipeline AJAX ────────────────────────────────────────────────────────
async function updateStage(select) {
    const id    = select.dataset.resultId;
    const stage = select.value;
    try {
        const res = await fetch(`/pipeline/${id}/stage`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ stage }),
        });
        if (!res.ok) throw new Error('Error');

        // Update select styling
        const colors = {
            aplicado:   'blue',
            revision:   'yellow',
            entrevista: 'purple',
            oferta:     'orange',
            contratado: 'green',
            rechazado:  'red',
        };
        const c = colors[stage] || 'gray';
        select.className = `pipeline-select text-xs border rounded-lg px-2 py-1 focus:outline-none bg-${c}-50 text-${c}-700 border-${c}-200`;

        // Pulse feedback
        select.style.transition = 'opacity .2s';
        select.style.opacity = '.5';
        setTimeout(() => select.style.opacity = '1', 300);
    } catch {
        alert('No se pudo actualizar el pipeline.');
    }
}
</script>
@endpush
@endif
@endsection
