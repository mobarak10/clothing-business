<script setup lang="ts">
const props = defineProps<{
  current: number
  last: number
}>()

defineEmits<{ change: [page: number] }>()

/**
 * A compact window around the current page, with ellipses standing in for the
 * stretches nobody clicks. Always shows first and last.
 */
const pages = computed<(number | '…')[]>(() => {
  const { current, last } = props

  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  const window: (number | '…')[] = [1]

  const from = Math.max(2, current - 1)
  const to = Math.min(last - 1, current + 1)

  if (from > 2) window.push('…')

  for (let page = from; page <= to; page++) window.push(page)

  if (to < last - 1) window.push('…')

  window.push(last)

  return window
})
</script>

<template>
  <nav class="flex items-center justify-center gap-1" aria-label="Pagination">
    <button
      type="button"
      class="px-3 py-2 text-xs disabled:opacity-30"
      :disabled="current === 1"
      @click="$emit('change', current - 1)"
    >
      Previous
    </button>

    <template v-for="(page, index) in pages" :key="`${page}-${index}`">
      <span v-if="page === '…'" class="px-2 text-xs text-ink-muted">…</span>

      <button
        v-else
        type="button"
        class="h-9 w-9 text-xs transition-colors"
        :class="page === current ? 'bg-ink text-paper' : 'hover:bg-paper-sunk'"
        :aria-current="page === current ? 'page' : undefined"
        @click="$emit('change', page as number)"
      >
        {{ page }}
      </button>
    </template>

    <button
      type="button"
      class="px-3 py-2 text-xs disabled:opacity-30"
      :disabled="current === last"
      @click="$emit('change', current + 1)"
    >
      Next
    </button>
  </nav>
</template>
