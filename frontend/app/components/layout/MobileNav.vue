<script setup lang="ts">
import type { NavigationNode } from '~/types/catalog'

defineProps<{
  navigation: NavigationNode[]
  open: boolean
}>()

defineEmits<{ close: [] }>()

/**
 * Accordion state. Only one branch is expanded at a time -- on a phone,
 * several open branches turn the drawer into an unnavigable wall.
 */
const expanded = ref<number | null>(null)

function toggle(id: number) {
  expanded.value = expanded.value === id ? null : id
}
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0"
    leave-active-class="transition duration-200 ease-in"
    leave-to-class="opacity-0"
  >
    <div
      v-if="open"
      class="fixed inset-x-0 top-16 bottom-0 z-40 overflow-y-auto border-t border-line bg-paper text-ink lg:hidden"
    >
      <nav class="container-page py-6" aria-label="Mobile">
        <ul class="divide-y divide-line">
          <li v-for="node in navigation" :key="node.id" class="py-1">
            <div class="flex items-center justify-between">
              <NuxtLink :to="node.url" class="py-3 font-display text-xl">
                {{ node.name }}
              </NuxtLink>

              <button
                v-if="node.children?.length"
                type="button"
                class="p-3"
                :aria-expanded="expanded === node.id"
                :aria-label="`Expand ${node.name}`"
                @click="toggle(node.id)"
              >
                <svg
                  width="14" height="14" viewBox="0 0 14 14"
                  fill="none" stroke="currentColor" stroke-width="1.2"
                  class="transition-transform duration-300"
                  :class="expanded === node.id ? 'rotate-45' : ''"
                >
                  <path d="M7 1v12M1 7h12" />
                </svg>
              </button>
            </div>

            <Transition
              enter-active-class="transition-all duration-300 ease-out overflow-hidden"
              enter-from-class="max-h-0 opacity-0"
              enter-to-class="max-h-[80vh] opacity-100"
              leave-active-class="transition-all duration-200 ease-in overflow-hidden"
              leave-from-class="max-h-[80vh] opacity-100"
              leave-to-class="max-h-0 opacity-0"
            >
              <ul v-if="expanded === node.id" class="space-y-1 pb-4 pl-1">
                <li v-for="child in node.children" :key="child.id">
                  <NuxtLink :to="child.url" class="block py-2 text-sm text-ink-soft">
                    {{ child.name }}
                  </NuxtLink>
                </li>
              </ul>
            </Transition>
          </li>
        </ul>

        <div class="mt-8 space-y-3 border-t border-line pt-8">
          <NuxtLink to="/account" class="label-caps block py-1">Account</NuxtLink>
          <NuxtLink to="/account/wishlist" class="label-caps block py-1">Wishlist</NuxtLink>
          <NuxtLink to="/store-finder" class="label-caps block py-1">Find a Boutique</NuxtLink>
        </div>
      </nav>
    </div>
  </Transition>
</template>
