<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CompatibilityResult;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacancyController extends Controller
{
    public function index(): View
    {
        $vacancies = Vacancy::withCount('candidates')
            ->withMax('compatibilityResults', 'total_score')
            ->latest()
            ->get();
        return view('vacancies.index', compact('vacancies'));
    }

    public function create(): View
    {
        return view('vacancies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                     => 'required|string|max:255',
            'description'               => 'required|string',
            'required_years_experience' => 'required|integer|min:0|max:50',
            'required_education_level'  => 'required|string',
            'required_skills'           => 'nullable|string',
            'required_languages'        => 'nullable|string',
            'preferred_certifications'  => 'nullable|string',
            'location'                  => 'nullable|string|max:255',
            'job_type'                  => 'nullable|string|max:100',
            'salary_range'              => 'nullable|string|max:100',
        ]);

        Vacancy::create([
            'title'                     => $data['title'],
            'description'               => $data['description'],
            'required_years_experience' => $data['required_years_experience'],
            'required_education_level'  => $data['required_education_level'],
            'required_skills'           => $this->parseCommaSeparated($data['required_skills'] ?? ''),
            'required_languages'        => $this->parseCommaSeparated($data['required_languages'] ?? ''),
            'preferred_certifications'  => $this->parseCommaSeparated($data['preferred_certifications'] ?? ''),
            'location'                  => $data['location'] ?? null,
            'job_type'                  => $data['job_type'] ?? null,
            'salary_range'              => $data['salary_range'] ?? null,
        ]);

        return redirect()->route('vacancies.index')->with('success', 'Vacante creada.');
    }

    public function show(Vacancy $vacancy): View
    {
        $results = CompatibilityResult::with('candidate')
            ->where('vacancy_id', $vacancy->id)
            ->orderByDesc('total_score')
            ->get();

        return view('vacancies.show', compact('vacancy', 'results'));
    }

    public function edit(Vacancy $vacancy): View
    {
        return view('vacancies.edit', compact('vacancy'));
    }

    public function update(Request $request, Vacancy $vacancy): RedirectResponse
    {
        $data = $request->validate([
            'title'                     => 'required|string|max:255',
            'description'               => 'required|string',
            'required_years_experience' => 'required|integer|min:0|max:50',
            'required_education_level'  => 'required|string',
            'required_skills'           => 'nullable|string',
            'required_languages'        => 'nullable|string',
            'preferred_certifications'  => 'nullable|string',
            'location'                  => 'nullable|string|max:255',
            'job_type'                  => 'nullable|string|max:100',
            'salary_range'              => 'nullable|string|max:100',
        ]);

        $vacancy->update([
            'title'                     => $data['title'],
            'description'               => $data['description'],
            'required_years_experience' => $data['required_years_experience'],
            'required_education_level'  => $data['required_education_level'],
            'required_skills'           => $this->parseCommaSeparated($data['required_skills'] ?? ''),
            'required_languages'        => $this->parseCommaSeparated($data['required_languages'] ?? ''),
            'preferred_certifications'  => $this->parseCommaSeparated($data['preferred_certifications'] ?? ''),
            'location'                  => $data['location'] ?? null,
            'job_type'                  => $data['job_type'] ?? null,
            'salary_range'              => $data['salary_range'] ?? null,
        ]);

        return redirect()->route('vacancies.index')->with('success', 'Vacante actualizada.');
    }

    public function destroy(Vacancy $vacancy): RedirectResponse
    {
        $vacancy->delete();
        return redirect()->route('vacancies.index')->with('success', 'Vacante eliminada.');
    }

    private function parseCommaSeparated(string $value): array
    {
        if (empty(trim($value))) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
