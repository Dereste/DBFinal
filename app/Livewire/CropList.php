<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Crop;
use App\Models\PlantingRecord;
use App\Models\HarvestRecord;

class CropList extends Component
{
    protected $listeners = ['harvestUpdated' => '$refresh'];

    public function deletePlantingRecord($plantingId)
    {
        $planting = PlantingRecord::with('harvestRecords')->find($plantingId);

        if ($planting) {
            // Delete related harvest records first
            HarvestRecord::where('PlantingID', $plantingId)->delete();

            // Then delete the planting record
            $planting->delete();

            // Dispatch an event to update the UI
            $this->dispatch('plantingDeleted', $plantingId);
        }
    }

    public function render()
    {
        $crops = Crop::with([
            'plantingRecords.field',
            'plantingRecords.latestStatus', // uses latestStatus instead of full history
            'plantingRecords.harvestRecords', // Ensure we load harvest records for each planting
        ])->get();

        $flattened = collect();
        foreach ($crops as $crop) {
            foreach ($crop->plantingRecords as $planting) {
                $matchingHarvests = HarvestRecord::where('PlantingID', $planting->PlantingID)->get();
                $totalYield = $matchingHarvests->sum('HarvestYield');
                $lastHarvested = $matchingHarvests->isNotEmpty()
                    ? $matchingHarvests->sortByDesc('DateHarvested')->first()->DateHarvested
                    : null;

                $latestStatus = optional($planting->latestStatus)->Status;
                $flattened->push([
                    'PlantingID'    => $planting->PlantingID, // Unique identifier required
                    'CropID'        => $crop->CropID,
                    'CropName'      => $crop->CropName,
                    'CropType'      => $crop->CropType,
                    'DatePlanted'   => $planting->DatePlanted,
                    'Quantity'      => $planting->Quantity,
                    'DateHarvested' => $lastHarvested,
                    'HarvestYield'  => $totalYield,
                    'Status'        => $latestStatus,
                    'Location'      => optional($planting->field)->Location,
                ]);
            }
        }
        return view('livewire.crop-list', [
            'crops' => $flattened,
        ])->extends('layouts.app');
    }
}
