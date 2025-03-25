<?php

namespace App\Charts;

use App\Models\HarvestRecord;
use Carbon\Carbon;

class TimeComparisonChart
{
    public function build()
    {
        // Get all distinct dates for labels
        $labels = HarvestRecord::selectRaw('DATE(DateHarvested) as date')
            ->groupBy(DB::raw('DATE(DateHarvested)'))
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();

        $currentPeriod = HarvestRecord::where('DateHarvested', '>=', Carbon::now()->subMonth())
            ->selectRaw('DATE(DateHarvested) as date, SUM(HarvestYield) as yield')
            ->groupBy(DB::raw('DATE(DateHarvested)'))
            ->orderBy('date', 'asc')
            ->pluck('yield')
            ->toArray();

        $previousPeriod = HarvestRecord::whereBetween('DateHarvested', [
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonth(),
        ])
            ->selectRaw('DATE(DateHarvested) as date, SUM(HarvestYield) as yield')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('yield')
            ->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Current Period',
                    'type' => 'line',
                    'data' => $currentPeriod,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Previous Period',
                    'type' => 'line',
                    'data' => $previousPeriod,
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
        ];
    }
}
