<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HarvestRecord;
use App\Models\Crop;
use App\Models\Field;
use App\Models\PlantingRecord;
use Carbon\Carbon;

class HarvestRecordList extends Component
{
    use WithPagination;

    public $selectedPlanting = null;
    public $plantingId, $cropId, $fieldId, $dateHarvested, $harvestYield, $startDate, $endDate, $totalYield = 0;

    public function mount()
    {
        $this->startDate = Carbon::now()->subMonth()->toDateString();
        $this->endDate   = Carbon::now()->toDateString();
        $this->calculateTotalYield();
    }

    public function updated()
    {
        $this->calculateTotalYield();
    }

    public function calculateTotalYield()
    {
        $this->totalYield = HarvestRecord::whereBetween('DateHarvested', [$this->startDate, $this->endDate])
            ->sum('HarvestYield');
        // Notify the UI
        $this->dispatch('totalYieldUpdated', $this->totalYield);
    }

    public function render()
    {
        $harvestRecords = HarvestRecord::with(['crop','field'])
            ->orderBy('HarvestID', 'desc')
            ->paginate(10);

        $activePlantings = PlantingRecord::notFullyHarvested()
            ->whereDoesntHave('cropStatusHistory', function ($query) {
                $query->whereIn('Status', ['Failed', 'Destroyed']);
            })
            ->with(['crop', 'field'])
            ->paginate(5); // Paginate the table

        return view('livewire.harvest-record-list', [
            'harvestRecords'   => $harvestRecords,
            'activePlantings'  => $activePlantings,
            'crops'            => Crop::all(),
            'fields'           => Field::all(),
            'totalYield'       => $this->totalYield
        ])->extends('layouts.app');
    }

    public function selectPlanting($plantingId)
    {
        $this->plantingId = $plantingId;
        $this->selectedPlanting = PlantingRecord::with(['crop', 'field'])->find($plantingId);
        session()->flash('message', 'Crop selected for harvesting.');
    }


    public function store()
    {
        $this->validate([
            'plantingId'    => 'required|exists:planting_records,PlantingID',
            'dateHarvested' => 'required|date',
            'harvestYield'  => 'required|numeric',
        ]);
        // Optionally auto-fill cropId, fieldId from the chosen planting
        $planting = PlantingRecord::findOrFail($this->plantingId);
        // Create the new harvest record
        HarvestRecord::create([
            'PlantingID'    => $planting->PlantingID,
            'CropID'        => $planting->CropID,   // or $this->cropId if you want to override
            'FieldID'       => $planting->FieldID,  // or $this->fieldId
            'DateHarvested' => $this->dateHarvested,
            'HarvestYield'  => $this->harvestYield,
        ]);
        // Update the status
        if (method_exists($planting, 'cropStatusHistory')) {
            $planting->cropStatusHistory()->create([
                'Status'     => 'Harvested',
                'StatusDate' => now(),
                'Notes'      => 'Harvested via HarvestRecordList component',
            ]);
        }

        session()->flash('message', 'Harvest record added successfully.');
        $this->dispatch('harvestAdded');
        $this->resetForm();
        $this->calculateTotalYield();
    }

    public function updatedPlantingId()
    {
        // If you want to auto-fill $this->cropId / $this->fieldId from the chosen planting
        $planting = PlantingRecord::find($this->plantingId);

        if ($planting) {
            $this->cropId  = $planting->CropID;
            $this->fieldId = $planting->FieldID;
        }
    }
    private function resetForm()
    {
        $this->reset([
            'plantingId',
            'cropId',
            'fieldId',
            'dateHarvested',
            'harvestYield',
        ]);
    }
}
