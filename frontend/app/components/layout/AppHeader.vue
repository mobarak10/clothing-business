<script setup lang="ts">
import type { NavigationNode } from '~/types/catalog'

const { data: navigation } = await useNavigation()
const { data: settings } = await useShopSettings()

const route = useRoute()

/**
 * The header sits transparently over a full-bleed hero on the homepage and
 * turns solid once the hero has scrolled past. Everywhere else it is solid
 * from the start, because there is no hero to sit over.
 */
const isHome = computed(() => route.path === '/')
const scrolled = ref(false)
const openMenu = ref<number | null>(null)
const mobileOpen = ref(false)

const isSolid = computed(() => !isHome.value || scrolled.value || openMenu.value !== null)

function onScroll() {
  scrolled.value = window.scrollY > 40
}

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll))

// A route change should never leave a menu hanging open.
watch(() => route.fullPath, () => {
  openMenu.value = null
  mobileOpen.value = false
})

// Body scroll lock while the mobile drawer is open.
watch(mobileOpen, (open) => {
  if (import.meta.client) {
    document.body.style.overflow = open ? 'hidden' : ''
  }
})

onBeforeUnmount(() => {
  if (import.meta.client) document.body.style.overflow = ''
})

function hasChildren(node: NavigationNode) {
  return node.children && node.children.length > 0
}
</script>

<template>
  <header
    class="fixed inset-x-0 top-0 z-50 transition-colors duration-500"
    :class="isSolid ? 'bg-paper text-ink' : 'bg-transparent text-white'"
    @mouseleave="openMenu = null"
  >
    <!-- Hairline only once solid; over a hero it would cut the image. -->
    <div
      class="absolute inset-x-0 bottom-0 h-px transition-opacity duration-500"
      :class="isSolid ? 'bg-line opacity-100' : 'opacity-0'"
    />

    <div class="container-page relative flex h-16 items-center justify-between gap-4 md:h-20">
      <!-- Mobile: menu toggle -->
      <button
        type="button"
        class="-ml-2 p-2 lg:hidden"
        :aria-expanded="mobileOpen"
        aria-label="Open menu"
        @click="mobileOpen = !mobileOpen"
      >
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.2">
          <path v-if="!mobileOpen" d="M2 5h16M2 10h16M2 15h16" />
          <path v-else d="M4 4l12 12M16 4L4 16" />
        </svg>
      </button>

      <!-- Desktop nav -->
      <nav class="hidden lg:flex lg:items-center lg:gap-8" aria-label="Main">
        <div
          v-for="node in navigation"
          :key="node.id"
          @mouseenter="openMenu = hasChildren(node) ? node.id : null"
        >
          <NuxtLink
            :to="node.url"
            class="label-caps link-underline py-2"
            :class="openMenu === node.id ? 'opacity-100' : 'opacity-90 hover:opacity-100'"
          >
            {{ node.name }}
          </NuxtLink>
        </div>
      </nav>

      <!-- Wordmark: centred on desktop, left on mobile -->
      <NuxtLink
        to="/"
        class="absolute left-1/2 -translate-x-1/2 font-display text-xl tracking-[0.28em] uppercase md:text-2xl"
      >
        {{ settings.brand.name }}
      </NuxtLink>

      <div class="ml-auto flex items-center gap-1 md:gap-2">
        <NuxtLink to="/search" class="p-2" aria-label="Search">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.2">
            <circle cx="8" cy="8" r="6" />
            <path d="M12.5 12.5L17 17" />
          </svg>
        </NuxtLink>

        <NuxtLink to="/account/wishlist" class="hidden p-2 sm:block" aria-label="Wishlist">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.2">
            <path d="M9 15.5S1.5 11 1.5 6.2A3.7 3.7 0 019 4.3a3.7 3.7 0 017.5 1.9C16.5 11 9 15.5 9 15.5z" />
          </svg>
        </NuxtLink>

        <NuxtLink to="/account" class="hidden p-2 sm:block" aria-label="Account">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.2">
            <circle cx="9" cy="6" r="3.2" />
            <path d="M2.5 16c.6-3.3 3.3-5 6.5-5s5.9 1.7 6.5 5" />
          </svg>
        </NuxtLink>

        <NuxtLink to="/cart" class="p-2" aria-label="Shopping bag">
          <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.2">
            <path d="M3.5 5.5h11l-.9 10.2a1 1 0 01-1 .8H5.4a1 1 0 01-1-.8L3.5 5.5z" />
            <path d="M6.4 5.5V4a2.6 2.6 0 015.2 0v1.5" />
          </svg>
        </NuxtLink>
      </div>
    </div>

    <LayoutMegaMenu
      :navigation="navigation"
      :open-id="openMenu"
      @close="openMenu = null"
    />

    <LayoutMobileNav
      :navigation="navigation"
      :open="mobileOpen"
      @close="mobileOpen = false"
    />
  </header>
</template>
