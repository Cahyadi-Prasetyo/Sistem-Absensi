<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Recent Attendances</h2>
      <span class="text-sm text-muted-foreground">Live updating</span>
    </div>

    <div v-if="attendances.length === 0" class="rounded-lg border border-dashed p-8 text-center">
      <p class="text-muted-foreground">No attendances yet today</p>
    </div>

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
