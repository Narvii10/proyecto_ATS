<?php

namespace App\Http\Controllers;

use App\Models\LexicalError;
use App\Models\SemanticError;
use App\Models\SyntacticError;
use Illuminate\View\View;

class ErrorsController extends Controller
{
    public function index(): View
    {
        $lexErrors = LexicalError::with('cvDocument.candidate')->latest()->get();
        $synErrors = SyntacticError::with('cvDocument.candidate')->latest()->get();
        $semErrors = SemanticError::with('cvDocument.candidate')->latest()->get();

        $totalErrors    = $lexErrors->count() + $synErrors->count() + $semErrors->count();
        $criticalErrors = $lexErrors->count() + $semErrors->where('severity', 'error')->count();
        $warnings       = $synErrors->count() + $semErrors->where('severity', 'warning')->count();

        return view('errors.index', compact(
            'lexErrors', 'synErrors', 'semErrors',
            'totalErrors', 'criticalErrors', 'warnings'
        ));
    }
}
