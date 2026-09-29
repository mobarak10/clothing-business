<script setup lang="ts">
defineProps<{
  payload: {
    heading?: string
    body?: string
    cta_label?: string
    cta_url?: string
    image?: string
  }
}>()
</script>

<template>
  <section class="relative overflow-hidden bg-ink text-white">
    <img
      v-if="payload.image"
      :src="payload.image"
      :alt="payload.heading ?? ''"
      class="absolute inset-0 h-full w-full object-cover opacity-45"
      loading="lazy"
    >

    <div class="container-page relative flex flex-col items-center gap-6 py-24 text-center md:py-32">
      <h2 v-if="payload.heading" class="font-display text-3xl md:text-5xl">
        {{ payload.heading }}
      </h2>

      <p v-if="payload.body" class="max-w-md text-sm opacity-90">
        {{ payload.body }}
      </p>

      <NuxtLink
        v-if="payload.cta_url"
        :to="payload.cta_url"
        class="btn mt-2 border-white text-white transition-colors hover:bg-white hover:text-ink"
      >
        {{ payload.cta_label ?? 'Find a boutique' }}
      </NuxtLink>
    </div>
  </section>
</template>
