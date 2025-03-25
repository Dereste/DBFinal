<?php

namespace App\Livewire;

use AllowDynamicProperties;
use Carbon\CarbonInterface;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HarvestRecord;
use App\Models\PlantingRecord;
use App\Models\Crop;
use App\Models\Field;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;


class ReportGenerator extends Component
{

    use WithPagination;
    public $startDate, $endDate, $totalYield = 0, $yieldTrendChart = [], $timeComparisonChart = [], $fieldProductivityChart = [], $cropDistributionChart = [];
    public  $cropFilter = '', $fieldFilter = '', $sortOrder = 'DateHarvested';
    public $year = '';
    public $startYear, $endYear;


    protected $updatesQueryString = [
        'cropFilter' => ['except' => ''],
        'fieldFilter' => ['except' => ''],
        'sortOrder' => ['except' => 'DateHarvested'],
        'year'       => ['except' => ''],
    ];
    public function mount()
    {
        $this->updateReport();
    }
    public function export()
    {
        $startYear = request('startYear', now()->year);
        $endYear = request('endYear', now()->year);

        return Excel::download(new ReportExport($startYear, $endYear, $this->cropFilter, $this->fieldFilter), 'farm_report_' . $startYear . '_to_' . $endYear . '.xlsx');
    }

    public function updatingCropFilter()
    {
        $this->resetPage();
    }
    public function updatingFieldFilter()
    {
        $this->resetPage();
    }
    public function updatingSortOrder()
    {
        $this->resetPage();
    }
    function calculateMovingAverage($data, $window = 4) {
        $movingAverage = [];
        $count = count($data);

        for ($i = 0; $i < $count; $i++) {
            $start = max(0, $i - $window + 1);
            $subset = array_slice($data, $start, $i - $start + 1);
            $movingAverage[] = array_sum($subset) / count($subset);
        }

        return $movingAverage;
    }
    private function getYearDateRange($year = null)
    {
        if (!$year) {
            $year = now()->year; // default to current year
        }

        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        return [$start, $end];
    }

    public function updateReport()
    {
        $selectedYear = request('year', now()->year);
        [$start, $end] = $this->getYearDateRange($selectedYear);
        $totalFieldCount = Field::count();

        // Fetch yield data, aggregated by month
        $harvestRecords = HarvestRecord::selectRaw("DATE_FORMAT(DateHarvested, '%Y-%m') as month, SUM(HarvestYield) as total")
            ->when($this->cropFilter, fn($query) => $query->where('CropID', $this->cropFilter))
            ->when($this->fieldFilter, fn($query) => $query->where('FieldID', $this->fieldFilter))
            ->whereBetween('DateHarvested', [$start, $end])
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Fetch failed and planted data, aggregated by month
        $failedRecords = PlantingRecord::join('crop_status_history', 'planting_records.PlantingID', '=', 'crop_status_history.PlantingID')
            ->where('crop_status_history.Status', 'Failed')
            ->when($this->cropFilter, fn($query) => $query->where('CropID', $this->cropFilter))
            ->when($this->fieldFilter, fn($query) => $query->where('FieldID', $this->fieldFilter))
            ->whereBetween('crop_status_history.StatusDate', [$start, $end])
            ->selectRaw("DATE_FORMAT(crop_status_history.StatusDate, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $plantedRecords = PlantingRecord::selectRaw("DATE_FORMAT(DatePlanted, '%Y-%m') as month, COUNT(*) as total")
            ->when($this->cropFilter, fn($query) => $query->where('CropID', $this->cropFilter))
            ->when($this->fieldFilter, fn($query) => $query->where('FieldID', $this->fieldFilter))
            ->whereBetween('DatePlanted', [$start, $end])
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Generate a list of all months in the selected year
        $allMonths = [];
        $current = $start->copy()->startOfMonth();
        while ($current->lte($end)) {
            $monthKey = $current->format('Y-m');
            $allMonths[$monthKey] = $current->format('F Y'); // Example: "January 2025"
            $current->addMonth();
        }

        // Fill Missing Months with 0 Data
        $harvestData = $harvestRecords->pluck('total', 'month')->toArray();
        $failedData = $failedRecords->pluck('total', 'month')->toArray();
        $plantedData = $plantedRecords->pluck('total', 'month')->toArray();

        $filledHarvestData = [];
        $filledFailedData = [];
        $filledPlantedData = [];

        foreach ($allMonths as $month => $label) {
            $filledHarvestData[] = $harvestData[$month] ?? 0;
            $filledFailedData[] = $failedData[$month] ?? 0;
            $filledPlantedData[] = $plantedData[$month] ?? 0;
        }

        // Fetch field productivity data for the selected year
        $fieldData = HarvestRecord::join('fields', 'harvest_records.FieldID', '=', 'fields.FieldID')
            ->whereBetween('DateHarvested', [$start, $end])
            ->select('fields.Location as location', DB::raw('SUM(HarvestYield) as total_yield'))
            ->groupBy('fields.Location')
            ->orderBy('location', 'asc')
            ->get();

        // Fetch crop distribution data for the selected year
        $cropData = HarvestRecord::join('crops', 'harvest_records.CropID', '=', 'crops.CropID')
            ->whereBetween('DateHarvested', [$start, $end])
            ->select('crops.CropName as crop_name', DB::raw('SUM(HarvestYield) as total_yield'))
            ->groupBy('crops.CropName')
            ->orderBy('crop_name', 'asc')
            ->get();

        // Fetch time comparison data for the selected year
        $harvestComparison = HarvestRecord::with(['plantingRecord.crop'])
            ->whereNotNull('DateHarvested')
            ->whereHas('plantingRecord', function ($q) {
                $q->whereNotNull('DatePlanted');
            })
            ->whereBetween('DateHarvested', [$start, $end])
            ->get()
            ->map(function ($harvest) {
                $expected = $harvest->plantingRecord->ExpectedHarvestDate;
                $actual = $harvest->DateHarvested;

                if (!$expected || !$actual) {
                    return null;
                }

                $differenceInDays = (strtotime($actual) - strtotime($expected)) / 86400;

                return [
                    'harvested_date' => date('Y-m-d', strtotime($actual)), // X-axis
                    'difference_in_days' => $differenceInDays, // Y-axis
                ];
            })
            ->filter()
            ->sortBy('harvested_date') // Ensure chronological order
            ->values();

        $harvestedDates = [];
        $differences = [];

        foreach ($harvestComparison as $record) {
            $harvestedDates[] = $record['harvested_date'];
            $differences[] = $record['difference_in_days'];
        }
        $trendLine = $this->calculateMovingAverage($filledHarvestData);
        // Fetch harvest records for the table
        $harvestRecordsTable = HarvestRecord::with(['crop', 'field', 'plantingRecord'])
            ->when($this->cropFilter, function ($query) {
                $query->where('CropID', $this->cropFilter);
            })
            ->when($this->fieldFilter, function ($query) {
                $query->where('FieldID', $this->fieldFilter);
            })
            ->whereBetween('DateHarvested', [$start, $end])
            ->orderBy($this->sortOrder, 'asc')
            ->paginate(10);
        // Prepare chart data
        $this->yieldTrendChart = [
            'labels' => array_values($allMonths),
            'datasets' => [
                [
                    'label' => 'Total Yield (kg)',
                    'data' => $filledHarvestData,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'Trend Line (Moving Avg)',
                    'data' => $trendLine,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderDash' => [5, 5],
                    'borderWidth' => 2,
                    'fill' => false,
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'Failed Crops',
                    'data' => $filledFailedData,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y1'
                ],
                [
                    'label' => 'Planted Crops',
                    'data' => $filledPlantedData,
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y1'
                ]
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'position' => 'left',
                        'title' => ['display' => true, 'text' => 'Yield (kg)']
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'position' => 'right',
                        'title' => ['display' => true, 'text' => 'Count'],
                        'grid' => ['drawOnChartArea' => false],
                        'max' => $totalFieldCount,
                        'beginAtZero' => true
                    ]
                ]
            ]
        ];

        $this->fieldProductivityChart = [
            'labels' => $fieldData->pluck('location')->toArray(),
            'datasets' => [[
                'label' => 'Total Yield',
                'data' => $fieldData->pluck('total_yield')->toArray(),
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
            ]],
        ];

        $this->cropDistributionChart = [
            'labels' => $cropData->pluck('crop_name')->toArray(),
            'datasets' => [[
                'label' => 'Total Yield',
                'data' => $cropData->pluck('total_yield')->toArray(),
                'backgroundColor' => [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                'borderColor' => [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                'borderWidth' => 1,
            ]]
        ];

        $this->timeComparisonChart = [
            'labels' => $harvestedDates, // X-axis
            'datasets' => [
                [
                    'label' => 'Deviation from Expected Harvest (Days)',
                    'data' => $differences, // Y-axis
                    'borderColor' => 'rgba(255, 159, 64, 1)',
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'pointRadius' => 5,
                ]
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'title' => [
                            'display' => true,
                            'text' => 'Deviation from Expected Harvest (Days)'
                        ]
                    ],
                    'x' => [
                        'type' => 'time',
                        'time' => [
                            'parser' => 'YYYY-MM-DD',
                            'unit' => 'day',
                            'tooltipFormat' => 'MMM dd, yyyy'
                        ],
                        'title' => [
                            'display' => true,
                            'text' => 'Harvested Date'
                        ]
                    ]
                ]
            ]
        ];



        $this->dispatch('updateCharts', $this->yieldTrendChart);
        $this->dispatch('updateCharts', $this->fieldProductivityChart);
        $this->dispatch('updateCharts', $this->cropDistributionChart);
        $this->dispatch('updateCharts', $this->timeComparisonChart);
        $this->harvestRecordsTable = $harvestRecordsTable;
        $this->totalHarvestedYield = array_sum($filledHarvestData);
        $this->totalHarvestCount = HarvestRecord::whereBetween('DateHarvested', [$start, $end])->count();
        $this->totalFailedCrops = array_sum($filledFailedData);
        $this->averageHarvestDeviation = count($differences) > 0 ? array_sum($differences) / count($differences) : 0;
        $this->totalLandArea = Field::whereIn('FieldID', $fieldData->pluck('location')->toArray())->sum('Size');

    }

    public function render()
    {
        $harvestRecords = HarvestRecord::with(['crop', 'field', 'plantingRecord'])
            ->when($this->cropFilter, function ($query) {
                $query->where('CropID', $this->cropFilter);
            })
            ->when($this->fieldFilter, function ($query) {
                $query->where('FieldID', $this->fieldFilter);
            })
            ->orderBy($this->sortOrder, 'asc')
            ->paginate(10);
        return view('livewire.report-generator', [
            'harvestRecords'         => $this->harvestRecordsTable,
            'totalYield'             => $this->totalYield,
            'yieldTrendChart'        => $this->yieldTrendChart,
            'fieldProductivityChart' => $this->fieldProductivityChart,
            'cropDistributionChart'  => $this->cropDistributionChart,
            'timeComparisonChart' => $this->timeComparisonChart,
            'crops'                  => Crop::all(),
            'fields'                 => Field::all(),
        ])->extends('layouts.app');
    }
}
