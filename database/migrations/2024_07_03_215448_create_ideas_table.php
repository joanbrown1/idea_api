<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('division')->nullable();
            $table->string('innovation')->nullable();
            $table->string('improvement')->nullable();
            $table->string('problem')->nullable();
            $table->string('effectuate')->nullable();
            $table->string('others')->nullable();
            $table->string('picture')->nullable();
            $table->string('proposal')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->nullable();
            $table->string('submitted_name')->nullable();
            $table->string('submitted_department')->nullable();
            $table->string('submitted_zone')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
