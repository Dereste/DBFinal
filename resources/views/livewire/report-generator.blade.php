@extends('layouts.app')

@section('content')
    <div class="p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Report</h2>
        <form method="GET" action="">
            <div class="mb-4">
                <label for="year" class="block text-sm font-medium text-gray-700">Year:</label>
                <select name="year" id="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @for ($y = now()->year - 5; $y <= now()->year; $y++)
                        <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Apply
            </button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 mt-6">
            @if(isset($yieldTrendChart) && count($yieldTrendChart['labels']) > 0)
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Yield Trend & Seasonality</h3>
                    <div wire:ignore class="relative aspect-square">
                        <canvas id="yieldTrendChartCanvas" class="absolute inset-0"></canvas>
                    </div>
                </div>
            @endif
            @if(isset($timeComparisonChart) && count($timeComparisonChart['labels']) > 0)
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Time Comparison</h3>
                    <div wire:ignore class="relative aspect-square">
                        <canvas id="timeComparisonChartCanvas" class="absolute inset-0"></canvas>
                    </div>
                </div>
            @endif
            @if(isset($fieldProductivityChart))
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Field Results</h3>
                    <div wire:ignore class="relative aspect-square">
                        <canvas id="fieldProductivityChartCanvas" class="absolute inset-0"></canvas>
                    </div>
                </div>
            @endif
            @if(isset($cropDistributionChart))
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Crops</h3>
                    <div wire:ignore class="relative aspect-square">
                        <canvas id="cropDistributionChartCanvas" class="absolute inset-0"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <h2 class="text-2xl font-bold mb-6 text-gray-800">Export Summary</h2>
        <form method="GET" action="{{ route('report.export') }}" class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="startYear" class="block text-sm font-medium text-gray-700">Start Year</label>
                    <select id="startYear" name="startYear" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach(range(date('Y'), 2000) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="endYear" class="block text-sm font-medium text-gray-700">End Year</label>
                    <select id="endYear" name="endYear" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach(range(date('Y'), 2000) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold mb-16 py-2 px-4 rounded">
                    Export Report
                </button>
            </div>
        </form>

        <div class="bg-white p-4 shadow rounded-lg">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Harvest Records</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-200 rounded-md">
                    <thead>
                    <tr class="bg-green-600 text-white">
                        <th class="p-3 border text-left cursor-pointer" wire:click="sortBy('CropName')">Name</th>
                        <th class="p-3 border text-left cursor-pointer" wire:click="sortBy('CropType')">Type</th>
                        <th class="p-3 border text-center cursor-pointer" wire:click="sortBy('DatePlanted')">Date Planted</th>
                        <th class="p-3 border text-center cursor-pointer" wire:click="sortBy('DateHarvested')">Harvested</th>
                        <th class="p-3 border text-center cursor-pointer" wire:click="sortBy('HarvestYield')">Yield</th>
                        <th class="p-3 border text-left cursor-pointer" wire:click="sortBy('Location')">Location</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($harvestRecords as $harvest)
                        <tr class="hover:bg-gray-100">
                            <td class="p-3 border">{{ $harvest->crop->CropName }}</td>
                            <td class="p-3 border">{{ $harvest->crop->CropType }}</td>
                            <td class="p-3 border text-center">{{ $harvest->plantingRecord->DatePlanted }}</td>
                            <td class="p-3 border text-center">{{ $harvest->DateHarvested }}</td>
                            <td class="p-3 border text-center">{{ $harvest->HarvestYield }}</td>
                            <td class="p-3 border">{{ $harvest->field->Location }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                function getLivewireComponent() {
                    const livewireElement = document.querySelector('[wire\\:id]');
                    return livewireElement ? Livewire.find(livewireElement.getAttribute('wire:id')) : null;
                }

                let charts = {};

                function createGradient(ctx, color) {
                    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, color.replace("1)", "0.6)"));
                    gradient.addColorStop(1, color.replace("1)", "0)"));
                    return gradient;
                }

                function createChart(canvasId, type, data, options) {
                    let canvas = document.getElementById(canvasId);
                    if (!canvas) return;
                    let ctx = canvas.getContext("2d");

                    if (charts[canvasId]) {
                        charts[canvasId].destroy();
                    }

                    if (type === "line") {
                        let color = 'rgba(75, 192, 192, 1)';
                        data.datasets[0].backgroundColor = createGradient(ctx, color);
                    }

                    charts[canvasId] = new Chart(ctx, { type, data, options });
                }

                function initCharts() {
                    @if(isset($yieldTrendChart) && count($yieldTrendChart['labels']) > 0)
                    createChart("yieldTrendChartCanvas", "line", {
                        labels: @json($yieldTrendChart['labels']),
                        datasets: @json($yieldTrendChart['datasets'])
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { title: { display: true, text: 'Week' } },
                            y: { title: { display: true, text: 'Yield (kg)' }, beginAtZero: true }
                        }
                    });
                    @endif

                    @if(isset($fieldProductivityChart) && isset($fieldProductivityChart['labels']) && count($fieldProductivityChart['labels']) > 0)
                    createChart("fieldProductivityChartCanvas", "bar", {
                        labels: @json($fieldProductivityChart['labels']),
                        datasets: @json($fieldProductivityChart['datasets'])
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { title: { display: true, text: 'Field Location' } },
                            y: { title: { display: true, text: 'Total Yield (kg)' }, beginAtZero: true }
                        }
                    });
                    @endif

                    @if(isset($cropDistributionChart) && isset($cropDistributionChart['labels']) && count($cropDistributionChart['labels']) > 0)
                    createChart("cropDistributionChartCanvas", "pie", {
                        labels: @json($cropDistributionChart['labels']),
                        datasets: @json($cropDistributionChart['datasets'])
                    }, {
                        responsive: true,
                        maintainAspectRatio: false
                    });
                    @endif
                    @if(isset($timeComparisonChart) && isset($timeComparisonChart['labels']) && count($timeComparisonChart['labels']) > 0)
                    createChart("timeComparisonChartCanvas", "line", {
                        labels: @json($timeComparisonChart['labels']),
                        datasets: @json($timeComparisonChart['datasets'])
                    }, {
                        responsive: true,
                        maintainAspectRatio: false
                    });
                    @endif
                }

                initCharts();

                Livewire.hook('message.processed', () => {
                    initCharts();
                });
            });
        </script>
    @endpush
@endsection
