@extends('layouts.karyawan')

@section('page-title', 'Portal Karyawan')
@section('page-subtitle', now()->locale('id')->isoFormat('dddd, D MMMM YYYY'))

@section('content')
<div x-data="karyawanDashboard()" x-init="init()">
    <!-- Hero Section -->
    <div class="bg-blue-600 rounded-lg p-8 mb-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">Halo, {{ auth()->user()->name }}!</h2>
                <p class="text-blue-100" x-text="currentDate">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                <div class="flex items-center space-x-2 mt-2">
                    <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-blue-200 text-sm">Jakarta Pusat</span>
                </div>
            </div>
            <button @click="refreshStatus()" class="px-6 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition">
                Belum Absen
            </button>
        </div>
    </div>

    <!-- Absensi Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Absen Masuk -->
        <div class="bg-white rounded-lg p-6 shadow-lg">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center"
                     :class="canClockIn ? 'bg-blue-600' : 'bg-gray-300'">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Absen Masuk</h3>
                <p class="text-gray-600 text-sm mb-6">Klik tombol di bawah untuk mencatat waktu kedatangan Anda</p>
                
                <button @click="clockIn()" 
                        :disabled="!canClockIn || loading"
                        :class="canClockIn && !loading ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 cursor-not-allowed'"
                        class="w-full py-3 rounded-lg text-white font-medium transition">
                    <span x-show="!loading">Absen Masuk</span>
                    <span x-show="loading">Memproses...</span>
                </button>
            </div>
        </div>

        <!-- Absen Pulang -->
        <div class="bg-white rounded-lg p-6 shadow-lg">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center"
                     :class="canClockOut ? 'bg-orange-600' : 'bg-gray-300'">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Absen Pulang</h3>
                <p class="text-gray-600 text-sm mb-6">Klik tombol di bawah untuk mencatat waktu kepulangan Anda</p>
                
                <button @click="clockOut()" 
                        :disabled="!canClockOut || loading"
                        :class="canClockOut && !loading ? 'bg-orange-600 hover:bg-orange-700' : 'bg-gray-600 cursor-not-allowed'"
                        class="w-full py-3 rounded-lg text-white font-medium transition">
                    <span x-show="!loading">Absen Pulang</span>
                    <span x-show="loading">Memproses...</span>
                </button>
                
                <p x-show="!canClockOut && !canClockIn" class="text-gray-500 text-xs mt-2">
                    Absen masuk terlebih dahulu
                </p>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="bg-white rounded-lg p-6 shadow-lg">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Sistem Sinkronisasi Real-Time</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Data absensi Anda akan otomatis tersinkronisasi ke semua server secara real-time. 
                    Sistem ini memastikan ketersediaan data tinggi (high availability) dan konsistensi data 
                    di berbagai server menggunakan teknologi terdistribusi untuk kecepatan dan keandalan maksimal.
                </p>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="showToast" 
         x-transition
         class="fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg"
         :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'">
        <p class="text-white font-medium" x-text="toastMessage"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function karyawanDashboard() {
    return {
        canClockIn: {{ $canClockIn ? 'true' : 'false' }},
        canClockOut: {{ $canClockOut ? 'true' : 'false' }},
        loading: false,
        showToast: false,
        toastMessage: '',
        toastType: 'success',
        currentDate: '{{ now()->locale("id")->isoFormat("dddd, D MMMM YYYY") }}',
        
        init() {
            console.log('Karyawan dashboard initialized');
        },
        
        async clockIn() {
            if (!this.canClockIn || this.loading) return;
            
            this.loading = true;
            
            try {
                // Get geolocation
                const position = await this.getGeolocation();
                
                // Send request
                const response = await fetch('/absensi/masuk', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.canClockIn = false;
                    this.canClockOut = true;
                    this.showToastNotification('Absensi masuk berhasil dicatat!', 'success');
                } else {
                    this.showToastNotification(data.message || 'Gagal mencatat absensi', 'error');
                }
                
            } catch (error) {
                console.error('Clock in error:', error);
                this.showToastNotification('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async clockOut() {
            if (!this.canClockOut || this.loading) return;
            
            this.loading = true;
            
            try {
                // Get geolocation
                const position = await this.getGeolocation();
                
                // Send request
                const response = await fetch('/absensi/pulang', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.canClockOut = false;
                    this.showToastNotification('Absensi pulang berhasil dicatat!', 'success');
                } else {
                    this.showToastNotification(data.message || 'Gagal mencatat absensi', 'error');
                }
                
            } catch (error) {
                console.error('Clock out error:', error);
                this.showToastNotification('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async getGeolocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation tidak didukung oleh browser Anda'));
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(resolve, (error) => {
                    reject(new Error('Gagal mendapatkan lokasi: ' + error.message));
                });
            });
        },
        
        async refreshStatus() {
            try {
                const response = await fetch('/absensi/status');
                const data = await response.json();
                
                if (data.success) {
                    this.canClockIn = !data.data.has_clock_in;
                    this.canClockOut = data.data.has_clock_in && !data.data.has_clock_out;
                }
            } catch (error) {
                console.error('Failed to refresh status:', error);
            }
        },
        
        showToastNotification(message, type = 'success') {
            this.toastMessage = message;
            this.toastType = type;
            this.showToast = true;
            
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        }
    }
}
</script>
@endpush
