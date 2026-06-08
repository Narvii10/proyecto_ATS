<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParseTree extends Model
{
    protected $fillable = ['cv_document_id', 'tree_json', 'ast_json'];

    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(CvDocument::class);
    }
}
