<script setup lang="ts">
import type { CategoryPayload, PagePayload, ResolveResponse } from '~/types/catalog'

/**
 * The catch-all route.
 *
 * Categories nest three deep and CMS pages sit at the root, so the storefront
 * cannot tell from the URL alone what it is looking at. It asks the API, which
 * answers with a type -- meaning a new addressable content type needs no new
 * frontend route.
 *
 * Static routes (/cart, /search, /store-finder) outrank this one in Nuxt's
 * router, so they are never swallowed here.
 */
const route = useRoute()
const api = useApi()

const path = computed(() =>
  (Array.isArray(route.params.path) ? route.params.path.join('/') : route.params.path) || '',
)

const { data: resolved } = await useAsyncData(
  () => `resolve:${path.value}`,
  () => api<ResolveResponse>('/api/v1/resolve', { query: { path: path.value } }),
  { watch: [path] },
)

if (!resolved.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

const isCategory = computed(() => resolved.value?.type === 'category')
const category = computed(() => (isCategory.value ? resolved.value!.data as CategoryPayload : null))
const page = computed(() => (!isCategory.value ? resolved.value!.data as PagePayload : null))

const seo = computed(() => resolved.value!.data.seo)

useSeoMeta({
  title: () => seo.value.title,
  description: () => seo.value.description ?? undefined,
  ogTitle: () => seo.value.title,
  ogDescription: () => seo.value.description ?? undefined,
})
</script>

<template>
  <CatalogCategoryView v-if="category" :key="category.path" :category="category" />

  <ContentPageView v-else-if="page" :page="page" />
</template>
