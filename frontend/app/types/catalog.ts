/**
 * Shapes returned by the Laravel API.
 *
 * These mirror app/Http/Resources/* on the backend. When a resource there
 * changes, change it here too -- nothing generates one from the other.
 */

export interface Money {
  amount: number // minor units (poisha)
  formatted: string // "৳189,000"
  currency: string
}

export interface OptionValue {
  id?: number
  name: string
  slug: string
  hex: string | null
}

export interface OptionAxis {
  slug: string // "color" | "size"
  name: string
  presentation: 'swatch' | 'text'
  values: OptionValue[]
}

export interface ProductImage {
  thumb?: string
  card: string
  zoom?: string
  alt: string
  color?: string | null
}

export interface ProductCard {
  id: number
  name: string
  slug: string
  url: string
  subtitle: string | null
  fit: string | null
  price: Money
  compare_at_price: Money | null
  on_sale: boolean
  is_new_arrival: boolean
  images: { card: string, alt: string }[]
  colors: OptionValue[]
  in_stock: boolean
}

export interface ProductVariant {
  id: number
  sku: string
  /** Keyed by option type slug: { color: 'navy', size: '50' } */
  options: Record<string, string>
  price: Money
  compare_at_price: Money | null
  in_stock: boolean
  low_stock: boolean
  stock_quantity: number
}

export interface Breadcrumb {
  label: string
  url: string
}

export interface ProductDetail {
  id: number
  name: string
  slug: string
  url: string
  sku_root: string
  subtitle: string | null
  description: string | null
  composition: string | null
  care_instructions: string | null
  fit: string | null
  price: Money
  compare_at_price: Money | null
  on_sale: boolean
  is_new_arrival: boolean
  in_stock: boolean
  images: ProductImage[]
  options: OptionAxis[]
  variants: ProductVariant[]
  categories: { name: string, path: string, url: string }[]
  breadcrumbs: Breadcrumb[]
  seo: { title: string, description: string | null }
}

export interface NavigationNode {
  id: number
  name: string
  path: string
  url: string
  is_featured: boolean
  image: string | null
  children: NavigationNode[]
}

export interface CategoryPayload {
  id: number
  name: string
  path: string
  description: string | null
  image: string | null
  breadcrumbs: Breadcrumb[]
  children: Breadcrumb[]
  seo: { title: string, description: string | null }
}

export interface PagePayload {
  slug: string
  title: string
  excerpt: string | null
  body: string | null
  blocks: unknown[] | null
  seo: { title: string, description: string | null }
}

export type ResolveResponse =
  | { type: 'category', data: CategoryPayload }
  | { type: 'page', data: PagePayload }

export interface Facets {
  options: OptionAxis[]
  fits: string[]
  price: { min: number, max: number } | null
}

export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    facets?: Facets
    applied_filters?: Record<string, unknown>
    category?: { id: number, name: string, path: string }
    query?: string
    message?: string
    related?: ProductCard[]
  }
  links?: { first: string, last: string, prev: string | null, next: string | null }
}

export interface Store {
  id: number
  name: string
  slug: string
  address: {
    line1: string
    line2: string | null
    city: string
    district: string | null
    postcode: string | null
    country_code: string
    single_line: string
  }
  phone: string | null
  email: string | null
  coordinates: { lat: number, lng: number } | null
  opening_hours: Record<string, [string, string] | null> | null
  offers_made_to_measure: boolean
  image: string | null
  distance_km?: number
}

export type ContentBlockType =
  | 'hero'
  | 'look_grid'
  | 'category_tiles'
  | 'editorial_split'
  | 'product_carousel'
  | 'store_finder'

export interface ContentBlock {
  id: number
  type: ContentBlockType
  payload: Record<string, any>
  products?: ProductCard[]
}

export interface ShopSettings {
  brand: { name: string, tagline: string }
  currency: { code: string, symbol: string, divisor: number }
  payment_methods: { key: string, label: string }[]
}
