<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PlantingRecord;
use App\Models\HarvestRecord;
use App\Models\Crop;
use App\Models\Field;
use Carbon\Carbon;

class PlantingRecordList extends Component
{
    protected $listeners = ['harvestRecord'];

    public $cropId, $fieldId, $datePlanted, $quantity, $plantings;

    public function mount()
    {
        $this->loadPlantings();
    }

    public function render()
    {
        $plantingRecords = PlantingRecord::with('crop', 'field')->get();
        return view('livewire.planting-record-list', [
            'plantingRecords' => $plantingRecords,
            'plantings' => $this->plantings,
            'crops' => Crop::all(),
            'fields' => Field::all()
        ])->extends('layouts.app');
    }
    public function store()
    {
        $this->validate([
            'cropId'      => 'required|exists:crops,CropID',
            'fieldId'     => 'required|exists:fields,FieldID',
            'datePlanted' => 'required|date',
            'quantity'    => 'required|numeric',
        ]);

        $newPlantingStart = Carbon::parse($this->datePlanted);
        $crop = Crop::find($this->cropId);
        $newPlantingEnd = $newPlantingStart->copy()->addDays($crop->HarvestTime); // Expected harvest date

        // Find an existing planting that overlaps with the new planting period
        $existingPlanting = PlantingRecord::where('FieldID', $this->fieldId)
            ->whereDoesntHave('cropStatusHistory', function ($query) {
                $query->whereIn('Status', ['Harvested', 'Failed', 'Destroyed']);
            })
            ->where(function ($query) use ($newPlantingStart, $newPlantingEnd) {
                $query->whereBetween('DatePlanted', [$newPlantingStart, $newPlantingEnd]) // Starts within the new crop's duration
                ->orWhereBetween('DatePlanted', [$newPlantingStart, $newPlantingEnd]) // Ends within the new crop's duration
                ->orWhere(function ($query) use ($newPlantingStart, $newPlantingEnd) {
                    $query->where('DatePlanted', '<', $newPlantingStart)
                        ->whereRaw("(DatePlanted + INTERVAL (SELECT HarvestTime FROM crops WHERE crops.CropID = planting_records.CropID) DAY) > ?", [$newPlantingStart]);
                }); // Existing crop spans over the new planting
            })
            ->first();

        if ($existingPlanting) {
            $this->addError('fieldId', 'This field is already occupied by an active crop during this period.');
            return;
        }

        PlantingRecord::create([
            'CropID'      => $this->cropId,
            'FieldID'     => $this->fieldId,
            'DatePlanted' => $this->datePlanted,
            'Quantity'    => $this->quantity,
        ]);

        session()->flash('message', 'Planting record added.');
        $this->reset(['cropId', 'fieldId', 'datePlanted', 'quantity']);
        $this->loadPlantings();
    }


    public function markAsFailed($plantingId, $notes)
    {
        $planting = PlantingRecord::findOrFail($plantingId);

        // Record the failure event in cropStatusHistory.
        if (method_exists($planting, 'cropStatusHistory')) {
            $planting->cropStatusHistory()->create([
                'Status'     => 'Failed',  // Renamed from "Destroyed"
                'StatusDate' => now(),
                'Notes'      => $notes,
            ]);
        }

        // (Optional) If you add a dedicated status column to planting_records,
        // you could update it here:
        // $planting->update(['status' => 'Failed']);

        session()->flash('message', 'Record marked as failed.');
        $this->loadPlantings();  // refresh the list
    }

    private function loadPlantings()
    {
        $this->plantings = PlantingRecord::with(['crop', 'field'])
            ->whereDoesntHave('cropStatusHistory', function ($query) {
                $query->whereIn('Status', ['Harvested', 'Failed', 'Destroyed']);
            })->get();
    }

}

