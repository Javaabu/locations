import { LinkButton, Pagination, Table, TableCell, TableEmptyRow, TableHeading, TableRow } from "@javaabu/forms"
import { StencilForm } from "@javaabu/stencil"
import { LocationActions } from "../../components/LocationActions"
import { StatusAlerts } from "../../components/StatusAlerts"
import type { LocationsIndexProps } from "../../types"

/** locations::Resources/Index — the locations of one type, with search and filters */
export default function Index(props: LocationsIndexProps) {
  const { strings, urls, columns, rows, locations } = props

  return (
    <div className="flex flex-col gap-6" data-locations-index={props.resource}>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold">{props.title}</h1>
        {props.can.create ? (
          <LinkButton url={urls.create} startIcon="plus">
            {strings.new}
          </LinkButton>
        ) : null}
      </div>

      <StatusAlerts status={props.status} />

      <div className="locations-filters" data-locations-filters>
        <StencilForm schema={props.filters} />
      </div>

      <Table
        model={props.resource}
        noCheckbox
        noBulk
        headers={
          <>
            {columns.map((column) => (
              <TableHeading key={column.key} label={column.label} />
            ))}
            <TableHeading label={strings.actions} />
          </>
        }
        pagination={locations.last_page > 1 ? <Pagination paginator={locations} /> : null}
      >
        {rows.length ? (
          rows.map((row) => (
            <TableRow key={row.id} modelId={String(row.id)} noCheckbox data-location-row={row.id}>
              {columns.map((column, index) => (
                <TableCell key={column.key} name={column.key} label={column.label}>
                  {index === 0 && row.can.view ? (
                    <a href={row.links.show} className="font-medium hover:underline" dir={column.dir}>
                      {row.cells[column.key]}
                    </a>
                  ) : (
                    <span dir={column.dir}>{row.cells[column.key]}</span>
                  )}
                </TableCell>
              ))}
              <TableCell name="actions" label={strings.actions}>
                <LocationActions links={row.links} can={row.can} strings={strings} />
              </TableCell>
            </TableRow>
          ))
        ) : (
          <TableEmptyRow columns={columns.length + 1} noCheckbox>
            <p className="font-medium">{props.filtered ? strings.empty_filtered : strings.empty}</p>
          </TableEmptyRow>
        )}
      </Table>
    </div>
  )
}
