import { columns } from "./Components/columns"
import { DataTable } from "./Components/data-table"

export default function Index({ applications }) {
  return (
    <div className="container mx-auto py-10">
      <DataTable columns={columns} data={applications.data} />
    </div>
  )
}