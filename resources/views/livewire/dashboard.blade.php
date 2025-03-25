@extends('layouts.app')

@section('content')
    <div class="p-6 bg-white shadow-lg rounded-xl relative">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('/path/to/your/image.jpg');"></div>
        <div class="relative z-10">
            <!-- Dashboard Title -->
            <h2 class="text-3xl font-extrabold mb-6 text-green-800">Dashboard</h2>

            <!-- Yield Comparison -->
            <div class="bg-gray-50 p-5 rounded-lg shadow flex flex-col md:flex-row items-center justify-between mb-6 hover:shadow-xl transition duration-200">
                <div>
                    <p class="text-lg font-semibold text-gray-700">Yield Comparison</p>
                    <p class="text-2xl font-bold">
                        @if ($yieldChange > 0)
                            <span class="text-green-600">↑ {{ round($yieldChange, 1) }}%</span>
                        @elseif ($yieldChange < 0)
                            <span class="text-red-600">↓ {{ round(abs($yieldChange), 1) }}%</span>
                        @else
                            <span class="text-gray-600">No change</span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-500">Compared to last year</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 p-3 rounded-lg hover:bg-green-200 transition duration-200">
                        <p class="text-lg font-semibold text-green-700">Current</p>
                        <p class="text-xl font-bold text-green-900">{{ $currentYearYield }} kg</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg hover:bg-red-200 transition duration-200">
                        <p class="text-lg font-semibold text-red-700">Previous</p>
                        <p class="text-xl font-bold text-red-900">{{ $lastYearYield }} kg</p>
                    </div>
                </div>
            </div>

            <!-- Farm Management Section -->
            <div class="mt-10">
                <h1 class="text-2xl font-bold text-green-800 mb-4">Farm Overview</h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($crops as $crop)
                        @foreach ($crop->plantingRecords as $planting)
                            @if ($planting->cropStatusHistory->last()->Status == 'Growing')
                                @php
                                    $expectedHarvestDate = \Carbon\Carbon::parse($planting->ExpectedHarvestDate);
                                    $daysPassed = \Carbon\Carbon::parse($planting->DatePlanted)->diffInDays(now());
                                    $totalDays = \Carbon\Carbon::parse($planting->DatePlanted)->diffInDays($expectedHarvestDate);
                                    $progress = $totalDays > 0 ? max(0, min(100, ($daysPassed / $totalDays) * 100)) : 0;
                                @endphp

                                <div class="p-4 bg-white rounded-lg shadow text-center border border-gray-300 hover:shadow-lg transition duration-200">
                                    <h2 class="text-lg font-semibold text-green-800">{{ $crop->CropName }}</h2>
                                    <p class="text-sm text-gray-600">Field: <span class="font-medium">{{ $planting->field->Location }}</span></p>
                                    <p class="text-sm text-gray-600 mb-2">Harvest Time: <span class="font-medium">{{ $crop->HarvestTime }} days</span></p>

                                    <!-- Progress Bar -->
                                    <div class="relative pt-2">
                                        <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-300">
                                            <div style="width: {{ $progress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                                        </div>
                                        <p class="text-sm text-gray-700 mt-1 font-semibold">{{ round($progress, 1) }}% to harvest</p>
                                        <p class="text-xs text-gray-500">Expected Harvest: {{ $expectedHarvestDate->format('Y-m-d') }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>

            <!-- Harvest Records Table -->
            <div class="bg-white p-6 shadow rounded-lg mt-10">
                <h3 class="text-2xl font-bold mb-4 text-green-800">Recent Harvests</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300 rounded-md">
                        <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="p-3 border">Crop</th>
                            <th class="p-3 border">Type</th>
                            <th class="p-3 border">Date Harvested</th>
                            <th class="p-3 border">Yield (kg)</th>
                            <th class="p-3 border">Field</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($harvestRecords as $harvest)
                            <tr class="hover:bg-gray-100 transition duration-200">
                                <td class="p-3 border">{{ $harvest->crop->CropName }}</td>
                                <td class="p-3 border">{{ $harvest->crop->CropType }}</td>
                                <td class="p-3 border">{{ $harvest->DateHarvested }}</td>
                                <td class="p-3 border">{{ $harvest->HarvestYield }}</td>
                                <td class="p-3 border">{{ $harvest->field->Location }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="mt-8 grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach([
                    ['route' => 'crop-list', 'label' => 'Crop List'],
                    ['route' => 'crop-add', 'label' => 'Add Crop'],
                    ['route' => 'planting-record-list', 'label' => 'Planting Records'],
                    ['route' => 'harvest-record-list', 'label' => 'Harvest Records'],
                    ['route' => 'field-list', 'label' => 'Fields'],
                    ['route' => 'report-generator', 'label' => 'Report Generator']
                ] as $link)
                    <a href="{{ route($link['route']) }}" class="p-3 bg-green-100 text-green-700 rounded-lg text-center hover:bg-green-200 transition duration-200">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
