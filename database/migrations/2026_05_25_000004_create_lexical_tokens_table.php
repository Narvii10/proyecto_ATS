<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lexical_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_document_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->text('value');
            $table->unsignedInteger('line')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lexical_tokens');
    }
};
