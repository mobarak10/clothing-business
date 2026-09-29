import type { LocationQueryRaw } from 'vue-router'

/**
 * Keeps listing filters in the URL rather than in component state.
 *
 * That makes a filtered view shareable, bookmarkable, and correct on a
 * server-rendered first paint -- none of which is true if the selection lives
 * only in a ref.
 */
export function useCatalogFilters() {
  const route = useRoute()
  const router = useRouter()

  /** Query keys that are not filters, so "clear all" leaves them alone. */
  const reserved = new Set(['page', 'q'])

  /** Reads a repeatable param (?color=navy&color=black) as an array. */
  function selected(key: string): string[] {
    const value = route.query[key]

    if (value === undefined || value === null) return []

    return (Array.isArray(value) ? value : [value]).filter(Boolean) as string[]
  }

  function isSelected(key: string, value: string): boolean {
    return selected(key).includes(value)
  }

  function apply(query: LocationQueryRaw) {
    router.push({
      query: {
        ...route.query,
        ...query,
        // Any filter change invalidates the current page number.
        page: undefined,
      },
    })
  }

  function toggle(key: string, value: string) {
    const current = selected(key)
    const next = current.includes(value)
      ? current.filter(item => item !== value)
      : [...current, value]

    apply({ [key]: next.length ? next : undefined })
  }

  function setSort(sort: string) {
    apply({ sort: sort === 'newest' ? undefined : sort })
  }

  function setPriceRange(min?: number, max?: number) {
    apply({
      price_min: min ?? undefined,
      price_max: max ?? undefined,
    })
  }

  function setPage(page: number) {
    router.push({
      query: { ...route.query, page: page > 1 ? page : undefined },
    })
  }

  function clearAll() {
    const kept: LocationQueryRaw = {}

    for (const [key, value] of Object.entries(route.query)) {
      if (reserved.has(key) && key !== 'page') kept[key] = value as string
    }

    router.push({ query: kept })
  }

  /** Everything currently narrowing the grid, for the "active filters" row. */
  const activeChips = computed(() => {
    const chips: { key: string, value: string, label: string }[] = []

    for (const [key, raw] of Object.entries(route.query)) {
      if (reserved.has(key) || key === 'sort') continue

      const values = Array.isArray(raw) ? raw : [raw]

      for (const value of values) {
        if (!value) continue

        chips.push({
          key,
          value: String(value),
          label: key === 'price_min'
            ? `From ${value}`
            : key === 'price_max'
              ? `To ${value}`
              : String(value),
        })
      }
    }

    return chips
  })

  function removeChip(key: string, value: string) {
    if (key === 'price_min' || key === 'price_max' || key === 'in_stock') {
      apply({ [key]: undefined })
      return
    }

    toggle(key, value)
  }

  const sort = computed(() => (route.query.sort as string) ?? 'newest')
  const page = computed(() => Number(route.query.page ?? 1))

  /** Passed straight to the API; it understands the same names. */
  const apiQuery = computed(() => ({ ...route.query }))

  return {
    apply,
    selected,
    isSelected,
    toggle,
    setSort,
    setPriceRange,
    setPage,
    clearAll,
    removeChip,
    activeChips,
    sort,
    page,
    apiQuery,
  }
}
