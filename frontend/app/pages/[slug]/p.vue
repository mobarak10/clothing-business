<script setup lang="ts">
import type { ProductCard, ProductDetail } from '~/types/catalog'

/**
 * Product detail, at /{slug}/p
 *
 * The literal "p" segment is what separates a product URL from a category one.
 * Vue Router ranks a static segment above a dynamic one, so this route wins
 * over the [...path] catch-all without any extra configuration.
 */
const route = useRoute()
const api = useApi()

const slug = computed(() => String(route.params.slug))

const { data: response } = await useAsyncData(
  () => `product:${slug.value}`,
  () => api<{ data: ProductDetail, meta: { related: ProductCard[] } }>(
    `/api/v1/products/${slug.value}`,
  ),
  { watch: [slug] },
)

if (!response.value) {
  throw createError({ statusCode: 404, statusMessage: 'Product not found', fatal: true })
}

const product = computed(() => response.value!.data)
const related = computed(() => response.value!.meta.related ?? [])

const {
  select,
  isChosen,
  isAvailable,
  selectedVariant,
  missingAxes,
  canAddToBag,
  displayPrice,
  displayComparePrice,
  chosen,
} = useVariantSelection(product as Ref<ProductDetail>)

const openSection = ref<string | null>('details')

function toggleSection(key: string) {
  openSection.value = openSection.value === key ? null : key
}

// Cart lands in Phase 5; until then the button reports what it would do.
const adding = ref(false)
const notice = ref('')

async function addToBag() {
  if (!canAddToBag.value) {
    notice.value = `Please choose a ${missingAxes.value[0]?.name.toLowerCase() ?? 'size'}.`
    return
  }

  adding.value = true
  notice.value = `${product.value.name} (${selectedVariant.value!.sku}) is ready to add — the bag arrives in the next phase.`
  adding.value = false
}

useSeoMeta({
  title: () => product.value.seo.title,
  description: () => product.value.seo.description ?? undefined,
  ogTitle: () => product.value.name,
  ogDescription: () => product.value.seo.description ?? undefined,
  ogType: 'website',
  ogImage: () => product.value.images?.[0]?.card,
})

// Product structured data, so search engines can show price and availability.
useHead(() => ({
  script: [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'Product',
      'name': product.value.name,
      'description': product.value.description,
      'sku': product.value.sku_root,
      'image': product.value.images?.map(image => image.zoom ?? image.card) ?? [],
      'offers': {
        '@type': 'Offer',
        'price': (product.value.price.amount / 100).toFixed(2),
        'priceCurrency': product.value.price.currency,
        'availability': product.value.in_stock
          ? 'https://schema.org/InStock'
          : 'https://schema.org/OutOfStock',
      },
    }),
  }],
}))
</script>

<template>
  <div v-if="product">
    <div class="container-page grid gap-10 py-10 md:py-14 lg:grid-cols-2 lg:gap-16">
      <!-- Gallery -->
      <div>
        <ProductGallery
          :images="product.images"
          :name="product.name"
          :color-filter="chosen.color ?? null"
        />
      </div>

      <!-- Buy column -->
      <div class="lg:sticky lg:top-28 lg:self-start">
        <nav aria-label="Breadcrumb">
          <ol class="flex flex-wrap items-center gap-1.5 text-xs text-ink-muted">
            <li><NuxtLink to="/" class="hover:text-ink">Home</NuxtLink></li>
            <li v-for="crumb in product.breadcrumbs" :key="crumb.url" class="flex items-center gap-1.5">
              <span aria-hidden="true">/</span>
              <NuxtLink :to="crumb.url" class="hover:text-ink">{{ crumb.label }}</NuxtLink>
            </li>
          </ol>
        </nav>

        <h1 class="mt-5 font-display text-3xl md:text-4xl">{{ product.name }}</h1>

        <p v-if="product.subtitle" class="mt-2 text-sm text-ink-muted">
          {{ product.subtitle }}
        </p>

        <p class="mt-5 flex items-baseline gap-3">
          <span class="text-lg" :class="product.on_sale ? 'text-sale' : ''">
            {{ displayPrice.formatted }}
          </span>
          <span v-if="displayComparePrice" class="text-sm text-ink-muted line-through">
            {{ displayComparePrice.formatted }}
          </span>
        </p>

        <!-- Option pickers -->
        <div v-for="axis in product.options" :key="axis.slug" class="mt-8">
          <div class="flex items-baseline justify-between">
            <span class="label-caps">{{ axis.name }}</span>
            <span v-if="chosen[axis.slug]" class="text-xs text-ink-muted">
              {{ axis.values.find(v => v.slug === chosen[axis.slug])?.name }}
            </span>
          </div>

          <ul v-if="axis.presentation === 'swatch'" class="mt-3 flex flex-wrap gap-3">
            <li v-for="value in axis.values" :key="value.slug">
              <button
                type="button"
                class="h-8 w-8 rounded-full border transition-all"
                :class="[
                  isChosen(axis.slug, value.slug)
                    ? 'border-ink ring-1 ring-ink ring-offset-2 ring-offset-paper'
                    : 'border-line-strong hover:border-ink',
                  !isAvailable(axis.slug, value.slug) ? 'opacity-30' : '',
                ]"
                :style="{ backgroundColor: value.hex ?? 'transparent' }"
                :title="value.name"
                :aria-label="value.name"
                :aria-pressed="isChosen(axis.slug, value.slug)"
                @click="select(axis.slug, value.slug)"
              />
            </li>
          </ul>

          <ul v-else class="mt-3 flex flex-wrap gap-2">
            <li v-for="value in axis.values" :key="value.slug">
              <button
                type="button"
                class="min-w-12 border px-3.5 py-2.5 text-xs transition-colors"
                :class="[
                  isChosen(axis.slug, value.slug)
                    ? 'border-ink bg-ink text-paper'
                    : 'border-line-strong hover:border-ink',
                  // Unavailable combinations stay clickable-looking but struck
                  // through, so the shopper learns what exists rather than
                  // wondering why an option vanished.
                  !isAvailable(axis.slug, value.slug)
                    ? 'text-ink-muted line-through opacity-50'
                    : '',
                ]"
                :aria-pressed="isChosen(axis.slug, value.slug)"
                @click="select(axis.slug, value.slug)"
              >
                {{ value.name }}
              </button>
            </li>
          </ul>
        </div>

        <p
          v-if="selectedVariant?.low_stock"
          class="mt-5 text-xs text-sale"
        >
          Only {{ selectedVariant.stock_quantity }} left in this size.
        </p>

        <div class="mt-8 flex gap-3">
          <button
            type="button"
            class="btn btn-primary flex-1"
            :disabled="adding || !product.in_stock"
            @click="addToBag"
          >
            {{ product.in_stock ? 'Add to bag' : 'Sold out' }}
          </button>

          <button
            type="button"
            class="btn btn-outline px-4"
            aria-label="Add to wishlist"
          >
            <svg width="16" height="16" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.2">
              <path d="M9 15.5S1.5 11 1.5 6.2A3.7 3.7 0 019 4.3a3.7 3.7 0 017.5 1.9C16.5 11 9 15.5 9 15.5z" />
            </svg>
          </button>
        </div>

        <p v-if="notice" class="mt-4 text-xs text-ink-soft" role="status">
          {{ notice }}
        </p>

        <!-- Detail accordions -->
        <div class="mt-12 border-t border-line">
          <div
            v-for="section in [
              { key: 'details', label: 'Details', body: product.description },
              { key: 'composition', label: 'Composition & Care', body: [product.composition, product.care_instructions].filter(Boolean).join('\n\n') },
              { key: 'shipping', label: 'Shipping & Returns', body: 'Complimentary standard delivery above ৳5,000. Returns accepted within 14 days, unworn and with tags attached.' },
            ]"
            :key="section.key"
            class="border-b border-line"
          >
            <button
              type="button"
              class="flex w-full items-center justify-between py-5"
              :aria-expanded="openSection === section.key"
              @click="toggleSection(section.key)"
            >
              <span class="label-caps">{{ section.label }}</span>
              <svg
                width="12" height="12" viewBox="0 0 12 12"
                fill="none" stroke="currentColor" stroke-width="1.2"
                class="transition-transform duration-300"
                :class="openSection === section.key ? 'rotate-45' : ''"
              >
                <path d="M6 1v10M1 6h10" />
              </svg>
            </button>

            <p
              v-if="openSection === section.key && section.body"
              class="whitespace-pre-line pb-6 text-sm leading-relaxed text-ink-soft"
            >
              {{ section.body }}
            </p>
          </div>
        </div>

        <p v-if="product.fit" class="mt-6 text-xs text-ink-muted">
          {{ product.fit }} · Style {{ product.sku_root }}
        </p>
      </div>
    </div>

    <!-- Related -->
    <section v-if="related.length" class="container-page pb-section pt-8">
      <h2 class="font-display text-2xl md:text-3xl">You may also like</h2>

      <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4 md:gap-x-6">
        <ProductTile
          v-for="item in related.slice(0, 4)"
          :key="item.id"
          :product="item"
        />
      </div>
    </section>
  </div>
</template>
