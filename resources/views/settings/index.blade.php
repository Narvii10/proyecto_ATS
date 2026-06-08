@extends('layouts.app')
@section('title', 'Configuración')

@section('content')
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease forwards; }
</style>

<div class="p-8 space-y-6 animate-fade-in" x-data="{
    notifications: true,
    darkMode: false,
    autoProcess: true,
    aiEnabled: {{ config('services.groq.key') ? 'true' : 'false' }},
}">

    {{-- Header --}}
    <div class="flex items-start gap-3">
        <div class="w-1 h-10 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full mt-0.5 shrink-0"></div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Configuración</h2>
            <p class="text-sm text-gray-500 mt-0.5">Ajustes del sistema ATS Compiler</p>
        </div>
    </div>

    {{-- Settings grid --}}
    <div class="grid grid-cols-2 gap-5">

        {{-- Preferencias --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Preferencias</h3>
            </div>
            <div class="px-6 py-5 space-y-4">
                @foreach([
                    ['autoProcess', 'Procesamiento automático', 'Analizar CV al subir sin confirmación', 'bg-blue-500'],
                    ['aiEnabled',   'Clasificación IA (Groq)',  'Requiere GROQ_API_KEY en .env',          'bg-blue-500'],
                    ['darkMode',    'Mostrar AST detallado',    'Visualizar todos los nodos del árbol',    'bg-blue-500'],
                ] as [$model, $title, $desc, $color])
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                    </div>
                    <button @click="{{ $model }} = !{{ $model }}"
                            :class="{{ $model }} ? '{{ $color }}' : 'bg-gray-200'"
                            class="relative w-10 h-5 rounded-full transition-colors duration-200 focus:outline-none cursor-pointer shrink-0">
                        <span :class="{{ $model }} ? 'translate-x-5' : 'translate-x-0.5'"
                              class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Notificaciones --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Análisis completado</p>
                        <p class="text-xs text-gray-400 mt-0.5">Notificar cuando el pipeline termina</p>
                    </div>
                    <button @click="notifications = !notifications"
                            :class="notifications ? 'bg-purple-500' : 'bg-gray-200'"
                            class="relative w-10 h-5 rounded-full transition-colors duration-200 focus:outline-none cursor-pointer shrink-0">
                        <span :class="notifications ? 'translate-x-5' : 'translate-x-0.5'"
                              class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Errores críticos</p>
                        <p class="text-xs text-gray-400 mt-0.5">Alertar ante errores semánticos graves</p>
                    </div>
                    <button class="relative w-10 h-5 bg-purple-500 rounded-full focus:outline-none cursor-pointer shrink-0">
                        <span class="absolute top-0.5 translate-x-5 w-4 h-4 bg-white rounded-full shadow"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between py-2">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Alto match detectado</p>
                        <p class="text-xs text-gray-400 mt-0.5">Avisar al encontrar compatibilidad ≥80%</p>
                    </div>
                    <button class="relative w-10 h-5 bg-gray-200 rounded-full focus:outline-none cursor-pointer shrink-0">
                        <span class="absolute top-0.5 translate-x-0.5 w-4 h-4 bg-white rounded-full shadow"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Apariencia --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Apariencia</h3>
            </div>
            <div class="px-6 py-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tema del sistema</p>
                <div class="space-y-2">
                    @foreach([['light','Claro','bg-white border border-gray-200'],['auto','Sistema','bg-gradient-to-r from-gray-100 to-gray-300 border border-gray-200']] as [$val,$label,$style])
                    <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors border border-transparent hover:border-gray-100">
                        <input type="radio" name="theme" value="{{ $val }}" {{ $val === 'light' ? 'checked' : '' }}
                               class="text-cyan-600 focus:ring-cyan-400">
                        <div class="w-8 h-5 rounded {{ $style }}"></div>
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sistema --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900">Sistema</h3>
            </div>
            <div class="px-6 py-5 space-y-3">
                @foreach([
                    ['Versión del app',  '1.0.0',           'bg-gray-100 text-gray-700'],
                    ['Framework',        'Laravel 13.8',    'bg-gray-100 text-gray-700'],
                    ['PHP',              PHP_VERSION,        'bg-gray-100 text-gray-700'],
                    ['Modelo IA',        'Groq / Llama 3.3', 'bg-indigo-50 text-indigo-700'],
                ] as [$key, $val, $badge])
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <span class="text-sm text-gray-500">{{ $key }}</span>
                    <span class="font-mono text-xs {{ $badge }} px-2 py-1 rounded-md">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pipeline stages --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center gap-2 px-6 py-4 border-b border-gray-100">
            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
            <h3 class="text-sm font-semibold text-gray-900">Pipeline del Compilador</h3>
            <span class="ml-auto text-xs font-mono text-gray-400">5 etapas</span>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-5 gap-3">
                @foreach([
                    ['1', 'Parseo',         'Detecta formato TXT/JSON/XML/PDF',          'from-blue-500 to-blue-600',    'bg-blue-50',   'text-blue-700'],
                    ['2', 'Léxico',          $stats['token_types'].' tipos de token',     'from-purple-500 to-purple-600','bg-purple-50', 'text-purple-700'],
                    ['3', 'Sintáctico',      'Árbol de parseo · 5 reglas',                'from-cyan-500 to-cyan-600',    'bg-cyan-50',   'text-cyan-700'],
                    ['4', 'Semántico',       $stats['semantic_rules'].' reglas · AST',    'from-indigo-500 to-indigo-600','bg-indigo-50', 'text-indigo-700'],
                    ['5', 'Compatibilidad',  'Scoring 30/25/20/15/10',                    'from-pink-500 to-pink-600',    'bg-pink-50',   'text-pink-700'],
                ] as [$n, $title, $desc, $grad, $bg, $text])
                <div class="{{ $bg }} rounded-2xl p-4 border border-{{ explode('-', $bg)[1] }}-100">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-sm font-bold mb-3 shadow-sm">
                        {{ $n }}
                    </div>
                    <p class="text-sm font-bold {{ $text }} mb-1">{{ $title }}</p>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Compiler numeric stats --}}
    <div class="grid grid-cols-3 gap-5">
        @foreach([
            [$stats['phases'],        'Fases del compilador', 'from-blue-500 to-purple-600',  'text-blue-600',   'from-blue-50 to-purple-50',  'border-blue-100'],
            [$stats['token_types'],   'Tipos de token',       'from-purple-500 to-pink-600',  'text-purple-600', 'from-purple-50 to-pink-50',  'border-purple-100'],
            [$stats['semantic_rules'],'Reglas semánticas',    'from-indigo-500 to-blue-600',  'text-indigo-600', 'from-indigo-50 to-blue-50',  'border-indigo-100'],
        ] as [$val, $label, $barGrad, $numColor, $bgGrad, $border])
        <div class="relative bg-gradient-to-br {{ $bgGrad }} rounded-2xl border {{ $border }} p-5 text-center overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white/30 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <p class="text-4xl font-bold {{ $numColor }} relative">{{ $val }}</p>
            <p class="text-sm text-gray-600 mt-1 relative">{{ $label }}</p>
        </div>
        @endforeach
    </div>

</div>
@endsection
