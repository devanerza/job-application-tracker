<h1>My Applications</h1>

@foreach ($applications as $application)
    <div>
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
        <p>Follow Up At: {{ $application->follow_up_at }}</p>
        <p>Applied At: {{ $application->applied_at }}</p>
        <p>Last Activity At: {{ $application->last_activity_at }}</p>
        <a href="/applications/{{ $application->id }}/edit">Edit</a>
        <form action="{{ route('applications.followUp', $application->id) }}", method="POST">
            @csrf
            @method('PATCH')
            <p>Follow Up At:</p>
            <input type="date" name='follow_up_at' onchange='this.form.submit()'>
        </form>
        <form action="{{ route('applications.destroy', $application->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit">Delete</button>
        </form>
    </div>
@endforeach