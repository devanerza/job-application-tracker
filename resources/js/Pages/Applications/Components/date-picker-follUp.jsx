"use client"

import * as React from "react"
import { format, parseISO } from "date-fns"
import { Calendar as CalendarIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

// 1. Destructure props with { }
export function DatePickerFoll({ selected, onSelect }) {
  
  const [ open, setOpen ] = React.useState(false);

  const dateValue = typeof selected === 'string' ? parseISO(selected) : selected;

  const handleSelect = (date) => {
    onSelect(date);
    setOpen(false); 
  };

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          className={cn(
            " p-3 justify-center text-center font-normal",
            !dateValue && "text-muted-foreground"
          )}
          onClick={() => setOpen(true)}
        >
          <CalendarIcon className="h-2 w-2" />
          {/* {dateValue ? format(dateValue, "PPP") : <span>Pick a date</span>} */}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar
          mode="single"
          selected={dateValue}
          onSelect={handleSelect}
          initialFocus={false}
        />
      </PopoverContent>
    </Popover>
  )
}