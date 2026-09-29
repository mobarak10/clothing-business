<script setup lang="ts">
defineProps<{
  payload: {
    heading?: string
    looks?: { title: string, caption?: string, image?: string, url?: string }[]
  }
}>()
</script>

<template>
  <section class="container-page py-section">
    <h2 v-if="payload.heading" class="font-display text-3xl md:text-4xl">
      {{ payload.heading }}
    </h2>

    <div class="mt-10 grid gap-6 md:grid-cols-3 md:gap-8">
      <NuxtLink
        v-for="look in payload.looks"
        :key="look.title"
        :to="look.url ?? '#'"
        class="group block"
      >
        <div class="aspect-[4/5] overflow-hidden bg-paper-sunk">
          <img
            v-if="look.image"
            :src="look.image"
            :alt="look.title"
            class="h-full w-full object-cover transition-transform duration-700 ease-editorial group-hover:scale-[1.04]"
            loading="lazy"
          >
        </div>

        <h3 class="mt-5 font-display text-xl">{{ look.title }}</h3>
        <p v-if="look.caption" class="mt-1.5 text-sm text-ink-muted">
          {{ look.caption }}
        </p>
      </NuxtLink>
    </div>
  </section>
</template>
