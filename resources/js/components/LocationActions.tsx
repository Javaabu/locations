import { Form, LinkButton, Submit } from "@javaabu/forms"
import type { MouseEvent } from "react"
import type { LocationAbilities, LocationLinks, LocationsStrings } from "../types"

export interface LocationActionsProps {
  links: Partial<LocationLinks>
  can: Partial<LocationAbilities>
  strings: Pick<LocationsStrings, "view" | "edit" | "delete" | "confirm_delete">
  /** Show the View button. Default `false` (the first column links to the location) */
  showView?: boolean
  /** Show the Edit button. Default `true` */
  showEdit?: boolean
}

function confirmFirst(message: string) {
  return (event: MouseEvent<HTMLButtonElement>) => {
    if (!window.confirm(message)) {
      event.preventDefault()
    }
  }
}

/** View, edit and delete buttons, shown when the user may use them */
export function LocationActions({ links, can, strings, showView = false, showEdit = true }: LocationActionsProps) {
  return (
    <div className="flex flex-wrap items-center gap-2" data-location-actions>
      {showView && can.view && links.show ? (
        <LinkButton url={links.show} color="secondary" size="sm">
          {strings.view}
        </LinkButton>
      ) : null}

      {showEdit && can.update && links.edit ? (
        <LinkButton url={links.edit} color="secondary" size="sm">
          {strings.edit}
        </LinkButton>
      ) : null}

      {can.delete && links.destroy ? (
        <Form method="DELETE" action={links.destroy}>
          <Submit color="danger" size="sm" animate={false} onClick={confirmFirst(strings.confirm_delete)}>
            {strings.delete}
          </Submit>
        </Form>
      ) : null}
    </div>
  )
}
