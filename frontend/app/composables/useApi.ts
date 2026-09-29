import type { $Fetch } from 'nitropack'

/**
 * Shorthand for the configured API client from plugins/api.ts.
 *
 *   const api = useApi()
 *   const product = await api('/api/v1/products/navy-wool-suit')
 *
 * For data that should be fetched during SSR and hydrated, pair it with
 * useAsyncData rather than calling this directly in setup.
 */
export function useApi(): $Fetch {
  return useNuxtApp().$api as $Fetch
}

/**
 * Ensures Laravel has issued an XSRF-TOKEN cookie before a mutating request.
 * Safe to call repeatedly -- it no-ops once the cookie exists.
 */
export async function useCsrfCookie(): Promise<void> {
  if (import.meta.server) return
  if (useCookie('XSRF-TOKEN').value) return

  const config = useRuntimeConfig()
  await $fetch('/sanctum/csrf-cookie', {
    baseURL: config.public.apiBase,
    credentials: 'include',
  })
}
