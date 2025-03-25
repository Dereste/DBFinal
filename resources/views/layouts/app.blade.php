<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Management</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
<div id="app">
    <!-- Top Navigation Bar -->
    <nav class="fixed top-0 left-0 w-full bg-green-700 shadow px-6 py-4 flex justify-between items-center h-16 z-10">
        <div class="flex items-center text-white text-2xl font-bold">
            <i class="fas fa-leaf"></i>
            <span class="ml-2">AgriBase</span>
        </div>
        <div class="relative" x-data="{ open: false }">
            <div class="flex items-center space-x-3 cursor-pointer" @click="open = !open">
                <i class="fas fa-user-circle text-2xl text-white"></i>
                <div class="text-right">
                    <p class="text-sm font-semibold text-white">{{ auth()->check() ? auth()->user()->name : 'User Name' }}</p>
                    <p class="text-xs text-gray-200">{{ auth()->check() ? auth()->user()->UserName : 'Guest' }}</p>
                </div>
                <i class="fas fa-chevron-down text-white"></i>
            </div>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white shadow-md rounded-md p-3 z-20">
                @if(auth()->check() && !session('is_admin'))
                    <form action="{{ route('admin.login') }}" method="POST">
                        @csrf
                        <label class="text-sm text-gray-600">Enter Admin Password</label>
                        <input type="password" name="admin_password" class="w-full border rounded p-1 mt-1">
                        <button type="submit" class="w-full bg-green-600 text-white rounded mt-2 p-1 text-sm">
                            <i class="fas fa-key"></i> Enter Admin Mode
                        </button>
                    </form>
                @elseif(session('is_admin'))
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 text-white rounded mt-2 p-1 text-sm">
                            <i class="fas fa-sign-out-alt"></i> Exit Admin Mode
                        </button>
                    </form>
                @endif
                @if(auth()->check())
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-red-600 text-sm mt-2">
                            <i class="fas fa-power-off"></i> Logout
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen pt-6">
        <!-- Sidebar -->
        <aside class="fixed top-16 left-0 w-64 min-h-screen  text-gray-200 p-6 shadow-md">
            @php
                $isAdmin = auth()->check() && (optional(auth()->user())->id == 1 || session('is_admin'));
            @endphp
            <ul class="space-y-3">
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('crop-add') }}" class="sidebar-link {{ request()->routeIs('crop-add') ? 'active' : '' }}">
                        <i class="fas fa-seedling"></i> Crop Management
                    </a>
                    <ul class="pl-8 mt-1 space-y-1 text-sm text-gray-400 list-disc">
                        <li><a href="{{ route('planting-record-list') }}" class="sidebar-sub-link {{ request()->routeIs('planting-record-list') ? 'active' : '' }}"><i class="fas fa-leaf"></i> Plant</a></li>
                        <li><a href="{{ route('harvest-record-list') }}" class="sidebar-sub-link {{ request()->routeIs('harvest-record-list') ? 'active' : '' }}"><i class="fas fa-tractor"></i> Harvest</a></li>
                        <li><a href="{{ route('crop-list') }}" class="sidebar-sub-link {{ request()->routeIs('crop-list') ? 'active' : '' }}"><i class="fas fa-list"></i> Crop</a></li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('report-generator') }}" class="sidebar-link {{ request()->routeIs('report-generator') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i> Reports & Analytics
                    </a>
                </li>
                @if ($isAdmin)
                    <li>
                        <a href="#" class="sidebar-link text-green-400">
                            <i class="fas fa-user-shield"></i> Admin
                        </a>
                        <ul class="pl-8 mt-1 space-y-1 text-sm text-gray-400 list-disc">
                            <li><a href="{{ route('field-list') }}" class="sidebar-sub-link {{ request()->routeIs('field-list') ? 'active' : '' }}"><i class="fas fa-map-marked-alt"></i> Land Plot Management</a></li>
                            <li><a href="{{ route('user-list') }}" class="sidebar-sub-link {{ request()->routeIs('user-list') ? 'active' : '' }}"><i class="fas fa-users"></i> Users</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 ml-64">
            @yield('content')
        </main>
    </div>
</div>

@livewireScripts
<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log("Livewire is loaded", Livewire);
        console.log("Alpine is loaded", Alpine);
    });
</script>
@stack('scripts')
</body>
</html>
