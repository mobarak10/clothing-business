<script setup lang="ts">
import type { ContentBlock } from '~/types/catalog'

const api = useApi()

const { data: blocks } = await useAsyncData(
  'home',
  () => api<{ data: ContentBlock[] }>('/api/v1/home').then(r => r.data),
  { default: () => [] as ContentBlock[] },
)

const { data: settings } = await useShopSettings()

useSeoMeta({
  title: `${settings.value.brand.name} — ${settings.value.brand.tagline}`,
  description: settings.value.brand.tagline,
  ogTitle: settings.value.brand.name,
  ogDescription: settings.value.brand.tagline,
  ogType: 'website',
})
</script>

<template>
  <div>
    <BlocksBlockRenderer v-if="blocks.length" :blocks="blocks" />

    <!-- Nothing published yet: point the operator at the admin rather than
         showing a blank page. -->
    <section v-else class="container-page flex min-h-[60vh] flex-col justify-center py-24">
      <p class="label-caps text-ink-muted">Homepage</p>
      <h1 class="mt-4 font-display text-4xl md:text-5xl">No content blocks published</h1>
      <p class="mt-4 max-w-md text-sm text-ink-soft">
        Add a campaign hero and other sections under <strong>Content → Content blocks</strong>
        in the admin, and they will appear here in order.
      </p>
    </section>
  </div>
</template>
