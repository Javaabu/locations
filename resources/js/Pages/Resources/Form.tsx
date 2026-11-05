import { LinkButton } from "@javaabu/forms"
import { StencilForm } from "@javaabu/stencil"
import { LocationActions } from "../../components/LocationActions"
import { StatusAlerts } from "../../components/StatusAlerts"
import type { LocationsFormProps } from "../../types"

/** locations::Resources/Form — create a location (no `location`) or edit one */
export default function Form(props: LocationsFormProps) {
  const { strings, urls } = props

  return (
    <div className="flex flex-col gap-6" data-locations-form={props.resource}>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold" dir="auto">
          {props.title}
        </h1>
        <LinkButton url={urls.index} color="secondary" size="sm" startIcon="chevron-left">
          {strings.back}
        </LinkButton>
      </div>

      <StatusAlerts status={props.status} />

      <StencilForm schema={props.form} />

      {props.location && props.can.delete ? (
        <div className="flex justify-end">
          <LocationActions links={urls} can={props.can} strings={strings} showEdit={false} />
        </div>
      ) : null}
    </div>
  )
}
