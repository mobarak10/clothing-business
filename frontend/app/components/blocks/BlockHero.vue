<script setup lang="ts">
const props = defineProps<{
  payload: {
    eyebrow?: string
    heading?: string
    body?: string
    cta_label?: string
    cta_url?: string
    image?: string
    image_mobile?: string
    alignment?: 'left' | 'center'
    theme?: 'light' | 'dark'
  }
  /** The first block on the page carries the LCP image. */
  first?: boolean
}>()

// "light" means light text on a dark image.
const isLightText = computed(() => (props.payload.theme ?? 'light') === 'light')

const alignment = computed(() =>
  props.payload.alignment === 'center'
    ? 'items-center text-center'
    : 'items-start text-left',
)
</script>

<template>
  <section class="relative min-h-[85vh] w-full overflow-hidden bg-ink md:min-h-screen">
    <picture v-if="payload.image">
      <source
        v-if="payload.image_mobile"
        :srcset="payload.image_mobile"
        media="(max-width: 767px)"
      >
      <img
        :src="payload.image"
        :alt="payload.heading ?? ''"
        class="absolute inset-0 h-full w-full object-cover"
        :loading="first ? 'eager' : 'lazy'"
        :fetchpriority="first ? 'high' : 'auto'"
      >
    </picture>

    <!-- Scrim: keeps the headline legible whatever the photograph does. -->
    <div
      v-if="isLightText"
      class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/20 to-black/25"
    />

    <div class="container-page relative flex min-h-[85vh] flex-col justify-end pb-20 md:min-h-screen md:pb-28">
      <div
        class="flex max-w-2xl flex-col gap-5"
        :class="[alignment, isLightText ? 'text-white' : 'text-ink']"
      >
        <p v-if="payload.eyebrow" class="label-caps opacity-90">
          {{ payload.eyebrow }}
        </p>

        <h1 v-if="payload.heading" class="font-display text-4xl leading-[1.05] sm:text-5xl md:text-6xl lg:text-7xl">
          {{ payload.heading }}
        </h1>

        <p v-if="payload.body" class="max-w-md text-sm opacity-90 md:text-base">
          {{ payload.body }}
        </p>

        <NuxtLink
          v-if="payload.cta_url"
          :to="payload.cta_url"
          class="btn mt-2 self-start"
          :class="isLightText ? 'border-white text-white hover:bg-white hover:text-ink' : 'btn-outline'"
        >
          {{ payload.cta_label ?? 'Discover' }}
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
