import type { NavigationNode, ShopSettings } from '~/types/catalog'

/**
 * App-wide data that every page needs: the mega-menu tree and the shop's
 * brand/currency settings.
 *
 * Both are fetched once per request (or once per client session) and shared
 * through useAsyncData's cache, so a five-page click-through does not refetch
 * the navigation five times.
 */

export function useNavigation() {
  const api = useApi()

  return useAsyncData(
    'navigation',
    () => api<{ data: NavigationNode[] }>('/api/v1/navigation').then(r => r.data),
    { default: () => [] as NavigationNode[] },
  )
}

export function useShopSettings() {
  const api = useApi()

  return useAsyncData(
    'settings',
    () => api<ShopSettings>('/api/v1/settings'),
    {
      default: (): ShopSettings => ({
        brand: { name: 'Atelier', tagline: '' },
        currency: { code: 'BDT', symbol: '৳', divisor: 100 },
        payment_methods: [],
      }),
    },
  )
}
