<?php

use App\Http\Controllers\ASTViewController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CandidateNoteController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\CVUploadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorsController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Candidates
Route::get('/candidates',         [CandidateController::class, 'index'])->name('candidates.index');
Route::get('/candidates/upload',  [CVUploadController::class,  'create'])->name('candidates.upload');
Route::post('/candidates/upload', [CVUploadController::class,  'store'])->name('candidates.store');
Route::get('/candidates/{candidate}',     [CandidateController::class, 'show'])->name('candidates.show');
Route::delete('/candidates/{candidate}',         [CandidateController::class, 'destroy'])->name('candidates.destroy');
Route::post('/candidates/{candidate}/reanalyze', [CandidateController::class, 'reanalyze'])->name('candidates.reanalyze');
Route::get('/candidates/{candidate}/cv',         [CandidateController::class, 'serveCV'])->name('candidates.cv');

// Notes
Route::post('/candidates/{candidate}/notes',              [CandidateNoteController::class, 'store'])->name('notes.store');
Route::delete('/candidates/{candidate}/notes/{note}',     [CandidateNoteController::class, 'destroy'])->name('notes.destroy');

// Compare
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');

// Pipeline
Route::patch('/pipeline/{result}/stage', [PipelineController::class, 'update'])->name('pipeline.update');

// Vacancies
Route::resource('vacancies', VacancyController::class);

// Ranking
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// AST viewer
Route::get('/ast', [ASTViewController::class, 'index'])->name('ast.index');

// Errors
Route::get('/errors', [ErrorsController::class, 'index'])->name('errors.index');

// Settings
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

// Reports
Route::get('/reports/candidate/{candidate}',          [ReportController::class, 'candidate'])->name('reports.candidate');
Route::get('/reports/ranking/{vacancy}',              [ReportController::class, 'ranking'])->name('reports.ranking');
Route::get('/reports/ranking/{vacancy}/csv',          [ReportController::class, 'rankingCsv'])->name('reports.ranking.csv');
