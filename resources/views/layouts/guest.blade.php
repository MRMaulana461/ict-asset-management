<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ICT Asset Loan - Saipem')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Bar -->
    <header class="bg-saipem-primary text-white">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
            <img
                src="{{ asset('images/overrides-logo.svg')}}"
                alt="Saipem Logo"
                class="h-10 w-auto object-contain"
            />
            </div>
        <nav class="flex gap-6">
            <a href="{{ route('loan.form') }}"
            class="font-medium transition border-b-2
                    {{ request()->routeIs('loan.form') 
                        ? 'border-saipem-accent text-saipem-accent' 
                        : 'border-transparent hover:text-saipem-accent' }}">
            Loan Form
            </a>

            <a href="{{ route('withdrawal.create') }}"
            class="font-medium transition border-b-2
                    {{ request()->routeIs('withdrawal.create') 
                        ? 'border-saipem-accent text-saipem-accent' 
                        : 'border-transparent hover:text-saipem-accent' }}">
            Withdrawal Form
            </a>

            <a href="{{ route('login') }}"
            class="font-medium transition border-b-2
                    {{ request()->routeIs('login') 
                        ? 'border-saipem-accent text-saipem-accent' 
                        : 'border-transparent hover:text-saipem-accent' }}">
            Login
            </a>
        </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-12 text-center text-gray-500 text-sm pb-6">
        © {{ date('Y') }} ICT Department - Saipem. Internal System Only.
    </footer>

    @stack('scripts')
</body>
</html>