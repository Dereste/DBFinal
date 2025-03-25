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
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('ReportID');
            $table->foreignId('UserID')->constrained('users')->onDelete('cascade');
            $table->date('DateGenerated');
            $table->text('HarvestSummary')->nullable();
            $table->text('YieldAnalysis')->nullable();
            $table->float('TotalLandArea')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
