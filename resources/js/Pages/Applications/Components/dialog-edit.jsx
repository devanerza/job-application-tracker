import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Edit } from 'lucide-react';
import { DatePicker } from './date-picker-apply'
import { Button } from '@/Components/ui/button'
import { Label } from '@/Components/ui/label'
import { Input } from '@/Components/ui/input'
import {
    Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog"

export function EditApplicationDialog({ application }) {
    const [open, setOpen] = useState(false);

    // 1. Initialize the Inertia Form Hook
    const { data, setData, post, processing, errors, reset } = useForm({
        company_name: application?.company_name || '',
        role_title: application?.role_title || '',
        job_url: application?.job_url || '',
        applied_at: application?.applied_at || new Date().toISOString().split('T')[0],
        status: application?.status || 'applied',
    });

    // 2. Handle Form Submission
    const handleSubmit = (e) => {
        e.preventDefault();
        patch(route('applications.update', application.id), {
            preserveState: true,
            onSuccess: () => {
                setOpen(false); // Close dialog on success
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline" onClick={(e) => e.stopPropagation()}>
                    <Edit />Edit
                </Button>
            </DialogTrigger>
            
            <DialogContent>
                <form onSubmit={handleSubmit}>
                    <DialogHeader>
                        <DialogTitle>Fill your application details</DialogTitle>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        <div>
                            <Label htmlFor="company">Company Name</Label>
                            <Input 
                                id="company" 
                                value={data.company_name}
                                onChange={e => setData('company_name', e.target.value)}
                                placeholder="e.g Microsoft" 
                            />
                            {errors.company_name && <p className="text-red-500 text-xs mt-1">{errors.company_name}</p>}
                        </div>

                        <div>
                            <Label htmlFor="role">Role Title</Label>
                            <Input 
                                id="role" 
                                value={data.role_title}
                                onChange={e => setData('role_title', e.target.value)}
                                placeholder="e.g Software Engineer" 
                            />
                            {errors.role_title && <p className="text-red-500 text-xs mt-1">{errors.role_title}</p>}
                        </div>

                        <div>
                            <Label htmlFor="url">Job Url</Label>
                            <Input 
                                id="url" 
                                value={data.job_url}
                                onChange={e => setData('job_url', e.target.value)}
                                placeholder="e.g https://linkedin.com/..." 
                            />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label>Date Applied</Label>
                            <DatePicker 
                            selected={data.applied_at} 
                            onSelect={(date) => setData('applied_at', date)} 
                            />
                        </div>
                    </div>

                    <div className="pt-5 flex justify-end gap-2">
                        <Button 
                            type="button" 
                            variant="outline" 
                            onClick={() => setOpen(false)}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Adding...' : 'Add'}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}