<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Crop;
use App\Models\PlantingRecord;

class CropAdd extends Component
{
    public $cropName, $cropType, $harvestTime, $crops;

    protected $rules = [
        'cropName' => 'required|string|max:255|unique:crops,CropName',
        'cropType' => 'required|string|max:255',
        'harvestTime' => 'required|integer|min:1',
    ];
    public function store()
    {
        $this->validate();
        Crop::create([
            'CropName' => $this->cropName,
            'CropType' => $this->cropType,
            'HarvestTime' => $this->harvestTime,
        ]);
        session()->flash('message', 'Crop added successfully.');
        $this->reset(['cropName', 'cropType', 'harvestTime']);
        $this->render();
    }
    public function render()
    {
        $this->crops = Crop::withCount([
            'plantingRecords as currently_planted' => function ($query) {
                $query->leftJoin('harvest_records', 'harvest_records.PlantingID', '=', 'planting_records.PlantingID')
                    ->whereNull('harvest_records.PlantingID')
                    ->whereNotExists(function ($subQuery) {
                        $subQuery->selectRaw(1)
                            ->from('crop_status_history')
                            ->whereRaw('crop_status_history.PlantingID = planting_records.PlantingID')
                            ->orderByDesc('crop_status_history.updated_at')
                            ->limit(1)
                            ->where('crop_status_history.Status', '=', 'Failed'); // Exclude Failed crops
                    });
            }
        ])->get();

        return view('livewire.crop-add', ['crops' => $this->crops])->extends('layouts.app');
    }

}
