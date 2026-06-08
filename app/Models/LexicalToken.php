<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LexicalToken extends Model
{
    protected $fillable = ['cv_document_id', 'type', 'value', 'line', 'position'];

    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(CvDocument::class);
    }
}
