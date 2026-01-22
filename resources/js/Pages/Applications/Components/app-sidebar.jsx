import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarHeader,
} from "@/components/ui/sidebar"
 
export function AppSidebar() {
  return (
    <Sidebar variants="float">
      <SidebarContent>
        <SidebarGroup>
            <SidebarHeader>
              <h1 className="font-bold text-2xl">Stride</h1>
            </SidebarHeader>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter />
    </Sidebar>
  )
}