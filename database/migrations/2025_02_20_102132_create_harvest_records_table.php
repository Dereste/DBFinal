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
        Schema::create('harvest_records', function (Blueprint $table) {
            $table->bigIncrements('HarvestID');
            $table->foreignId('PlantingID')->constrained('planting_records', 'PlantingID')->onDelete('cascade');
            $table->foreignId('CropID')->constrained('crops','CropID')->onDelete('cascade');
            $table->foreignId('FieldID')->constrained('fields','FieldID')->onDelete('cascade');
            $table->date('DateHarvested');
            $table->float('HarvestYield'); // kg
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_records');
    }
};
