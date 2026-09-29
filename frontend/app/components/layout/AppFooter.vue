<script setup lang="ts">
const { data: settings } = await useShopSettings()

const year = new Date().getFullYear()

const columns = [
  {
    heading: 'The House',
    links: [
      { label: 'About', to: '/about' },
      { label: 'Made to Measure', to: '/made-to-measure' },
      { label: 'Garment Care', to: '/care' },
      { label: 'Find a Boutique', to: '/store-finder' },
    ],
  },
  {
    heading: 'Collection',
    links: [
      { label: 'New Arrivals', to: '/new-arrivals' },
      { label: 'Clothing', to: '/clothing' },
      { label: 'Shoes', to: '/shoes' },
      { label: 'Accessories', to: '/accessories' },
    ],
  },
  {
    heading: 'Client Care',
    links: [
      { label: 'Shipping & Returns', to: '/shipping-and-returns' },
      { label: 'My Account', to: '/account' },
      { label: 'Order History', to: '/account/orders' },
      { label: 'Book an Appointment', to: '/store-finder' },
    ],
  },
]

const legal = [
  { label: 'Terms', to: '/terms' },
  { label: 'Privacy', to: '/privacy' },
]
</script>

<template>
  <footer class="mt-section border-t border-line bg-paper-sunk">
    <MarketingNewsletterSignup />

    <div class="container-page grid grid-cols-2 gap-x-8 gap-y-12 border-t border-line py-16 md:grid-cols-4 md:py-20">
      <div class="col-span-2 md:col-span-1">
        <p class="font-display text-2xl tracking-[0.24em] uppercase">
          {{ settings.brand.name }}
        </p>
        <p class="mt-4 max-w-xs text-sm text-ink-muted">
          {{ settings.brand.tagline }}
        </p>
      </div>

      <div v-for="column in columns" :key="column.heading">
        <h2 class="label-caps text-ink-muted">{{ column.heading }}</h2>
        <ul class="mt-5 space-y-3">
          <li v-for="link in column.links" :key="link.to">
            <NuxtLink :to="link.to" class="link-underline text-sm text-ink-soft">
              {{ link.label }}
            </NuxtLink>
          </li>
        </ul>
      </div>
    </div>

    <div class="container-page flex flex-col gap-4 border-t border-line py-8 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-xs text-ink-muted">
        &copy; {{ year }} {{ settings.brand.name }}. All rights reserved.
      </p>

      <ul class="flex gap-6">
        <li v-for="link in legal" :key="link.to">
          <NuxtLink :to="link.to" class="text-xs text-ink-muted transition-colors hover:text-ink">
            {{ link.label }}
          </NuxtLink>
        </li>
      </ul>
    </div>
  </footer>
</template>
