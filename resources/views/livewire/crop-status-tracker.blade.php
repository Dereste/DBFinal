<div>
    <h2>Update Crop Status</h2>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <form wire:submit.prevent="updateStatus">
        <select wire:model="plantingId">
            <option value="">Select Planting</option>
            @foreach ($plantings as $planting)
                <option value="{{ $planting->id }}">{{ $planting->id }} - {{ $planting->crop->CropName }}</option>
            @endforeach
        </select>

        <select wire:model="status">
            <option value="Planted">Planted</option>
            <option value="Growing">Growing</option>
            <option value="Ready to Harvest">Ready to Harvest</option>
            <option value="Harvested">Harvested</option>
            <option value="Failed">Failed</option>
        </select>

        <input type="text" wire:model="notes" placeholder="Notes (optional)">
        <button type="submit">Update Status</button>
    </form>
</div>
