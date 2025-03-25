<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('planting_records', function (Blueprint $table) {
            $table->bigIncrements('PlantingID');
            $table->foreignId('CropID')->constrained('crops','CropID')->onDelete('cascade');
            $table->foreignId('FieldID')->constrained('fields','FieldID')->onDelete('cascade');
            $table->date('DatePlanted');
            $table->float('Quantity'); // kg or number of plants
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_records');
    }
};
