<template>
  <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg border border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-3 mb-6">
      <div class="rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 p-2">
        <Icon name="zap" class="h-5 w-5 text-white" />
      </div>
      <h3 class="text-lg font-bold text-gray-900 dark:text-white">Quick Actions</h3>
    </div>
    
    <!-- Not checked in yet -->
    <div v-if="!myAttendance" class="space-y-4">
      <div class="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 text-center border border-blue-200 dark:border-blue-800">
        <div class="mx-auto w-fit rounded-full bg-blue-100 dark:bg-blue-900/50 p-4 mb-3">
          <Icon name="clock" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
        </div>
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ready to start?</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">You haven't checked in today</p>
      </div>
      <button
        @click="handleCheckIn"
        :disabled="loading"
        class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 text-base font-semibold text-white shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 active:scale-95"
      >
        <span v-if="loading" class="flex items-center justify-center gap-2">
          <Icon name="loader-circle" class="h-5 w-5 animate-spin" />
          Checking in...
        </span>
        <span v-else class="flex items-center justify-center gap-2">
          <Icon name="log-in" class="h-5 w-5" />
          Check In Now
        </span>
      </button>
    </div>

    <!-- Checked in, waiting for check out -->
    <div v-else-if="!myAttendance.check_out" class="space-y-4">
      <div class="rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-6 border border-green-200 dark:border-green-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="rounded-full bg-green-100 dark:bg-green-900/50 p-2">
            <Icon name="check-circle" class="h-5 w-5 text-green-600 dark:text-green-400" />
          </div>
          <div>
            <p class="text-sm font-semibold text-green-800 dark:text-green-300">
              Checked in successfully
            </p>
            <p class="text-xs text-green-600 dark:text-green-400">
              {{ formatTime(myAttendance.check_in) }}
            </p>
          </div>
        </div>
        <StatusBadge :status="myAttendance.status" class="mt-3" />
      </div>
      <button
        @click="handleCheckOut"
        :disabled="loading"
        class="w-full rounded-xl bg-gradient-to-r from-orange-500 to-red-500 px-6 py-4 text-base font-semibold text-white shadow-lg hover:shadow-xl hover:from-orange-600 hover:to-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 active:scale-95"
      >
        <span v-if="loading" class="flex items-center justify-center gap-2">
          <Icon name="loader-circle" class="h-5 w-5 animate-spin" />
          Checking out...
        </span>
        <span v-else class="flex items-center justify-center gap-2">
          <Icon name="log-out" class="h-5 w-5" />
          Check Out
        </span>
      </button>
    </div>

    <!-- Completed -->
    <div v-else class="space-y-4">
      <div class="rounded-xl bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 p-6 border border-purple-200 dark:border-purple-800">
        <div class="flex items-center gap-3 mb-4">
          <div class="rounded-full bg-purple-100 dark:bg-purple-900/50 p-2">
            <Icon name="check-circle-2" class="h-5 w-5 text-purple-600 dark:text-purple-400" />
          </div>
          <p class="text-sm font-semibold text-purple-800 dark:text-purple-300">
            All done for today! 🎉
          </p>
        </div>
        <div class="space-y-2 text-sm">
          <div class="flex items-center justify-between py-2 border-b border-purple-200 dark:border-purple-800">
            <span class="text-gray-600 dark:text-gray-400">Check in</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ formatTime(myAttendance.check_in) }}</span>
          </div>
          <div class="flex items-center justify-between py-2 border-b border-purple-200 dark:border-purple-800">
            <span class="text-gray-600 dark:text-gray-400">Check out</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ formatTime(myAttendance.check_out) }}</span>
          </div>
          <div v-if="myAttendance.work_duration" class="flex items-center justify-between py-2">
            <span class="text-gray-600 dark:text-gray-400">Duration</span>
            <span class="font-bold text-purple-600 dark:text-purple-400">{{ formatDuration(myAttendance.work_duration) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import StatusBadge from './StatusBadge.vue'
import Icon from './Icon.vue'

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
