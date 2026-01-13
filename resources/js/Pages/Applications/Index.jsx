import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout'
import { CreateApplicationDialog } from './Components/dialog-create'
import { DatePicker } from './Components/date-picker'
import { Head } from '@inertiajs/react'
import { columns } from "./Components/columns"
import { DataTable } from "./Components/data-table"
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import {
    Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog"


export default function Index({ auth, applications }) {
  return (
    <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Dashboard" />

            <header className="py-5 mx-10 flex justify-between align-top">
                <div>
                    <h3 className="scroll-m-20 text-2xl font-bold tracking-tight">
                        Applications
                    </h3>
                    <p>Manage and track your active job hunt status</p>
                </div>
                <div>
                    <CreateApplicationDialog />
                    {/* <Dialog>
                        <form action="">
                            <DialogTrigger asChild>
                                <Button>Add new</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Fill your application details</DialogTitle>
                                </DialogHeader>
                                <div id="company-name" className="pt-5">
                                    <Label htmlFor="" >Company Name</Label>
                                    <Input id="company-name-1" name="company name" placeHolder="e.g Microsoft" />
                                </div>
                                <div id="role-title" className="pt-5">
                                    <Label htmlFor="">Role Title</Label>
                                    <Input id="role-title-1" name="role title" placeHolder="e.g Software Engineer" />
                                </div>
                                <div id="job-url" className="pt-5">
                                    <Label htmlFor="">Job Url</Label>
                                    <Input id="job-url-1" name="job url" placeHolder="e.g https://www.linkedin.com/jobs/collection/***" />
                                </div>
                                <div id="company-name" className="pt-5">
                                    <Label htmlFor="" className="mr-3">Date Applied</Label>
                                    <DatePicker />
                                </div>
                                <div id="action-btn" className="pt-5 flex justify-end">
                                    <Button className="mr-2">Add</Button>
                                    <Button variant="outline">Cancel</Button>
                                </div>
                            </DialogContent>
                        </form>
                    </Dialog> */}
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