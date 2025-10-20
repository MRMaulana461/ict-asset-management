<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ICT Asset Management - Saipem')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ mobileMenuOpen: false }">
    <!-- Top Navigation Bar -->
    <header class="bg-saipem-primary text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('images/overrides-logo.svg')}}"
                        alt="Saipem Logo"
                        class="h-10 w-auto object-contain"
                    />
                </div>

                <!-- Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" 
                       class="text-white hover:text-saipem-accent transition {{ request()->routeIs('dashboard') ? 'font-semibold border-b-2 border-saipem-accent' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('employees.index') }}" 
                       class="text-white hover:text-saipem-accent transition {{ request()->routeIs('employees.*') ? 'font-semibold border-b-2 border-saipem-accent' : '' }}">
                        Employee
                    </a>
                    
                    <!-- Assets Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="text-white hover:text-saipem-accent transition flex items-center {{ request()->routeIs('assets.*') || request()->routeIs('asset-types.*') ? 'font-semibold border-b-2 border-saipem-accent' : '' }}">
                            Asset
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="{{ route('assets.index') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                All Assets
                            </a>
                            <a href="{{ route('assets.create') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Add New Asset
                            </a>
                            <a href="{{ route('assets.import') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Import from Excel
                            </a>
                            <hr class="my-2">
                            <a href="{{ route('asset-types.index') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Asset Types
                            </a>
                        </div>
                    </div>
                    
                    <a href="{{ route('loan-log.index') }}" 
                       class="text-white hover:text-saipem-accent transition {{ request()->routeIs('loan-log.*') ? 'font-semibold border-b-2 border-saipem-accent' : '' }}">
                        Loan Log
                    </a>

                    <!-- Tambahkan Withdrawals -->
                    <a href="{{ route('withdrawals.index') }}" 
                       class="text-white hover:text-saipem-accent transition {{ request()->routeIs('withdrawals.*') ? 'font-semibold border-b-2 border-saipem-accent' : '' }}">
                        Withdrawals
                    </a>
                </nav>

                <!-- Profile Dropdown -->
                <div class="flex items-center gap-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center gap-2 text-white hover:text-saipem-accent transition">
                            <i data-lucide="user-circle" class="w-6 h-6"></i>
                            <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i data-lucide="user" class="w-4 h-4 inline mr-2"></i>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left px-4 py-2 font-semibold text-red-600 hover:bg-gray-100">
                                    <i data-lucide="log-out" class="w-4 h-4 inline mr-2"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="text-white hover:text-saipem-accent">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" 
                 x-transition
                 class="md:hidden pb-4 space-y-2">
                <a href="{{ route('dashboard') }}" 
                   class="block py-2 text-white hover:text-saipem-accent">
                    Dashboard
                </a>
                <a href="{{ route('employees.index') }}" 
                   class="block py-2 text-white hover:text-saipem-accent">
                    Employees
                </a>
                <a href="{{ route('assets.index') }}" 
                   class="block py-2 text-white hover:text-saipem-accent">
                    Assets
                </a>
                <a href="{{ route('asset-types.index') }}" 
                   class="block py-2 text-white hover:text-saipem-accent pl-4">
                    - Asset Types
                </a>
                <a href="{{ route('assets.import') }}" 
                   class="block py-2 text-white hover:text-saipem-accent pl-4">
                    - Import Excel
                </a>
                <a href="{{ route('loan-log.index') }}" 
                   class="block py-2 text-white hover:text-saipem-accent">
                    Loan Log
                </a>
                <a href="{{ route('withdrawals.index') }}" 
                   class="block py-2 text-white hover:text-saipem-accent">
                    Withdrawals
                </a>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-600 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-12 text-center text-gray-500 text-sm pb-6">
        © {{ date('Y') }} ICT Department - Saipem. Internal System Only.
    </footer>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>