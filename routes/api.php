<?php

use App\Http\Controllers\Api\ASTController;
use Illuminate\Support\Facades\Route;

Route::get('/cv/{id}/ast', [ASTController::class, 'show']);
