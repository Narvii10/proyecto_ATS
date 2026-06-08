<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CompatibilityResult;
use App\Models\CvDocument;
use App\Models\LexicalError;
use App\Models\LexicalToken;
use App\Models\SemanticError;
use App\Models\SyntacticError;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalCandidates  = Candidate::count();
        $analyzedCvs      = CvDocument::where('processing_status', 'completed')->count();
        $matchRate        = round(CompatibilityResult::avg('total_score') ?? 0, 1);
        $topMatches       = CompatibilityResult::where('total_score', '>=', 80)->count();

        // Recent candidates with best compatibility score
        $recentCandidates = Candidate::with(['cvDocuments', 'compatibilityResults'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($c) {
                $c->best_score = $c->compatibilityResults->max('total_score') ?? 0;
                $c->status     = $c->cvDocuments->first()?->processing_status ?? 'pending';
                return $c;
            });

        // Latest vacancy for top-5 chart
        $latestVacancy = Vacancy::latest()->first();
        $topCandidates = [];
        if ($latestVacancy) {
            $topCandidates = CompatibilityResult::with('candidate')
                ->where('vacancy_id', $latestVacancy->id)
                ->orderByDesc('total_score')
                ->limit(5)
                ->get()
                ->map(fn($r) => [
                    'name'  => $r->candidate?->name ?? 'Sin nombre',
                    'score' => $r->total_score,
                ])
                ->toArray();
        }

        // Error distribution
        $errorDistribution = [
            'Léxicos'     => LexicalError::count(),
            'Sintácticos' => SyntacticError::count(),
            'Semánticos'  => SemanticError::count(),
        ];

        // Uploads per day (last 14 days)
        $uploadsByDay = CvDocument::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $uploadsByDayFilled = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $uploadsByDayFilled[$date] = $uploadsByDay[$date] ?? 0;
        }

        // Top 10 skills
        $skillsFrequency = LexicalToken::select('value', DB::raw('COUNT(*) as count'))
            ->where('type', 'TOKEN_SKILL')
            ->groupBy('value')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'value')
            ->toArray();

        return view('dashboard.index', [
            'totalCandidates'   => $totalCandidates,
            'analyzedCvs'       => $analyzedCvs,
            'matchRate'         => $matchRate,
            'topMatches'        => $topMatches,
            'recentCandidates'  => $recentCandidates,
            'topCandidates'     => $topCandidates,
            'errorDistribution' => $errorDistribution,
            'uploadsByDay'      => $uploadsByDayFilled,
            'skillsFrequency'   => $skillsFrequency,
            'latestVacancy'     => $latestVacancy,
        ]);
    }
}
