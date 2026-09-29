import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  modules: ['@pinia/nuxt', '@nuxt/image', '@vueuse/nuxt'],

  css: ['~/assets/css/main.css'],

  // Tailwind v4 ships as a Vite plugin. @nuxtjs/tailwindcss is still v3-oriented,
  // so wiring the plugin directly is the supported path.
  vite: {
    plugins: [tailwindcss()],
  },

  runtimeConfig: {
    public: {
      // Browser-side calls. Overridable with NUXT_PUBLIC_API_BASE.
      apiBase: 'http://localhost:8000',
    },
    // Server-side (SSR) calls can take a different host in production --
    // e.g. an internal address that skips the public load balancer.
    apiBaseServer: '',
  },

  app: {
    head: {
      htmlAttrs: { lang: 'en' },
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'format-detection', content: 'telephone=no' },
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Inter:wght@300;400;500;600&display=swap',
        },
      ],
    },
  },

  image: {
    quality: 82,
    format: ['webp'],
    screens: { xs: 380, sm: 640, md: 768, lg: 1024, xl: 1280, xxl: 1536, '2xl': 1536 },
  },

  future: { compatibilityVersion: 4 },
})
