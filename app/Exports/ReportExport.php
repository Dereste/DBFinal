<?php

namespace App\Exports;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\HarvestRecord;
use App\Models\Field;
use App\Models\PlantingRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


// app/Exports/ReportExport.php

class ReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startYear, $endYear, $cropFilter, $fieldFilter;

    public function __construct($startYear, $endYear, $cropFilter = null, $fieldFilter = null)
    {
        $this->startYear = $startYear;
        $this->endYear = $endYear;
        $this->cropFilter = $cropFilter;
        $this->fieldFilter = $fieldFilter;
    }

    public function collection()
    {
        $data = collect();

        for ($year = $this->startYear; $year <= $this->endYear; $year++) {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();

            $harvestSummary = HarvestRecord::whereBetween('DateHarvested', [$start, $end])
                ->when($this->cropFilter, function ($query) {
                    $query->where('CropID', $this->cropFilter);
                })
                ->when($this->fieldFilter, function ($query) {
                    $query->where('FieldID', $this->fieldFilter);
                })
                ->sum('HarvestYield');

            $yieldAnalysis = HarvestRecord::selectRaw('CropID, SUM(HarvestYield) as total_yield')
                ->whereBetween('DateHarvested', [$start, $end])
                ->when($this->cropFilter, function ($query) {
                    $query->where('CropID', $this->cropFilter);
                })
                ->when($this->fieldFilter, function ($query) {
                    $query->where('FieldID', $this->fieldFilter);
                })
                ->groupBy('CropID')
                ->get()
                ->map(function ($item) {
                    return [
                        'Crop' => optional($item->crop)->CropName,
                        'Yield' => $item->total_yield,
                    ];
                });

            $totalLandArea = Field::sum('Size');

            $data->push([
                'Year' => $year,
                'User' => auth()->user()->UserName ?? 'Unknown',
                'Date Generated' => now()->toDateTimeString(),
                'Harvest Summary' => $harvestSummary . ' kg',
                'Yield Analysis' => $yieldAnalysis->isEmpty() ? 'No Data' : json_encode($yieldAnalysis->toArray()),
                'Total Land Area' => $totalLandArea . ' hectares'
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return ["Year", "User", "Date Generated", "Harvest Summary", "Yield Analysis", "Total Land Area"];
    }

    public function map($report): array
    {
        return [
            $report['Year'],
            $report['User'],
            $report['Date Generated'],
            $report['Harvest Summary'],
            $report['Yield Analysis'],
            $report['Total Land Area'],
        ];
    }
}
