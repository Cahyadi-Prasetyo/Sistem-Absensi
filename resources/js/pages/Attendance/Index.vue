<template>
  <Head title="Attendance History" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold">Attendance History</h1>
          <p class="text-muted-foreground">View all attendance records</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex gap-4">
        <input
          type="date"
          v-model="filters.date"
          @change="applyFilters"
          class="rounded-md border px-3 py-2 text-sm"
        />
        <button
          @click="clearFilters"
          class="rounded-md border px-4 py-2 text-sm hover:bg-accent"
        >
          Clear Filters
        </button>
      </div>

      <!-- Attendance Table -->
      <div class="rounded-lg border">
        <table class="w-full">
          <thead class="border-b bg-muted/50">
            <tr>
              <th class="p-4 text-left text-sm font-medium">User</th>
              <th class="p-4 text-left text-sm font-medium">Check In</th>
              <th class="p-4 text-left text-sm font-medium">Check Out</th>
              <th class="p-4 text-left text-sm font-medium">Duration</th>
              <th class="p-4 text-left text-sm font-medium">Status</th>
              <th class="p-4 text-left text-sm font-medium">Node</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="attendance in localAttendances"
              :key="attendance.id"
              class="border-b hover:bg-muted/50"
            >
              <td class="p-4">
                <div>
                  <p class="font-medium">{{ attendance.user.name }}</p>
                  <p class="text-sm text-muted-foreground">{{ attendance.user.email }}</p>
                </div>
              </td>
              <td class="p-4">{{ formatDateTime(attendance.check_in) }}</td>
              <td class="p-4">
                {{ attendance.check_out ? formatDateTime(attendance.check_out) : '-' }}
              </td>
              <td class="p-4">
                {{ attendance.work_duration ? formatDuration(attendance.work_duration) : '-' }}
              </td>
              <td class="p-4">
                <StatusBadge :status="attendance.status" />
              </td>
              <td class="p-4 text-sm text-muted-foreground">
                {{ attendance.node_id || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="attendances.links" class="flex justify-center gap-2">
        <Link
          v-for="link in attendances.links"
          :key="link.label"
          :href="link.url"
          :class="[
            'rounded-md px-3 py-2 text-sm',
            link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-accent',
            !link.url && 'opacity-50 cursor-not-allowed',
          ]"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import { type BreadcrumbItem } from '@/types'

interface Props {
  attendances: {
    data: any[]
    links: any[]
  }
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Attendance History', href: '/attendances' },
]

const filters = ref({
  date: '',
})

// Local state untuk real-time updates
const localAttendances = ref(props.attendances.data)

const applyFilters = () => {
  router.get('/attendances', filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  filters.value.date = ''
  router.get('/attendances')
}

const formatDateTime = (datetime: string) => {
  return new Date(datetime).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatDuration = (minutes: number) => {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return `${hours}h ${mins}m`
}

// Real-time WebSocket listeners
onMounted(() => {
  // Listen untuk attendance baru
  window.Echo.channel('attendances')
    .listen('.attendance.created', (event: any) => {
      console.log('New attendance received:', event)
      
      // Tambahkan attendance baru ke awal list
      const newAttendance = {
        id: event.attendance.id,
        user_id: event.attendance.user_id,
        check_in: event.attendance.check_in,
        check_out: null,
        status: event.attendance.status,
        work_duration: null,
        node_id: event.attendance.node_id,
        user: event.user,
      }
      
      localAttendances.value.unshift(newAttendance)
      
      // Tampilkan notifikasi
      showNotification(`${event.user.name} telah check-in dari Node ${event.attendance.node_id}`)
    })
    .listen('.attendance.updated', (event: any) => {
      console.log('Attendance updated:', event)
      
      // Update attendance yang sudah ada
      const index = localAttendances.value.findIndex(a => a.id === event.attendance.id)
      if (index !== -1) {
        localAttendances.value[index] = {
          ...localAttendances.value[index],
          check_out: event.attendance.check_out,
          work_duration: event.attendance.work_duration,
          status: event.attendance.status,
        }
        
        // Tampilkan notifikasi
        showNotification(`${event.user.name} telah check-out dari Node ${event.attendance.node_id}`)
      }
    })
})

onUnmounted(() => {
  // Cleanup: leave channel saat component unmount
  window.Echo.leave('attendances')
})

// Helper untuk notifikasi
const showNotification = (message: string) => {
  // Bisa diganti dengan toast library seperti vue-toastification
  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification('Attendance Update', { body: message })
  }
}
</script>
