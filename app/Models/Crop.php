<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    use HasFactory;
    protected $table = 'crops';
    protected $primaryKey = 'CropID';
    protected $fillable = ['CropName', 'CropType', 'HarvestTime'];
    public function plantingRecords()
    {
        return $this->hasMany(PlantingRecord::class, 'CropID', 'CropID');
    }
    public function harvestRecords()
    {
        return $this->hasMany(HarvestRecord::class, 'CropID', 'CropID');
    }
    public function fields()
    {
        return $this->belongsToMany(Field::class, 'planting_records', 'CropID', 'FieldID');
    }
}

