import * as React from "react"

export interface LocationsConfig {
  /** Where `Route::locationOptions()` is mounted. Default `/location-options` */
  endpoint: string
}

const LocationsContext = React.createContext<LocationsConfig>({ endpoint: "/location-options" })

export interface LocationsProviderProps extends Partial<LocationsConfig> {
  children: React.ReactNode
}

/** Sets where the location selects load their options from, for everything inside it */
export function LocationsProvider({ children, ...config }: LocationsProviderProps) {
  const parent = React.useContext(LocationsContext)
  const value = React.useMemo(() => ({ ...parent, ...config }), [parent, config.endpoint]) // eslint-disable-line react-hooks/exhaustive-deps

  return <LocationsContext.Provider value={value}>{children}</LocationsContext.Provider>
}

export function useLocationsConfig(): LocationsConfig {
  return React.useContext(LocationsContext)
}
