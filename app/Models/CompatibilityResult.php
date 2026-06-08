<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompatibilityResult extends Model
{
    protected $fillable = [
        'candidate_id', 'vacancy_id',
        'total_score', 'skills_score', 'languages_score',
        'experience_score', 'education_score', 'certifications_score',
        'matched', 'missing', 'rank', 'pipeline_stage',
    ];

    protected $casts = [
        'matched'             => 'array',
        'missing'             => 'array',
        'total_score'         => 'float',
        'skills_score'        => 'float',
        'languages_score'     => 'float',
        'experience_score'    => 'float',
        'education_score'     => 'float',
        'certifications_score'=> 'float',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }
}
