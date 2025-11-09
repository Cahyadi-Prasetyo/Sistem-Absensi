<template>
  <span :class="badgeClass" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">
    {{ statusText }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  status: string
}

const props = defineProps<Props>()

const statusText = computed(() => {
  const statusMap: Record<string, string> = {
    present: 'Present',
    late: 'Late',
    absent: 'Absent',
    leave: 'Leave',
  }
  return statusMap[props.status] || props.status
})

const badgeClass = computed(() => {
  const classMap: Record<string, string> = {
    present: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    late: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    absent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    leave: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  }
  return classMap[props.status] || 'bg-gray-100 text-gray-800'
})
</script>
