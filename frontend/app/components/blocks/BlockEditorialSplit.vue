<script setup lang="ts">
const props = defineProps<{
  payload: {
    eyebrow?: string
    heading?: string
    body?: string
    cta_label?: string
    cta_url?: string
    image?: string
    image_position?: 'left' | 'right'
  }
}>()

// Text first in the DOM either way; order is a visual concern only, so the
// reading order stays sensible for screen readers.
const imageFirst = computed(() => (props.payload.image_position ?? 'right') === 'left')
</script>

<template>
  <section class="py-section">
    <div class="container-page grid items-center gap-10 md:grid-cols-2 md:gap-16">
      <div :class="imageFirst ? 'md:order-2' : ''">
        <p v-if="payload.eyebrow" class="label-caps text-ink-muted">
          {{ payload.eyebrow }}
        </p>

        <h2 v-if="payload.heading" class="mt-4 font-display text-3xl md:text-4xl lg:text-5xl">
          {{ payload.heading }}
        </h2>

        <p v-if="payload.body" class="mt-5 max-w-md text-sm text-ink-soft md:text-base">
          {{ payload.body }}
        </p>

        <NuxtLink
          v-if="payload.cta_url"
          :to="payload.cta_url"
          class="btn btn-outline mt-8"
        >
          {{ payload.cta_label ?? 'Discover' }}
        </NuxtLink>
      </div>

      <div :class="imageFirst ? 'md:order-1' : ''">
        <div class="aspect-[4/5] overflow-hidden bg-paper-sunk md:aspect-[3/4]">
          <img
            v-if="payload.image"
            :src="payload.image"
            :alt="payload.heading ?? ''"
            class="h-full w-full object-cover"
            loading="lazy"
          >
        </div>
      </div>
    </div>
  </section>
</template>
