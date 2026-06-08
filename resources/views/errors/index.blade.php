@extends('layouts.app')
@section('title', 'Errores de Análisis')

@section('content')
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease forwards; }
</style>

<div class="p-8 space-y-6 animate-fade-in">

    {{-- Header --}}
    <div class="flex items-start gap-3">
        <div class="w-1 h-10 bg-gradient-to-b from-red-500 to-rose-600 rounded-full mt-0.5 shrink-0"></div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Errores de Análisis</h2>
            <p class="text-sm text-gray-500 mt-0.5">Errores detectados en todos los CVs procesados</p>
        </div>
    </div>

    {{-- Summary stat cards --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Total --}}
        <div class="relative bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-5 shadow-lg shadow-red-500/20 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 right-8 w-16 h-16 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <p class="text-4xl font-bold text-white">{{ $totalErrors }}</p>
                <p class="text-sm text-white/80 mt-0.5 font-medium">Total errores</p>
            </div>
        </div>

        {{-- Críticos --}}
        <div class="relative bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl p-5 shadow-lg shadow-orange-500/20 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 right-8 w-16 h-16 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-4xl font-bold text-white">{{ $criticalErrors }}</p>
                <p class="text-sm text-white/80 mt-0.5 font-medium">Críticos</p>
            </div>
        </div>

        {{-- Advertencias --}}
        <div class="relative bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-5 shadow-lg shadow-amber-500/20 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 right-8 w-16 h-16 bg-white/5 rounded-full translate-y-1/2"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <p class="text-4xl font-bold text-white">{{ $warnings }}</p>
                <p class="text-sm text-white/80 mt-0.5 font-medium">Advertencias</p>
            </div>
        </div>
    </div>

    {{-- Lexical Errors --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Errores Léxicos</h3>
            </div>
            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-mono font-semibold rounded-md border border-blue-100">
                {{ $lexErrors->count() }}
            </span>
        </div>
        @if($lexErrors->isEmpty())
        <div class="py-10 text-center">
            <p class="text-sm text-gray-400">Sin errores léxicos</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($lexErrors as $err)
            <div class="px-6 py-4 hover:bg-blue-50/20 transition-colors duration-150">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-xs font-mono font-semibold">
                            {{ $err->code }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800">{{ $err->message }}</p>
                            @if($err->value)
                            <p class="text-xs text-gray-400 font-mono mt-0.5">valor: <span class="text-gray-600">{{ $err->value }}</span></p>
                            @endif
                            @if($err->cvDocument?->candidate)
                            <p class="text-xs text-gray-400 mt-1">
                                <a href="{{ route('candidates.show', $err->cvDocument->candidate) }}"
                                   class="text-blue-500 hover:text-blue-700 hover:underline transition-colors">
                                    {{ $err->cvDocument->candidate->name ?? 'Candidato' }}
                                </a>
                            </p>
                            @endif
                        </div>
                    </div>
                    @if($err->line)
                    <span class="shrink-0 text-xs text-gray-400 font-mono bg-gray-50 px-2 py-0.5 rounded border border-gray-100">L{{ $err->line }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Syntactic Errors --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Errores Sintácticos</h3>
            </div>
            <span class="px-2 py-0.5 bg-orange-50 text-orange-600 text-xs font-mono font-semibold rounded-md border border-orange-100">
                {{ $synErrors->count() }}
            </span>
        </div>
        @if($synErrors->isEmpty())
        <div class="py-10 text-center">
            <p class="text-sm text-gray-400">Sin errores sintácticos</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($synErrors as $err)
            <div class="px-6 py-4 hover:bg-orange-50/20 transition-colors duration-150">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <span class="shrink-0 mt-0.5 px-2 py-0.5 bg-orange-50 text-orange-600 border border-orange-100 rounded-lg text-xs font-mono font-semibold">
                            {{ $err->code }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800">{{ $err->message }}</p>
                            @if($err->section)
                            <p class="text-xs text-gray-400 mt-0.5">sección: <span class="font-medium text-gray-600">{{ $err->section }}</span></p>
                            @endif
                            @if($err->cvDocument?->candidate)
                            <p class="text-xs text-gray-400 mt-1">
                                <a href="{{ route('candidates.show', $err->cvDocument->candidate) }}"
                                   class="text-blue-500 hover:text-blue-700 hover:underline transition-colors">
                                    {{ $err->cvDocument->candidate->name ?? 'Candidato' }}
                                </a>
                            </p>
                            @endif
                        </div>
                    </div>
                    @if($err->line)
                    <span class="shrink-0 text-xs text-gray-400 font-mono bg-gray-50 px-2 py-0.5 rounded border border-gray-100">L{{ $err->line }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Semantic Errors --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Errores Semánticos</h3>
            </div>
            <span class="px-2 py-0.5 bg-purple-50 text-purple-600 text-xs font-mono font-semibold rounded-md border border-purple-100">
                {{ $semErrors->count() }}
            </span>
        </div>
        @if($semErrors->isEmpty())
        <div class="py-10 text-center">
            <p class="text-sm text-gray-400">Sin errores semánticos</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($semErrors as $err)
            <div class="px-6 py-4 hover:bg-purple-50/20 transition-colors duration-150">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <span class="shrink-0 mt-0.5 px-2 py-0.5 rounded-lg text-xs font-mono font-semibold border
                        {{ $err->severity === 'error'
                           ? 'bg-red-50 text-red-600 border-red-100'
                           : 'bg-amber-50 text-amber-600 border-amber-100' }}">
                        {{ $err->code }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border
                                {{ $err->severity === 'error'
                                   ? 'bg-red-50 text-red-700 border-red-200'
                                   : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                {{ $err->severity === 'error' ? 'Error' : 'Warning' }}
                            </span>
                            @if($err->field)
                            <span class="text-xs text-gray-400 font-mono">campo: {{ $err->field }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-800">{{ $err->message }}</p>
                        @if($err->suggestion)
                        <p class="text-xs text-gray-400 italic mt-0.5">{{ $err->suggestion }}</p>
                        @endif
                        @if($err->cvDocument?->candidate)
                        <p class="text-xs text-gray-400 mt-1">
                            <a href="{{ route('candidates.show', $err->cvDocument->candidate) }}"
                               class="text-blue-500 hover:text-blue-700 hover:underline transition-colors">
                                {{ $err->cvDocument->candidate->name ?? 'Candidato' }}
                            </a>
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
