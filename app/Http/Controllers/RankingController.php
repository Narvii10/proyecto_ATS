<?php

namespace App\Http\Controllers;

use App\Models\CompatibilityResult;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function index(Request $request): View
    {
        $vacancies     = Vacancy::withCount('compatibilityResults')->latest()->get();
        $vacancyId     = $request->input('vacancy_id', $vacancies->first()?->id);
        $vacancy       = $vacancies->firstWhere('id', $vacancyId) ?? $vacancies->first();

        $results = $vacancy
            ? CompatibilityResult::with('candidate')
                ->where('vacancy_id', $vacancy->id)
                ->orderByDesc('total_score')
                ->get()
            : collect();

        $avgScore   = round($results->avg('total_score') ?? 0, 1);
        $bestScore  = round($results->max('total_score') ?? 0, 1);
        $topCount   = $results->where('total_score', '>=', 80)->count();

        return view('ranking.index', compact(
            'vacancies', 'vacancy', 'results',
            'avgScore', 'bestScore', 'topCount'
        ));
    }
}
