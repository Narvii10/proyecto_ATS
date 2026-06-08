<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CvDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ASTViewController extends Controller
{
    public function index(Request $request): View
    {
        $candidates = Candidate::whereHas('cvDocuments.parseTree')->latest()->get();
        $candidateId = $request->input('candidate_id', $candidates->first()?->id);
        $candidate  = $candidates->firstWhere('id', $candidateId) ?? $candidates->first();

        $latestCv  = $candidate?->cvDocuments()->has('parseTree')->latest()->first();
        $astData   = null;

        if ($latestCv?->parseTree?->ast_json) {
            $astData = json_decode($latestCv->parseTree->ast_json, true);
        }

        $totalNodes   = $astData ? $this->countNodes($astData) : 0;
        $depth        = $astData ? $this->calcDepth($astData) : 0;
        $sectionCount = $astData ? count($astData['children'] ?? []) : 0;

        $nodeTypes = $astData ? $this->collectTypes($astData) : [];

        return view('ast.show', compact(
            'candidates', 'candidate', 'latestCv',
            'astData', 'totalNodes', 'depth', 'sectionCount', 'nodeTypes'
        ));
    }

    private function countNodes(?array $node): int
    {
        if (!$node) {
            return 0;
        }
        $count = 1;
        foreach ($node['children'] ?? [] as $child) {
            $count += $this->countNodes($child);
        }
        return $count;
    }

    private function calcDepth(?array $node, int $d = 0): int
    {
        if (!$node || empty($node['children'])) {
            return $d;
        }
        $max = $d;
        foreach ($node['children'] as $child) {
            $max = max($max, $this->calcDepth($child, $d + 1));
        }
        return $max;
    }

    private function collectTypes(?array $node, array &$types = []): array
    {
        if (!$node) {
            return $types;
        }
        $types[$node['type']] = true;
        foreach ($node['children'] ?? [] as $child) {
            $this->collectTypes($child, $types);
        }
        return array_keys($types);
    }
}
