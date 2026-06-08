<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->json('required_skills')->nullable();
            $table->json('required_languages')->nullable();
            $table->unsignedTinyInteger('required_years_experience')->default(0);
            $table->string('required_education_level')->default('any');
            $table->json('preferred_certifications')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
