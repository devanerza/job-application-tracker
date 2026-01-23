import { Button } from "@/Components/ui/button"
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarGroupContent,
  SidebarMenuButton,
  SidebarHeader,
} from "@/components/ui/sidebar"
import { LayoutDashboardIcon, Table2Icon, Calendar1Icon } from "lucide-react"
 
export function AppSidebar() {
  return (
    <Sidebar variants="float">
      <SidebarContent className="px-5 pt-2">
        <SidebarHeader>
              <h1 className="font-bold text-2xl">Stride</h1>
            </SidebarHeader>
        <SidebarGroup>
            <SidebarGroupLabel>Menu</SidebarGroupLabel>
            <SidebarGroupContent asChild>
              <a href="">
                <Button variant="ghost" className="w-full justify-start px-2 rounded-lg">
                  <LayoutDashboardIcon />
                  Dashboard
                </Button>
              </a>
              <a href="">
                <Button variant="ghost" className="w-full justify-start px-2 rounded-lg">
                  <Table2Icon />
                  Application
                </Button>
              </a>
              <a href="">
                <Button variant="ghost" className="w-full justify-start px-2 rounded-lg">
                  <Calendar1Icon />
                  Follow-up
                </Button>
              </a>
            </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter />
    </Sidebar>
  )
}