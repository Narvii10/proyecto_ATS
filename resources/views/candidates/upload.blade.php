@extends('layouts.app')
@section('title', 'Subir CV')

@section('content')
<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease forwards; }
    .pipeline-step { position: relative; }
    .pipeline-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 20px;
        right: -12px;
        width: 24px;
        height: 2px;
        background: linear-gradient(to right, #e2e8f0, #cbd5e1);
    }
</style>

<div class="p-8 space-y-6 animate-fade-in" x-data="uploader()">

    {{-- Header --}}
    <div class="flex items-start gap-3">
        <div class="w-1 h-10 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full mt-0.5 shrink-0"></div>
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-2xl font-bold text-gray-900">Subir CV</h2>
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-xs font-mono font-semibold rounded-md border border-indigo-100">PIPELINE</span>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">Analiza el CV completo con el pipeline de compiladores</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Upload form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Documento CV</h3>
            </div>
            <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded-md border border-gray-100">TXT · JSON · XML · PDF</span>
        </div>

        <form action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data"
              class="p-8 space-y-6" @submit="submitting = true">
            @csrf

            {{-- Vacancy selector --}}
            @if($vacancies->isNotEmpty())
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Vacante de interés
                    <span class="normal-case font-normal text-gray-400 ml-1">(opcional)</span>
                </label>
                <select name="vacancy_id"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white cursor-pointer transition-colors">
                    <option value="">— Sin vacante específica (evaluar contra todas) —</option>
                    @foreach($vacancies as $v)
                    <option value="{{ $v->id }}">{{ $v->title }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-400">Sin vacante, el sistema evalúa contra todas y recomienda el mejor encaje automáticamente.</p>
            </div>
            @endif

            {{-- Drop zone --}}
            <div class="border-2 border-dashed rounded-2xl p-12 text-center transition-all duration-200 cursor-pointer"
                 :class="dragging
                     ? 'border-blue-400 bg-blue-50/40 scale-[1.01]'
                     : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50/20'"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)"
                 @click="$refs.fileInput.click()">

                <div class="flex flex-col items-center pointer-events-none">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-lg shadow-blue-500/25 flex items-center justify-center mb-5">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Arrastra tu CV aquí</h3>
                    <p class="text-sm text-gray-500 mb-5">o haz clic para seleccionar un archivo</p>
                    <span class="pointer-events-auto px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200">
                        Seleccionar archivo
                    </span>
                    <p class="text-xs text-gray-400 mt-4 font-mono">Máximo 10 MB</p>
                </div>

                <input x-ref="fileInput" id="cv_file" name="cv_file" type="file"
                       accept=".txt,.json,.xml,.pdf" class="sr-only"
                       @change="handleFileSelect($event)">
            </div>

            {{-- File preview --}}
            <div x-show="fileName" x-cloak
                 class="bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900" x-text="fileName"></p>
                            <p class="text-xs text-gray-500 font-mono" x-text="fileSize"></p>
                        </div>
                    </div>
                    <button type="button" @click="clearFile()"
                            class="p-1.5 text-gray-400 hover:text-red-500 transition-colors cursor-pointer rounded-lg hover:bg-red-50">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="submitting" x-cloak class="px-5 pb-5">
                    <div class="flex items-center gap-2 text-amber-600 text-sm mb-3">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="font-medium">Procesando pipeline de análisis...</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full animate-pulse" style="width: 70%"></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        :disabled="!fileName || submitting"
                        :class="(!fileName || submitting) ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-lg hover:shadow-blue-500/25 cursor-pointer'"
                        class="inline-flex items-center gap-2 px-7 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold transition-all duration-200">
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span x-show="!submitting">Analizar CV</span>
                    <span x-show="submitting" x-cloak>Analizando...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Pipeline info --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                <h3 class="text-sm font-semibold text-gray-900">Pipeline de Análisis</h3>
            </div>
            <span class="text-xs font-mono text-gray-400">4 etapas</span>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-4 gap-3">
                @foreach([
                    ['1', 'Parseo',     'Detecta formato TXT/JSON/XML/PDF',       'from-blue-500 to-blue-600',   'bg-blue-50',   'text-blue-700'],
                    ['2', 'Léxico',     '11 tipos de token, tabla de símbolos',   'from-purple-500 to-purple-600','bg-purple-50', 'text-purple-700'],
                    ['3', 'Sintáctico', 'Árbol de parseo + 5 reglas estructura',  'from-cyan-500 to-cyan-600',   'bg-cyan-50',   'text-cyan-700'],
                    ['4', 'Semántico',  '11 reglas de validación + AST final',    'from-indigo-500 to-indigo-600','bg-indigo-50', 'text-indigo-700'],
                ] as [$n, $title, $desc, $grad, $bg, $text])
                <div class="pipeline-step relative {{ $bg }} rounded-2xl p-5 border border-{{ explode('-', $bg)[1] }}-100">
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

</div>
@endsection

@push('scripts')
<script>
function uploader() {
    return {
        dragging: false,
        fileName: '',
        fileSize: '',
        submitting: false,
        handleFileSelect(e) {
            const f = e.target.files[0];
            if (f) this.setFile(f);
        },
        handleDrop(e) {
            this.dragging = false;
            const f = e.dataTransfer.files[0];
            if (f) {
                this.$refs.fileInput.files = e.dataTransfer.files;
                this.setFile(f);
            }
        },
        setFile(f) {
            this.fileName = f.name;
            this.fileSize = (f.size / 1024).toFixed(0) + ' KB';
        },
        clearFile() {
            this.fileName = ''; this.fileSize = '';
            this.$refs.fileInput.value = '';
        },
    };
}
</script>
@endpush
