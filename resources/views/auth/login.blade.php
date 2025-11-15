<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login - Sistem Absensi Real-Time</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        
        /* Responsive adjustments */
        @media (max-width: 640px) {
            .mobile-header {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-3 sm:p-4 md:p-6">
    <div class="w-full max-w-6xl flex flex-col lg:flex-row gap-4 sm:gap-6 lg:gap-8 items-center">
        <!-- Mobile Header - Only visible on mobile -->
        <div class="lg:hidden w-full text-white text-center mobile-header">
            <h1 class="text-2xl sm:text-3xl font-bold mb-2">Sistem Absensi Real-Time</h1>
            <p class="text-sm sm:text-base text-white/90">Platform absensi terdistribusi dengan sinkronisasi real-time</p>
        </div>

        <!-- Left Side - Info Section (Desktop & Tablet) -->
        <div class="flex-1 text-white hidden lg:block">
            <div class="rounded-3xl p-6 xl:p-8">
                <img src="/images/team-meeting.png" alt="Team Meeting" class="w-full h-48 xl:h-64 object-cover rounded-2xl mb-6">
                <h2 class="text-xl xl:text-2xl font-semibold text-center mb-4">Sistem Absensi Real-Time</h2>
                <p class="text-center text-white/90 mb-6 xl:mb-8 text-sm xl:text-base">Platform absensi terdistribusi dengan sinkronisasi data secara real-time di berbagai server</p>
                
                <div class="grid grid-cols-3 gap-4 xl:gap-6 text-center">
                    <div>
                        <div class="w-12 h-12 xl:w-16 xl:h-16 mx-auto mb-3 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-xs xl:text-sm font-medium">Real-Time</p>
                    </div>
                    <div>
                        <div class="w-12 h-12 xl:w-16 xl:h-16 mx-auto mb-3 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                            </svg>
                        </div>
                        <p class="text-xs xl:text-sm font-medium">Terdistribusi</p>
                    </div>
                    <div>
                        <div class="w-12 h-12 xl:w-16 xl:h-16 mx-auto mb-3 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 xl:w-8 xl:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <p class="text-xs xl:text-sm font-medium">Cepat & Aman</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:flex-1 lg:max-w-md">
            <div class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl p-5 sm:p-6 md:p-8">
                <!-- Header -->
                <div class="flex items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">Sistem Absensi</h1>
                        <p class="text-xs sm:text-sm text-gray-500">Real-Time Terdistribusi</p>
                    </div>
                </div>

                <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Masuk ke akun Anda untuk melanjutkan</p>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-3 sm:mb-4 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="text-xs sm:text-sm text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email/Username -->
                    <div class="mb-3 sm:mb-4">
                        <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Email / Username</label>
                        <input 
                            type="text" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            class="w-full px-3 py-2.5 sm:px-4 sm:py-3 text-sm sm:text-base bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Masukkan email atau username"
                            required
                            autofocus
                        >
                        <p class="mt-1.5 sm:mt-2 text-xs text-gray-500">Gunakan 'admin@test.com' untuk role admin atau email lain untuk user</p>
                    </div>

                    <!-- Password -->
                    <div class="mb-3 sm:mb-4">
                        <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-3 py-2.5 sm:px-4 sm:py-3 text-sm sm:text-base bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Masukkan password"
                            required
                        >
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-xs sm:text-sm text-gray-600">Ingat saya</span>
                        </label>
                        <a href="#" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium">Lupa Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 sm:py-3 text-sm sm:text-base rounded-lg transition duration-200 shadow-lg shadow-blue-500/30"
                    >
                        Masuk
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200 text-center">
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Dilengkapi dengan teknologi 
                        <span class="font-medium text-blue-600">Laravel</span> • 
                        <span class="font-medium text-red-600">Redis</span> • 
                        <span class="font-medium text-blue-500">MySQL</span> • 
                        <span class="font-medium text-blue-400">Docker</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
