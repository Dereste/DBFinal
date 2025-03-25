<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropStatusHistory extends Model
{
    use HasFactory;
    protected $primaryKey = 'StatusID';
    protected $table = 'crop_status_history';
    protected $fillable = ['PlantingID', 'Status', 'StatusDate', 'Notes'];
    public $timestamps = true;
    public function plantingRecord()
    {
        return $this->belongsTo(PlantingRecord::class, 'PlantingID');
    }
    public function harvestRecord()
    {
        return $this->belongsTo(PlantingRecord::class, 'PlantingID');
    }
}

