<script setup lang="ts">
import type { NavigationNode } from '~/types/catalog'

const props = defineProps<{
  navigation: NavigationNode[]
  openId: number | null
}>()

defineEmits<{ close: [] }>()

const active = computed(() =>
  props.navigation.find(node => node.id === props.openId && node.children?.length),
)

/**
 * Second-level categories are dealt into columns of at most six, so a long
 * list like Clothing wraps rather than running off the bottom of the panel.
 */
const columns = computed(() => {
  const children = active.value?.children ?? []
  const perColumn = 6
  const count = Math.ceil(children.length / perColumn) || 1

  return Array.from({ length: count }, (_, i) =>
    children.slice(i * perColumn, (i + 1) * perColumn))
})

const featured = computed(() =>
  active.value?.children?.find(child => child.is_featured) ?? null,
)
</script>

<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-1"
    leave-active-class="transition duration-200 ease-in"
    leave-to-class="opacity-0"
  >
    <div
      v-if="active"
      class="absolute inset-x-0 top-full hidden border-t border-line bg-paper text-ink shadow-[0_20px_40px_-32px_rgba(0,0,0,0.4)] lg:block"
    >
      <div class="container-page grid grid-cols-12 gap-10 py-12">
        <div class="col-span-8 grid grid-cols-3 gap-10">
          <div v-for="(column, index) in columns" :key="index" class="space-y-5">
            <div v-for="child in column" :key="child.id">
              <NuxtLink
                :to="child.url"
                class="link-underline text-sm text-ink"
              >
                {{ child.name }}
              </NuxtLink>

              <!-- Third level: refinements shown small beneath their parent. -->
              <ul v-if="child.children?.length" class="mt-2 space-y-1.5">
                <li v-for="grandchild in child.children" :key="grandchild.id">
                  <NuxtLink
                    :to="grandchild.url"
                    class="text-xs text-ink-muted transition-colors hover:text-ink"
                  >
                    {{ grandchild.name }}
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-span-4">
          <NuxtLink
            v-if="featured"
            :to="featured.url"
            class="group block"
          >
            <div class="aspect-[4/5] overflow-hidden bg-paper-sunk">
              <img
                v-if="featured.image"
                :src="featured.image"
                :alt="featured.name"
                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                loading="lazy"
              >
            </div>
            <p class="label-caps mt-4 text-ink-muted">Featured</p>
            <p class="mt-1 font-display text-xl">{{ featured.name }}</p>
          </NuxtLink>

          <NuxtLink
            v-else
            :to="active.url"
            class="group block"
          >
            <div class="aspect-[4/5] overflow-hidden bg-paper-sunk" />
            <p class="label-caps mt-4 text-ink-muted">Explore</p>
            <p class="mt-1 font-display text-xl">All {{ active.name }}</p>
          </NuxtLink>
        </div>
      </div>
    </div>
  </Transition>
</template>
