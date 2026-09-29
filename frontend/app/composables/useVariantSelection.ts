import type { ProductDetail, ProductVariant } from '~/types/catalog'

/**
 * Turns a product's flat variant list into a working option picker.
 *
 * The important behaviour is cross-option availability: once a shopper picks
 * Navy, the size row must show which sizes exist *in Navy* rather than which
 * sizes the product has in general. Getting that wrong lets people select a
 * combination that cannot be bought.
 */
export function useVariantSelection(product: Ref<ProductDetail>) {
  /** Current choice per option type: { color: 'navy', size: '50' } */
  const chosen = ref<Record<string, string>>({})

  /** Pre-select any axis that offers only one value; it is not a choice. */
  function seedSingleValueAxes() {
    const seed: Record<string, string> = {}

    for (const axis of product.value.options) {
      if (axis.values.length === 1) seed[axis.slug] = axis.values[0]!.slug
    }

    chosen.value = seed
  }

  watch(product, seedSingleValueAxes, { immediate: true })

  function select(axisSlug: string, valueSlug: string) {
    if (chosen.value[axisSlug] === valueSlug) {
      // Clicking the active value clears it, which widens the other axes again.
      const { [axisSlug]: _removed, ...rest } = chosen.value
      chosen.value = rest
      return
    }

    chosen.value = { ...chosen.value, [axisSlug]: valueSlug }
  }

  /** The variant matching every chosen value, if the choice is complete. */
  const selectedVariant = computed<ProductVariant | null>(() => {
    const axes = product.value.options.map(axis => axis.slug)

    if (axes.some(axis => !chosen.value[axis])) return null

    return product.value.variants.find(variant =>
      axes.every(axis => variant.options[axis] === chosen.value[axis]),
    ) ?? null
  })

  /**
   * Whether a value can still be reached, given the *other* axes' choices.
   * Its own axis is excluded so switching colour never disables every colour.
   */
  function isAvailable(axisSlug: string, valueSlug: string): boolean {
    const others = Object.entries(chosen.value).filter(([axis]) => axis !== axisSlug)

    return product.value.variants.some((variant) => {
      if (variant.options[axisSlug] !== valueSlug) return false
      if (!variant.in_stock) return false

      return others.every(([axis, value]) => variant.options[axis] === value)
    })
  }

  function isChosen(axisSlug: string, valueSlug: string): boolean {
    return chosen.value[axisSlug] === valueSlug
  }

  /** Axes still awaiting a choice, for the "Select a size" prompt. */
  const missingAxes = computed(() =>
    product.value.options.filter(axis => !chosen.value[axis.slug]),
  )

  const canAddToBag = computed(() =>
    selectedVariant.value !== null && selectedVariant.value.in_stock,
  )

  /** Price follows the selected variant, falling back to the product's. */
  const displayPrice = computed(() => selectedVariant.value?.price ?? product.value.price)

  const displayComparePrice = computed(() =>
    selectedVariant.value?.compare_at_price ?? product.value.compare_at_price,
  )

  return {
    chosen,
    select,
    isChosen,
    isAvailable,
    selectedVariant,
    missingAxes,
    canAddToBag,
    displayPrice,
    displayComparePrice,
  }
}
