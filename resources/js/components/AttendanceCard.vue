<template>
  <div class="rounded-lg border bg-card p-6 shadow-sm">
    <div class="flex items-start justify-between">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
          <span class="text-sm font-semibold text-primary">
            {{ initials }}
          </span>
        </div>
        <div>
          <h3 class="font-semibold">{{ attendance.user.name }}</h3>
          <p class="text-sm text-muted-foreground">{{ attendance.user.email }}</p>
        </div>
      </div>
      <StatusBadge :status="attendance.status" />
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <p class="text-xs text-muted-foreground">Check In</p>
        <p class="font-medium">{{ formatTime(attendance.check_in) }}</p>
      </div>
      <div v-if="attendance.check_out">
        <p class="text-xs text-muted-foreground">Check Out</p>
        <p class="font-medium">{{ formatTime(attendance.check_out) }}</p>
      </div>
      <div v-else>
        <p class="text-xs text-muted-foreground">Check Out</p>
        <p class="font-medium text-muted-foreground">Not yet</p>
      </div>
    </div>

    <div v-if="attendance.work_duration" class="mt-4">
      <p class="text-xs text-muted-foreground">Work Duration</p>
      <p class="font-medium">{{ formatDuration(attendance.work_duration) }}</p>
    </div>

    <div v-if="attendance.node_id" class="mt-2">
      <p class="text-xs text-muted-foreground">Processed by: Node {{ attendance.node_id }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import StatusBadge from './StatusBadge.vue'

interface Props {
  attendance: {
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
}

const props = defineProps<Props>()

const initials = computed(() => {
  return props.attendance.user.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

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
