<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HarvestRecord;
use App\Models\PlantingRecord;
use App\Models\CropStatusHistory;
use App\Models\Field;
use App\Models\Crop;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $totalYield, $numFields, $numCrops, $latestHarvestDate, $harvestRecords, $yieldTrendChart, $fieldProductivityChart, $cropDistributionChart;
    public $selectedQuarter, $quarterOptions = ['Q1', 'Q2', 'Q3', 'Q4'];
    public $crops, $currentYearYield, $lastYearYield, $yieldChange;

    public function mount()
    {
        $this->selectedQuarter = $this->getCurrentQuarter();
        $this->fetchDashboardData();
    }
    public function updatedSelectedQuarter()
    {
        $this->fetchDashboardData();
    }
    private function getCurrentQuarter()
    {
        return 'Q' . ceil(now()->month / 3);
    }
    private function getQuarterMonths($quarter)
    {
        $year = now()->year;
        return match ($quarter) {
            'Q1' => ['start' => "$year-01-01", 'end' => "$year-03-31", 'labels' => ['Jan', 'Feb', 'Mar']],
            'Q2' => ['start' => "$year-04-01", 'end' => "$year-06-30", 'labels' => ['Apr', 'May', 'Jun']],
            'Q3' => ['start' => "$year-07-01", 'end' => "$year-09-30", 'labels' => ['Jul', 'Aug', 'Sep']],
            'Q4' => ['start' => "$year-10-01", 'end' => "$year-12-31", 'labels' => ['Oct', 'Nov', 'Dec']],
        };
    }

    private function fetchDashboardData()
    {
        $quarterMonths = $this->getQuarterMonths($this->selectedQuarter);

        // Fetch statistics
        $this->totalYield = HarvestRecord::whereBetween('DateHarvested', [$quarterMonths['start'], $quarterMonths['end']])->sum('HarvestYield');
        $this->numFields = Field::count();
        $this->numCrops = Crop::count();
        $this->latestHarvestDate = HarvestRecord::latest('DateHarvested')->value('DateHarvested') ?? 'N/A';

        // Fetch recent harvest records
        $this->harvestRecords = HarvestRecord::with(['crop', 'field'])
            ->whereBetween('DateHarvested', [$quarterMonths['start'], $quarterMonths['end']])
            ->latest('DateHarvested')
            ->limit(5)
            ->get();

        // Fetch only planting records with latest status "Planted"
        $this->crops = Crop::whereHas('plantingRecords', function ($query) {
            $query->whereHas('cropStatusHistory', function ($statusQuery) {
                $statusQuery->where('Status', 'Growing')
                    ->whereRaw('StatusDate = (SELECT MAX(StatusDate) FROM crop_status_history WHERE crop_status_history.PlantingID = planting_records.PlantingID)');
            });
        })->with(['plantingRecords' => function ($query) {
            $query->latest('DatePlanted');
        }])->get();

        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;
        $today = Carbon::now()->format('m-d'); // Get current month & day (e.g., "03-23")

        // Get Year-to-Date (YTD) harvest yield
        $this->currentYearYield = HarvestRecord::whereBetween('DateHarvested', ["$currentYear-01-01", "$currentYear-$today"])
            ->sum('HarvestYield');

        $this->lastYearYield = HarvestRecord::whereBetween('DateHarvested', ["$lastYear-01-01", "$lastYear-$today"])
            ->sum('HarvestYield');

        // Calculate percentage change only if last year's yield isn't zero
        if ($this->lastYearYield > 0) {
            $this->yieldChange = (($this->currentYearYield - $this->lastYearYield) / $this->lastYearYield) * 100;
        } else {
            $this->yieldChange = $this->currentYearYield > 0 ? 100 : 0;
        }

    }
    public function render()
    {
        return view('livewire.dashboard', [
            'crops' => $this->crops,
            'totalYield' => $this->totalYield,
            'numFields' => $this->numFields,
            'numCrops' => $this->numCrops,
            'latestHarvestDate' => $this->latestHarvestDate,
            'harvestRecords' => $this->harvestRecords,

        ])->extends('layouts.app')->section('content');
    }
}


