<template>
  <div class="rounded-lg border bg-card p-6">
    <h3 class="mb-4 text-lg font-semibold">Quick Actions</h3>
    
    <div v-if="!myAttendance" class="space-y-4">
      <p class="text-sm text-muted-foreground">You haven't checked in today</p>
      <button
        @click="handleCheckIn"
        :disabled="loading"
        class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
      >
        <span v-if="loading">Checking in...</span>
        <span v-else>Check In</span>
      </button>
    </div>

    <div v-else-if="!myAttendance.check_out" class="space-y-4">
      <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/20">
        <p class="text-sm font-medium text-green-800 dark:text-green-300">
          Checked in at {{ formatTime(myAttendance.check_in) }}
        </p>
        <StatusBadge :status="myAttendance.status" class="mt-2" />
      </div>
      <button
        @click="handleCheckOut"
        :disabled="loading"
        class="w-full rounded-md bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-700 disabled:opacity-50"
      >
        <span v-if="loading">Checking out...</span>
        <span v-else">Check Out</span>
      </button>
    </div>

    <div v-else class="space-y-4">
      <div class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
          You've completed today's attendance
        </p>
        <div class="mt-2 space-y-1 text-xs text-blue-700 dark:text-blue-400">
          <p>Check in: {{ formatTime(myAttendance.check_in) }}</p>
          <p>Check out: {{ formatTime(myAttendance.check_out) }}</p>
          <p v-if="myAttendance.work_duration">
            Duration: {{ formatDuration(myAttendance.work_duration) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import StatusBadge from './StatusBadge.vue'

interface Props {
  myAttendance?: {
    id: number
    check_in: string
    check_out?: string
    status: string
    work_duration?: number
  } | null
}

const props = defineProps<Props>()
const loading = ref(false)

const handleCheckIn = () => {
  loading.value = true
  router.post('/attendances', {}, {
    onFinish: () => {
      loading.value = false
    },
  })
}

const handleCheckOut = () => {
  if (!props.myAttendance) return
  
  loading.value = true
  router.put(`/attendances/${props.myAttendance.id}`, {}, {
    onFinish: () => {
      loading.value = false
    },
  })
}

const formatTime = (datetime: string) => {
  return new Date(datetime).toLocaleTimeString('id-ID', {
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
