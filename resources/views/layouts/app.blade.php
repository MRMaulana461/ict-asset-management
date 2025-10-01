<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem ICT Assets - Saipem')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-saipem-primary text-white flex flex-col flex-shrink-0">
            <!-- Logo -->
            <div class="flex items-center justify-center h-28 border-b border-white/10 px-6">
                <div class="flex items-center gap-3 w-full justify-center">
                    <img 
                        src="{{ asset('images/overrides-logo.svg') }}" 
                        alt="Logo Saipem" 
                        class="w-full max-w-[140px] sm:max-w-[120px] md:max-w-[140px] lg:max-w-[160px] h-auto object-contain"
                    />
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('dashboard') }}" 
                class="nav-link {{ request()->routeIs('dashboard') ? 'bg-[#1e3038] text-white font-semibold' : 'text-gray-300 hover:bg-[#3a5563] hover:text-white' }} flex items-center px-4 py-3 rounded-lg transition-colors duration-200">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="{{ route('assets.index') }}" 
                class="nav-link {{ request()->routeIs('assets.index') ? 'bg-[#1e3038] text-white font-semibold' : 'text-gray-300 hover:bg-[#3a5563] hover:text-white' }} flex items-center px-4 py-3 rounded-lg transition-colors duration-200">
                    <i data-lucide="hard-drive" class="w-5 h-5 mr-3"></i>
                    Daftar Aset
                </a>
                <a href="{{ route('assets.create') }}" 
                class="nav-link {{ request()->routeIs('assets.create') ? 'bg-[#1e3038] text-white font-semibold' : 'text-gray-300 hover:bg-[#3a5563] hover:text-white' }} flex items-center px-4 py-3 rounded-lg transition-colors duration-200">
                    <i data-lucide="file-plus-2" class="w-5 h-5 mr-3"></i>
                    Input Barang 
                </a>
                <a href="{{ route('loan-log.index') }}" 
                class="nav-link {{ request()->routeIs('loan-log.*') ? 'bg-[#1e3038] text-white font-semibold' : 'text-gray-300 hover:bg-[#3a5563] hover:text-white' }} flex items-center px-4 py-3 rounded-lg transition-colors duration-200">
                    <i data-lucide="arrow-right-left" class="w-5 h-5 mr-3"></i>
                    Peminjaman Barang
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 sm:p-8 overflow-y-auto">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>