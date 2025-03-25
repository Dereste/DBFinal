<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use Illuminate\Support\Facades\DB;
use App\Models\HarvestRecord;

class TotalYieldChart extends Chart
{
    public function __construct()
    {
        parent::__construct(); // Ensure the parent constructor is called
    }

    public function build()
    {
        // Fetch grouped data from the database
        $harvests = HarvestRecord::selectRaw('DATE(DateHarvested) as date, SUM(HarvestYield) as total_yield')
            ->groupBy(DB::raw('DATE(DateHarvested)'))
            ->orderBy('date', 'asc')
            ->get();

        // Extract labels (dates) and data (yields)
        $labels = $harvests->pluck('date')->toArray();
        $data = $harvests->pluck('total_yield')->toArray();

        // Assign labels and dataset
        return $this->labels($labels)
            ->dataset('Total Yield Over Time', 'line', $data) // This is correct for v6.8
            ->color("rgb(75, 192, 192)")
            ->backgroundcolor("rgba(75, 192, 192, 0.2)")
            ->fill(true);
    }
}
