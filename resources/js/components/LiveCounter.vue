<template>
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <!-- Total Attendance Card -->
    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 shadow-lg transition-all hover:shadow-xl hover:scale-105">
      <div class="absolute top-0 right-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-white/10"></div>
      <div class="relative">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-blue-100">Total Attendance</p>
          <div class="rounded-lg bg-white/20 p-2">
            <Icon name="users" class="h-5 w-5 text-white" />
          </div>
        </div>
        <p class="text-4xl font-bold text-white mb-1">{{ stats.today_attendances }}</p>
        <p class="text-sm text-blue-100">of {{ stats.total_users }} users</p>
      </div>
    </div>

    <!-- Present Card -->
    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 p-6 shadow-lg transition-all hover:shadow-xl hover:scale-105">
      <div class="absolute top-0 right-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-white/10"></div>
      <div class="relative">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-green-100">Present</p>
          <div class="rounded-lg bg-white/20 p-2">
            <Icon name="check-circle" class="h-5 w-5 text-white" />
          </div>
        </div>
        <p class="text-4xl font-bold text-white mb-1">{{ stats.present_today }}</p>
        <p class="text-sm text-green-100">on time today</p>
      </div>
    </div>

    <!-- Late Card -->
    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-6 shadow-lg transition-all hover:shadow-xl hover:scale-105">
      <div class="absolute top-0 right-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-white/10"></div>
      <div class="relative">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-amber-100">Late</p>
          <div class="rounded-lg bg-white/20 p-2">
            <Icon name="clock" class="h-5 w-5 text-white" />
          </div>
        </div>
        <p class="text-4xl font-bold text-white mb-1">{{ stats.late_today }}</p>
        <p class="text-sm text-amber-100">arrived late</p>
      </div>
    </div>

    <!-- Absent Card -->
    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-red-500 to-rose-600 p-6 shadow-lg transition-all hover:shadow-xl hover:scale-105">
      <div class="absolute top-0 right-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-white/10"></div>
      <div class="relative">
        <div class="flex items-center justify-between mb-3">
          <p class="text-sm font-medium text-red-100">Absent</p>
          <div class="rounded-lg bg-white/20 p-2">
            <Icon name="x-circle" class="h-5 w-5 text-white" />
          </div>
        </div>
        <p class="text-4xl font-bold text-white mb-1">{{ stats.absent_today }}</p>
        <p class="text-sm text-red-100">not checked in</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Icon from './Icon.vue'

interface Props {
  stats: {
    total_users: number
    today_attendances: number
    present_today: number
    late_today: number
    absent_today: number
  }
}

defineProps<Props>()
</script>
