<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CompatibilityResult;
use App\Models\Vacancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function candidate(Candidate $candidate): Response
    {
        $candidate->load([
            'cvDocuments.lexicalTokens',
            'cvDocuments.lexicalErrors',
            'cvDocuments.syntacticErrors',
            'cvDocuments.semanticErrors',
            'compatibilityResults.vacancy',
        ]);

        $pdf = Pdf::loadView('pdf.candidate', compact('candidate'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("reporte_candidato_{$candidate->id}.pdf");
    }

    public function ranking(Vacancy $vacancy): Response
    {
        $results = CompatibilityResult::with('candidate')
            ->where('vacancy_id', $vacancy->id)
            ->orderByDesc('total_score')
            ->get();

        $pdf = Pdf::loadView('pdf.ranking', compact('vacancy', 'results'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("ranking_vacante_{$vacancy->id}.pdf");
    }

    public function rankingCsv(Vacancy $vacancy): StreamedResponse
    {
        $results = CompatibilityResult::with('candidate')
            ->where('vacancy_id', $vacancy->id)
            ->orderByDesc('total_score')
            ->get();

        $filename = "ranking_{$vacancy->id}.csv";

        return response()->streamDownload(function () use ($results) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Candidato', 'Email', 'Total%', 'Skills%', 'Lenguajes%', 'Experiencia%', 'Educación%', 'Certs%', 'Pipeline']);
            foreach ($results as $i => $r) {
                fputcsv($out, [
                    $i + 1,
                    $r->candidate?->name ?? '—',
                    $r->candidate?->email ?? '—',
                    number_format($r->total_score, 1),
                    number_format($r->skills_score, 1),
                    number_format($r->languages_score, 1),
                    number_format($r->experience_score, 1),
                    number_format($r->education_score, 1),
                    number_format($r->certifications_score, 1),
                    $r->pipeline_stage ?? 'aplicado',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
