import type { StencilSchema } from "@javaabu/stencil"

/** The location types */
export type LocationType = "country" | "state" | "city" | "atoll" | "island"

/** Their resource names, as in routes and permissions */
export type LocationResourceName = "countries" | "states" | "cities" | "atolls" | "islands"

/** One option of a location select, as the options endpoint returns it */
export interface LocationOption {
  id: number | string
  /** The label in the request's locale, e.g. `HA. Haa Alifu` / `ހއ. ހާ އަލިފު` */
  name: string
  code: string | null
}

/** A location as LocationResource serializes it */
export interface LocationData {
  id: number
  type: LocationType
  code: string | null
  name: string | null
  dhivehi_name: string | null
  /** The name in the request's locale */
  localized_name: string
  /** What selects show */
  label: string
  /** Islands: `HA. Dhidhdhoo` */
  formatted_name?: string
  /** Islands */
  category?: string | null
  category_label?: string
  country?: LocationData | null
  state?: LocationData | null
  atoll?: LocationData | null
  can?: LocationAbilities
  links?: LocationLinks
  created_at: string | null
  updated_at: string | null
  [attribute: string]: unknown
}

export interface LocationAbilities {
  view: boolean
  update: boolean
  delete: boolean
}

export interface LocationLinks {
  show: string
  edit: string
  update: string
  destroy: string
}

export interface LocationsStrings {
  new: string
  back: string
  view: string
  edit: string
  delete: string
  actions: string
  confirm_delete: string
  empty: string
  empty_filtered: string
  details: string
  related: string
}

/** Props every admin page gets */
export interface LocationsPageProps {
  title: string
  type: LocationType
  resource: LocationResourceName
  labels: { singular: string; plural: string }
  guard: string
  urls: { index: string; create: string; store: string } & Partial<LocationLinks>
  can: { create: boolean } & LocationAbilities
  status: { success: string | null; error: string | null }
  strings: LocationsStrings
}

export interface LocationColumn {
  key: string
  label: string
  dir: "ltr" | "rtl" | "auto"
}

export interface LocationRow {
  id: number
  cells: Record<string, string | number | null>
  links: LocationLinks
  can: LocationAbilities
}

/** A Laravel length-aware paginator as JSON (`$paginator->toArray()`) */
export interface LaravelPaginator<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  path: string
  first_page_url: string
  last_page_url: string
  next_page_url: string | null
  prev_page_url: string | null
  links: Array<{ url: string | null; label: string; active: boolean }>
}

/** locations::Resources/Index */
export interface LocationsIndexProps extends LocationsPageProps {
  columns: LocationColumn[]
  rows: LocationRow[]
  locations: LaravelPaginator<unknown>
  filters: StencilSchema
  filtered: boolean
}

/** locations::Resources/Form, for creating (location is null) and editing */
export interface LocationsFormProps extends LocationsPageProps {
  form: StencilSchema
  location: LocationData | null
  translations: unknown
}

export interface LocationDetail {
  key: string
  label: string
  value: string | number | null
  dir: "ltr" | "rtl" | "auto"
}

/** locations::Resources/Show */
export interface LocationsShowProps extends LocationsPageProps {
  location: LocationData
  details: LocationDetail[]
  related: Array<{ label: string; url: string }>
}
