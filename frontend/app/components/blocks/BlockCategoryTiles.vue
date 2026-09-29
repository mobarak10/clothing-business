<script setup lang="ts">
defineProps<{
  payload: {
    heading?: string
    tiles?: { label: string, url: string, image?: string }[]
  }
}>()
</script>

<template>
  <section class="container-page py-section">
    <h2 v-if="payload.heading" class="font-display text-3xl md:text-4xl">
      {{ payload.heading }}
    </h2>

    <div class="mt-10 grid grid-cols-2 gap-4 md:gap-6 lg:grid-cols-4">
      <NuxtLink
        v-for="tile in payload.tiles"
        :key="tile.url"
        :to="tile.url"
        class="group block"
      >
        <div class="relative aspect-[3/4] overflow-hidden bg-paper-sunk">
          <img
            v-if="tile.image"
            :src="tile.image"
            :alt="tile.label"
            class="h-full w-full object-cover transition-transform duration-700 ease-editorial group-hover:scale-105"
            loading="lazy"
          >
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
          <span class="label-caps absolute bottom-4 left-4 text-white">
            {{ tile.label }}
          </span>
        </div>
      </NuxtLink>
    </div>
  </section>
</template>
