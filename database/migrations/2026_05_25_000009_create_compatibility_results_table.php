<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compatibility_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vacancy_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_score', 5, 2)->default(0);
            $table->decimal('skills_score', 5, 2)->default(0);
            $table->decimal('languages_score', 5, 2)->default(0);
            $table->decimal('experience_score', 5, 2)->default(0);
            $table->decimal('education_score', 5, 2)->default(0);
            $table->decimal('certifications_score', 5, 2)->default(0);
            $table->json('matched')->nullable();
            $table->json('missing')->nullable();
            $table->unsignedSmallInteger('rank')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compatibility_results');
    }
};
