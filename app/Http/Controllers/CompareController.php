<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    public function index(Request $request): View
    {
        $vacancies   = Vacancy::orderBy('title')->get();
        $candidates  = collect();
        $vacancy     = null;
        $results     = collect();

        if ($request->filled('vacancy_id')) {
            $vacancy = Vacancy::findOrFail($request->vacancy_id);

            $ids = array_filter((array) $request->input('candidates', []));

            if (!empty($ids)) {
                $candidates = Candidate::with([
                    'cvDocuments.lexicalTokens',
                    'compatibilityResults' => fn($q) => $q->where('vacancy_id', $vacancy->id),
                ])->whereIn('id', $ids)->get();

                $results = $candidates->mapWithKeys(fn($c) => [
                    $c->id => $c->compatibilityResults->first(),
                ]);
            }
        }

        $allCandidates = Candidate::orderBy('name')->get();

        return view('candidates.compare', compact('vacancies', 'vacancy', 'candidates', 'allCandidates', 'results'));
    }
}
