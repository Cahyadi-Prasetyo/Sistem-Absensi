<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Users, Calendar, Clock, TrendingUp } from 'lucide-vue-next';

interface Stats {
    total_users: number;
    total_attendances: number;
    today_attendances: number;
    on_time_today: number;
    late_today: number;
}

interface Attendance {
    id: number;
    check_in: string;
    check_out: string | null;
    status: string;
    node_id?: number;
    user: {
        name: string;
        email: string;
    };
}

interface Props {
    stats: Stats;
    recent_attendances: Attendance[];
}

const props = defineProps<Props>();

// Local reactive state untuk real-time updates
const localStats = ref({ ...props.stats });
const localAttendances = ref([...props.recent_attendances]);

const statCards = computed(() => [
    {
        name: 'Total Users',
        value: localStats.value.total_users,
        icon: Users,
        color: 'blue',
    },
    {
        name: 'Total Attendances',
        value: localStats.value.total_attendances,
        icon: Calendar,
        color: 'green',
    },
    {
        name: 'Today Check-ins',
        value: localStats.value.today_attendances,
        icon: Clock,
        color: 'purple',
    },
    {
        name: 'On Time Today',
        value: localStats.value.on_time_today,
        icon: TrendingUp,
        color: 'emerald',
    },
]);

const formatTime = (datetime: string) => {
    return new Date(datetime).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDate = (datetime: string) => {
    return new Date(datetime).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

// Real-time WebSocket listeners
onMounted(() => {
    console.log('Admin Dashboard: Subscribing to attendance channel...');
    
    window.Echo.channel('attendances')
        .listen('.attendance.created', (event: any) => {
            console.log('Admin Dashboard: New attendance received', event);
            
            // Update stats
            localStats.value.total_attendances++;
            localStats.value.today_attendances++;
            if (event.attendance.status === 'on_time') {
                localStats.value.on_time_today++;
            } else {
                localStats.value.late_today++;
            }
            
            // Tambahkan ke recent attendances
            const newAttendance: Attendance = {
                id: event.attendance.id,
                check_in: event.attendance.check_in,
                check_out: null,
                status: event.attendance.status,
                node_id: event.attendance.node_id,
                user: event.user,
            };
            
            localAttendances.value.unshift(newAttendance);
            
            // Batasi hanya 10 recent attendances
            if (localAttendances.value.length > 10) {
                localAttendances.value.pop();
            }
            
            // Show notification
            showNotification(
                `✅ ${event.user.name} check-in dari Node ${event.attendance.node_id}`,
                event.attendance.status === 'on_time' ? 'success' : 'warning'
            );
        })
        .listen('.attendance.updated', (event: any) => {
            console.log('Admin Dashboard: Attendance updated', event);
            
            // Update attendance di list
            const index = localAttendances.value.findIndex(a => a.id === event.attendance.id);
            if (index !== -1) {
                localAttendances.value[index] = {
                    ...localAttendances.value[index],
                    check_out: event.attendance.check_out,
                    status: event.attendance.status,
                };
                
                showNotification(
                    `🏁 ${event.user.name} check-out dari Node ${event.attendance.node_id}`,
                    'info'
                );
            }
        });
});

onUnmounted(() => {
    console.log('Admin Dashboard: Unsubscribing from attendance channel...');
    window.Echo.leave('attendances');
});

// Helper untuk notifikasi
const showNotification = (message: string, type: 'success' | 'warning' | 'info' = 'info') => {
    // Browser notification
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('Attendance System', { 
            body: message,
            icon: '/favicon.ico'
        });
    }
    
    // Console log untuk debugging
    console.log(`[${type.toUpperCase()}] ${message}`);
};

// Request notification permission saat mount
onMounted(() => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});
</script>

<template>
    <AdminLayout title="Dashboard">
        <!-- Welcome Banner -->
        <div class="mb-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 text-white">
            <h1 class="text-2xl font-bold mb-2">Welcome back, Admin! 👋</h1>
            <p class="text-blue-100">Here's what's happening with your attendance system today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div
                v-for="stat in statCards"
                :key="stat.name"
                class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 p-6 border-l-4"
                :class="`border-${stat.color}-500`"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 mb-1">{{ stat.name }}</p>
                        <p class="text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                    </div>
                    <div
                        :class="[
                            `bg-${stat.color}-100`,
                            'rounded-full p-4',
                        ]"
                    >
                        <component
                            :is="stat.icon"
                            :class="[
                                `text-${stat.color}-600`,
                                'h-8 w-8',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Attendances -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Attendances</h3>
                    <span class="text-sm text-gray-500">Real-time updates</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Check In
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Check Out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr
                            v-for="attendance in localAttendances"
                            :key="attendance.id"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ attendance.user.name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ attendance.user.email }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ formatDate(attendance.check_in) }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ formatTime(attendance.check_in) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div v-if="attendance.check_out" class="text-sm text-gray-900">
                                    {{ formatTime(attendance.check_out) }}
                                </div>
                                <div v-else class="text-sm text-gray-500">-</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        attendance.status === 'on_time'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800',
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    ]"
                                >
                                    {{ attendance.status === 'on_time' ? 'On Time' : 'Late' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
