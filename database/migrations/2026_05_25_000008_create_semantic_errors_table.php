<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semantic_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_document_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('field', 60);
            $table->string('severity', 10);
            $table->text('message');
            $table->text('suggestion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semantic_errors');
    }
};
