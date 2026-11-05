import { Alert } from "@javaabu/forms"
import type { LocationsPageProps } from "../types"

/** The success or error message a redirect flashed */
export function StatusAlerts({ status }: Pick<LocationsPageProps, "status">) {
  return (
    <>
      {status.success ? <Alert type="success">{status.success}</Alert> : null}
      {status.error ? <Alert type="danger">{status.error}</Alert> : null}
    </>
  )
}
