<h1>My Applications</h1> <a href="/applications/create">Add New</a>

<form action="{{ route('applications.index') }}" method="GET" style="margin-bottom: 20px;">
    <label for="status">Filter by Status:</label>
    <select name="status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="applied" {{ request('status') == 'applied' ? 'selected' : '' }}>Applied</option>
        <option value="screening" {{ request('status') == 'screening' ? 'selected' : '' }}>Screening</option>
        <option value="interviewing" {{ request('status') == 'interviewing' ? 'selected' : '' }}>Interviewing</option>
        <option value="offer" {{ request('status') == 'offer' ? 'selected' : '' }}>Offer</option>
        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        <option value="ghosted" {{ request('status') == 'ghosted' ? 'selected' : '' }}>Ghosted</option>
    </select>

    <label for="sort" style="margin-left: 10px;">Sort by:</label>
    <select name="sort" onchange="this.form.submit()">
        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
    </select>
    
    <a href="{{ route('applications.index') }}" style="margin-left: 10px;">Reset</a>
</form>

<hr>

@foreach ($applications as $application)
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
        <h3>{{ $application->company_name }}</h3>
        <p>{{ $application->role_title }}</p>
        
        <form action="{{ route('applications.statusUpdate', $application->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <label>Status:</label>
            <select name="status" onchange="this.form.submit()">
                <option value="applied" {{ $application->status == 'applied' ? 'selected' : '' }}>Applied</option>
                <option value="screening" {{ $application->status == 'screening' ? 'selected' : '' }}>Screening</option>
                <option value="interviewing" {{ $application->status == 'interviewing' ? 'selected' : '' }}>Interviewing</option>
                <option value="offer" {{ $application->status == 'offer' ? 'selected' : '' }}>Offer</option>
                <option value="rejected" {{ $application->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="ghosted" {{ $application->status == 'ghosted' ? 'selected' : '' }}>Ghosted</option>
            </select>
        </form>

        <p>Applied At: {{ $application->applied_at }}</p>
        <p>Last Activity At: {{ $application->last_activity_at }}</p>

        <form action="{{ route('applications.followUp', $application->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <p>Follow Up At: {{ $application->follow_up_at ?? 'Not set' }}</p>
            <input type="date" name='follow_up_at' onchange='this.form.submit()'>
        </form>

        <div style="margin-top: 10px;">
            <a href="/applications/{{ $application->id }}/edit">Edit</a>
            
            <form action="{{ route('applications.destroy', $application->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this application?')">Delete</button>
            </form>
        </div>
    </div>
@endforeach