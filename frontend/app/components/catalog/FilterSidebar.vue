<script setup lang="ts">
import type { Facets } from '~/types/catalog'

const props = defineProps<{
  facets: Facets | undefined
  /** Rendered inside the mobile drawer rather than the desktop rail. */
  compact?: boolean
}>()

const filters = useCatalogFilters()
const route = useRoute()

/** Sections start open; a filter panel that hides its options helps nobody. */
const collapsed = ref<Record<string, boolean>>({})

function toggleSection(key: string) {
  collapsed.value[key] = !collapsed.value[key]
}

const divisor = 100

const priceMin = ref<string>((route.query.price_min as string) ?? '')
const priceMax = ref<string>((route.query.price_max as string) ?? '')

watch(() => route.query, (query) => {
  priceMin.value = (query.price_min as string) ?? ''
  priceMax.value = (query.price_max as string) ?? ''
})

const bounds = computed(() => {
  if (!props.facets?.price) return null

  return {
    min: Math.floor(props.facets.price.min / divisor),
    max: Math.ceil(props.facets.price.max / divisor),
  }
})

function applyPrice() {
  filters.setPriceRange(
    priceMin.value ? Number(priceMin.value) : undefined,
    priceMax.value ? Number(priceMax.value) : undefined,
  )
}
</script>

<template>
  <div :class="compact ? '' : 'sticky top-28'">
    <div v-for="axis in facets?.options" :key="axis.slug" class="border-b border-line py-5">
      <button
        type="button"
        class="flex w-full items-center justify-between"
        :aria-expanded="!collapsed[axis.slug]"
        @click="toggleSection(axis.slug)"
      >
        <span class="label-caps">{{ axis.name }}</span>
        <svg
          width="12" height="12" viewBox="0 0 12 12"
          fill="none" stroke="currentColor" stroke-width="1.2"
          class="transition-transform duration-300"
          :class="collapsed[axis.slug] ? '' : 'rotate-45'"
        >
          <path d="M6 1v10M1 6h10" />
        </svg>
      </button>

      <div v-if="!collapsed[axis.slug]" class="mt-4">
        <!-- Colours read as swatches; everything else as text pills. -->
        <ul v-if="axis.presentation === 'swatch'" class="flex flex-wrap gap-2.5">
          <li v-for="value in axis.values" :key="value.slug">
            <button
              type="button"
              class="relative h-7 w-7 rounded-full border transition-all"
              :class="filters.isSelected(axis.slug, value.slug)
                ? 'border-ink ring-1 ring-ink ring-offset-2 ring-offset-paper'
                : 'border-line-strong hover:border-ink'"
              :style="{ backgroundColor: value.hex ?? 'transparent' }"
              :title="value.name"
              :aria-label="value.name"
              :aria-pressed="filters.isSelected(axis.slug, value.slug)"
              @click="filters.toggle(axis.slug, value.slug)"
            />
          </li>
        </ul>

        <ul v-else class="flex flex-wrap gap-2">
          <li v-for="value in axis.values" :key="value.slug">
            <button
              type="button"
              class="min-w-11 border px-3 py-1.5 text-xs transition-colors"
              :class="filters.isSelected(axis.slug, value.slug)
                ? 'border-ink bg-ink text-paper'
                : 'border-line-strong hover:border-ink'"
              :aria-pressed="filters.isSelected(axis.slug, value.slug)"
              @click="filters.toggle(axis.slug, value.slug)"
            >
              {{ value.name }}
            </button>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="facets?.fits?.length" class="border-b border-line py-5">
      <span class="label-caps">Fit</span>
      <ul class="mt-4 space-y-2.5">
        <li v-for="fit in facets.fits" :key="fit">
          <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink-soft">
            <input
              type="checkbox"
              class="h-3.5 w-3.5 accent-[var(--color-ink)]"
              :checked="filters.isSelected('fit', fit)"
              @change="filters.toggle('fit', fit)"
            >
            {{ fit }}
          </label>
        </li>
      </ul>
    </div>

    <div v-if="bounds" class="border-b border-line py-5">
      <span class="label-caps">Price</span>
      <p class="mt-2 text-xs text-ink-muted">
        {{ bounds.min.toLocaleString() }} – {{ bounds.max.toLocaleString() }}
      </p>

      <div class="mt-4 flex items-center gap-2">
        <input
          v-model="priceMin"
          type="number"
          inputmode="numeric"
          :min="bounds.min"
          :max="bounds.max"
          :placeholder="String(bounds.min)"
          aria-label="Minimum price"
          class="w-full border border-line-strong bg-surface px-2.5 py-2 text-xs outline-none focus:border-ink"
        >
        <span class="text-ink-muted">–</span>
        <input
          v-model="priceMax"
          type="number"
          inputmode="numeric"
          :min="bounds.min"
          :max="bounds.max"
          :placeholder="String(bounds.max)"
          aria-label="Maximum price"
          class="w-full border border-line-strong bg-surface px-2.5 py-2 text-xs outline-none focus:border-ink"
        >
      </div>

      <button type="button" class="label-caps mt-3 underline underline-offset-4" @click="applyPrice">
        Apply
      </button>
    </div>

    <div class="py-5">
      <label class="flex cursor-pointer items-center gap-2.5 text-sm text-ink-soft">
        <input
          type="checkbox"
          class="h-3.5 w-3.5 accent-[var(--color-ink)]"
          :checked="route.query.in_stock === '1'"
          @change="filters.apply({ in_stock: route.query.in_stock === '1' ? undefined : '1' })"
        >
        In stock only
      </label>
    </div>
  </div>
</template>
