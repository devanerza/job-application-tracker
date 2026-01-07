<h1>My Applications</h1>

@foreach ($applications as $application)
    <div>
        <h3>{{ $application->company_name }}</h3>
        <p>{{ $application->role_title }}</p>
        <strong>Status: {{ $application->status }}</strong>
        <p>Applied At: {{ $application->applied_at }}</p>
        <p>Last Activity At: {{ $application->last_activity_at }}</p>
        <a href="/applications/{{ $application->id }}/edit">Edit</a>
        <form action="{{ route('applications.destroy', $application->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit">Delete</button>
    </div>
@endforeach