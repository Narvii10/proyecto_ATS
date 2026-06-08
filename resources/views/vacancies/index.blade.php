@extends('layouts.app')
@section('title', 'Vacantes')

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
                    <h2 class="text-2xl font-bold text-gray-900">Vacantes</h2>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-mono font-semibold rounded-md border border-blue-100">
                        {{ $vacancies->count() }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Gestión de puestos y requisitos</p>
            </div>
        </div>
        <button onclick="openVacanteModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Vacante
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3.5 flex items-center gap-3">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-3.5 flex items-center gap-3">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Vacancies grid --}}
    @if($vacancies->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-24 text-center">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 01-2.25 2.25h-12a2.25 2.25 0 01-2.25-2.25V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-1.5a2.251 2.251 0 00-2.15 1.586"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500 mb-1">Sin vacantes todavía</p>
        <button onclick="openVacanteModal()" class="text-sm text-blue-600 hover:underline cursor-pointer">Crea la primera →</button>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($vacancies as $vacancy)
        @php
            $best   = round($vacancy->compatibility_results_max_total_score ?? 0);
            $barColor = $best >= 80 ? 'from-emerald-500 to-teal-500'
                      : ($best >= 60 ? 'from-blue-500 to-indigo-500'
                      : ($best >= 40 ? 'from-amber-500 to-orange-500'
                      : 'from-red-400 to-rose-500'));
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 transition-all duration-200 overflow-hidden flex flex-col group">

            {{-- Card top accent line --}}
            <div class="h-0.5 bg-gradient-to-r from-blue-500 to-purple-600"></div>

            {{-- Body --}}
            <div class="p-5 flex-1 space-y-4">
                {{-- Title + status --}}
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-sm font-bold text-gray-900 leading-snug">{{ $vacancy->title }}</h3>
                    <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-semibold border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Activa
                    </span>
                </div>

                {{-- Description --}}
                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ $vacancy->description }}</p>

                {{-- Meta --}}
                <div class="space-y-1.5">
                    @if($vacancy->location)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        {{ $vacancy->location }}
                    </div>
                    @endif
                    @if($vacancy->job_type)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $vacancy->job_type }}
                    </div>
                    @endif
                    @if($vacancy->salary_range)
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                        {{ $vacancy->salary_range }}
                    </div>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                        </svg>
                        {{ ucfirst($vacancy->required_education_level) }} · {{ $vacancy->required_years_experience }} año(s) exp.
                    </div>
                </div>

                {{-- Skill tags --}}
                @if(!empty($vacancy->required_skills))
                <div class="flex flex-wrap gap-1.5">
                    @foreach(array_slice($vacancy->required_skills, 0, 4) as $skill)
                    <span class="px-2 py-0.5 bg-blue-50 border border-blue-100 text-blue-700 rounded-md text-xs font-medium">{{ $skill }}</span>
                    @endforeach
                    @if(count($vacancy->required_skills) > 4)
                    <span class="px-2 py-0.5 bg-gray-50 text-gray-500 rounded-md text-xs border border-gray-100">+{{ count($vacancy->required_skills) - 4 }}</span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-5 py-3 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs text-gray-500 shrink-0">{{ $vacancy->candidates_count }} postulante(s)</span>
                    @if($best > 0)
                    <div class="flex items-center gap-1.5 min-w-0">
                        <div class="w-14 h-1.5 bg-gray-200 rounded-full overflow-hidden shrink-0">
                            <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full" style="width: {{ min($best, 100) }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-700 font-mono tabular-nums">{{ $best }}%</span>
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('ranking.index', ['vacancy_id' => $vacancy->id]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold hover:bg-blue-100 transition-colors duration-150 cursor-pointer">
                        Ver Ranking
                        <svg class="w-3 h-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <button onclick="openEditModal({{ $vacancy->id }}, {{ json_encode([
                        'title'                     => $vacancy->title,
                        'description'               => $vacancy->description,
                        'location'                  => $vacancy->location ?? '',
                        'job_type'                  => $vacancy->job_type ?? '',
                        'salary_range'              => $vacancy->salary_range ?? '',
                        'required_years_experience' => $vacancy->required_years_experience,
                        'required_education_level'  => $vacancy->required_education_level,
                        'required_skills'           => implode(', ', $vacancy->required_skills ?? []),
                        'required_languages'        => implode(', ', $vacancy->required_languages ?? []),
                        'preferred_certifications'  => implode(', ', $vacancy->preferred_certifications ?? []),
                    ]) }})"
                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-150 cursor-pointer"
                       title="Editar">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('vacancies.destroy', $vacancy) }}"
                          onsubmit="return confirm('¿Eliminar esta vacante?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors duration-150 cursor-pointer"
                                title="Eliminar">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════ MODAL — NUEVA VACANTE ══ --}}
<div id="vacanteModal"
     class="fixed inset-0 z-50 flex items-center justify-center hidden"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeVacanteModal()"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col overflow-hidden">
        {{-- Modal accent top --}}
        <div class="h-1 bg-gradient-to-r from-blue-500 to-purple-600 shrink-0"></div>

        {{-- Header --}}
        <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-sm shadow-blue-500/30">
                    <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 01-2.25 2.25h-12a2.25 2.25 0 01-2.25-2.25V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-1.5a2.251 2.251 0 00-2.15 1.586"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Nueva Vacante</h3>
                    <p class="text-xs text-gray-500">Define los requisitos del puesto</p>
                </div>
            </div>
            <button onclick="closeVacanteModal()"
                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto px-7 py-6 flex-1">
            @if($errors->any() && !old('editing_vacancy_id'))
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <ul class="text-sm text-red-700 space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form id="vacanteForm" method="POST" action="{{ route('vacancies.store') }}" class="space-y-5" autocomplete="off">
                @csrf
                @include('vacancies._form', ['vacancy' => null])
                <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                    <button type="button" onclick="closeVacanteModal()"
                            class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Vacante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ MODAL — EDITAR VACANTE ══ --}}
<div id="editVacanteModal"
     class="fixed inset-0 z-50 flex items-center justify-center hidden"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditModal()"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-purple-500 to-blue-600 shrink-0"></div>

        <div class="flex items-center justify-between px-7 py-5 border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center shadow-sm shadow-purple-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Editar Vacante</h3>
                    <p id="editModalSubtitle" class="text-xs text-gray-500 truncate max-w-xs">Modifica los requisitos del puesto</p>
                </div>
            </div>
            <button onclick="closeEditModal()"
                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto px-7 py-6 flex-1">
            @if($errors->any() && old('editing_vacancy_id'))
            <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <ul class="text-sm text-red-700 space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form id="editVacanteForm" method="POST" action="" class="space-y-5" autocomplete="off">
                @csrf @method('PUT')
                <input type="hidden" name="editing_vacancy_id" id="editVacancyId">

                {{-- Título --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Título del puesto <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="title" id="editTitle" value="{{ old('title', '') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
                           placeholder="Ej: Desarrollador Backend PHP" required>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Descripción <span class="text-red-400">*</span>
                    </label>
                    <textarea name="description" id="editDescription" rows="3"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent resize-none transition-colors"
                              placeholder="Descripción del puesto y responsabilidades..." required>{{ old('description', '') }}</textarea>
                </div>

                {{-- Ubicación / Tipo / Salario --}}
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Ubicación</label>
                        <input type="text" name="location" id="editLocation" value="{{ old('location', '') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
                               placeholder="Ciudad o Remoto">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Tipo</label>
                        <select name="job_type" id="editJobType"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent bg-white cursor-pointer transition-colors">
                            @foreach([''=>'Seleccionar','Tiempo completo'=>'Tiempo completo','Medio tiempo'=>'Medio tiempo','Freelance'=>'Freelance','Prácticas'=>'Prácticas','Remoto'=>'Remoto','Híbrido'=>'Híbrido'] as $val => $label)
                            <option value="{{ $val }}" {{ old('job_type', '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Salario</label>
                        <input type="text" name="salary_range" id="editSalaryRange" value="{{ old('salary_range', '') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
                               placeholder="Q.5,000–Q.15,000">
                    </div>
                </div>

                {{-- Exp / Educación --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Años de experiencia <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="required_years_experience" id="editYearsExp" min="0" max="50"
                               value="{{ old('required_years_experience', 0) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Nivel educativo <span class="text-red-400">*</span>
                        </label>
                        <select name="required_education_level" id="editEducationLevel"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent bg-white cursor-pointer transition-colors">
                            @foreach(['any'=>'Cualquiera','bachillerato'=>'Bachillerato','tecnico'=>'Técnico','licenciatura'=>'Licenciatura','ingenieria'=>'Ingeniería','maestria'=>'Maestría','doctorado'=>'Doctorado'] as $val => $label)
                            <option value="{{ $val }}" {{ old('required_education_level', 'any') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Habilidades --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Habilidades requeridas</label>
                    <input type="text" name="required_skills" id="editRequiredSkills" value="{{ old('required_skills', '') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
                           placeholder="PHP, Laravel, MySQL, Docker">
                    <p class="mt-1 text-xs text-gray-400">Separadas por coma</p>
                </div>

                {{-- Lenguajes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Lenguajes de programación</label>
                    <input type="text" name="required_languages" id="editRequiredLanguages" value="{{ old('required_languages', '') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
                           placeholder="PHP, JavaScript, Python">
                    <p class="mt-1 text-xs text-gray-400">Separados por coma</p>
                </div>

                {{-- Certificaciones --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Certificaciones preferidas</label>
                    <input type="text" name="preferred_certifications" id="editCertifications" value="{{ old('preferred_certifications', '') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
                           placeholder="AWS Certified, Scrum Master">
                    <p class="mt-1 text-xs text-gray-400">Separadas por coma</p>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-50">
                    <button type="button" onclick="closeEditModal()"
                            class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openVacanteModal() {
    document.getElementById('vacanteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeVacanteModal() {
    document.getElementById('vacanteModal').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('vacanteForm').reset();
}
function openEditModal(id, data) {
    const form = document.getElementById('editVacanteForm');
    form.action = '{{ url("vacancies") }}/' + id;
    document.getElementById('editVacancyId').value = id;
    document.getElementById('editModalSubtitle').textContent = data.title;
    document.getElementById('editTitle').value         = data.title || '';
    document.getElementById('editDescription').value   = data.description || '';
    document.getElementById('editLocation').value      = data.location || '';
    document.getElementById('editJobType').value       = data.job_type || '';
    document.getElementById('editSalaryRange').value   = data.salary_range || '';
    document.getElementById('editYearsExp').value      = data.required_years_experience ?? 0;
    document.getElementById('editEducationLevel').value= data.required_education_level || 'any';
    document.getElementById('editRequiredSkills').value= data.required_skills || '';
    document.getElementById('editRequiredLanguages').value = data.required_languages || '';
    document.getElementById('editCertifications').value    = data.preferred_certifications || '';
    document.getElementById('editVacanteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('editVacanteModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeVacanteModal(); closeEditModal(); }
});
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('vacanteForm').reset();
    @if($errors->any() && old('editing_vacancy_id'))
        document.getElementById('editVacanteForm').action = '{{ url("vacancies") }}/{{ old("editing_vacancy_id") }}';
        document.getElementById('editVacanteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    @elseif($errors->any())
        openVacanteModal();
    @endif
});
</script>
@endpush
@endsection
