<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantingRecord;
use App\Models\HarvestRecord;
use App\Models\Crop;
use App\Models\Field;
use App\Models\CropStatusHistory;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 🌱 Seed Crops
        $this->seedCrops();

        // 🏞️ Seed Fields
        $this->seedFields();

        // 🔄 Run Planting & Harvest Seeder
        $this->seedPlantingAndHarvest();
    }

    private function seedCrops()
    {
        $crops = [
            ['name' => 'Rice', 'type' => 'Grain', 'harvest_time' => 120],
            ['name' => 'Carrot', 'type' => 'Root Vegetable', 'harvest_time' => 75],
            ['name' => 'Tomato', 'type' => 'Fruit', 'harvest_time' => 90],
            ['name' => 'Corn', 'type' => 'Grain', 'harvest_time' => 100],
            ['name' => 'Lettuce', 'type' => 'Leafy Green', 'harvest_time' => 45],
        ];

        foreach ($crops as $crop) {
            Crop::updateOrCreate(['CropName' => $crop['name']], [
                'CropType' => $crop['type'],
                'HarvestTime' => $crop['harvest_time']
            ]);
        }
    }

    private function seedFields()
    {
        $locations = ['Zone A', 'Zone B', 'Zone C', 'Zone D', 'Zone E'];

        foreach ($locations as $location) {
            Field::updateOrCreate(['Location' => $location], [
                'Size' => fake()->numberBetween(3, 18) // Assign a random size between 3 and 18
            ]);
        }
    }

    private function seedPlantingAndHarvest()
    {
        $crops = Crop::all();
        $fields = Field::all();
        $years = range(now()->year - 5, 2024); // Generate records from 5 years ago up to 2024

        $latestPlantingDate = Carbon::create(now()->year - 5, 1, 1); // Track latest planting date

        foreach ($years as $year) {
            $isBadYear = fake()->boolean(20); // 20% chance of a bad year
            $isBountifulYear = !$isBadYear && fake()->boolean(10); // 10% chance of a bountiful year if not a bad year

            foreach ([1, 2, 3, 4] as $quarter) {
                $startDate = Carbon::create($year, ($quarter - 1) * 3 + 1, 1);
                $endDate = $startDate->copy()->endOfQuarter();

                // Ensure we don't backtrack planting records
                if ($startDate->lessThan($latestPlantingDate)) {
                    continue;
                }

                foreach ($crops as $crop) {
                    $field = $fields->random();
                    $datePlanted = Carbon::parse(fake()->dateTimeBetween(now()->subYears(5), $endDate));

                    // Skip if planting is beyond Jan 1, 2025
                    if ($datePlanted->greaterThan(Carbon::create(2025, 1, 1))) {
                        continue;
                    }

                    $harvestDate = $datePlanted->copy()->addDays($crop->HarvestTime);

                    // Prevent overlapping plantings in the same field
                    $existingPlanting = PlantingRecord::where('FieldID', $field->FieldID)
                        ->where(function ($query) use ($datePlanted, $harvestDate) {
                            $query->whereBetween('DatePlanted', [$datePlanted, $harvestDate])
                                ->orWhereRaw("? BETWEEN DatePlanted AND (DatePlanted + INTERVAL (SELECT HarvestTime FROM crops WHERE crops.CropID = planting_records.CropID) DAY)", [$datePlanted]);
                        })
                        ->exists();

                    if ($existingPlanting) {
                        continue;
                    }

                    // Adjust failure rate based on crop type and season
                    $failureRate = $this->getFailureRate($crop->CropType, $datePlanted->month);
                    $failed = fake()->boolean($failureRate);

                    // 🌱 Create Planting Record
                    $planting = PlantingRecord::create([
                        'CropID'      => $crop->CropID,
                        'FieldID'     => $field->FieldID,
                        'DatePlanted' => $datePlanted,
                        'Quantity'    => fake()->numberBetween(50, 300),
                    ]);

                    // Update latest planting date
                    if ($datePlanted->greaterThan($latestPlantingDate)) {
                        $latestPlantingDate = $datePlanted;
                    }

                    if ($failed) {
                        // ❌ Log "Failed" status in CropStatusHistory
                        CropStatusHistory::create([
                            'PlantingID' => $planting->PlantingID,
                            'Status'     => 'Failed',
                            'StatusDate' => $datePlanted->copy()->addDays(fake()->numberBetween(5, 30)),
                            'Notes'      => 'Crop failed due to environmental conditions.',
                        ]);
                    } else {
                        // 🎲 Randomize harvest deviation
                        $bigDeviation = fake()->boolean(20);
                        $harvestDeviation = $bigDeviation
                            ? fake()->numberBetween(-30, 30) // Big deviation: ±10 to 30 days
                            : fake()->numberBetween(-7, 7);  // Small deviation: ±3 to 7 days

                        $actualHarvestDate = $harvestDate->copy()->addDays($harvestDeviation);

                        // Ensure actualHarvestDate is less than 1 year after planting
                        if ($actualHarvestDate->diffInDays($datePlanted) >= 365) {
                            $actualHarvestDate = $datePlanted->copy()->addDays(364);
                        }

                        // Ensure actualHarvestDate is not beyond Jan 1, 2025
                        if ($actualHarvestDate->greaterThan(Carbon::create(2025, 1, 1))) {
                            $actualHarvestDate = Carbon::create(2024, 12, 31);
                        }

                        // Adjust yield based on season, crop type, and major events
                        $baseYield = 100; // Base yield for pattern
                        $yieldVariation = $this->getYieldVariation($crop->CropType, $datePlanted->month, $isBadYear, $isBountifulYear);
                        $harvestYield = $baseYield + $yieldVariation;

                        // 🌾 Create Harvest Record
                        HarvestRecord::create([
                            'CropID'        => $crop->CropID,
                            'FieldID'       => $field->FieldID,
                            'PlantingID'    => $planting->PlantingID,
                            'DateHarvested' => $actualHarvestDate,
                            'HarvestYield'  => $harvestYield,
                        ]);

                        // 📝 Log "Harvested" status in CropStatusHistory
                        CropStatusHistory::create([
                            'PlantingID' => $planting->PlantingID,
                            'Status'     => 'Harvested',
                            'StatusDate' => $actualHarvestDate,
                            'Notes'      => 'Harvested successfully.',
                        ]);
                    }
                }
            }
        }
    }

    private function getFailureRate($cropType, $month)
    {
        $seasonalFailureRates = [
            'Grain' => [10, 15, 20, 25, 30, 35, 40, 35, 30, 25, 20, 15],
            'Root Vegetable' => [15, 20, 25, 30, 35, 40, 45, 40, 35, 30, 25, 20],
            'Fruit' => [20, 25, 30, 35, 40, 45, 50, 45, 40, 35, 30, 25],
            'Leafy Green' => [25, 30, 35, 40, 45, 50, 55, 50, 45, 40, 35, 30],
        ];

        return $seasonalFailureRates[$cropType][$month - 1] ?? 15;
    }

    private function getYieldVariation($cropType, $month, $isBadYear, $isBountifulYear)
    {
        $seasonalYieldVariations = [
            'Grain' => [10, 15, 20, 25, 30, 35, 40, 35, 30, 25, 20, 15],
            'Root Vegetable' => [15, 20, 25, 30, 35, 40, 45, 40, 35, 30, 25, 20],
            'Fruit' => [20, 25, 30, 35, 40, 45, 50, 45, 40, 35, 30, 25],
            'Leafy Green' => [25, 30, 35, 40, 45, 50, 55, 50, 45, 40, 35, 30],
        ];

        $variation = $seasonalYieldVariations[$cropType][$month - 1] ?? 0;

        if ($isBadYear) {
            $variation -= 20; // Reduce yield in bad years
        } elseif ($isBountifulYear) {
            $variation += 20; // Increase yield in bountiful years
        }

        return $variation;
    }
}
