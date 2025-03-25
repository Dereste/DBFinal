<div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6 mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">  Farm Fields</h2>
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 border border-green-300 rounded">
            {{ session('message') }}
        </div>
    @endif
    <!-- Form -->
    <form wire:submit.prevent="store" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="number" wire:model="size" placeholder="Size (hectares)"
                   class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-400">
            <input type="text" wire:model="location" placeholder="Location"
                   class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-400">
        </div>
        <button type="submit"
                class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            Add Field
        </button>
    </form>
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead class="bg-green-600 text-white">
            <tr>
                <th class="py-3 px-4 text-left">Size (ha)</th>
                <th class="py-3 px-4 text-left">Location</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($fields as $field)
                <tr class="border-b hover:bg-green-50">
                    <td class="py-2 px-4">{{ $field->Size }}</td>
                    <td class="py-2 px-4">{{ $field->Location }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
