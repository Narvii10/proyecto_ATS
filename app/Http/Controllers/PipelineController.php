<?php

namespace App\Http\Controllers;

use App\Models\CompatibilityResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public const STAGES = [
        'aplicado'   => 'Aplicado',
        'revision'   => 'En Revisión',
        'entrevista' => 'Entrevista',
        'oferta'     => 'Oferta',
        'contratado' => 'Contratado',
        'rechazado'  => 'Rechazado',
    ];

    public function update(Request $request, CompatibilityResult $result): JsonResponse
    {
        $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys(self::STAGES)),
        ]);

        $result->update(['pipeline_stage' => $request->stage]);

        return response()->json(['ok' => true, 'stage' => $request->stage]);
    }
}
