<script setup lang="ts">
import type { ContentBlock } from '~/types/catalog'

import BlockCategoryTiles from './BlockCategoryTiles.vue'
import BlockEditorialSplit from './BlockEditorialSplit.vue'
import BlockHero from './BlockHero.vue'
import BlockLookGrid from './BlockLookGrid.vue'
import BlockProductCarousel from './BlockProductCarousel.vue'
import BlockStoreFinder from './BlockStoreFinder.vue'

defineProps<{
  blocks: ContentBlock[]
}>()

/**
 * Maps a block's `type` (the ContentBlockType enum on the backend) to the
 * component that renders it. An unknown type renders nothing rather than
 * throwing -- a block added in the admin before its component ships should
 * not take the homepage down.
 */
const registry: Record<string, Component> = {
  hero: BlockHero,
  category_tiles: BlockCategoryTiles,
  product_carousel: BlockProductCarousel,
  look_grid: BlockLookGrid,
  editorial_split: BlockEditorialSplit,
  store_finder: BlockStoreFinder,
}
</script>

<template>
  <component
    :is="registry[block.type]"
    v-for="(block, index) in blocks.filter(b => registry[b.type])"
    :key="block.id"
    :payload="block.payload"
    :products="block.products"
    :first="index === 0"
  />
</template>
