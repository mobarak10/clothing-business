<script setup lang="ts">
import type { ProductCard } from '~/types/catalog'

const props = defineProps<{
  product: ProductCard
  /** Above-the-fold cards should not lazy-load; it delays LCP. */
  priority?: boolean
}>()

const hovering = ref(false)

const primary = computed(() => props.product.images?.[0] ?? null)
const secondary = computed(() => props.product.images?.[1] ?? null)

// Only swap on hover when there is genuinely a second shot.
const showSecondary = computed(() => hovering.value && !!secondary.value)
</script>

<template>
  <article
    class="group"
    @mouseenter="hovering = true"
    @mouseleave="hovering = false"
  >
    <NuxtLink :to="product.url" class="block">
      <div class="relative aspect-[3/4] overflow-hidden bg-paper-sunk">
        <img
          v-if="primary"
          :src="primary.card"
          :alt="primary.alt"
          class="absolute inset-0 h-full w-full object-cover transition-opacity duration-500"
          :class="showSecondary ? 'opacity-0' : 'opacity-100'"
          :loading="priority ? 'eager' : 'lazy'"
          :fetchpriority="priority ? 'high' : 'auto'"
        >

        <img
          v-if="secondary"
          :src="secondary.card"
          :alt="secondary.alt"
          class="absolute inset-0 h-full w-full object-cover transition-opacity duration-500"
          :class="showSecondary ? 'opacity-100' : 'opacity-0'"
          loading="lazy"
        >

        <!-- No photography yet: a plain field is better than a broken icon. -->
        <div
          v-if="!primary"
          class="absolute inset-0 flex items-center justify-center"
        >
          <span class="label-caps text-ink-muted">{{ product.name }}</span>
        </div>

        <div class="absolute left-0 top-0 flex flex-col items-start gap-1 p-3">
          <span v-if="product.on_sale" class="label-caps bg-sale px-2 py-1 text-white">
            Sale
          </span>
          <span v-else-if="product.is_new_arrival" class="label-caps bg-ink px-2 py-1 text-paper">
            New
          </span>
        </div>

        <div
          v-if="!product.in_stock"
          class="absolute inset-x-0 bottom-0 bg-paper/90 py-2 text-center"
        >
          <span class="label-caps text-ink-muted">Sold out</span>
        </div>
      </div>
    </NuxtLink>

    <div class="mt-4 space-y-1.5">
      <NuxtLink :to="product.url" class="link-underline text-sm">
        {{ product.name }}
      </NuxtLink>

      <p v-if="product.subtitle" class="text-xs text-ink-muted">
        {{ product.subtitle }}
      </p>

      <p class="flex items-baseline gap-2 text-sm">
        <span :class="product.on_sale ? 'text-sale' : ''">
          {{ product.price.formatted }}
        </span>
        <span v-if="product.compare_at_price" class="text-xs text-ink-muted line-through">
          {{ product.compare_at_price.formatted }}
        </span>
      </p>

      <ul v-if="product.colors?.length" class="flex items-center gap-1.5 pt-1">
        <li
          v-for="color in product.colors.slice(0, 5)"
          :key="color.slug"
          class="h-3 w-3 rounded-full border border-line-strong"
          :style="{ backgroundColor: color.hex ?? 'transparent' }"
          :title="color.name"
        />
        <li v-if="product.colors.length > 5" class="text-xs text-ink-muted">
          +{{ product.colors.length - 5 }}
        </li>
      </ul>
    </div>
  </article>
</template>
