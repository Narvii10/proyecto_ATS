<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacancy extends Model
{
    protected $fillable = [
        'title', 'description', 'location', 'job_type', 'salary_range',
        'required_skills', 'required_languages',
        'required_years_experience', 'required_education_level',
        'preferred_certifications',
    ];

    protected $casts = [
        'required_skills'          => 'array',
        'required_languages'       => 'array',
        'preferred_certifications' => 'array',
        'required_years_experience'=> 'integer',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(CompatibilityResult::class);
    }
}
