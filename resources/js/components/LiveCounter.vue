<template>
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border bg-card p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-muted-foreground">Total Attendance</p>
        <Icon name="users" class="h-4 w-4 text-muted-foreground" />
      </div>
      <div class="mt-2">
        <p class="text-3xl font-bold">{{ stats.today_attendances }}</p>
        <p class="text-xs text-muted-foreground">of {{ stats.total_users }} users</p>
      </div>
    </div>

    <div class="rounded-lg border bg-card p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-muted-foreground">Present</p>
        <Icon name="check-circle" class="h-4 w-4 text-green-600" />
      </div>
      <div class="mt-2">
        <p class="text-3xl font-bold text-green-600">{{ stats.present_today }}</p>
        <p class="text-xs text-muted-foreground">on time</p>
      </div>
    </div>

    <div class="rounded-lg border bg-card p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-muted-foreground">Late</p>
        <Icon name="clock" class="h-4 w-4 text-yellow-600" />
      </div>
      <div class="mt-2">
        <p class="text-3xl font-bold text-yellow-600">{{ stats.late_today }}</p>
        <p class="text-xs text-muted-foreground">arrived late</p>
      </div>
    </div>

    <div class="rounded-lg border bg-card p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-muted-foreground">Absent</p>
        <Icon name="x-circle" class="h-4 w-4 text-red-600" />
      </div>
      <div class="mt-2">
        <p class="text-3xl font-bold text-red-600">{{ stats.absent_today }}</p>
        <p class="text-xs text-muted-foreground">not checked in</p>
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
