<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemanticError extends Model
{
    protected $fillable = ['cv_document_id', 'code', 'field', 'severity', 'message', 'suggestion'];

    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(CvDocument::class);
    }
}
