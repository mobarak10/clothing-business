<script setup lang="ts">
import type { CategoryPayload, Paginated, ProductCard } from '~/types/catalog'

const props = defineProps<{ category: CategoryPayload }>()

const api = useApi()
const route = useRoute()
const filters = useCatalogFilters()

const drawerOpen = ref(false)

const { data: listing, status } = await useAsyncData(
  () => `listing:${props.category.path}:${JSON.stringify(route.query)}`,
  () => api<Paginated<ProductCard>>(
    `/api/v1/categories/${props.category.path}/products`,
    { query: filters.apiQuery.value },
  ),
  { watch: [() => route.query] },
)

const products = computed(() => listing.value?.data ?? [])
const meta = computed(() => listing.value?.meta)

const sortOptions = [
  { value: 'newest', label: 'Newest' },
  { value: 'price_asc', label: 'Price: low to high' },
  { value: 'price_desc', label: 'Price: high to low' },
  { value: 'name_asc', label: 'A – Z' },
]

watch(drawerOpen, (open) => {
  if (import.meta.client) document.body.style.overflow = open ? 'hidden' : ''
})

onBeforeUnmount(() => {
  if (import.meta.client) document.body.style.overflow = ''
})
</script>

<template>
  <div>
    <!-- Header -->
    <header class="container-page pt-10 md:pt-14">
      <nav aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5 text-xs text-ink-muted">
          <li><NuxtLink to="/" class="hover:text-ink">Home</NuxtLink></li>
          <li v-for="crumb in category.breadcrumbs" :key="crumb.url" class="flex items-center gap-1.5">
            <span aria-hidden="true">/</span>
            <NuxtLink :to="crumb.url" class="hover:text-ink">{{ crumb.label }}</NuxtLink>
          </li>
        </ol>
      </nav>

      <h1 class="mt-6 font-display text-4xl md:text-5xl">{{ category.name }}</h1>

      <p v-if="category.description" class="mt-4 max-w-xl text-sm text-ink-muted">
        {{ category.description }}
      </p>

      <!-- Child categories as refinement links -->
      <ul v-if="category.children?.length" class="mt-8 flex flex-wrap gap-2">
        <li v-for="child in category.children" :key="child.url">
          <NuxtLink
            :to="child.url"
            class="label-caps border border-line-strong px-4 py-2 transition-colors hover:border-ink"
          >
            {{ child.label }}
          </NuxtLink>
        </li>
      </ul>
    </header>

    <!-- Toolbar -->
    <div class="container-page mt-10 flex items-center justify-between gap-4 border-b border-line pb-4">
      <p class="text-xs text-ink-muted">
        {{ meta?.total ?? 0 }} {{ (meta?.total ?? 0) === 1 ? 'piece' : 'pieces' }}
      </p>

      <div class="flex items-center gap-4">
        <button
          type="button"
          class="label-caps lg:hidden"
          @click="drawerOpen = true"
        >
          Filter
        </button>

        <label class="flex items-center gap-2">
          <span class="sr-only">Sort by</span>
          <select
            class="label-caps cursor-pointer border-0 bg-transparent pr-6 outline-none"
            :value="filters.sort.value"
            @change="filters.setSort(($event.target as HTMLSelectElement).value)"
          >
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </label>
      </div>
    </div>

    <!-- Active filter chips -->
    <div v-if="filters.activeChips.value.length" class="container-page mt-5 flex flex-wrap items-center gap-2">
      <button
        v-for="chip in filters.activeChips.value"
        :key="`${chip.key}:${chip.value}`"
        type="button"
        class="flex items-center gap-2 border border-line-strong px-3 py-1.5 text-xs transition-colors hover:border-ink"
        @click="filters.removeChip(chip.key, chip.value)"
      >
        {{ chip.label }}
        <svg width="9" height="9" viewBox="0 0 9 9" fill="none" stroke="currentColor" stroke-width="1.3">
          <path d="M1 1l7 7M8 1L1 8" />
        </svg>
      </button>

      <button type="button" class="text-xs underline underline-offset-4" @click="filters.clearAll()">
        Clear all
      </button>
    </div>

    <!-- Grid + sidebar -->
    <div class="container-page mt-8 grid gap-10 pb-section lg:grid-cols-[220px_1fr] lg:gap-12">
      <aside class="hidden lg:block">
        <CatalogFilterSidebar :facets="meta?.facets" />
      </aside>

      <div>
        <div v-if="products.length" class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 md:gap-x-6 xl:grid-cols-4">
          <ProductTile
            v-for="(product, index) in products"
            :key="product.id"
            :product="product"
            :priority="index < 4"
          />
        </div>

        <div v-else-if="status !== 'pending'" class="py-24 text-center">
          <p class="font-display text-2xl">Nothing matches those filters</p>
          <p class="mt-3 text-sm text-ink-muted">Try removing one to widen the search.</p>
          <button type="button" class="btn btn-outline mt-8" @click="filters.clearAll()">
            Clear filters
          </button>
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

    <!-- Mobile filter drawer -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-200"
        leave-to-class="opacity-0"
      >
        <div v-if="drawerOpen" class="fixed inset-0 z-[60] bg-ink/40 lg:hidden" @click="drawerOpen = false" />
      </Transition>

      <Transition
        enter-active-class="transition-transform duration-300 ease-editorial"
        enter-from-class="translate-y-full"
        leave-active-class="transition-transform duration-200 ease-in"
        leave-to-class="translate-y-full"
      >
        <div
          v-if="drawerOpen"
          class="fixed inset-x-0 bottom-0 z-[70] max-h-[85vh] overflow-y-auto bg-paper lg:hidden"
        >
          <div class="sticky top-0 flex items-center justify-between border-b border-line bg-paper px-5 py-4">
            <span class="label-caps">Filter</span>
            <button type="button" class="p-1" aria-label="Close filters" @click="drawerOpen = false">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M2 2l12 12M14 2L2 14" />
              </svg>
            </button>
          </div>

          <div class="px-5 pb-8">
            <CatalogFilterSidebar :facets="meta?.facets" compact />
          </div>

          <div class="sticky bottom-0 border-t border-line bg-paper p-5">
            <button type="button" class="btn btn-primary w-full" @click="drawerOpen = false">
              Show {{ meta?.total ?? 0 }} results
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
