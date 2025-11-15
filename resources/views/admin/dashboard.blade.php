@extends('layouts.admin')

@section('page-title', 'Dashboard Admin')
@section('page-subtitle', now()->locale('id')->isoFormat('dddd, D MMMM YYYY'))

@section('content')
<div x-data="adminDashboard()" x-init="init()" class="w-full">
    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6">
        <!-- Absensi Hari Ini -->
        <div class="bg-blue-600 rounded-lg p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-200 text-sm font-medium">Absensi Hari Ini</p>
                    <p class="text-white text-3xl font-bold mt-2" x-text="metrics.today_count">{{ $metrics['today_count'] }}</p>
                    <p class="text-blue-200 text-xs mt-1">Karyawan</p>
                </div>
                <div class="bg-blue-500 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Minggu Ini -->
        <div class="bg-green-600 rounded-lg p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-200 text-sm font-medium">Minggu Ini</p>
                    <p class="text-white text-3xl font-bold mt-2" x-text="metrics.week_count">{{ $metrics['week_count'] }}</p>
                    <p class="text-green-200 text-xs mt-1">Total Absensi</p>
                </div>
                <div class="bg-green-500 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tingkat Kehadiran -->
        <div class="bg-purple-600 rounded-lg p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-200 text-sm font-medium">Tingkat Kehadiran</p>
                    <p class="text-white text-3xl font-bold mt-2"><span x-text="metrics.attendance_rate">{{ $metrics['attendance_rate'] }}</span>%</p>
                    <p class="text-purple-200 text-xs mt-1">Rata-rata</p>
                </div>
                <div class="bg-purple-500 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Server Online -->
        <div class="bg-orange-600 rounded-lg p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-200 text-sm font-medium">Server Online</p>
                    <p class="text-white text-3xl font-bold mt-2">
                        <span x-text="metrics.servers_online">{{ $metrics['servers_online'] }}</span>/<span x-text="metrics.servers_total">{{ $metrics['servers_total'] }}</span>
                    </p>
                    <p class="text-orange-200 text-xs mt-1">Server Aktif</p>
                </div>
                <div class="bg-orange-500 rounded-full p-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        <!-- Absensi Terbaru -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <h2 class="text-lg font-semibold text-gray-900">Absensi Terbaru</h2>
                    <span class="flex items-center space-x-1 px-2 py-1 text-white text-xs rounded-full"
                          :class="reverbConnected ? 'bg-green-600' : 'bg-red-600'">
                        <span class="w-2 h-2 bg-white rounded-full" :class="reverbConnected ? 'animate-pulse' : ''"></span>
                        <span x-text="reverbConnected ? 'Live • Connected' : 'Disconnected'"></span>
                    </span>
                </div>
            </div>
            <div class="p-6">
                <!-- Empty State -->
                <div x-show="latestAttendances.length === 0" class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Belum ada data absensi hari ini</p>
                    <p class="text-gray-400 text-xs mt-1">Data akan muncul secara real-time saat karyawan absen</p>
                </div>
                
                <!-- Attendance List -->
                <div x-show="latestAttendances.length > 0" class="space-y-4">
                    <template x-for="attendance in latestAttendances" :key="attendance.id">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                    <span x-text="(attendance.user_name || (attendance.user && attendance.user.name) || 'U').charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <p class="text-gray-900 font-medium" x-text="attendance.user_name || (attendance.user && attendance.user.name) || 'Unknown'"></p>
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <span>Masuk: <span x-text="attendance.jam_masuk || '-'"></span></span>
                                        <template x-if="attendance.jam_pulang">
                                            <span class="text-orange-600">• Pulang: <span x-text="attendance.jam_pulang"></span></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium"
                                  :class="{
                                      'bg-green-600 text-white': attendance.status === 'Hadir',
                                      'bg-yellow-600 text-white': attendance.status === 'Terlambat',
                                      'bg-red-600 text-white': attendance.status === 'Alpha'
                                  }"
                                  x-text="attendance.status || 'Unknown'">
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Status Server -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 ">
                <h2 class="text-lg font-semibold text-gray-900">Status Server</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($serverStatus as $server)
                    <div class="p-4 bg-gray-100 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-gray-900 font-medium">{{ $server['name'] }}</p>
                            @if($server['status'] === 'online')
                                <span class="flex items-center space-x-1 px-2 py-1 bg-green-600 text-white text-xs rounded-full">
                                    <span class="w-2 h-2 bg-white rounded-full"></span>
                                    <span>Online</span>
                                </span>
                            @else
                                <span class="flex items-center space-x-1 px-2 py-1 bg-red-600 text-white text-xs rounded-full">
                                    <span class="w-2 h-2 bg-white rounded-full"></span>
                                    <span>Offline</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-900 text-xs">Sinkronisasi: {{ $server['last_sync'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminDashboard() {
    return {
        metrics: @json($metrics),
        latestAttendances: @json($latestAttendances),
        reverbConnected: false,
        listenersSetup: false,
        
        init() {
            // Wait for Echo to be initialized
            setTimeout(() => {
                this.checkReverbConnection();
                this.listenToEvents();
            }, 500);
            this.startMetricsPolling();
        },
        
        checkReverbConnection() {
            // Check if Echo is available
            if (!window.Echo) {
                console.error('❌ Echo not initialized, retrying...');
                this.reverbConnected = false;
                // Retry after 1 second
                setTimeout(() => this.checkReverbConnection(), 1000);
                return;
            }
            
            // Listen to connection events
            if (window.Echo.connector && window.Echo.connector.pusher) {
                const pusher = window.Echo.connector.pusher;
                
                pusher.connection.bind('connected', () => {
                    console.log('✅ Reverb WebSocket connected');
                    this.reverbConnected = true;
                });
                
                pusher.connection.bind('disconnected', () => {
                    console.warn('⚠️ Reverb WebSocket disconnected');
                    this.reverbConnected = false;
                });
                
                pusher.connection.bind('error', (error) => {
                    console.error('❌ Reverb WebSocket error:', error);
                    this.reverbConnected = false;
                });
                
                pusher.connection.bind('unavailable', () => {
                    console.warn('⚠️ Reverb WebSocket unavailable');
                    this.reverbConnected = false;
                });
                
                // Check current state
                if (pusher.connection.state === 'connected') {
                    this.reverbConnected = true;
                    console.log('✅ Already connected to Reverb');
                }
            }
        },
        
        listenToEvents() {
            // Check if Echo is available
            if (!window.Echo) {
                console.warn('⚠️ Echo not available yet, will retry...');
                setTimeout(() => this.listenToEvents(), 1000);
                return;
            }
            
            // Prevent duplicate listeners
            if (this.listenersSetup) {
                console.log('⚠️ Listeners already setup, skipping...');
                return;
            }
            
            console.log('🎧 Setting up event listeners on channel: attendances');
            this.listenersSetup = true;
            
            // Listen to real-time attendance events
            window.Echo.channel('attendances')
                .listen('AttendanceCreated', (event) => {
                    console.log('📥 AttendanceCreated event received:', event);
                    console.log('📊 Current latestAttendances count:', this.latestAttendances.length);
                    
                    // Check if already exists (prevent duplicate)
                    const exists = this.latestAttendances.find(a => a.id === event.id);
                    if (exists) {
                        console.log('⚠️ Attendance already exists, skipping...', event.id);
                        return;
                    }
                    
                    // Add to latest attendances with consistent format
                    const newAttendance = {
                        id: event.id,
                        user_name: event.user.name,
                        user: event.user,
                        date: event.date,
                        jam_masuk: event.jam_masuk ? event.jam_masuk.substring(0, 5) : null,
                        jam_pulang: event.jam_pulang ? event.jam_pulang.substring(0, 5) : null,
                        duration_minutes: event.duration_minutes,
                        status: event.status
                    };
                    
                    console.log('➕ Adding new attendance:', newAttendance);
                    this.latestAttendances.unshift(newAttendance);
                    console.log('✅ New latestAttendances count:', this.latestAttendances.length);
                    
                    // Keep only 10 latest
                    if (this.latestAttendances.length > 10) {
                        this.latestAttendances.pop();
                    }
                    
                    // Update metrics
                    this.refreshMetrics();
                })
                .listen('AttendanceUpdated', (event) => {
                    console.log('Attendance updated:', event);
                    
                    // Update existing attendance in list
                    const index = this.latestAttendances.findIndex(a => a.id === event.id);
                    if (index !== -1) {
                        // Force reactivity by creating new array with consistent format
                        const updated = [...this.latestAttendances];
                        updated[index] = {
                            id: event.id,
                            user_name: event.user.name,
                            user: event.user,
                            date: event.date,
                            jam_masuk: event.jam_masuk ? event.jam_masuk.substring(0, 5) : null,
                            jam_pulang: event.jam_pulang ? event.jam_pulang.substring(0, 5) : null,
                            duration_minutes: event.duration_minutes,
                            status: event.status
                        };
                        this.latestAttendances = updated;
                        console.log('✅ Updated attendance at index:', index, updated[index]);
                    } else {
                        console.warn('⚠️ Attendance not found in list, ID:', event.id);
                    }
                    
                    // Update metrics
                    this.refreshMetrics();
                });
        },
        
        startMetricsPolling() {
            // Refresh metrics every 30 seconds
            setInterval(() => {
                this.refreshMetrics();
            }, 30000);
        },
        
        async refreshMetrics() {
            try {
                const response = await fetch('/api/dashboard/metrics');
                const data = await response.json();
                if (data.success) {
                    this.metrics = data.data;
                }
            } catch (error) {
                console.error('Failed to refresh metrics:', error);
            }
        }
    }
}
</script>
@endpush
