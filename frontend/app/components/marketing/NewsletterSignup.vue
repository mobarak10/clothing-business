<script setup lang="ts">
const api = useApi()

const email = ref('')
const state = ref<'idle' | 'sending' | 'done' | 'error'>('idle')
const message = ref('')

async function submit() {
  if (state.value === 'sending') return

  state.value = 'sending'
  message.value = ''

  try {
    await useCsrfCookie()

    const response = await api<{ message: string }>('/api/v1/newsletter', {
      method: 'POST',
      body: { email: email.value, source: 'footer' },
    })

    state.value = 'done'
    message.value = response.message
    email.value = ''
  }
  catch (error: any) {
    state.value = 'error'
    // Laravel returns 422 with field errors; show the first one.
    message.value = error?.data?.errors?.email?.[0]
      ?? error?.data?.message
      ?? 'Something went wrong. Please try again.'
  }
}
</script>

<template>
  <section class="container-page py-16 md:py-20">
    <div class="mx-auto max-w-xl text-center">
      <h2 class="font-display text-3xl md:text-4xl">Stay in touch</h2>
      <p class="mt-4 text-sm text-ink-muted">
        New collections, private previews and appointment openings. No more than twice a month.
      </p>

      <form class="mt-8" @submit.prevent="submit">
        <div class="flex flex-col gap-3 sm:flex-row">
          <label for="newsletter-email" class="sr-only">Email address</label>
          <input
            id="newsletter-email"
            v-model="email"
            type="email"
            required
            autocomplete="email"
            placeholder="Email address"
            class="w-full border border-line-strong bg-surface px-4 py-3.5 text-sm outline-none transition-colors placeholder:text-ink-muted focus:border-ink"
          >
          <button
            type="submit"
            class="btn btn-primary shrink-0"
            :disabled="state === 'sending'"
          >
            {{ state === 'sending' ? 'Signing up…' : 'Sign up' }}
          </button>
        </div>

        <p
          v-if="message"
          class="mt-4 text-sm"
          :class="state === 'error' ? 'text-sale' : 'text-ink-soft'"
          role="status"
        >
          {{ message }}
        </p>
      </form>
    </div>
  </section>
</template>
