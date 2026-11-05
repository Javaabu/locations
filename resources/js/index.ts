// The React components of javaabu/locations. Apps import them as @javaabu/locations,
// through javaabu/manifest's Vite plugin. The admin pages are locations::Resources/Index,
// locations::Resources/Form and locations::Resources/Show.

export {
  AtollSelect,
  CitySelect,
  CountrySelect,
  IslandSelect,
  LocationSelect,
  StateSelect,
  LOCATION_RESOURCES,
  PARENT_FILTERS,
  locationOptionsUrl,
  toOptions,
  useLocationOptions,
  type LocationSelectProps,
  type TypedLocationSelectProps,
} from "./components/LocationSelect"
export {
  LocationsProvider,
  useLocationsConfig,
  type LocationsConfig,
  type LocationsProviderProps,
} from "./components/LocationsProvider"
export { LocationActions, type LocationActionsProps } from "./components/LocationActions"
export { StatusAlerts } from "./components/StatusAlerts"
export type * from "./types"
