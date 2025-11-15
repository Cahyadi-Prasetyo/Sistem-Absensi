@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.karyawan')

@section('page-title', 'Tentang Sistem')
@section('page-subtitle', 'Informasi tentang Sistem Absensi Real-Time Terdistribusi')

@section('content')
<div class="space-y-6">
    <!-- Hero Section -->
    <div class="p-8 text-center">
        <div class="w-20 h-20 mx-auto mb-4 bg-blue-600 rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Sistem Absensi Real-Time Terdistribusi</h1>
        <p class="text-gray-900 max-w-3xl mx-auto">
            Platform absensi modern dengan arsitektur terdistribusi yang memungkinkan sinkronisasi data 
            secara real-time di berbagai server
        </p>
    </div>

    <!-- Tujuan Proyek -->
    <div class="bg-blue-600 rounded-lg p-6 shadow-lg">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-white mb-3">Tujuan Proyek</h2>
                <p class="text-blue-100 leading-relaxed mb-4">
                    Proyek ini bertujuan untuk mengimplementasikan sistem absensi berbasis web dengan arsitektur terdistribusi. 
                    Sistem ini memastikan ketersediaan data tinggi (high availability) dan konsistensi data di berbagai server 
                    menggunakan teknologi sinkronisasi real-time. Dengan pendekatan ini, sistem dapat terus beroperasi meskipun 
                    salah satu server mengalami gangguan, serta menjamin data absensi selalu up-to-date di seluruh node.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-500 rounded-lg p-4">
                        <h3 class="font-semibold text-white mb-2">High Availability</h3>
                        <p class="text-blue-100 text-sm">Sistem tetap berjalan meskipun ada server yang down</p>
                    </div>
                    <div class="bg-blue-500 rounded-lg p-4">
                        <h3 class="font-semibold text-white mb-2">Real-Time Sync</h3>
                        <p class="text-blue-100 text-sm">Data tersinkronisasi otomatis tanpa delay</p>
                    </div>
                    <div class="bg-blue-500 rounded-lg p-4">
                        <h3 class="font-semibold text-white mb-2">Data Consistency</h3>
                        <p class="text-blue-100 text-sm">Eventual consistency dengan max delay 1-2 detik</p>
                    </div>
                    <div class="bg-blue-500 rounded-lg p-4">
                        <h3 class="font-semibold text-white mb-2">Load Balancing</h3>
                        <p class="text-blue-100 text-sm">Traffic didistribusikan ke multiple nodes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teknologi yang Digunakan -->
    <div class="bg-white rounded-lg p-6 shadow-lg">
        <h2 class="text-xl font-bold text-white mb-6">Teknologi yang Digunakan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($technologies as $tech)
            <div class="p-4">
                <div class="w-12 h-12 mb-3 rounded-lg flex items-center justify-center
                    @if($tech['color'] === 'red') bg-red-600
                    @elseif($tech['color'] === 'blue') bg-blue-600
                    @elseif($tech['color'] === 'green') bg-green-600
                    @elseif($tech['color'] === 'purple') bg-purple-600
                    @elseif($tech['color'] === 'teal') bg-teal-600
                    @elseif($tech['color'] === 'cyan') bg-cyan-600
                    @else bg-gray-600
                    @endif">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-white mb-1">{{ $tech['name'] }}</h3>
                <p class="text-gray-900 text-sm">{{ $tech['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
