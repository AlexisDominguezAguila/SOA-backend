<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->year('year_start');
            $table->year('year_end');
            $table->boolean('is_active')->default(true);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deans');
    }
};
