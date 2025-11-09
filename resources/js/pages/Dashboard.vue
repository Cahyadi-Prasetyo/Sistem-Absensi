<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { dashboard } from '@/routes'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import LiveCounter from '@/components/LiveCounter.vue'
import AttendanceList from '@/components/AttendanceList.vue'
import CheckInButton from '@/components/CheckInButton.vue'

interface Props {
    stats: {
        total_users: number
        today_attendances: number
        present_today: number
        late_today: number
        absent_today: number
    }
    recent_attendances: any[]
    my_attendance: any
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
]

// Reactive state for real-time updates
const stats = ref(props.stats)
const recentAttendances = ref(props.recent_attendances)
const myAttendance = ref(props.my_attendance)

// Setup Echo listeners for real-time updates
onMounted(() => {
    // Listen to public attendances channel
    window.Echo.channel('attendances')
        .listen('.attendance.created', (e: any) => {
            console.log('New attendance:', e)
            
            // Update stats
            stats.value.today_attendances++
            if (e.attendance.status === 'present') {
                stats.value.present_today++
            } else if (e.attendance.status === 'late') {
                stats.value.late_today++
            }
            stats.value.absent_today = stats.value.total_users - stats.value.today_attendances
            
            // Add to recent list (prepend)
            recentAttendances.value.unshift({
                id: e.attendance.id,
                user: e.user,
                check_in: e.attendance.check_in,
                status: e.attendance.status,
                node_id: e.attendance.node_id,
            })
            
            // Keep only last 10
            if (recentAttendances.value.length > 10) {
                recentAttendances.value.pop()
            }
        })
        .listen('.attendance.updated', (e: any) => {
            console.log('Attendance updated:', e)
            
            // Update in recent list
            const index = recentAttendances.value.findIndex(a => a.id === e.attendance.id)
            if (index !== -1) {
                recentAttendances.value[index] = {
                    ...recentAttendances.value[index],
                    check_out: e.attendance.check_out,
                    work_duration: e.attendance.work_duration,
                }
            }
        })
})

onUnmounted(() => {
    window.Echo.leave('attendances')
})
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold">Dashboard</h1>
                <p class="text-muted-foreground">Real-time attendance monitoring</p>
            </div>

            <!-- Live Statistics -->
            <LiveCounter :stats="stats" />

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Recent Attendances (2/3 width) -->
                <div class="lg:col-span-2">
                    <AttendanceList :attendances="recentAttendances" />
                </div>

                <!-- Quick Actions (1/3 width) -->
                <div>
                    <CheckInButton :my-attendance="myAttendance" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
