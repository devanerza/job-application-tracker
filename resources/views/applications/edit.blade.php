<form method="POST" action="/applications/{{ $application->id }}">
    @csrf
    @method('PUT')
    <div>
        <label>Company Name</label>
        <input type="text" name="company_name">
        @error('company_name') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Role Title</label>
        <input type="text" name="role_title">
        @error('role_title') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Job URL</label>
        <input type="text" name="job_url">
        @error('job_url') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Status</label>
        <select name="status">
            <option value="applied">Applied</option>
            <option value="screening">Screening</option>
            <option value="interviewing">Interviewing</option>
            <option value="offer">Offer</option>
            <option value="rejected">Rejected</option>
            <option value="ghosted">Ghosted</option>
        </select>
        @error('status') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Applied At</label>
        <input type="date" name="applied_at">
        @error('applied_at') <p>{{ $message }}</p> @enderror
    </div>

    <button type="submit">Submit</button>
</form>
