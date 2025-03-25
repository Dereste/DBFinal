<?php

namespace App\Charts;

use App\Models\HarvestRecord;

class CropDistributionChart
{
    public function build()
    {
        $crops = HarvestRecord::with('crop')
            ->selectRaw('CropID, SUM(HarvestYield) as total_yield')
            ->groupBy('CropID')
            ->orderBy('total_yield', 'desc')
            ->get();

        return [
            'labels' => $crops->pluck('crop.CropName')->toArray(),
            'datasets' => [
                [
                    'label' => 'Crop Distribution',
                    'type' => 'pie',
                    'data' => $crops->pluck('total_yield')->toArray(),
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
                ],
            ],
        ];
    }
}
