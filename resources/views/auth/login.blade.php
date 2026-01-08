@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500">
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md transform hover:scale-105 transition-transform duration-300">
        <div class="text-center mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <i class="fas fa-ticket-alt text-white text-3xl"></i>
            </div>
            <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Queue Management System</h2>
            <p class="text-gray-500 mt-2">Sign in to continue</p>
        </div>
        
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-4 shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label for="username" class="block text-gray-700 font-semibold mb-2 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i> Username
                </label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" 
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                       placeholder="Enter your username"
                       required autofocus>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-semibold mb-2 flex items-center">
                    <i class="fas fa-lock mr-2 text-blue-600"></i> Password
                </label>
                <input type="password" id="password" name="password" 
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                       placeholder="Enter your password"
                       required>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg font-semibold">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>

        <div class="mt-8 text-center">
            <div class="border-t pt-6">
                <p class="text-gray-600 text-sm mb-3">Need a queue number?</p>
                <a href="{{ route('kiosk.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                    <i class="fas fa-desktop mr-2"></i> Go to Kiosk
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
