"use client"

import { router, usePage } from "@inertiajs/react"
import { useState } from "react";
import { format } from "date-fns";
import { ExternalLink, TrashIcon } from "lucide-react"
import { Button } from "@/Components/ui/button"
import { Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue, } from "@/Components/ui/select"
import { DatePickerFoll } from "./date-picker-follUp"
import { EditApplicationDialog } from "./dialog-edit";


// Component for action column
const ActionCell = ({ application }) => {
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);

    

    return (
        <div className="w-21 flex justify-end">
            <EditApplicationDialog 
                application={application} 
                key={application.id}
                open={isEditDialogOpen} 
                onOpenChange={setIsEditDialogOpen} 
            />
            <Button 
              variant="outline"
              className="ml-1 text-red-600"
              onClick={() => {
                if(confirm('Delete?')) router.delete(route('applications.destroy', application.id))
              }}
            >
              <TrashIcon />
            </Button>
        </div>
    );
};

const FollowUpCell = ({ application }) => {
  const { errors } = usePage().props;

  const rowError = errors[`application_${application.id}`]?.invalid_follow_up_date;

  // console.log(errors)
  const handleFollowUp = (date) => {
      const formattedDate = date ? format(date, "yyyy-MM-dd") : null;
      router.patch(route('applications.followUp', application.id), {
        follow_up_at: formattedDate
      }, {
        preserveScroll: true
      }
    );
  };

  return (
    <>
      <div>
        {/* {isEmpty(application.follow_up_at)} */}
        <DatePickerFoll 
          selected={application.follow_up_at} 
          onSelect={handleFollowUp} 
          err={rowError}
        />
      </div>
    </>
  );
}


// List of columns 
export const columns = [
  {
    accessorKey: "company_name", 
    header: "Jobs",
    cell: ({ row }) => {
      const application = row.original;

      return (
        <div  className="py-3">
          <h2 className="text-lg font-bold">{application.role_title}</h2>
          <p className="text-sm">{application.company_name}</p>
        </div>
      )
    }
  },
  // {
  //   accessorKey: "role_title",
  //   header: "Role",
  // },
  {
    accessorKey: "job_url",
    header: "URL",
    cell: ({ row }) => {
      const application = row.original;

      if (application.job_url == null) return <p className="opacity-30">No Link Provided</p>

      return (
        <a 
          href={application.job_url} 
          target="_blank" 
          rel="noopener noreferrer" 
          className="text-blue-600 hover:underline font-medium flex"
        >
          View Post <ExternalLink className="ml-1 size-4" />
        </a>
      );
    },
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const application = row.original;
      
      const handleStatusChange = (newStatus) => {
        router.patch(route('applications.statusUpdate', application.id), {
          status: newStatus,
        });
      };
      
      return (
        // <Select value={value} onValueChange={setValue}>
        //   <SelectTrigger className="w-[180px]">
        //     <SelectValue placeholder="Select Status" />
        //   </SelectTrigger>
        //   <SelectContent>
        //     <SelectGroup>
        //       <SelectLabel>Select Status</SelectLabel>
        //       <SelectItem value="applied">Applied</SelectItem>
        //       <SelectItem value="interviewing">Interviewing</SelectItem>
        //       <SelectItem value="offer">Offer</SelectItem>
        //       <SelectItem value="rejected">Rejected</SelectItem>
        //       <SelectItem value="ghosted">Ghosted</SelectItem>
        //     </SelectGroup>
        //   </SelectContent>
        // </Select>

        <select
          value={application.status}
          onChange={(e) => handleStatusChange(e.target.value)}
          className=" block w-full rounded-xl border-gray-300 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="applied">Applied</option>
          <option value="interviewing">Interviewing</option>
          <option value="offer">Offer</option>
          <option value="rejected">Rejected</option>
          <option value="ghosted">Ghosted</option>
        </select>
      );
    },
  },
  // {
  //   accessorKey: "applied_at",
  //   header: "Date Applied",
  // },
  // {
  //   accessorKey: "follow_up_at",
  //   header: "Next Follow Up",
  //   cell: ({ row }) => <FollowUpCell application={row.original} />,
  // },
  // {
  //   accessorKey: "last_activity_at",
  //   header: "Last Activity"
  // },
  {
    id: "actions",
    // header: "Actions",
    cell: ({ row }) => <ActionCell application={row.original} />,
  },
];