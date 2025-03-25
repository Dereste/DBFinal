<!-- Include FontAwesome in your layout (if not already added) -->
<div class="p-6 space-y-6">
    <!-- Page Header -->
    <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
        <i class="fas fa-seedling text-green-500"></i> Manage Active Crops
    </h2>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-800 p-3 rounded-lg shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-2"></i> {{ session('message') }}
        </div>
    @endif

    <!-- Add New Planting Form -->
    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-500"></i> Add a New Planting
        </h3>

        <form wire:submit.prevent="store" class="grid md:grid-cols-4 gap-4">
            <!-- Crop Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-600">Crop</label>
                <select wire:model="cropId" class="w-full border p-2 rounded-lg">
                    <option value="">Select Crop</option>
                    @foreach ($crops as $crop)
                        <option value="{{ $crop->CropID }}">{{ $crop->CropName }}</option>
                    @endforeach
                </select>
                @error('cropId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Field Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-600">Field</label>
                <select wire:model="fieldId" class="w-full border p-2 rounded-lg">
                    <option value="">Select Field</option>
                    @foreach ($fields as $field)
                        <option value="{{ $field->FieldID }}">{{ $field->Location }}</option>
                    @endforeach
                </select>
                @error('fieldId') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Date Planted -->
            <div>
                <label class="block text-sm font-semibold text-gray-600">Date Planted</label>
                <input type="date" wire:model="datePlanted" class="w-full border p-2 rounded-lg">
                @error('datePlanted') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label class="block text-sm font-semibold text-gray-600">Quantity (kg/plants)</label>
                <input type="number" wire:model="quantity" class="w-full border p-2 rounded-lg" placeholder="0">
                @error('quantity') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Submit Button -->
            <div class="md:col-span-4 flex justify-end">
                <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 shadow-md flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add Record
                </button>
            </div>
        </form>
    </div>

    <!-- Planting Records Table -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        <!-- Table Header -->
        <div class="bg-green-500 text-white text-lg font-bold p-3 rounded-t-lg flex items-center gap-2">
            <i class="fas fa-leaf"></i> Active Plantings
        </div>

        <!-- Scrollable Table -->
        <div class="overflow-x-auto max-h-[600px]">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100 text-gray-700">
                <tr class="text-left">
                    <th class="p-3">Crop</th>
                    <th class="p-3">Field</th>
                    <th class="p-3">Date Planted</th>
                    <th class="p-3">Expected Harvest Date</th>
                    <th class="p-3">Quantity</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
                </thead>
                <tbody class="text-gray-800">
                @foreach ($plantings as $planting)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3">{{ optional($planting->crop)->CropName }}</td>
                        <td class="p-3">{{ optional($planting->field)->Location }}</td>
                        <td class="p-3">{{ $planting->DatePlanted }}</td>
                        <td class="p-3 text-blue-600 font-semibold">{{ $planting->ExpectedHarvestDate }}</td>
                        <td class="p-3">{{ $planting->Quantity }}</td>
                        <td class="p-3 flex justify-center space-x-3">

                            <!-- Mark as Failed Button -->
                            <button wire:click="markAsFailed({{ $planting->PlantingID }}, 'Pest damage observed')"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600 shadow-sm flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i> Failed
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
