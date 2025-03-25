<?php

namespace App\Charts;

use App\Models\HarvestRecord;

class FieldProductivityChart
{
    public function build()
    {
        $fields = HarvestRecord::with('field')
            ->selectRaw('FieldID, SUM(HarvestYield) as total_yield')
            ->groupBy('FieldID')
            ->orderBy('total_yield', 'desc')
            ->get();

        return [
            'labels' => $fields->pluck('field.Location')->toArray(),
            'datasets' => [
                [
                    'label' => 'Field Productivity',
                    'type' => 'bar',
                    'data' => $fields->pluck('total_yield')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}
