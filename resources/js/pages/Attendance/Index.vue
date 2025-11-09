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
              v-for="attendance in attendances.data"
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
import { ref } from 'vue'
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

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Attendance History', href: '/attendances' },
]

const filters = ref({
  date: '',
})

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
</script>
