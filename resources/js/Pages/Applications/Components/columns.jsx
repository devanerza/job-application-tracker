"use client"

import { router } from "@inertiajs/react";
import { MoreHorizontal, Edit, Trash } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";


export const columns = [
  {
    accessorKey: "company_name", 
    header: "Company",
  },
  {
    accessorKey: "role_title",
    header: "Role",
  },
  {
    accessorKey: "job_url",
    header: "URL",
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
        <select
          value={application.status}
          onChange={(e) => handleStatusChange(e.target.value)}
          className="block w-full rounded-md border-gray-300 py-1 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
  {
    accessorKey: "applied_at",
    header: "Date Applied",
  },
  {
    accessorKey: "follow_up_at",
    header: "Next Follow Up"
  },
  {
    accessorKey: "last_activity_at",
    header: "Last Activity"
  },
  {
    id: "actions",
    header: "Actions",
    cell: ({ row }) => {
      const application = row.original;

      return (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="ghost" className="h-8 w-8 p-0">
              <MoreHorizontal className="h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuItem 
                onClick={() => router.get(route('applications.edit', application.id))}
            >
              <Edit className="mr-2 h-4 w-4" /> Edit
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem 
                className="text-red-600"
                onClick={() => {
                    if(confirm('Are you sure you want to delete this?')) {
                        router.delete(route('applications.destroy', application.id))
                    }
                }}
            >
              <Trash className="mr-2 h-4 w-4" /> Delete
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      );
    },
  },
];