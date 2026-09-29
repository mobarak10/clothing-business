<script setup lang="ts">
import type { ProductImage } from '~/types/catalog'

const props = defineProps<{
  images: ProductImage[]
  name: string
  /** When set, the gallery narrows to images tagged with this colour. */
  colorFilter?: string | null
}>()

/**
 * Images may carry a `color` custom property. When a shopper picks a swatch we
 * show only that colourway -- but only if such images actually exist, so a
 * catalog that has not been tagged still shows everything.
 */
const visible = computed(() => {
  if (!props.colorFilter) return props.images

  const matching = props.images.filter(image => image.color === props.colorFilter)

  return matching.length ? matching : props.images
})

const activeIndex = ref(0)

watch(visible, () => {
  activeIndex.value = 0
})

const active = computed(() => visible.value[activeIndex.value] ?? null)
</script>

<template>
  <div class="flex flex-col-reverse gap-4 md:flex-row md:gap-6">
    <!-- Thumbnail rail -->
    <ul v-if="visible.length > 1" class="flex gap-3 overflow-x-auto md:w-20 md:flex-col md:overflow-visible">
      <li v-for="(image, index) in visible" :key="index" class="shrink-0">
        <button
          type="button"
          class="block aspect-[3/4] w-16 overflow-hidden border transition-colors md:w-full"
          :class="index === activeIndex ? 'border-ink' : 'border-transparent hover:border-line-strong'"
          :aria-label="`View image ${index + 1}`"
          :aria-current="index === activeIndex"
          @click="activeIndex = index"
        >
          <img
            :src="image.thumb ?? image.card"
            :alt="image.alt"
            class="h-full w-full object-cover"
            loading="lazy"
          >
        </button>
      </li>
    </ul>

    <!-- Main image -->
    <div class="flex-1">
      <div class="aspect-[3/4] overflow-hidden bg-paper-sunk">
        <img
          v-if="active"
          :src="active.zoom ?? active.card"
          :alt="active.alt"
          class="h-full w-full object-cover"
          fetchpriority="high"
        >

        <div v-else class="flex h-full items-center justify-center">
          <span class="label-caps text-ink-muted">{{ name }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
