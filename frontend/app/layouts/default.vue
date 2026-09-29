<script setup lang="ts">
const route = useRoute()

/**
 * The homepage's hero runs under a transparent header, so it must start at
 * y=0. Every other page needs top padding to clear the fixed header.
 */
const isHome = computed(() => route.path === '/')
</script>

<template>
  <div class="flex min-h-screen flex-col">
    <LayoutAppHeader />

    <main :class="isHome ? '' : 'pt-16 md:pt-20'" class="flex-1">
      <slot />
    </main>

    <LayoutAppFooter />
  </div>
</template>
