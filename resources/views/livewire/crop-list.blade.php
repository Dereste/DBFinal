<div class="p-8"
     x-data="{
        // Raw data from Livewire
        crops: @js($crops),
        // Reactive states for filters
        search: '',
        sortKey: '',
        sortAsc: true,
        // Date Planted filters
        datePlantedYear: '',
        datePlantedMonth: '',
        // Date Harvested filters
        dateHarvestedYear: '',
        dateHarvestedMonth: '',
        // Numeric filters for quantity & yield
        quantityMin: '',
        quantityMax: '',
        harvestMin: '',
        harvestMax: '',
        // Filters for status & location
        statusFilter: '',
        locationFilter: '',
        // Final list after filtering/sorting
        sortedCrops: [],
        // init is called on page load
        init() {
            this.applyFilters();
            // Listen for Livewire delete event
            Livewire.on('plantingDeleted', id => {
                // Remove the deleted record from sortedCrops and crops
                this.crops = this.crops.filter(crop => crop.PlantingID !== id);
                this.applyFilters(); // Reapply filters to update the UI
            });
        },
        // Main filtering & sorting function
        applyFilters() {
            let data = [...this.crops]; // shallow copy of the raw data

            // 1. Basic text search across CropName, CropType, and Status
            if (this.search.trim() !== '') {
                let s = this.search.toLowerCase();
                data = data.filter(item => {
                    let text = (item.CropName + item.CropType + (item.Status ?? '')).toLowerCase();
                    return text.includes(s);
                });
            }
            // 2. Filter by DatePlanted year/month
            if (this.datePlantedYear) {
                data = data.filter(item => {
                    if (!item.DatePlanted) return false;
                    let dt = new Date(item.DatePlanted);
                    return dt.getFullYear().toString() === this.datePlantedYear;
                });
            }
            if (this.datePlantedMonth) {
                data = data.filter(item => {
                    if (!item.DatePlanted) return false;
                    let dt = new Date(item.DatePlanted);
                    return (dt.getMonth() + 1).toString() === this.datePlantedMonth;
                });
            }
            // 3. Filter by DateHarvested year/month
            if (this.dateHarvestedYear) {
                data = data.filter(item => {
                    if (!item.DateHarvested || item.Status === 'Failed') return false;
                    let dt = new Date(item.DateHarvested);
                    return !isNaN(dt.getTime()) && dt.getFullYear().toString() === this.dateHarvestedYear;
                });
            }
            if (this.dateHarvestedMonth) {
                data = data.filter(item => {
                    if (!item.DateHarvested || item.Status === 'Failed') return false;
                    let dt = new Date(item.DateHarvested);
                    return (dt.getMonth() + 1).toString() === this.dateHarvestedMonth;
                });
            }
            // 4. Numeric range: Quantity
            if (this.quantityMin !== '') {
                let minQ = parseFloat(this.quantityMin) || 0;
                data = data.filter(item => parseFloat(item.Quantity ?? 0) >= minQ);
            }
            if (this.quantityMax !== '') {
                let maxQ = parseFloat(this.quantityMax) || 999999999;
                data = data.filter(item => parseFloat(item.Quantity ?? 0) <= maxQ);
            }
            // 5. Numeric range: HarvestYield
            if (this.harvestMin !== '') {
                let minY = parseFloat(this.harvestMin) || 0;
                data = data.filter(item => parseFloat(item.HarvestYield ?? 0) >= minY);
            }
            if (this.harvestMax !== '') {
                let maxY = parseFloat(this.harvestMax) || 999999999;
                data = data.filter(item => parseFloat(item.HarvestYield ?? 0) <= maxY);
            }
            // 6. Filter by Status (exact match, consider normalizing if needed)
            if (this.statusFilter) {
                data = data.filter(item => (item.Status ?? '') === this.statusFilter);
            }
            // 7. Filter by Location (exact match)
            if (this.locationFilter) {
                data = data.filter(item => (item.Location ?? '') === this.locationFilter);
            }
            // 8. Sorting logic based on selected sortKey and sort order
            if (this.sortKey) {
                data.sort((a, b) => {
                    let valA = a[this.sortKey] ?? '';
                    let valB = b[this.sortKey] ?? '';
                    // Handle numeric sorting
                    let numA = parseFloat(valA);
                    let numB = parseFloat(valB);
                    if (!isNaN(numA) && !isNaN(numB)) {
                        return this.sortAsc ? (numA - numB) : (numB - numA);
                    }
                    // Handle date sorting
                    if (this.sortKey.includes('Date')) {
                        let timeA = new Date(valA).getTime() || 0;
                        let timeB = new Date(valB).getTime() || 0;
                        return this.sortAsc ? (timeA - timeB) : (timeB - timeA);
                    }
                    // Fallback to string comparison
                    valA = valA.toString().toLowerCase();
                    valB = valB.toString().toLowerCase();
                    if (valA < valB) return this.sortAsc ? -1 : 1;
                    if (valA > valB) return this.sortAsc ? 1 : -1;
                    return 0;
                });
            }
            this.sortedCrops = data;
        },
        // Helper function to toggle sorting on a field
        sortBy(field) {
            if (this.sortKey === field) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortKey = field;
                this.sortAsc = true;
            }
            this.applyFilters();
        }
     }">

    <!-- Page Title -->
    <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
        <i class="fas fa-seedling text-green-600"></i> Crop List
    </h2>

    <!-- Search & Filters -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
        <!-- Search Bar -->
        <div class="relative col-span-1 md:col-span-2 lg:col-span-1">
        <span class="absolute inset-y-0 left-2 flex items-center text-gray-400">
            <i class="fas fa-search"></i>
        </span>
            <input
                type="text"
                x-model="search"
                @input="applyFilters()"
                placeholder="Search..."
                class="pl-8 p-2 border rounded w-full shadow-sm"
            >
        </div>
    </div>

    <!-- Date Filters -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
        <!-- Date Planted -->
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-calendar-alt"></i> Planted Year</label>
            <select x-model="datePlantedYear" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">All</option>
                <template x-for="year in [...new Set(crops.map(c => new Date(c.DatePlanted).getFullYear()).filter(y => !isNaN(y)))]" :key="year">
                    <option :value="year" x-text="year"></option>
                </template>
            </select>

        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-calendar-alt"></i> Planted Month</label>
            <select x-model="datePlantedMonth" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">All</option>
                <template x-for="(month, index) in ['January','February','March','April','May','June','July','August','September','October','November','December']" :key="index">
                    <option :value="index + 1" x-text="month"></option>
                </template>
            </select>
        </div>

        <!-- Date Harvested -->
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-calendar-check"></i> Harvested Year</label>
            <select x-model="dateHarvestedYear" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">All</option>
                <template x-for="year in [...new Set(
                    crops
                        .filter(c => c.DateHarvested && c.DateHarvested !== '0000-00-00' && c.Status !== 'Failed') // ✅ Exclude failed crops
                        .map(c => new Date(c.DateHarvested).getFullYear())
                )]" :key="year">
                    <option :value="year" x-text="year"></option>
                </template>
            </select>

        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-calendar-check"></i> Harvested Month</label>
            <select x-model="dateHarvestedMonth" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">All</option>
                <template x-for="(month, index) in ['January','February','March','April','May','June','July','August','September','October','November','December']" :key="index">
                    <option :value="index + 1" x-text="month"></option>
                </template>
            </select>
        </div>
    </div>

    <!-- Quantity, Status, Location, Harvest Yield -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4">
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-cubes"></i> Qty Min</label>
            <input type="number" x-model="quantityMin" @input="applyFilters()" class="border p-2 rounded w-full shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-cubes"></i> Qty Max</label>
            <input type="number" x-model="quantityMax" @input="applyFilters()" class="border p-2 rounded w-full shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-info-circle"></i> Status</label>
            <select x-model="statusFilter" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">All</option>
                <template x-for="status in [...new Set(crops.map(c => c.Status).filter(Boolean))]" :key="status">
                    <option :value="status" x-text="status"></option>
                </template>
            </select>

        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-map-marker-alt"></i> Location</label>
            <select x-model="locationFilter" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">All</option>
                <template x-for="loc in [...new Set(crops.map(c => c.Location).filter(Boolean))]" :key="loc">
                    <option :value="loc" x-text="loc"></option>
                </template>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-chart-line"></i> HY Min</label>
            <input type="number" x-model="harvestMin" @input="applyFilters()" class="border p-2 rounded w-full shadow-sm">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1"><i class="fas fa-chart-line"></i> HY Max</label>
            <input type="number" x-model="harvestMax" @input="applyFilters()" class="border p-2 rounded w-full shadow-sm">
        </div>
    </div>

    <!-- Sorting -->
    <div class="flex flex-wrap gap-4 mb-4 items-center">
        <div>
            <select x-model="sortKey" @change="applyFilters()" class="border p-2 rounded w-full">
                <option value="">Sort By</option>
                <option value="CropName">Name</option>
                <option value="CropType">Type</option>
                <option value="DatePlanted">Date Planted</option>
                <option value="DateHarvested">Date Harvested</option>
                <option value="Quantity">Quantity</option>
                <option value="HarvestYield">Harvest Yield</option>
                <option value="Status">Status</option>
                <option value="Location">Location</option>
            </select>
        </div>
        <button
            @click="sortAsc = !sortAsc; applyFilters()"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center gap-2 shadow-md"
        >
            <i class="fas fa-sort"></i>
            <span x-text="sortAsc ? 'Asc' : 'Desc'"></span>
        </button>
    </div>


    <!-- Table -->
    <div class="border border-gray-300 rounded-lg overflow-hidden">
        <!-- Table Header -->
        <div class="bg-green-500 text-white p-2 rounded-t-lg sticky top-0 z-10">
            <div class="grid grid-cols-9 w-full font-bold text-left">
                <div class="px-2 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('CropName')">
                    <i class="fas fa-leaf"></i> Name
                </div>
                <div class="px-2 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('CropType')">
                    <i class="fas fa-tags"></i> Type
                </div>
                <div class="px-1.5 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('DatePlanted')">
                    <i class="fas fa-calendar-plus"></i> Planted
                </div>
                <div class="px-1 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('DateHarvested')">
                    <i class="fas fa-calendar-check"></i> Harvested
                </div>
                <div class="px-2 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('Quantity')">
                    <i class="fas fa-cubes"></i> Qty
                </div>
                <div class="px-1 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('HarvestYield')">
                    <i class="fas fa-weight-hanging"></i> Yield
                </div>
                <div class="px-3 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('Status')">
                    <i class="fas fa-info-circle"></i> Status
                </div>
                <div class="px-2 py-2 cursor-pointer flex items-center gap-1" @click="sortBy('Location')">
                    <i class="fas fa-map-marker-alt"></i> Location
                </div>
                <th class="px-4 py-2 text-center">
                    <div class="flex justify-center items-center gap-1">
                        <i class="fas fa-trash-alt"></i>
                        <span>Delete</span>
                    </div>
                </th>

            </div>
        </div>

        <!-- Table Body -->
        <div class="bg-white max-h-[732px] overflow-y-auto">
            <table class="w-full border-collapse">
                <tbody>
                <template x-for="row in sortedCrops" :key="row.PlantingID">
                    <tr class="grid grid-cols-9 border-b hover:bg-gray-100 transition duration-150 text-left odd:bg-gray-100 even:bg-white">
                        <td class="px-4 py-3 truncate" x-text="row.CropName"></td>
                        <td class="px-4 py-3 truncate" x-text="row.CropType"></td>
                        <td class="px-4 py-3 truncate" x-text="row.DatePlanted ?? 'N/A'"></td>
                        <td class="px-4 py-3 truncate" x-text="row.DateHarvested ?? 'N/A'"></td>
                        <td class="px-4 py-3 truncate" x-text="row.Quantity ?? ''"></td>
                        <td class="px-4 py-3 truncate" x-text="row.HarvestYield ?? 'N/A'"></td>
                        <td class="px-4 py-3 font-semibold">
                            <span class="px-2 py-1 text-xs rounded-full"
                                  :class="{
                                    'bg-blue-200 text-blue-800': row.Status === 'Planted',
                                    'bg-yellow-200 text-yellow-800': row.Status === 'Growing',
                                    'bg-green-200 text-green-800': row.Status === 'Ready to Harvest',
                                    'bg-gray-200 text-gray-800': row.Status === 'Harvested',
                                    'bg-red-200 text-red-600': row.Status === 'Failed',
                                    'bg-gray-300 text-gray-700': !row.Status
                                  }">
                                <span x-text="row.Status ?? 'Unknown'"></span>
                            </span>
                        </td>
                        <td class="px-4 py-3 truncate" x-text="row.Location ?? ''"></td>
                        <!-- Delete Button -->
                        <td class="px-4 py-2 text-center">
                            <button
                                @click="
                                    if (confirm('Are you sure you want to delete this record?')) {
                                        $wire.deletePlantingRecord(row.PlantingID)
                                    }
                                "
                                class="text-red-500 hover:text-red-700"
                            >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
