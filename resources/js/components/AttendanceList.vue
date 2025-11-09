<template>
  <div class="space-y-4">
    <!-- Header with live indicator -->
    <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg border border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 p-2">
            <Icon name="activity" class="h-5 w-5 text-white" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recent Attendances</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Real-time updates</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
          </span>
          <span class="text-sm font-medium text-green-600 dark:text-green-400">Live</span>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="attendances.length === 0" class="rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-12 text-center">
      <div class="mx-auto w-fit rounded-full bg-gray-200 dark:bg-gray-700 p-4 mb-4">
        <Icon name="inbox" class="h-8 w-8 text-gray-400" />
      </div>
      <p class="text-gray-600 dark:text-gray-400 font-medium">No attendances yet today</p>
      <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Check-ins will appear here in real-time</p>
    </div>

    <!-- Attendance cards -->
    <div v-else class="grid gap-4 md:grid-cols-2">
      <AttendanceCard
        v-for="attendance in attendances"
        :key="attendance.id"
        :attendance="attendance"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import AttendanceCard from './AttendanceCard.vue'
import Icon from './Icon.vue'

interface Attendance {
  id: number
  user: {
    name: string
    email: string
  }
  check_in: string
  check_out?: string
  status: string
  work_duration?: number
  node_id?: string
}

interface Props {
  attendances: Attendance[]
}

defineProps<Props>()
</script>
