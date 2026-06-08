@extends('layouts.app')
@section('title', 'Nueva Vacante')

@section('content')
<div class="p-8 space-y-6">

    <div class="flex items-center gap-4">
        <a href="{{ route('vacancies.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
        </a>
        <div>
            <h2 class="text-3xl font-semibold text-gray-900">Nueva Vacante</h2>
            <p class="text-gray-500 mt-1">Define los requisitos del puesto</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('vacancies.store') }}" class="space-y-5">
                @csrf
                @include('vacancies._form')
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('vacancies.index') }}"
                       class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors duration-200">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl text-sm font-medium hover:shadow-lg transition-all duration-200">
                        Crear Vacante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
