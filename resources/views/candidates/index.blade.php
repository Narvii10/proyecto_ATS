@extends('layouts.app')
@section('title', 'Candidatos')

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
                    <h2 class="text-2xl font-bold text-gray-900">Candidatos</h2>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-mono font-semibold rounded-md border border-blue-100">
                        {{ $candidates->total() }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Gestión y análisis de candidatos</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('compare.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 border border-purple-200 text-purple-700 rounded-xl text-sm font-medium hover:bg-purple-50 transition-colors duration-150 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Comparar
            </a>
            <a href="{{ route('candidates.upload') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Subir CV
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('candidates.index') }}"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Buscar</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Nombre o correo..."
                           class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300 transition-colors">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Score mínimo</label>
                <input type="number" name="min_score" value="{{ $minScore ?? '' }}"
                       min="0" max="100" placeholder="0%"
                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Estado</label>
                <select name="status"
                        class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white cursor-pointer transition-colors">
                    <option value="">Todos</option>
                    <option value="completed" @selected(($status??'') === 'completed')>Analizado</option>
                    <option value="failed"    @selected(($status??'') === 'failed')>Error</option>
                    <option value="pending"   @selected(($status??'') === 'pending')>Pendiente</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-50">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors duration-150 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtrar
            </button>
            @if($search || $minScore || $status)
            <a href="{{ route('candidates.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-500 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Limpiar
            </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($candidates->isEmpty())
        <div class="py-24 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 mb-1">
                @if($search || $minScore || $status)
                    Sin resultados para los filtros aplicados
                @else
                    Sin candidatos todavía
                @endif
            </p>
            @if(!$search && !$minScore && !$status)
            <a href="{{ route('candidates.upload') }}" class="text-sm text-blue-600 hover:underline cursor-pointer">Sube el primero →</a>
            @endif
        </div>
        @else
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Candidato</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Categoría IA</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Mejor Match</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Registrado</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($candidates as $c)
                @php
                    $initials  = collect(explode(' ', trim($c->name ?? '')))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1)))->take(2)->join('');
                    $bestScore = round($c->compatibilityResults->max('total_score') ?? 0, 1);
                    $cv        = $c->cvDocuments->first();
                    $statusMap = [
                        'completed'  => ['Analizado',  'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'processing' => ['Procesando', 'bg-amber-50   text-amber-700   border-amber-200'],
                        'pending'    => ['Pendiente',  'bg-amber-50   text-amber-700   border-amber-200'],
                        'failed'     => ['Error',      'bg-red-50     text-red-700     border-red-200'],
                    ];
                    [$statusLabel, $statusClass] = $statusMap[$cv?->processing_status ?? 'pending'] ?? ['—', 'bg-gray-50 text-gray-500 border-gray-200'];
                    $barColor = $bestScore >= 80 ? 'from-emerald-500 to-teal-500'
                              : ($bestScore >= 60 ? 'from-blue-500 to-indigo-500'
                              : ($bestScore >= 40 ? 'from-amber-500 to-orange-500'
                              : 'from-red-400 to-rose-500'));
                @endphp
                <tr class="hover:bg-blue-50/30 transition-colors duration-150 group">
                    <td class="px-5 py-4">
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
                    <td class="px-5 py-4">
                        <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-md">{{ $c->ai_category ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <div class="flex-1 max-w-[90px] h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full"
                                     style="width: {{ min($bestScore, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-900 font-mono tabular-nums">{{ $bestScore }}%</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-400 font-mono">{{ $c->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('candidates.show', $c) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition-colors duration-150 cursor-pointer">
                            Ver perfil
                            <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($candidates->hasPages())
        <div class="px-6 py-4 border-t border-gray-50">{{ $candidates->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
