@extends('layouts.admin')

@section('page-title', 'Riwayat Absensi')
@section('page-subtitle', 'Lihat dan kelola data riwayat absensi karyawan')

@section('content')
<div x-data="riwayatRealtime()" class="space-y-6">
    
    <!-- Tabs Navigation -->
    <div class="flex space-x-1 bg-gray-100 p-1 rounded-xl w-fit">
        <button @click="activeTab = 'today'" 
                :class="activeTab === 'today' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-lg font-medium text-sm transition-all duration-200 flex items-center space-x-2">
            <span class="relative flex h-2 w-2 mr-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            <span>Hari Ini (Live)</span>
        </button>
        <button @click="activeTab = 'history'" 
                :class="activeTab === 'history' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="px-6 py-2.5 rounded-lg font-medium text-sm transition-all duration-200">
            Semua Riwayat
        </button>
    </div>

    <!-- Tab: Hari Ini (Real-time) -->
    <div x-show="activeTab === 'today'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Absensi Hari Ini</h3>
            <div class="text-sm text-gray-500">
                Updated: <span x-text="lastUpdated"></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Karyawan</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="item in attendances" :key="item.id">
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs mr-3 shadow-sm">
                                        <span x-text="item.user_name.charAt(0)"></span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900" x-text="item.user_name"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono" x-text="item.jam_masuk"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono" x-text="item.jam_pulang"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="item.duration"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-medium border"
                                      :class="item.status_badge_class"
                                      x-text="item.status">
                                </span>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="attendances.length === 0 && !isLoading">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Belum ada data absensi hari ini
                        </td>
                    </tr>
                    
                    <tr x-show="isLoading">
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-indigo-500 hover:bg-indigo-400 transition ease-in-out duration-150 cursor-not-allowed">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading data...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Semua Riwayat (Existing Static Content) -->
    <div x-show="activeTab === 'history'" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-white rounded-lg shadow-lg">
        <!-- Header with Filters -->
        <div class="px-6 py-4 ">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <h2 class="text-lg font-semibold text-gray-900">Filter Riwayat</h2>
                
                <div class="flex flex-col lg:flex-row space-y-2 lg:space-y-0 lg:space-x-2">
                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('admin.riwayat') }}" class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <input type="hidden" name="tab" value="history">
                        <input type="text" 
                               name="search" 
                               value="{{ $search ?? '' }}"
                               placeholder="Cari nama..." 
                               class="px-4 py-2 bg-gray-50 text-gray-900 rounded-lg border border-gray-600 focus:outline-none focus:border-blue-500 text-sm w-full sm:w-auto">
                        
                        <input type="date" 
                               name="start_date" 
                               value="{{ $startDate?->format('Y-m-d') }}"
                               class="px-4 py-2 bg-gray-50 text-gray-900 rounded-lg border border-gray-600 focus:outline-none focus:border-blue-500 text-sm">
                        
                        <input type="date" 
                               name="end_date" 
                               value="{{ $endDate?->format('Y-m-d') }}"
                               class="px-4 py-2 bg-gray-50 text-gray-900 rounded-lg border border-gray-600 focus:outline-none focus:border-blue-500 text-sm">
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-gray-900 rounded-lg hover:bg-blue-700 text-sm">
                            Filter
                        </button>
                    </form>
                    
                    <!-- Export Button -->
                    <a href="{{ route('riwayat.export', request()->query()) }}" 
                       class="px-4 py-2 bg-green-600 text-gray-900 rounded-lg hover:bg-green-700 flex items-center justify-center space-x-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="hidden sm:inline">Export</span>
                        <span class="sm:hidden">CSV</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Table - Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Jam Pulang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $attendance->date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-gray-900 font-bold mr-3">
                                    {{ strtoupper(substr($attendance->user->name, 0, 1)) }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">{{ $attendance->user->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $attendance->jam_masuk?->format('H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $attendance->jam_pulang?->format('H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $attendance->getDurationFormatted() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($attendance->status === 'Hadir') bg-green-600 text-gray-900
                                @elseif($attendance->status === 'Terlambat') bg-yellow-600 text-gray-900
                                @else bg-red-600 text-gray-900
                                @endif">
                                {{ $attendance->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-600">
                            Tidak ada data riwayat ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Cards - Mobile -->
        <div class="md:hidden p-4 space-y-4 pb-20">
            @forelse($attendances as $attendance)
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-gray-900 font-bold">
                            {{ strtoupper(substr($attendance->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $attendance->user->name }}</div>
                            <div class="text-xs text-gray-600">{{ $attendance->date->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        @if($attendance->status === 'Hadir') bg-green-600 text-gray-900
                        @elseif($attendance->status === 'Terlambat') bg-yellow-600 text-gray-900
                        @else bg-red-600 text-gray-900
                        @endif">
                        {{ $attendance->status }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <div>
                        <div class="text-gray-600 text-xs">Masuk</div>
                        <div class="text-gray-900">{{ $attendance->jam_masuk?->format('H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-xs">Pulang</div>
                        <div class="text-gray-900">{{ $attendance->jam_pulang?->format('H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-xs">Durasi</div>
                        <div class="text-gray-900">{{ $attendance->getDurationFormatted() }}</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-600">
                Tidak ada data riwayat ditemukan
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
        <div class="px-4 md:px-6 py-4  bg-white">
            <div class="flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ $attendances->firstItem() }} - {{ $attendances->lastItem() }} dari {{ $attendances->total() }} data
                </div>
                <div class="flex space-x-2">
                    @if($attendances->onFirstPage())
                        <span class="px-4 py-2 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">Previous</span>
                    @else
                        <a href="{{ $attendances->previousPageUrl() }}&tab=history" class="px-4 py-2 bg-blue-600 text-gray-900 rounded-lg hover:bg-blue-700">Previous</a>
                    @endif

                    @if($attendances->hasMorePages())
                        <a href="{{ $attendances->nextPageUrl() }}&tab=history" class="px-4 py-2 bg-blue-600 text-gray-900 rounded-lg hover:bg-blue-700">Next</a>
                    @else
                        <span class="px-4 py-2 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function riwayatRealtime() {
    return {
        activeTab: '{{ request('tab') ?? 'today' }}',
        attendances: [],
        isLoading: true,
        lastUpdated: '-',

        init() {
            this.fetchTodayData();
            
            // Listen for real-time updates
            if (window.Echo) {
                window.Echo.channel('attendances')
                    .listen('AttendanceCreated', (e) => {
                        console.log('New attendance:', e);
                        this.fetchTodayData(); // Refresh data to ensure consistency
                        this.showNotification('Absensi Baru', `${e.attendance.user_name} baru saja absen masuk.`);
                    })
                    .listen('AttendanceUpdated', (e) => {
                        console.log('Attendance updated:', e);
                        this.fetchTodayData();
                        this.showNotification('Absensi Update', `${e.attendance.user_name} baru saja absen pulang.`);
                    });
            }
        },

        async fetchTodayData() {
            try {
                const response = await axios.get('{{ route("api.admin.riwayat.today") }}');
                this.attendances = response.data;
                this.lastUpdated = new Date().toLocaleTimeString();
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        showNotification(title, message) {
            // Simple browser notification or toast could go here
            // For now just log it
            console.log(title, message);
        }
    }
}
</script>
@endsection
