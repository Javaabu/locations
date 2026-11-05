import { render, screen, waitFor } from "@testing-library/react"
import { afterEach, describe, expect, it, vi } from "vitest"

import {
  AtollSelect,
  CitySelect,
  CountrySelect,
  IslandSelect,
  LocationsProvider,
  StateSelect,
  locationOptionsUrl,
  toOptions,
} from "@javaabu/locations"

function respond(data: unknown) {
  return vi.fn(
    async (_url: string, _init?: RequestInit) =>
      new Response(JSON.stringify({ data }), { status: 200, headers: { "Content-Type": "application/json" } }),
  )
}

afterEach(() => {
  vi.unstubAllGlobals()
})

describe("locationOptionsUrl", () => {
  it("builds the endpoint URL of a type with filters", () => {
    expect(locationOptionsUrl("/location-options", "island")).toBe("/location-options/islands")
    expect(locationOptionsUrl("/api/locations/", "island", { category: "residential", named: true, empty: "" })).toBe(
      "/api/locations/islands?filter%5Bcategory%5D=residential&filter%5Bnamed%5D=true",
    )
  })

  it("turns endpoint options into select options", () => {
    expect(toOptions([{ id: 1, name: "ހއ. ހާ އަލިފު", code: "HA" }])).toEqual([{ value: "1", label: "ހއ. ހާ އަލިފު" }])
  })
})

describe("LocationSelect", () => {
  it("loads every country when it has no options", async () => {
    const fetch = respond([
      { id: 1, name: "ދިވެހިރާއްޖެ", code: "MV" },
      { id: 2, name: "އޮޅުދޫކަރަ", code: "LK" },
    ])
    vi.stubGlobal("fetch", fetch)

    render(<CountrySelect name="country_id" label="ޤައުމު" />)

    await waitFor(() => expect(screen.getByRole("option", { name: "ދިވެހިރާއްޖެ" })).toBeInTheDocument())
    expect(fetch).toHaveBeenCalledWith(
      "/location-options/countries?per_page=-1",
      expect.objectContaining({ credentials: "same-origin" }),
    )
  })

  it("uses the provider's endpoint and the filters", async () => {
    const fetch = respond([{ id: 1, name: "ހއ. ހާ އަލިފު", code: "HA" }])
    vi.stubGlobal("fetch", fetch)

    render(
      <LocationsProvider endpoint="/api/locations">
        <AtollSelect name="atoll_id" label="އަތޮޅު" filters={{ country: "MV" }} />
      </LocationsProvider>,
    )

    await waitFor(() => expect(screen.getByRole("option", { name: "ހއ. ހާ އަލިފު" })).toBeInTheDocument())
    expect(fetch.mock.calls[0]?.[0]).toBe("/api/locations/atolls?filter%5Bcountry%5D=MV&per_page=-1")
  })

  it("doesn't load options it was given", () => {
    const fetch = respond([])
    vi.stubGlobal("fetch", fetch)

    render(<AtollSelect name="atoll_id" label="އަތޮޅު" options={{ 1: "ހއ. ހާ އަލިފު" }} />)

    expect(screen.getByRole("option", { name: "ހއ. ހާ އަލިފު" })).toBeInTheDocument()
    expect(fetch).not.toHaveBeenCalled()
  })

  it("loads a child's options for its parent's value", async () => {
    const fetch = vi.fn(async (url: string) => {
      const data = url.includes("/islands")
        ? [{ id: 7, name: "ދިއްދޫ", code: "LD0568" }]
        : [{ id: 3, name: "ހއ. ހާ އަލިފު", code: "HA" }]

      return new Response(JSON.stringify({ data }), { status: 200 })
    })
    vi.stubGlobal("fetch", fetch)

    render(
      <LocationsProvider>
        <AtollSelect name="atoll_id" id="atoll_id" label="އަތޮޅު" default="3" options={{ 3: "ހއ. ހާ އަލިފު" }} />
        <IslandSelect name="island_id" label="ރަށް" parent="#atoll_id" />
      </LocationsProvider>,
    )

    await waitFor(() => expect(screen.getByRole("option", { name: "ދިއްދޫ" })).toBeInTheDocument())

    const url = String(fetch.mock.calls.find((call) => String(call[0]).includes("/islands"))?.[0])
    expect(url).toContain("/location-options/islands")
    expect(decodeURIComponent(url)).toContain("filter[atoll]=3")
  })

  it("shows no options when the endpoint fails", async () => {
    vi.stubGlobal(
      "fetch",
      vi.fn(async () => new Response("", { status: 500 })),
    )

    render(<CountrySelect name="country_id" label="ޤައުމު" />)

    await waitFor(() =>
      expect(screen.queryAllByRole("option").filter((option) => option.getAttribute("value"))).toHaveLength(0),
    )
  })

  it("has a select for states and cities", async () => {
    vi.stubGlobal("fetch", respond([{ id: 1, name: "Western Province", code: "LK-1" }]))

    render(
      <>
        <StateSelect name="state_id" id="state_id" label="ސްޓޭޓް" />
        <CitySelect name="city_id" label="ސިޓީ" parent="#state_id" />
      </>,
    )

    await waitFor(() => expect(screen.getByRole("option", { name: "Western Province" })).toBeInTheDocument())
    expect(document.querySelector('select[name="city_id"]')).not.toBeNull()
  })
})
