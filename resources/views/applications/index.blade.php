<h1>My Applications</h1>

@foreach ($applications as $application)
    <div>
        <h3>{{ $application->company_name }}</h3>
        <p>{{ $application->role_title }}</p>
        <strong>Status: {{ $application->status }}</strong>
        <p>Applied At: {{ $application->applied_at }}</p>
        <p>Last Activity At: {{ $application->last_activity_at }}</p>
    </div>
@endforeach