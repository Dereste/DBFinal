<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantingRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'PlantingID';
    protected $fillable = ['CropID', 'FieldID', 'DatePlanted', 'Quantity'];

    // Add your booted logic if needed:
    protected static function booted()
    {
        static::created(function ($planting) {
            // Example: automatically create a status of "Growing"
            $planting->cropStatusHistory()->create([
                'Status' => 'Growing',
                'StatusDate' => $planting->DatePlanted,
                'Notes' => 'Automatically marked as growing upon planting.',
            ]);
        });
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class, 'CropID', 'CropID');
    }

    public function field()
    {
        return $this->belongsTo(Field::class, 'FieldID', 'FieldID');
    }

    public function cropStatusHistory()
    {
        return $this->hasMany(CropStatusHistory::class, 'PlantingID', 'PlantingID');
    }

    public function harvestRecords()
    {
        return $this->hasMany(HarvestRecord::class, 'HarvestID', 'HarvestID');
    }

    public function latestStatus()
    {
        return $this->hasOne(CropStatusHistory::class, 'PlantingID', 'PlantingID')->latestOfMany('StatusDate');
    }

    public function scopeNotFullyHarvested($query)
    {
        return $query->whereDoesntHave('cropStatusHistory', function ($q) {
            $q->where('Status', 'Harvested');
        });
    }
    // Accessor for Expected Harvest Date
    public function getExpectedHarvestDateAttribute()
    {
        return $this->DatePlanted ? date('Y-m-d', strtotime($this->DatePlanted . ' + ' . $this->crop->HarvestTime . ' days')) : null;
    }

}
