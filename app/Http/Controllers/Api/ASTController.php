<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CvDocument;
use Illuminate\Http\JsonResponse;

class ASTController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $cv = CvDocument::findOrFail($id);
        $tree = $cv->parseTree;

        if (!$tree || !$tree->ast_json) {
            return response()->json(['error' => 'AST not yet generated for this CV.'], 404);
        }

        return response()->json([
            'cv_id' => $cv->id,
            'ast'   => json_decode($tree->ast_json, true),
        ]);
    }
}
