
<div class="p-8" wire:poll.5s>
<h2 class="text-2xl font-bold mb-4">Add New Crop</h2>
    @if (session()->has('message'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    <div class="border border-gray-300 p-4 rounded-lg mb-6">
        <form wire:submit.prevent="store" class="space-y-4">
            <!-- Crop Name -->
            <div>
                <label class="block text-sm font-semibold mb-1">Crop Name</label>
                <input type="text" wire:model="cropName" class="border p-2 rounded w-full" placeholder="Enter crop name">
                @error('cropName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Crop Type -->
            <div>
                <label class="block text-sm font-semibold mb-1">Crop Type</label>
                <input type="text" wire:model="cropType" class="border p-2 rounded w-full" placeholder="Enter crop type">
                @error('cropType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Harvest Time -->
            <div>
                <label class="block text-sm font-semibold mb-1">Harvest Time (Days)</label>
                <input type="number" wire:model="harvestTime" class="border p-2 rounded w-full" placeholder="Enter harvest time">
                @error('harvestTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Submit Button -->
            <div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add Crop
                </button>
            </div>
        </form>
    </div>
    <!-- Crops Table -->
    <h2 class="text-2xl font-bold mb-4">Crops</h2>
    <div class="border border-gray-300 rounded-lg overflow-hidden">
        <div class="bg-green-500 text-white p-2 rounded-t-lg font-bold">
            <div class="grid grid-cols-4">
                <div class="p-2">Crop Name</div>
                <div class="p-2">Type</div>
                <div class="p-2">Harvest Time</div>
                <div class="p-2">Currently Planted</div>
            </div>
        </div>
        <div class="bg-white max-h-[600px] overflow-y-auto">
            <table class="w-full table-fixed border-collapse">
                <tbody>
                @forelse($crops as $crop)
                    <tr class="grid grid-cols-4 border-b hover:bg-gray-100 transition duration-150 text-left odd:bg-gray-50">
                        <td class="p-2 truncate">{{ $crop->CropName }}</td>
                        <td class="p-2 truncate">{{ $crop->CropType }}</td>
                        <td class="p-2 truncate">{{ $crop->HarvestTime }} days</td>
                        <td class="p-2 truncate">{{ $crop->currently_planted }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            No crops found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
