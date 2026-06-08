<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CvDocument extends Model
{
    protected $fillable = [
        'candidate_id', 'original_filename', 'format',
        'file_path', 'raw_content', 'parsed_content', 'processing_status',
    ];

    protected $casts = [
        'parsed_content' => 'array',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function lexicalTokens(): HasMany
    {
        return $this->hasMany(LexicalToken::class);
    }

    public function lexicalErrors(): HasMany
    {
        return $this->hasMany(LexicalError::class);
    }

    public function parseTree(): HasOne
    {
        return $this->hasOne(ParseTree::class);
    }

    public function syntacticErrors(): HasMany
    {
        return $this->hasMany(SyntacticError::class);
    }

    public function semanticErrors(): HasMany
    {
        return $this->hasMany(SemanticError::class);
    }
}
