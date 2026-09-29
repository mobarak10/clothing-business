<script setup lang="ts">
import type { Paginated, ProductCard } from '~/types/catalog'

const route = useRoute()
const router = useRouter()
const api = useApi()
const filters = useCatalogFilters()

// Local field so typing does not push a route on every keystroke.
const term = ref(String(route.query.q ?? ''))

watch(() => route.query.q, value => (term.value = String(value ?? '')))

function submit() {
  router.push({ query: { q: term.value || undefined } })
}

const { data: results, status } = await useAsyncData(
  () => `search:${JSON.stringify(route.query)}`,
  () => (route.query.q
    ? api<Paginated<ProductCard>>('/api/v1/search', { query: filters.apiQuery.value })
    : Promise.resolve(null)),
  { watch: [() => route.query] },
)

const products = computed(() => results.value?.data ?? [])
const meta = computed(() => results.value?.meta)

useSeoMeta({
  title: () => (route.query.q ? `Search: ${route.query.q}` : 'Search'),
  robots: 'noindex',
})
</script>

<template>
  <div class="container-page py-12 md:py-16">
    <h1 class="font-display text-3xl md:text-4xl">Search</h1>

    <form class="mt-8 max-w-xl" @submit.prevent="submit">
      <div class="flex gap-3">
        <label for="search-field" class="sr-only">Search the collection</label>
        <input
          id="search-field"
          v-model="term"
          type="search"
          autofocus
          placeholder="Suits, cashmere, loafers…"
          class="w-full border border-line-strong bg-surface px-4 py-3.5 text-sm outline-none transition-colors placeholder:text-ink-muted focus:border-ink"
        >
        <button type="submit" class="btn btn-primary shrink-0">Search</button>
      </div>
    </form>

    <div v-if="route.query.q" class="mt-12">
      <p class="text-xs text-ink-muted">
        <template v-if="meta?.message">{{ meta.message }}</template>
        <template v-else>
          {{ meta?.total ?? 0 }} {{ (meta?.total ?? 0) === 1 ? 'result' : 'results' }}
          for “{{ route.query.q }}”
        </template>
      </p>

      <div
        v-if="products.length"
        class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 md:gap-x-6 xl:grid-cols-4"
      >
        <ProductTile
          v-for="(product, index) in products"
          :key="product.id"
          :product="product"
          :priority="index < 4"
        />
      </div>

      <div v-else-if="status !== 'pending' && !meta?.message" class="py-20">
        <p class="font-display text-2xl">Nothing found</p>
        <p class="mt-3 text-sm text-ink-muted">
          Try a broader term, or browse
          <NuxtLink to="/clothing" class="underline underline-offset-4">the collection</NuxtLink>.
        </p>
      </div>

      <CatalogPagination
        v-if="meta && meta.last_page > 1"
        class="mt-16"
        :current="meta.current_page"
        :last="meta.last_page"
        @change="filters.setPage"
      />
    </div>
  </div>
</template>
