@extends('layouts.app')
@section('title', 'AST Viewer')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&display=swap');
    .ast-tree { font-family: 'Fira Code', 'Cascadia Code', 'JetBrains Mono', monospace; }
    .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-2px); }
    .node-badge { font-family: 'Fira Code', monospace; }
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
                    <h2 class="text-2xl font-bold text-gray-900">AST Viewer</h2>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-mono font-semibold rounded-md border border-blue-100">COMPILER</span>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-xs font-mono font-semibold rounded-md border border-emerald-100">LIVE</span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Árbol Sintáctico Abstracto generado por el compilador</p>
            </div>
        </div>
    </div>

    {{-- Candidate selector --}}
    @if($candidates->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('ast.index') }}" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-600 shrink-0 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Candidato:
            </label>
            <select name="candidate_id" onchange="this.form.submit()"
                    class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white cursor-pointer font-medium text-gray-800 transition-colors duration-150 hover:border-blue-300">
                @foreach($candidates as $c)
                <option value="{{ $c->id }}" {{ $candidate?->id === $c->id ? 'selected' : '' }}>
                    {{ $c->name ?? 'Sin nombre' }}
                </option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    @if($candidate && $astData)

    <div class="grid grid-cols-4 gap-6">

        {{-- AST Tree — 3 cols --}}
        <div class="col-span-3 rounded-2xl overflow-hidden shadow-lg border border-gray-200/60 flex flex-col">

            {{-- Code editor title bar --}}
            <div class="flex items-center justify-between px-5 py-3.5 bg-gray-900 border-b border-gray-700/60">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-500/90 hover:bg-red-400 cursor-default transition-colors"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500/90 hover:bg-yellow-400 cursor-default transition-colors"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500/90 hover:bg-green-400 cursor-default transition-colors"></div>
                    </div>
                    <div class="w-px h-4 bg-gray-600"></div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span class="text-sm font-mono text-gray-300 font-medium">
                            {{ strtolower(str_replace(' ', '_', $candidate->name ?? 'candidato')) }}.ast
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 font-mono">AST · Depth {{ $depth }}</span>
                    <div class="flex items-center gap-1 px-2 py-1 bg-emerald-500/10 rounded-md border border-emerald-500/20">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs text-emerald-400 font-mono font-medium">parsed</span>
                    </div>
                </div>
            </div>

            {{-- Line numbers + tree --}}
            <div class="flex bg-[#0d1117] overflow-auto ast-tree" style="max-height: 580px;" x-data="{ search: '' }">

                {{-- Line numbers gutter --}}
                <div class="shrink-0 w-12 bg-[#0d1117] border-r border-gray-800/50 py-4 px-2 text-right select-none" id="ast-gutter">
                    <div class="text-xs text-gray-600 leading-6 font-mono space-y-0.5">
                        @for($i = 1; $i <= max(40, $totalNodes); $i++)
                        <div>{{ $i }}</div>
                        @endfor
                    </div>
                </div>

                {{-- Tree content --}}
                <div class="flex-1 py-4 px-4 min-w-0">
                    @include('partials.ast-node', ['node' => $astData, 'depth' => 0])
                </div>
            </div>

            {{-- Status bar --}}
            <div class="flex items-center justify-between px-5 py-2 bg-blue-600 text-white text-xs font-mono">
                <div class="flex items-center gap-4">
                    <span>AST Explorer</span>
                    <span class="opacity-60">|</span>
                    <span>{{ $totalNodes }} nodos</span>
                </div>
                <div class="flex items-center gap-4 opacity-80">
                    <span>UTF-8</span>
                    <span>Depth: {{ $depth }}</span>
                    <span>ATS Compiler v1.0</span>
                </div>
            </div>
        </div>

        {{-- Stats sidebar --}}
        <div class="space-y-4">

            {{-- AST Stats --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900">Estadísticas AST</h3>
                </div>
                <div class="space-y-3">

                    <div class="stat-card relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-indigo-500 to-blue-600 text-white cursor-default">
                        <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-white/10 -mr-4 -mt-4"></div>
                        <p class="text-xs font-medium text-indigo-100">Total nodos</p>
                        <p class="text-3xl font-bold mt-1">{{ $totalNodes }}</p>
                    </div>

                    <div class="stat-card relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-purple-500 to-pink-600 text-white cursor-default">
                        <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-white/10 -mr-4 -mt-4"></div>
                        <p class="text-xs font-medium text-purple-100">Profundidad</p>
                        <p class="text-3xl font-bold mt-1">{{ $depth }}</p>
                    </div>

                    <div class="stat-card relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-cyan-500 to-teal-600 text-white cursor-default">
                        <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-white/10 -mr-4 -mt-4"></div>
                        <p class="text-xs font-medium text-cyan-100">Secciones</p>
                        <p class="text-3xl font-bold mt-1">{{ $sectionCount }}</p>
                    </div>

                </div>
            </div>

            {{-- Node types --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900">Tipos de Nodo</h3>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($nodeTypes as $type)
                    @php
                        $typeColors = [
                            'CVNode'               => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'PersonalDataNode'     => 'bg-blue-100 text-blue-700 border-blue-200',
                            'EducationNode'        => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'ExperienceNode'       => 'bg-amber-100 text-amber-700 border-amber-200',
                            'SkillsNode'           => 'bg-purple-100 text-purple-700 border-purple-200',
                            'DegreeNode'           => 'bg-green-100 text-green-700 border-green-200',
                            'JobNode'              => 'bg-orange-100 text-orange-700 border-orange-200',
                            'TechSkillNode'        => 'bg-rose-100 text-rose-700 border-rose-200',
                        ];
                        $colorClass = $typeColors[$type] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                    @endphp
                    <span class="node-badge inline-flex px-2 py-0.5 rounded-md text-xs font-medium border {{ $colorClass }} cursor-default">
                        {{ $type }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Color legend --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-900">Leyenda de Profundidad</h3>
                </div>
                <div class="space-y-2">
                    @foreach([
                        ['depth' => 0, 'label' => 'Root / CV',      'from' => 'from-indigo-500', 'to' => 'to-indigo-600'],
                        ['depth' => 1, 'label' => 'Secciones',      'from' => 'from-blue-500',   'to' => 'to-blue-600'],
                        ['depth' => 2, 'label' => 'Sub-secciones',  'from' => 'from-emerald-500','to' => 'to-emerald-600'],
                        ['depth' => 3, 'label' => 'Campos',         'from' => 'from-amber-500',  'to' => 'to-amber-600'],
                        ['depth' => 4, 'label' => 'Valores',        'from' => 'from-purple-500', 'to' => 'to-purple-600'],
                    ] as $item)
                    <div class="flex items-center gap-2.5">
                        <div class="w-4 h-4 rounded bg-gradient-to-br {{ $item['from'] }} {{ $item['to'] }} shrink-0 shadow-sm"></div>
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="text-xs font-mono text-gray-400">d{{ $item['depth'] }}</span>
                            <span class="text-xs text-gray-600 truncate">{{ $item['label'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    @elseif($candidates->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-24 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Sin candidatos con AST generado</p>
        <a href="{{ route('candidates.upload') }}" class="text-sm text-blue-600 hover:underline cursor-pointer">Sube un CV primero →</a>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-24 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">AST no disponible para este candidato.</p>
    </div>
    @endif

</div>
@endsection
