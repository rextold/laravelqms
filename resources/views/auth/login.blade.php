@extends('layouts.guest')

@section('title', 'Login - Queue Management System')

@section('content')
<style>
    /* Doodle background animation */
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }
    
    @keyframes pulse-glow {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.8; }
    }
    
    .doodle {
        position: absolute;
        opacity: 0.15;
        font-weight: 300;
        pointer-events: none;
    }
    
    .doodle-animated {
        animation: float 6s ease-in-out infinite;
    }
    
    .doodle-1 {
        top: 5%;
        left: 5%;
        font-size: 80px;
        animation: float 8s ease-in-out infinite;
    }
    
    .doodle-2 {
        top: 15%;
        right: 10%;
        font-size: 60px;
        animation: float 6s ease-in-out infinite 1s;
    }
    
    .doodle-3 {
        top: 50%;
        left: 10%;
        font-size: 100px;
        animation: float 7s ease-in-out infinite 2s;
    }
    
    .doodle-4 {
        bottom: 20%;
        right: 5%;
        font-size: 70px;
        animation: float 9s ease-in-out infinite 1.5s;
    }
    
    .doodle-5 {
        bottom: 10%;
        left: 20%;
        font-size: 90px;
        animation: float 8s ease-in-out infinite 2.5s;
    }
    
    .doodle-6 {
        top: 70%;
        right: 15%;
        font-size: 65px;
        animation: float 7s ease-in-out infinite 0.5s;
    }
    
    .login-container {
        position: relative;
        z-index: 10;
    }
    
    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }
    
    .login-card:hover {
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
    }
    
    .glow-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        animation: pulse-glow 3s ease-in-out infinite;
    }
    
    .input-field {
        position: relative;
        overflow: hidden;
    }
    
    .input-field::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }
    
    .input-field:focus-within::after {
        width: 100%;
    }
    
    .input-field input {
        border-bottom: 2px solid #e5e7eb !important;
        border-radius: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        background: transparent !important;
    }
    
    .input-field input:focus {
        border-bottom: 2px solid transparent !important;
        box-shadow: none !important;
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .submit-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .submit-btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
    }
    
    .error-box {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
        border-left: 4px solid #ef4444;
        animation: slideInDown 0.3s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .logo-animation {
        animation: bounceIn 0.6s ease-out;
    }
    
    @keyframes bounceIn {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 overflow-hidden">
    <!-- Doodle Background Elements -->
    <div class="doodle doodle-1 doodle-animated">
        <i class="fas fa-calendar-check"></i>
    </div>
    <div class="doodle doodle-2 doodle-animated">
        <i class="fas fa-clock"></i>
    </div>
    <div class="doodle doodle-3 doodle-animated">
        <i class="fas fa-users"></i>
    </div>
    <div class="doodle doodle-4 doodle-animated">
        <i class="fas fa-list-check"></i>
    </div>
    <div class="doodle doodle-5 doodle-animated">
        <i class="fas fa-queue"></i>
    </div>
    <div class="doodle doodle-6 doodle-animated">
        <i class="fas fa-ticket-alt"></i>
    </div>
    
    <!-- Login Card -->
    <div class="login-container w-full max-w-md px-6 sm:px-0">
        <div class="login-card rounded-3xl p-8 sm:p-10 transform transition-all duration-300">
            <!-- Logo & Header -->
            <div class="text-center mb-10">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl glow-icon flex items-center justify-center logo-animation shadow-xl">
                    <i class="fas fa-ticket-alt text-white text-4xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Welcome Back</h1>
                <p class="text-gray-500 text-lg">Queue Management System</p>
            </div>
            
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="error-box px-5 py-4 rounded-xl mb-8">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5 text-lg"></i>
                        <div class="flex-1">
                            @foreach ($errors->all() as $error)
                                <p class="text-sm text-red-700 font-medium">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Username Field -->
                <div class="input-field">
                    <label for="username" class="block text-gray-700 font-semibold mb-3 text-sm flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                            <i class="fas fa-user text-white text-sm"></i>
                        </span>
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}" 
                        class="w-full px-0 py-3 text-gray-900 placeholder-gray-400 focus:outline-none transition-all @error('username') border-b-2 border-red-500 @enderror"
                        placeholder="Enter your username"
                        required 
                        autofocus
                        aria-invalid="@error('username') true @else false @enderror"
                    >
                    @error('username')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="input-field">
                    <label for="password" class="block text-gray-700 font-semibold mb-3 text-sm flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                            <i class="fas fa-lock text-white text-sm"></i>
                        </span>
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="w-full px-0 py-3 text-gray-900 placeholder-gray-400 focus:outline-none transition-all @error('password') border-b-2 border-red-500 @enderror"
                        placeholder="Enter your password"
                        required
                        aria-invalid="@error('password') true @else false @enderror"
                    >
                    @error('password')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="submit-btn w-full text-white font-bold py-4 rounded-xl mt-8 flex items-center justify-center space-x-2 relative z-0 transition-all duration-300"
                >
                    <span class="relative z-10">Sign In</span>
                    <i class="fas fa-arrow-right relative z-10 text-sm"></i>
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-gray-600 text-sm">
                    <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                    Secure login • Protected system
                </p>
            </div>
        </div>
        
        <!-- Footer Text -->
        <div class="text-center mt-6">
            <p class="text-gray-600 text-sm">
                Queue Management System <span class="text-gray-400">v2.0</span>
            </p>
        </div>
    </div>
</div>
@endsection
