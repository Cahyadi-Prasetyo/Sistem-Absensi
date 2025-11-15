@extends('layouts.karyawan')

@section('page-title', 'Riwayat Absensi')
@section('page-subtitle', 'Lihat riwayat absensi Anda')

@section('content')
<div class="bg-white rounded-lg shadow-lg">
    <!-- Header with Filters -->
    <div class="px-6 py-4 ">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Absensi Anda</h2>
            
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2">
                <!-- Date Filter -->
                <form method="GET" action="{{ route('karyawan.riwayat') }}" class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
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
                    <span>Export</span>
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
                    <td colspan="5" class="px-6 py-8 text-center text-gray-600">
                        Tidak ada data riwayat ditemukan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Cards - Mobile & Tablet -->
    <div class="md:hidden p-4 space-y-4 pb-20">
        @forelse($attendances as $attendance)
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="text-sm font-medium text-gray-900">{{ $attendance->date->format('d/m/Y') }}</div>
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
                    <div class="text-gray-900 font-medium">{{ $attendance->jam_masuk?->format('H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-600 text-xs">Pulang</div>
                    <div class="text-gray-900 font-medium">{{ $attendance->jam_pulang?->format('H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-600 text-xs">Durasi</div>
                    <div class="text-gray-900 font-medium">{{ $attendance->getDurationFormatted() }}</div>
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
                    <span class="px-4 py-2 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed text-sm">Previous</span>
                @else
                    <a href="{{ $attendances->previousPageUrl() }}" class="px-4 py-2 bg-blue-600 text-gray-900 rounded-lg hover:bg-blue-700 text-sm">Previous</a>
                @endif

                @if($attendances->hasMorePages())
                    <a href="{{ $attendances->nextPageUrl() }}" class="px-4 py-2 bg-blue-600 text-gray-900 rounded-lg hover:bg-blue-700 text-sm">Next</a>
                @else
                    <span class="px-4 py-2 bg-gray-50 text-gray-500 rounded-lg cursor-not-allowed text-sm">Next</span>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
