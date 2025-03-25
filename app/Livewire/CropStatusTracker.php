<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CropStatusHistory;
use App\Models\PlantingRecord;

class CropStatusTracker extends Component
{
    public $plantingId, $status, $notes;
    public function render()
    {
        return view('livewire.crop-status-tracker', [
            'plantings' => PlantingRecord::all()
        ]);
    }

    public function updateStatus()
    {
        $this->validate([
            'plantingId' => 'required|exists:planting_records,id',
            'status' => 'required|in:Planted,Growing,Ready to Harvest,Harvested,Failed',
            'notes' => 'nullable|string'
        ]);
        CropStatusHistory::create([
            'PlantingID' => $this->plantingId,
            'Status' => $this->status,
            'Notes' => $this->notes,
        ]);
        session()->flash('message', 'Crop status updated successfully.');
    }
}

