/**
 * A single configured $fetch instance for every call to the Laravel API.
 *
 * The tricky part is SSR. During server rendering the request originates from
 * Node, not the browser, so nothing is attached automatically: we forward the
 * visitor's cookies by hand, and set an Origin that matches
 * SANCTUM_STATEFUL_DOMAINS so Sanctum still treats the call as a first-party
 * session request rather than falling back to token auth.
 */
export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()

  const baseURL = import.meta.server
    ? config.apiBaseServer || config.public.apiBase
    : config.public.apiBase

  const api = $fetch.create({
    baseURL,
    credentials: 'include',
    retry: false,

    onRequest({ options }) {
      const headers = new Headers(options.headers)
      headers.set('Accept', 'application/json')

      if (import.meta.server) {
        const cookie = useRequestHeaders(['cookie']).cookie
        if (cookie) headers.set('cookie', cookie)

        // Without this Sanctum sees no Origin and skips the stateful guard.
        headers.set('origin', useRequestURL().origin)
      } else {
        // Laravel expects the CSRF token it set as a cookie echoed back
        // as a header on every mutating request.
        const xsrf = useCookie('XSRF-TOKEN').value
        if (xsrf) headers.set('X-XSRF-TOKEN', xsrf)
      }

      options.headers = headers
    },
  })

  return {
    provide: { api },
  }
})
