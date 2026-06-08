{{-- Título --}}
<div>
    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
        Título del puesto <span class="text-red-400">*</span>
    </label>
    <input type="text" name="title" value="{{ old('title', $vacancy->title ?? '') }}"
           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
           placeholder="Ej: Desarrollador Backend PHP" required>
</div>

{{-- Descripción --}}
<div>
    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
        Descripción <span class="text-red-400">*</span>
    </label>
    <textarea name="description" rows="3"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent resize-none transition-colors"
              placeholder="Descripción del puesto y responsabilidades..." required>{{ old('description', $vacancy->description ?? '') }}</textarea>
</div>

{{-- Ubicación / Tipo / Salario --}}
<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Ubicación</label>
        <input type="text" name="location" value="{{ old('location', $vacancy->location ?? '') }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
               placeholder="Ciudad o Remoto">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Tipo</label>
        <select name="job_type"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent bg-white cursor-pointer transition-colors">
            @foreach([''=>'Seleccionar','Tiempo completo'=>'Tiempo completo','Medio tiempo'=>'Medio tiempo','Freelance'=>'Freelance','Prácticas'=>'Prácticas','Remoto'=>'Remoto','Híbrido'=>'Híbrido'] as $val => $label)
            <option value="{{ $val }}" {{ old('job_type', $vacancy->job_type ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Salario</label>
        <input type="text" name="salary_range" value="{{ old('salary_range', $vacancy->salary_range ?? '') }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
               placeholder="Q.5,000–Q.15,000">
    </div>
</div>

{{-- Experiencia / Educación --}}
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
            Años de experiencia <span class="text-red-400">*</span>
        </label>
        <input type="number" name="required_years_experience" min="0" max="50"
               value="{{ old('required_years_experience', $vacancy->required_years_experience ?? 0) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
            Nivel educativo <span class="text-red-400">*</span>
        </label>
        <select name="required_education_level"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent bg-white cursor-pointer transition-colors">
            @foreach(['any'=>'Cualquiera','bachillerato'=>'Bachillerato','tecnico'=>'Técnico','licenciatura'=>'Licenciatura','ingenieria'=>'Ingeniería','maestria'=>'Maestría','doctorado'=>'Doctorado'] as $val => $label)
            <option value="{{ $val }}" {{ old('required_education_level', $vacancy->required_education_level ?? 'any') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Habilidades --}}
<div>
    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Habilidades requeridas</label>
    <input type="text" name="required_skills"
           value="{{ old('required_skills', isset($vacancy) ? implode(', ', $vacancy->required_skills ?? []) : '') }}"
           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
           placeholder="PHP, Laravel, MySQL, Docker">
    <p class="mt-1 text-xs text-gray-400">Separadas por coma</p>
</div>

{{-- Lenguajes --}}
<div>
    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Lenguajes de programación</label>
    <input type="text" name="required_languages"
           value="{{ old('required_languages', isset($vacancy) ? implode(', ', $vacancy->required_languages ?? []) : '') }}"
           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
           placeholder="PHP, JavaScript, Python">
    <p class="mt-1 text-xs text-gray-400">Separados por coma</p>
</div>

{{-- Certificaciones --}}
<div>
    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Certificaciones preferidas</label>
    <input type="text" name="preferred_certifications"
           value="{{ old('preferred_certifications', isset($vacancy) ? implode(', ', $vacancy->preferred_certifications ?? []) : '') }}"
           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-transparent transition-colors"
           placeholder="AWS Certified, Scrum Master">
    <p class="mt-1 text-xs text-gray-400">Separadas por coma</p>
</div>
