@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&display=swap');
    .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-2px); }
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
                    <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-xs font-mono font-semibold rounded-md border border-emerald-100">LIVE</span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Resumen del sistema de análisis de CVs</p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <p class="text-xs font-mono text-gray-400">{{ now()->locale('es')->isoFormat('D MMM YYYY') }}</p>
            <p class="text-xs font-mono text-gray-300 mt-0.5">{{ now()->format('H:i') }} hrs</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="stat-card relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white cursor-default shadow-lg shadow-blue-500/20">
            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-4 -left-2 w-14 h-14 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="p-2 bg-white/20 rounded-xl w-fit mb-4">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-blue-100">Total Candidatos</p>
                <p class="text-4xl font-bold mt-1">{{ $totalCandidates }}</p>
            </div>
        </div>

        <div class="stat-card relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-purple-500 to-violet-600 text-white cursor-default shadow-lg shadow-purple-500/20">
            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-4 -left-2 w-14 h-14 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="p-2 bg-white/20 rounded-xl w-fit mb-4">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-purple-100">CVs Analizados</p>
                <p class="text-4xl font-bold mt-1">{{ $analyzedCvs }}</p>
            </div>
        </div>

        <div class="stat-card relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-cyan-500 to-teal-600 text-white cursor-default shadow-lg shadow-cyan-500/20">
            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-4 -left-2 w-14 h-14 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="p-2 bg-white/20 rounded-xl w-fit mb-4">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-cyan-100">Tasa de Match</p>
                <p class="text-4xl font-bold mt-1">{{ $matchRate }}<span class="text-xl font-semibold text-cyan-200">%</span></p>
            </div>
        </div>

        <div class="stat-card relative overflow-hidden rounded-2xl p-6 bg-gradient-to-br from-amber-500 to-orange-600 text-white cursor-default shadow-lg shadow-amber-500/20">
            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-4 -left-2 w-14 h-14 rounded-full bg-white/5"></div>
            <div class="relative">
                <div class="p-2 bg-white/20 rounded-xl w-fit mb-4">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-amber-100">Top Matches +80%</p>
                <p class="text-4xl font-bold mt-1">{{ $topMatches }}</p>
            </div>
        </div>

    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                    <h3 class="text-sm font-semibold text-gray-900">Top 5 Candidatos</h3>
                </div>
                <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">por score</span>
            </div>
            <div class="p-6">
                <canvas id="chart-top-candidates" height="180"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                    <h3 class="text-sm font-semibold text-gray-900">CVs subidos</h3>
                </div>
                <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">últimos 14 días</span>
            </div>
            <div class="p-6">
                <canvas id="chart-uploads" height="180"></canvas>
            </div>
        </div>

    </div>

    {{-- Recent candidates --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Candidatos Recientes</h3>
            </div>
            <a href="{{ route('candidates.index') }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-700 cursor-pointer transition-colors flex items-center gap-1">
                Ver todos
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @if($recentCandidates->isEmpty())
        <div class="py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">Sin candidatos todavía</p>
            <a href="{{ route('candidates.upload') }}" class="text-sm text-blue-600 hover:underline cursor-pointer">Sube el primero →</a>
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Candidato</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Categoría</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Match</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                    <th class="px-6 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recentCandidates as $c)
                @php
                    $initials   = collect(explode(' ', trim($c->name ?? '')))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
                    $score      = round($c->best_score, 1);
                    $statusMap  = [
                        'completed'  => ['Analizado',  'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'processing' => ['Procesando', 'bg-amber-50   text-amber-700   border-amber-200'],
                        'pending'    => ['Pendiente',  'bg-amber-50   text-amber-700   border-amber-200'],
                        'failed'     => ['Error',      'bg-red-50     text-red-700     border-red-200'],
                    ];
                    [$statusLabel, $statusClass] = $statusMap[$c->status] ?? ['—', 'bg-gray-50 text-gray-500 border-gray-200'];
                    $barColor = $score >= 80 ? 'from-emerald-500 to-teal-500'
                              : ($score >= 60 ? 'from-blue-500 to-indigo-500'
                              : ($score >= 40 ? 'from-amber-500 to-orange-500'
                              : 'from-red-400 to-rose-500'));
                @endphp
                <tr class="hover:bg-blue-50/30 transition-colors duration-150 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs shrink-0 shadow-sm">
                                {{ $initials ?: '?' }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $c->name ?? 'Sin nombre' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $c->email ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                            {{ $c->ai_category ?? '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="flex-1 max-w-[90px] h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full transition-all duration-500"
                                     style="width: {{ min($score, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-900 font-mono tabular-nums">{{ $score }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('candidates.show', $c) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition-colors duration-150 cursor-pointer">
                            Ver
                            <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const topCandidates = @json($topCandidates);
const uploadsByDay  = @json($uploadsByDay);

Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 12;

// Bar chart — Top 5 candidates
const barCtx = document.getElementById('chart-top-candidates').getContext('2d');
const barGradient = barCtx.createLinearGradient(0, 0, 0, 220);
barGradient.addColorStop(0, 'rgba(99,102,241,0.85)');
barGradient.addColorStop(1, 'rgba(139,92,246,0.65)');

new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: topCandidates.map(c => c.name.split(' ')[0]),
        datasets: [{
            label: 'Score',
            data: topCandidates.map(c => c.score),
            backgroundColor: barGradient,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { label: ctx => ` ${ctx.parsed.y.toFixed(1)}%` },
                backgroundColor: '#1e293b',
                titleColor: '#94a3b8',
                bodyColor: '#f1f5f9',
                padding: 10,
                cornerRadius: 8,
            }
        },
        scales: {
            x: { grid: { display: false }, border: { display: false } },
            y: {
                min: 0, max: 100,
                ticks: { callback: v => v + '%', color: '#94a3b8' },
                grid: { color: 'rgba(0,0,0,0.04)', drawTicks: false },
                border: { display: false },
            }
        },
        animation: { duration: 800, easing: 'easeOutQuart' },
    }
});

// Line chart — Uploads by day
const lineCtx = document.getElementById('chart-uploads').getContext('2d');
const lineGradient = lineCtx.createLinearGradient(0, 0, 0, 200);
lineGradient.addColorStop(0, 'rgba(139,92,246,0.18)');
lineGradient.addColorStop(1, 'rgba(139,92,246,0.01)');

new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: Object.keys(uploadsByDay).map(d => d.slice(5)),
        datasets: [{
            label: 'CVs',
            data: Object.values(uploadsByDay),
            borderColor: '#8b5cf6',
            backgroundColor: lineGradient,
            fill: true,
            tension: 0.45,
            pointRadius: 4,
            pointBackgroundColor: '#8b5cf6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 6,
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { label: ctx => ` ${ctx.parsed.y} CVs` },
                backgroundColor: '#1e293b',
                titleColor: '#94a3b8',
                bodyColor: '#f1f5f9',
                padding: 10,
                cornerRadius: 8,
            }
        },
        scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { color: '#94a3b8' } },
            y: {
                min: 0,
                ticks: { stepSize: 1, color: '#94a3b8' },
                grid: { color: 'rgba(0,0,0,0.04)', drawTicks: false },
                border: { display: false },
            }
        },
        animation: { duration: 800, easing: 'easeOutQuart' },
    }
});
</script>
@endpush
