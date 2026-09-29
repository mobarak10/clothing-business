<script setup lang="ts">
import type { PagePayload } from '~/types/catalog'

defineProps<{ page: PagePayload }>()
</script>

<template>
  <article class="container-page py-16 md:py-24">
    <header class="mx-auto max-w-2xl text-center">
      <h1 class="font-display text-4xl md:text-5xl">{{ page.title }}</h1>
      <p v-if="page.excerpt" class="mt-5 text-sm text-ink-muted md:text-base">
        {{ page.excerpt }}
      </p>
    </header>

    <!--
      Body is authored HTML from the admin, entered by staff -- not visitor
      input. Sanitisation belongs on the way in (the editor), not here.
    -->
    <div
      v-if="page.body"
      class="prose-editorial mx-auto mt-12 max-w-2xl"
      v-html="page.body"
    />
  </article>
</template>

<style scoped>
.prose-editorial :deep(p) {
  margin-bottom: 1.25rem;
  color: var(--color-ink-soft);
  line-height: 1.8;
}

.prose-editorial :deep(h2) {
  margin-top: 2.5rem;
  margin-bottom: 1rem;
  font-family: var(--font-display);
  font-size: 1.75rem;
}

.prose-editorial :deep(a) {
  text-decoration: underline;
  text-underline-offset: 3px;
}

.prose-editorial :deep(ul) {
  margin-bottom: 1.25rem;
  padding-left: 1.25rem;
  list-style: disc;
  color: var(--color-ink-soft);
}
</style>
