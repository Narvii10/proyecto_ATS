<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
            $table->string('job_type')->nullable()->after('location');
            $table->string('salary_range')->nullable()->after('job_type');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn(['location', 'job_type', 'salary_range']);
        });
    }
};
