import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { SearchIcon,
    CreditCardIcon,
    LogOutIcon,
    SettingsIcon,
    UserIcon,
 } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger, } from '@/Components/ui/dropdown-menu';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/Components/ui/input-group';
import { SidebarProvider } from '@/Components/ui/sidebar';
import { AppSidebar } from '@/Pages/Applications/Components/app-sidebar';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;

    return (
        <html lang='en'>
            <body>
                <SidebarProvider>
                    <AppSidebar />
                    <main className='w-full bg-slate-50 relative'>
                        <nav className="bg-white fixed z-20 inset-x-64 right-0 flex py-3 px-10 shadow-sm">
                            <div className='flex justify-between w-full'>
                                <div>
                                    <InputGroup className="w-72 rounded-full">
                                        <InputGroupInput placeholder="Search..." />
                                        <InputGroupAddon>
                                            <SearchIcon />
                                        </InputGroupAddon>
                                    </InputGroup>
                                </div>
                                <div>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button variant="ghost" className="border-none focus-visible:ring-0 rounded-full p-4">
                                                {user.name}
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent>
                                            <DropdownMenuItem>
                                            <UserIcon />
                                            Profile
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                            <CreditCardIcon />
                                            Billing
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                            <SettingsIcon />
                                            Settings
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem variant="destructive">
                                            <LogOutIcon />
                                            Log out
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </nav>
                        {children}
                    </main>
                </SidebarProvider>
            </body>
        </html>
    );
}
