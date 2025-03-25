<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HarvestRecord extends Model
{
    use HasFactory;
    protected $primaryKey = 'HarvestID';
    protected $table = 'harvest_records';
    protected $fillable = ['CropID', 'FieldID', 'PlantingID','DateHarvested', 'HarvestYield'];
    public function crop()
    {
        return $this->belongsTo(Crop::class, 'CropID', 'CropID');
    }
    public function field()
    {
        return $this->belongsTo(Field::class, 'FieldID', 'FieldID');
    }
    public function plantingRecord()
    {
        return $this->belongsTo(PlantingRecord::class, 'PlantingID', 'PlantingID');
    }
    protected static function booted()
    {
        static::created(function ($harvest) {
            $planting = PlantingRecord::find($harvest->PlantingID);
            if ($planting) {
                $planting->cropStatusHistory()->create([
                    'Status' => 'Harvested',
                    'StatusDate' => $harvest->DateHarvested,
                    'Notes' => 'Crop has been harvested.',
                ]);
            }
        });
    }
}

