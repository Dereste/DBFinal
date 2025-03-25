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
        Schema::create('crop_status_history', function (Blueprint $table) {
            $table->bigIncrements('StatusID');
            $table->foreignId('PlantingID')->constrained('planting_records','PlantingID')->onDelete('cascade');
            $table->enum('Status', ['Growing', 'Ready to Harvest', 'Harvested', 'Failed']);
            $table->date('StatusDate');
            $table->text('Notes')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_status_history');
    }
};
