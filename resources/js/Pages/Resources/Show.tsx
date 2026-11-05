import { Card, LinkButton } from "@javaabu/forms"
import { LocationActions } from "../../components/LocationActions"
import { StatusAlerts } from "../../components/StatusAlerts"
import type { LocationsShowProps } from "../../types"

/** locations::Resources/Show — one location's details and links to its children */
export default function Show(props: LocationsShowProps) {
  const { strings, urls } = props

  return (
    <div className="flex flex-col gap-6" data-locations-show={props.resource}>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold" dir="auto">
          {props.title}
        </h1>
        <div className="flex flex-wrap items-center gap-2">
          <LinkButton url={urls.index} color="secondary" size="sm" startIcon="chevron-left">
            {strings.back}
          </LinkButton>
          <LocationActions links={urls} can={props.can} strings={strings} />
        </div>
      </div>

      <StatusAlerts status={props.status} />

      <Card title={strings.details}>
        <dl className="grid gap-x-6 gap-y-4 sm:grid-cols-2" data-location-details>
          {props.details.map((detail) => (
            <div key={detail.key} className="flex flex-col gap-1" data-location-detail={detail.key}>
              <dt className="text-muted-foreground text-sm">{detail.label}</dt>
              <dd dir={detail.dir}>{detail.value === null || detail.value === "" ? "—" : detail.value}</dd>
            </div>
          ))}
        </dl>
      </Card>

      {props.related.length ? (
        <Card title={strings.related}>
          <ul className="flex flex-wrap gap-2" data-location-related>
            {props.related.map((link) => (
              <li key={link.url}>
                <LinkButton url={link.url} color="secondary" size="sm">
                  {link.label}
                </LinkButton>
              </li>
            ))}
          </ul>
        </Card>
      ) : null}
    </div>
  )
}
