<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'age', 'vacancy_id',
        'ai_summary', 'ai_category', 'ai_assessment',
        'ai_strengths', 'ai_weaknesses',
    ];

    protected $casts = [
        'ai_strengths'  => 'array',
        'ai_weaknesses' => 'array',
        'age'           => 'integer',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    public function cvDocuments(): HasMany
    {
        return $this->hasMany(CvDocument::class);
    }

    public function compatibilityResults(): HasMany
    {
        return $this->hasMany(CompatibilityResult::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CandidateNote::class)->latest();
    }

    public function latestCv(): ?CvDocument
    {
        return $this->cvDocuments()->latest()->first();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        return $query->where(fn($q) =>
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
        );
    }
}
