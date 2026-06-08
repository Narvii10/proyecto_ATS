<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $stats = [
            'phases'          => 5,
            'token_types'     => 11,
            'semantic_rules'  => 11,
        ];

        return view('settings.index', compact('stats'));
    }
}
