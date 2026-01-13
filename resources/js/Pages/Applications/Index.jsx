import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { CreateApplicationDialog } from './Components/dialog-create'
import { DatePicker } from './Components/date-picker'
import { Head } from '@inertiajs/react'
import { columns } from "./Components/columns"
import { DataTable } from "./Components/data-table"


export default function Index({ auth, applications }) {
  return (
    <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="My Applications" />

            <header className="py-5 mx-10 flex justify-between align-top">
                <div>
                    <h3 className="scroll-m-20 text-2xl font-bold tracking-tight">
                        Applications
                    </h3>
                    <p>Manage and track your active job hunt status</p>
                </div>
                <div>
                    <CreateApplicationDialog />
                </div>
            </header>



            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <DataTable columns={columns} data={applications.data} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
  )
}