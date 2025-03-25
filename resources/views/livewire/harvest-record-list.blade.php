<div class="p-8">
    <h2 class="text-2xl font-bold mb-4 flex items-center">
        <i class="fas fa-seedling mr-2 text-green-600"></i> Harvest Records
    </h2>

    @if (session()->has('message'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4 flex items-center">
            <i class="fas fa-check-circle mr-2"></i> {{ session('message') }}
        </div>
    @endif

    <div class="border border-gray-300 p-4 rounded-lg mb-6 shadow-md">
        <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">
            <i class="fas fa-seedling"></i> Select a Crop to Harvest
        </h2>

        <form wire:submit.prevent="store">
            <div>
                <h2 class="text-lg font-semibold mb-2">Select a Crop to Harvest</h2>
                <div class="overflow-x-auto border rounded-lg shadow">
                    <table class="w-full border border-gray-300">
                        <thead>
                        <tr class="bg-gray-200 text-left">
                            <th class="border px-4 py-2">Crop Name</th>
                            <th class="border px-4 py-2">Field</th>
                            <th class="border px-4 py-2">Date Planted</th>
                            <th class="border px-4 py-2">Quantity</th>
                            <th class="border px-4 py-2">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($activePlantings as $planting)
                            <tr class="hover:bg-gray-50">
                                <td class="border px-4 py-2">{{ $planting->crop->CropName }}</td>
                                <td class="border px-4 py-2">{{ $planting->field->Location }}</td>
                                <td class="border px-4 py-2">{{ $planting->DatePlanted }}</td>
                                <td class="border px-4 py-2">{{ $planting->Quantity }}</td>
                                <td class="border px-4 py-2">
                                    <button type="button"
                                            wire:click="selectPlanting({{ $planting->PlantingID }})"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded flex items-center">
                                        <i class="fas fa-hand-pointer mr-1"></i> Select
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    {{ $activePlantings->links() }}
                </div>
            </div>

            @if($selectedPlanting)
                <div class="bg-gray-100 p-4 rounded-lg mt-4 shadow-md">
                    <h3 class="text-lg font-semibold">Selected Crop</h3>
                    <p><strong>Crop:</strong> {{ optional($selectedPlanting->crop)->CropName }}</p>
                    <p><strong>Field:</strong> {{ optional($selectedPlanting->field)->Location }}</p>
                    <p><strong>Date Planted:</strong> {{ $selectedPlanting->DatePlanted }}</p>
                    <p><strong>Quantity:</strong> {{ $selectedPlanting->Quantity }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Date Harvested</label>
                    <input type="date" wire:model="dateHarvested" class="border p-2 rounded w-full">
                    @error('dateHarvested') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Yield (kg)</label>
                    <input type="number" wire:model="harvestYield" class="border p-2 rounded w-full" placeholder="0">
                    @error('harvestYield') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Add Harvest
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="border border-gray-300 rounded-lg overflow-hidden shadow-md">
        <div class="bg-green-500 text-white p-2 rounded-t-lg font-bold grid grid-cols-5">
            <div class="p-2">Crop</div>
            <div class="p-2">Field</div>
            <div class="p-2">Date Planted</div>
            <div class="p-2">Date Harvested</div>
            <div class="p-2">Yield (kg)</div>
        </div>
        <div class="bg-white max-h-[600px] overflow-y-auto">
            <table class="w-full table-fixed border-collapse">
                <tbody>
                @forelse($harvestRecords as $record)
                    <tr class="grid grid-cols-5 border-b hover:bg-gray-100 transition duration-150 text-left odd:bg-gray-50">
                        <td class="p-2 truncate">{{ optional($record->crop)->CropName }}</td>
                        <td class="p-2 truncate">{{ optional($record->field)->Location }}</td>
                        <td class="p-2 truncate">{{ optional($record->plantingRecord)->DatePlanted }}</td>
                        <td class="p-2 truncate">{{ $record->DateHarvested }}</td>
                        <td class="p-2 truncate">{{ $record->HarvestYield }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i> No harvest records found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($harvestRecords->hasPages())
            <div class="mt-4">
                {{ $harvestRecords->links() }}
            </div>
        @endif
    </div>
</div>
