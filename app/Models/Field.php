<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;
    protected $primaryKey = 'FieldID';
    protected $fillable = ['Size', 'Location'];
    public function plantingRecords()
    {
        return $this->hasMany(PlantingRecord::class, 'FieldID');
    }
    public function crops()
    {
        return $this->belongsToMany(Crop::class, 'planting_records', 'FieldID', 'CropID');
    }
    public function harvestRecords()
    {
        return $this->hasMany(HarvestRecord::class, 'FieldID');
    }
}

