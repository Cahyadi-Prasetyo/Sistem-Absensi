@extends('layouts.app')

@section('title', 'Dashboard Absensi')

@section('content')
<!-- Quick Login (Demo Only) -->
@guest
<div class="mb-6 bg-yellow-100 border-l-4 border-yellow-500 p-4">
    <div class="flex items-center">
        <div class="flex-1">
            <p class="text-sm text-yellow-700 font-medium">Demo Mode: Pilih user untuk login</p>
        </div>
        <div class="flex space-x-2">
            <a href="/login/1" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600">Admin</a>
            <a href="/login/2" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600">Teacher</a>
            <a href="/login/3" class="px-3 py-1 bg-yellow-500 text-white rounded text-sm hover:bg-yellow-600">Student</a>
        </div>
    </div>
</div>
@endguest

@auth
<div class="mb-6 bg-blue-100 border-l-4 border-blue-500 p-4">
    <p class="text-sm text-blue-700">
        Logged in as: <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->role }})
    </p>
</div>
@endauth

<div x-data="attendanceApp()" x-init="init()">
    <!-- Status Connection & Stats -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Connection Status -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div :class="connected ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium" x-text="connected ? 'Connected' : 'Disconnected'"></span>
                </div>
                <div class="text-sm text-gray-600">
                    <span class="font-mono font-bold" x-text="latency + 'ms'"></span>
                </div>
            </div>
        </div>

        <!-- Current Node -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Current Node</div>
            <div class="text-lg font-bold font-mono text-blue-600">{{ $nodeId }}</div>
        </div>

        <!-- Total Attendances Today -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-600">Absensi Hari Ini</div>
            <div class="text-lg font-bold text-green-600" x-text="attendances.length"></div>
        </div>
    </div>

    <!-- Attendance Form -->
    @auth
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">Absen Sekarang</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button 
                @click="submitAttendance('in')"
                :disabled="loading"
                class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg transition">
                <span x-show="!loading">Absen Masuk</span>
                <span x-show="loading">Processing...</span>
            </button>
            
            <button 
                @click="submitAttendance('out')"
                :disabled="loading"
                class="bg-red-500 hover:bg-red-600 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-lg transition">
                <span x-show="!loading">Absen Keluar</span>
                <span x-show="loading">Processing...</span>
            </button>
        </div>

        <!-- Message -->
        <div x-show="message" class="mt-4 p-4 rounded" :class="messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            <p x-text="message"></p>
        </div>
    </div>
    @endauth

    <!-- Today's Attendances -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-4">Absensi Hari Ini</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Node</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="attendance in attendances" :key="attendance.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm" x-text="formatTime(attendance.timestamp)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" x-text="attendance.user.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full" 
                                      :class="getRoleBadge(attendance.user.role)"
                                      x-text="attendance.user.role"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold"
                                      :class="attendance.type === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                      x-text="attendance.type === 'in' ? 'Masuk' : 'Keluar'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono" x-text="attendance.node_id"></td>
                        </tr>
                    </template>
                    <tr x-show="attendances.length === 0">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada absensi hari ini</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function attendanceApp() {
    return {
        attendances: @json($attendances),
        connected: false,
        latency: 0,
        loading: false,
        message: '',
        messageType: 'success',
        echo: null,

        init() {
            this.connectWebSocket();
            this.measureLatency();
            setInterval(() => this.measureLatency(), 5000);
        },

        connectWebSocket() {
            // Check if Echo is available
            if (!window.Echo) {
                console.warn('⚠️ Echo not available yet, will retry...');
                setTimeout(() => this.connectWebSocket(), 1000);
                return;
            }
            
            try {
                this.echo = window.Echo.channel('attendances')
                    .listen('AttendanceCreated', (e) => {
                        console.log('New attendance received:', e);
                        this.attendances.unshift(e);
                        this.connected = true;
                    });
                console.log('✅ WebSocket connected to attendances channel');
            } catch (error) {
                console.error('❌ Failed to connect WebSocket:', error);
                setTimeout(() => this.connectWebSocket(), 2000);
            }
        },

        async measureLatency() {
            const start = Date.now();
            try {
                await fetch('/attendance');
                this.latency = Date.now() - start;
                this.connected = true;
            } catch (error) {
                this.connected = false;
            }
        },

        async submitAttendance(type) {
            this.loading = true;
            this.message = '';

            try {
                const position = await this.getGeolocation();
                
                const response = await fetch('/attendance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        type: type,
                        latitude: position?.coords.latitude,
                        longitude: position?.coords.longitude
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.message = `Absensi ${type === 'in' ? 'masuk' : 'keluar'} berhasil! (Node: ${result.node_id})`;
                    this.messageType = 'success';
                } else {
                    this.message = result.message;
                    this.messageType = 'error';
                }
            } catch (error) {
                this.message = 'Terjadi kesalahan: ' + error.message;
                this.messageType = 'error';
            } finally {
                this.loading = false;
                setTimeout(() => this.message = '', 5000);
            }
        },

        async getGeolocation() {
            return new Promise((resolve) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(resolve, () => resolve(null));
                } else {
                    resolve(null);
                }
            });
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        },

        getRoleBadge(role) {
            const badges = {
                'student': 'bg-blue-100 text-blue-800',
                'teacher': 'bg-purple-100 text-purple-800',
                'admin': 'bg-yellow-100 text-yellow-800'
            };
            return badges[role] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
@endsection
