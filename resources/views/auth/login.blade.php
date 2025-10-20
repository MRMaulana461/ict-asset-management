@extends('layouts.guest')

@section('title', 'Login - ICT Asset Management')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-saipem-primary">ICT Department</h1>
            <p class="text-gray-500 mt-1">Administrator Login</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm"/>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-saipem-accent focus:border-saipem-accent sm:text-sm"/>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex justify-between items-center">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="remember" 
                           class="h-4 w-4 text-saipem-accent border-gray-300 rounded"/>
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" 
                       class="text-sm font-medium text-saipem-accent hover:underline">
                        Forgot password?
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full bg-saipem-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-opacity-90">
                Login
            </button>
        </form>

        <!-- Back to Loan Form -->
        <div class="mt-6 text-center">
            <a href="{{ route('loan.form') }}" class="text-sm text-gray-600 hover:text-saipem-accent">
                ← Back to Loan Form
            </a>
        </div>
    </div>
</div>
@endsection