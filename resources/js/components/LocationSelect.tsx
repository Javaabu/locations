import { Select2, type Select2Props } from "@javaabu/forms"
import * as React from "react"
import type { LocationOption, LocationResourceName, LocationType } from "../types"
import { useLocationsConfig } from "./LocationsProvider"

/** The resource name of each type, as the options endpoint names its URLs */
export const LOCATION_RESOURCES: Record<LocationType, LocationResourceName> = {
  country: "countries",
  state: "states",
  city: "cities",
  atoll: "atolls",
  island: "islands",
}

/** The filter a parent's value is sent as when a select depends on another */
export const PARENT_FILTERS: Record<LocationType, string> = {
  country: "country",
  state: "country",
  city: "state",
  atoll: "country",
  island: "atoll",
}

type Filters = Record<string, string | number | boolean | null | undefined>

export interface LocationSelectProps extends Omit<Select2Props, "ajaxUrl" | "isAjax" | "filterField"> {
  /** What the select lists */
  type: LocationType
  /**
   * The options. Leave them out to load every location of the type from the options endpoint,
   * or, with `parent`, pass the options for the parent's current value so the selected one has
   * its label before the first request.
   */
  options?: Select2Props["options"]
  /** Where `Route::locationOptions()` is mounted. Default: `LocationsProvider`'s, or `/location-options` */
  endpoint?: string
  /** Extra filters for the options, e.g. `{ category: "residential" }` for islands */
  filters?: Filters
  /** The filter the parent's value is sent as. Default: `atoll` for islands, `country` for states and atolls, `state` for cities */
  filterField?: string
}

/** The options endpoint URL of a type, with filters */
export function locationOptionsUrl(endpoint: string, type: LocationType, filters: Filters = {}): string {
  const url = `${endpoint.replace(/\/+$/, "")}/${LOCATION_RESOURCES[type]}`
  const query = new URLSearchParams()

  for (const [key, value] of Object.entries(filters)) {
    if (value === null || value === undefined || value === "") continue
    query.append(`filter[${key}]`, String(value))
  }

  const search = query.toString()

  return search ? `${url}?${search}` : url
}

/** Options for Select2, in the endpoint's order */
export function toOptions(options: LocationOption[]): Array<{ value: string; label: string }> {
  return options.map((option) => ({ value: String(option.id), label: option.name }))
}

/**
 * Loads every location of a type once, when no options were passed
 */
export function useLocationOptions(
  url: string,
  enabled: boolean,
): { options: LocationOption[]; loading: boolean; error: boolean } {
  const [state, setState] = React.useState<{ url: string; options: LocationOption[]; error: boolean } | null>(null)

  React.useEffect(() => {
    if (!enabled) return

    const controller = new AbortController()
    const separator = url.includes("?") ? "&" : "?"

    fetch(`${url}${separator}per_page=-1`, {
      headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
      credentials: "same-origin",
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        return response.json() as Promise<{ data?: LocationOption[] } | LocationOption[]>
      })
      .then((body) => {
        const options = Array.isArray(body) ? body : (body.data ?? [])
        setState({ url, options, error: false })
      })
      .catch((error: unknown) => {
        if ((error as Error).name === "AbortError") return
        setState({ url, options: [], error: true })
      })

    return () => controller.abort()
  }, [url, enabled])

  const current = state && state.url === url ? state : null

  return { options: current?.options ?? [], loading: enabled && !current, error: !!current?.error }
}

/**
 * A searchable select of countries, states, cities, atolls or islands, on @javaabu/forms' Select2
 *
 * With `parent`, it lists the locations of the parent field's value and reloads them when the
 * parent changes: `<IslandSelect name="island_id" parent="atoll_id" />`.
 */
export function LocationSelect({
  type,
  options,
  endpoint,
  filters,
  parent,
  filterField,
  ...props
}: LocationSelectProps) {
  const config = useLocationsConfig()
  const url = locationOptionsUrl(endpoint ?? config.endpoint, type, filters)
  const loadsAll = options === undefined && !parent
  const loaded = useLocationOptions(url, loadsAll)

  if (parent) {
    return (
      <Select2
        {...props}
        options={options ?? []}
        parent={parent}
        ajaxUrl={url}
        filterField={filterField ?? PARENT_FILTERS[type]}
        data-location-select={type}
      />
    )
  }

  return <Select2 {...props} options={options ?? toOptions(loaded.options)} data-location-select={type} />
}

export type TypedLocationSelectProps = Omit<LocationSelectProps, "type">

/** A searchable select of countries */
export function CountrySelect(props: TypedLocationSelectProps) {
  return <LocationSelect type="country" {...props} />
}

/** A searchable select of states; give it `parent` to list one country's */
export function StateSelect(props: TypedLocationSelectProps) {
  return <LocationSelect type="state" {...props} />
}

/** A searchable select of cities; give it `parent` to list one state's */
export function CitySelect(props: TypedLocationSelectProps) {
  return <LocationSelect type="city" {...props} />
}

/** A searchable select of the Maldivian atolls and Malé City */
export function AtollSelect(props: TypedLocationSelectProps) {
  return <LocationSelect type="atoll" {...props} />
}

/** A searchable select of islands; give it `parent` to list one atoll's */
export function IslandSelect(props: TypedLocationSelectProps) {
  return <LocationSelect type="island" {...props} />
}
