<script setup lang="ts">
import type { ProductCard } from '~/types/catalog'

defineProps<{
  payload: {
    heading?: string
    subheading?: string
    cta_label?: string
    cta_url?: string
  }
  products?: ProductCard[]
}>()

const track = ref<HTMLElement | null>(null)

/** Scrolls by roughly one card and a half, so the next one peeks in. */
function scrollBy(direction: 1 | -1) {
  if (!track.value) return

  track.value.scrollBy({
    left: direction * Math.round(track.value.clientWidth * 0.6),
    behavior: 'smooth',
  })
}
</script>

<template>
  <section v-if="products?.length" class="py-section">
    <div class="container-page flex items-end justify-between gap-6">
      <div>
        <h2 v-if="payload.heading" class="font-display text-3xl md:text-4xl">
          {{ payload.heading }}
        </h2>
        <p v-if="payload.subheading" class="mt-2 text-sm text-ink-muted">
          {{ payload.subheading }}
        </p>
      </div>

      <div class="hidden shrink-0 gap-2 md:flex">
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center border border-line-strong transition-colors hover:border-ink"
          aria-label="Previous"
          @click="scrollBy(-1)"
        >
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.2">
            <path d="M9 1L3 7l6 6" />
          </svg>
        </button>
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center border border-line-strong transition-colors hover:border-ink"
          aria-label="Next"
          @click="scrollBy(1)"
        >
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.2">
            <path d="M5 1l6 6-6 6" />
          </svg>
        </button>
      </div>
    </div>

    <!--
      Scroll-snap rather than a JS carousel: it is keyboard and touch native,
      degrades to a plain scroller without JS, and costs no library.
    -->
    <div
      ref="track"
      class="mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-4 md:gap-6 md:px-10 xl:px-16"
      style="scrollbar-width: none"
    >
      <div
        v-for="product in products"
        :key="product.id"
        class="w-[62%] shrink-0 snap-start sm:w-[38%] lg:w-[23%] xl:w-[19%]"
      >
        <ProductTile :product="product" />
      </div>
    </div>

    <div v-if="payload.cta_url" class="container-page mt-8">
      <NuxtLink :to="payload.cta_url" class="btn btn-outline">
        {{ payload.cta_label ?? 'View all' }}
      </NuxtLink>
    </div>
  </section>
</template>

<style scoped>
/* Firefox uses scrollbar-width above; this covers WebKit. */
div::-webkit-scrollbar {
  display: none;
}
</style>
