import { render, screen, within } from "@testing-library/react"
import { afterEach, describe, expect, it, vi } from "vitest"

import type { LocationsFormProps, LocationsIndexProps, LocationsShowProps } from "@javaabu/locations"
import FormPage from "@javaabu/locations/Pages/Resources/Form"
import IndexPage from "@javaabu/locations/Pages/Resources/Index"
import ShowPage from "@javaabu/locations/Pages/Resources/Show"

import formFixture from "./fixtures/form.json"
import indexDhivehiFixture from "./fixtures/index.dv.json"
import indexFixture from "./fixtures/index.json"
import showFixture from "./fixtures/show.json"

const index = indexFixture as unknown as LocationsIndexProps
const indexDhivehi = indexDhivehiFixture as unknown as LocationsIndexProps
const form = formFixture as unknown as LocationsFormProps
const show = showFixture as unknown as LocationsShowProps

afterEach(() => {
  vi.restoreAllMocks()
})

describe("locations::Resources/Index", () => {
  it("lists the locations with links to each", () => {
    render(<IndexPage {...index} />)

    expect(screen.getByRole("heading", { name: "Islands" })).toBeInTheDocument()

    const row = document.querySelector<HTMLElement>(`[data-location-row="${index.rows[0]?.id}"]`)!
    expect(within(row).getByRole("link", { name: "LD0568" })).toHaveAttribute("href", index.rows[0]?.links.show)
    expect(within(row).getByText("ދިއްދޫ")).toHaveAttribute("dir", "rtl")
    expect(within(row).getByText("HA. Haa Alifu")).toBeInTheDocument()
    expect(within(row).getByRole("link", { name: "Edit" })).toHaveAttribute("href", index.rows[0]?.links.edit)
    expect(screen.getByRole("link", { name: "New Island" })).toHaveAttribute("href", index.urls.create)
  })

  it("renders the filters", () => {
    render(<IndexPage {...index} />)

    const filters = document.querySelector("[data-locations-filters]")!
    expect(filters.querySelector('input[name="search"]')).not.toBeNull()
    expect(filters.querySelector('select[name="atoll"]')).not.toBeNull()
    expect(filters.querySelector('select[name="category"]')).not.toBeNull()
  })

  it("hides what the user may not do", () => {
    render(
      <IndexPage
        {...index}
        can={{ ...index.can, create: false }}
        rows={index.rows.map((row) => ({ ...row, can: { view: false, update: false, delete: false } }))}
      />,
    )

    expect(screen.queryByRole("link", { name: "New Island" })).toBeNull()
    expect(screen.queryByRole("link", { name: "Edit" })).toBeNull()
    expect(screen.queryByRole("button", { name: "Delete" })).toBeNull()
    expect(screen.queryByRole("link", { name: "LD0568" })).toBeNull()
  })

  it("says when nothing matches", () => {
    render(<IndexPage {...index} rows={[]} />)

    expect(screen.getByText(index.strings.empty_filtered)).toBeInTheDocument()
  })

  it("renders in Dhivehi", () => {
    render(<IndexPage {...indexDhivehi} />)

    expect(screen.getByRole("heading", { name: "ރަށްތައް" })).toBeInTheDocument()
    expect(screen.getByRole("link", { name: "އައު ރަށް" })).toBeInTheDocument()
    expect(screen.getAllByText("ހއ. ހާ އަލިފު").length).toBeGreaterThan(0)
  })

  it("asks before deleting", () => {
    const confirm = vi.spyOn(window, "confirm").mockReturnValue(false)

    render(<IndexPage {...index} />)

    screen.getAllByRole("button", { name: "Delete" })[0]!.click()

    expect(confirm).toHaveBeenCalledWith(index.strings.confirm_delete)
  })
})

describe("locations::Resources/Form", () => {
  it("renders the stencil form with the location's values", () => {
    render(<FormPage {...form} />)

    expect(screen.getByRole("heading", { name: form.title })).toBeInTheDocument()
    expect(document.querySelector<HTMLInputElement>('input[name="name"]')?.value).toBe("Dhidhdhoo")
    expect(document.querySelector<HTMLInputElement>('input[name="dhivehi_name"]')?.value).toBe("ދިއްދޫ")
    expect(document.querySelector('select[name="atoll_id"]')).not.toBeNull()
    expect(screen.getByRole("button", { name: "Save" })).toBeInTheDocument()
    expect(screen.getByRole("link", { name: form.strings.back })).toHaveAttribute("href", form.urls.index)
  })

  it("offers to delete an existing location", () => {
    render(<FormPage {...form} />)

    expect(screen.getByRole("button", { name: "Delete" })).toBeInTheDocument()
  })

  it("doesn't offer to delete a new location", () => {
    render(<FormPage {...form} location={null} />)

    expect(screen.queryByRole("button", { name: "Delete" })).toBeNull()
  })
})

describe("locations::Resources/Show", () => {
  it("lists the details and links to the children", () => {
    render(<ShowPage {...show} />)

    expect(screen.getByRole("heading", { name: "HA. Haa Alifu" })).toBeInTheDocument()

    const details = document.querySelector<HTMLElement>("[data-location-details]")!
    expect(within(details).getByText("ތިލަދުންމަތީ އުތުރުބުރި")).toHaveAttribute("dir", "rtl")
    expect(within(details).getByText("MV-07")).toHaveAttribute("dir", "ltr")

    expect(screen.getByRole("link", { name: "Islands (2)" })).toHaveAttribute("href", show.related[0]?.url)
  })

  it("shows a dash for empty details and the flashed status", () => {
    render(
      <ShowPage
        {...show}
        details={[{ key: "code", label: "Code", value: null, dir: "ltr" }]}
        status={{ success: "Atoll updated", error: null }}
      />,
    )

    expect(screen.getByText("—")).toBeInTheDocument()
    expect(screen.getByText("Atoll updated")).toBeInTheDocument()
  })
})
