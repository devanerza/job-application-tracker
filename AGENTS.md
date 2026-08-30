# AGENTS.md — Job Application Tracker Rebuild Plan

This document defines the complete rebuild of the Job Application Tracker ("Stride") based on `PRD.md`. It maps every change needed across database, backend, and frontend phases, with specific file-level instructions.

**Current state:** ~15-20% complete. Basic CRUD works, but the core intelligence layer (follow-up engine, ghosting detection, health states, action dashboard) is unbuilt. The UI library is still shadcn/Radix — must switch to daisyUI. Many files have bugs, commented-out code, and incomplete implementations.

**UI Workflow:** All `.jsx` frontend UI files (pages, components, layouts) will be **provided by the user (Devan)**. The agent's role is to **integrate, re-touch, and adjust** these files as needed — wiring up Inertia props, fixing imports, ensuring daisyUI class usage, and connecting to backend routes/controllers. The agent should NOT write UI from scratch; instead, wait for the user to provide each `.jsx` file, then adapt it to fit the backend and data layer.

---

## Table of Contents

1. [Phase 0 — Cleanup & Foundation](#phase-0--cleanup--foundation)
2. [Phase 1 — Application Lifecycle (MVP Core)](#phase-1--application-lifecycle-mvp-core)
3. [Phase 2 — Intelligence Layer](#phase-2--intelligence-layer)
4. [Phase 3 — Automation](#phase-3--automation)
5. [Phase 4 — Interview Management](#phase-4--interview-management)
6. [Phase 5 — Insights & Analytics](#phase-5--insights--analytics)
7. [UI Overhaul — daisyUI Migration](#ui-overhaul--daisyui-migration)
8. [Testing Strategy](#testing-strategy)
9. [File Reference](#file-reference)

---

## Phase 0 — Cleanup & Foundation

**Goal:** Remove dead code, fix bugs, establish a clean base before building new features.

### 0.1 Remove unused files

| Action | File | Reason |
|--------|------|--------|
| DELETE | `resources/views/dashboard.blade.php` | Unused Blade leftover |
| DELETE | `resources/views/layouts/app.blade.php` | Unused Blade layout |
| DELETE | `resources/views/layouts/guest.blade.php` | Unused Blade layout |
| DELETE | `resources/views/profile/` (entire dir) | Unused Blade profile views |
| DELETE | `resources/js/Pages/Welcome.jsx` | Default Breeze welcome page |
| DELETE | `resources/js/Pages/Dashboard.jsx` | Default Breeze placeholder |
| DELETE | `resources/js/Components/ApplicationLogo.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/Checkbox.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/DangerButton.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/Dropdown.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/InputError.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/InputLabel.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/NavLink.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/PrimaryButton.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/ResponsiveNavLink.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/SecondaryButton.jsx` | Unused Breeze component |
| DELETE | `resources/js/Components/TextInput.jsx` | Unused Breeze component |
| DELETE | `resources/js/hooks/use-mobile.jsx` | Unused shadcn helper |
| DELETE | `resources/js/Pages/FollowUp/` (empty dir) | Empty placeholder |
| DELETE | `resources/js/Pages/Applications/Components/date-picker-follUp.jsx` | Commented out, unused |
| DELETE | `resources/js/Pages/Applications/Components/date-picker-apply.jsx` | Buggy, replaced by daisyUI date input |
| DELETE | `components.json` | shadcn/ui config — switching to daisyUI |
| DELETE | `resources/js/lib/utils.js` | `cn()` helper — daisyUI uses different approach |

### 0.2 Remove all shadcn/ui components

Delete entire `resources/js/Components/ui/` directory (16 files):
- `button.jsx`, `calendar.jsx`, `dialog.jsx`, `dropdown-menu.jsx`, `input-group.jsx`, `input.jsx`, `label.jsx`, `popover.jsx`, `select.jsx`, `separator.jsx`, `sheet.jsx`, `sidebar.jsx`, `skeleton.jsx`, `table.jsx`, `textarea.jsx`, `tooltip.jsx`

### 0.3 Fix existing bugs

| File | Bug | Fix |
|------|-----|-----|
| `app/Http/Controllers/ApplicationController.php` | `create()` returns Blade view | Change to return Inertia render |
| `app/Http/Controllers/ApplicationController.php` | `edit()` returns Blade view + missing auth check | Change to Inertia render + add `$this->authorize('update', $application)` |
| `app/Http/Controllers/ApplicationController.php` | `update()` sets `last_activity_at = applied_at` | Change to `$application->update([... 'last_activity_at' => now()])` |
| `app/Http/Controllers/ApplicationController.php` | `dd()` left commented out | Remove entirely |
| `resources/js/Pages/Applications/Components/dialog-edit.jsx` | `console.log(data)` left in | Remove |
| `resources/js/Layouts/AuthenticatedLayout.jsx` | Large block of commented-out old Breeze layout | Remove all commented-out code |
| `database/factories/ApplicationFactory.php` | Uses old status values (`pending`, `approved`, `rejected`) | Update to match current enum: `applied`, `screening`, `interviewing`, `offer`, `rejected`, `ghosted` |

### 0.4 Clean up Application model

**File:** `app/Models/Application.php`

- Remove `$fillable` entries that don't exist yet — only keep `company_name`, `role_title`, `job_url`, `status`, `applied_at`
- Add `last_activity_at` and `follow_up_at` to `$casts` as `datetime`
- Remove the `filterSort` scope (will be replaced by a proper query builder approach)

### 0.5 Clean up routes

**File:** `routes/web.php`

- Remove the `GET /` welcome route (no Welcome page)
- Ensure dashboard route points to the new Dashboard (Phase 2)
- Remove `GET /api/reminders-test` from `routes/api.php` (test route, not needed)

### 0.6 Remove `.env` from git

```bash
git rm --cached .env
```

Ensure `.env` is in `.gitignore`.

---

## Phase 1 — Application Lifecycle (MVP Core)

**Goal:** Complete CRUD, proper status lifecycle, activity timeline, application detail page, and document tracking fields.

### 1.1 Database — Extend `applications` table

**New migration:** `database/migrations/xxxx_add_details_to_applications_table.php`

Add columns:
```
location          — string, nullable
employment_type   — enum('full_time','part_time','internship','contract','freelance'), nullable
salary_range      — string, nullable (store as text like "$80k-$100k")
source            — enum('linkedin','company_website','job_board','referral','other'), nullable
resume_version    — string, nullable (e.g., "v3")
cover_letter_version — string, nullable
notes             — text, nullable
```

### 1.2 Database — Create `activities` table

**New migration:** `database/migrations/xxxx_create_activities_table.php`

```
id              — bigint, auto-increment
application_id  — foreign ID, cascade delete
user_id         — foreign ID, cascade delete
type            — enum('application_submitted','email_sent','email_received',
                       'recruiter_contacted','recruiter_response','screening',
                       'interview_scheduled','interview_completed','technical_test',
                       'offer_received','rejected','follow_up_sent','note',
                       'status_change')
title           — string (human-readable label, e.g., "Interview scheduled with Google")
description     — text, nullable (optional detail)
activity_date   — date (when it happened, user-selectable, defaults to today)
created_at      — timestamp
updated_at      — timestamp
```

**Index:** composite index on `application_id` + `activity_date`.

### 1.3 Database — Create `documents` table

**New migration:** `database/migrations/xxxx_create_documents_table.php`

```
id              — bigint, auto-increment
user_id         — foreign ID, cascade delete
name            — string (e.g., "Resume v3")
type            — enum('resume','cover_letter','portfolio')
file_path       — string, nullable (for future file uploads)
version_number  — integer, default 1
notes           — text, nullable
created_at      — timestamp
updated_at      — timestamp
```

### 1.4 Database — Create `application_document` pivot table

**New migration:** `database/migrations/xxxx_create_application_document_table.php`

```
application_id  — foreign ID, cascade delete
document_id     — foreign ID, cascade delete
```

Primary key: composite (`application_id`, `document_id`).

### 1.5 Update Application model

**File:** `app/Models/Application.php`

```php
// Add to $fillable:
'location', 'employment_type', 'salary_range', 'source',
'resume_version', 'cover_letter_version', 'notes'

// Add casts:
'last_activity_at' => 'datetime',
'follow_up_at' => 'datetime',
'applied_at' => 'date',

// Add relationships:
public function activities()
{
    return $this->hasMany(Activity::class)->orderBy('activity_date', 'desc');
}

public function documents()
{
    return $this->belongsToMany(Document::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

// Add helper:
public function latestActivity()
{
    return $this->activities()->first();
}
```

### 1.6 Create Activity model

**New file:** `app/Models/Activity.php`

```php
class Activity extends Model
{
    protected $fillable = [
        'application_id', 'user_id', 'type', 'title',
        'description', 'activity_date',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 1.7 Create Document model

**New file:** `app/Models/Document.php`

```php
class Document extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'file_path',
        'version_number', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class);
    }
}
```

### 1.8 Update User model

**File:** `app/Models/User.php`

Add relationships:
```php
public function activities()
{
    return $this->hasMany(Activity::class);
}

public function documents()
{
    return $this->hasMany(Document::class);
}
```

### 1.9 Rewrite ApplicationController

**File:** `app/Http/Controllers/ApplicationController.php`

Full rewrite. Key changes:

- `index()` — render Inertia `Applications/Index` with paginated, filterable data. Remove the auto-expire follow-up logic (moves to Phase 2).
- `create()` — return Inertia `Applications/Create` page.
- `store()` — validate all fields including new ones. Set `user_id` and `last_activity_at = now()`. Log an "Application submitted" activity automatically.
- `show($id)` — **NEW** — render Inertia `Applications/Show` detail page with activities, documents, health status.
- `edit($id)` — return Inertia `Applications/Edit` page. Add authorization.
- `update($id)` — validate all fields, update, set `last_activity_at = now()`. Add authorization. If `status` changed, auto-log a **milestone** entry in the activity timeline (PRD §4.1/§4.2) — visually distinct from regular user-logged activities.
- `destroy($id)` — delete with authorization.
- Remove `statusUpdate()` and `followUp()` methods (status changes now go through standard `update()`; follow-up logic moves to Phase 2).

**Validation rules for all application fields:**
```php
'company_name' => 'required|string|max:255',
'role_title' => 'required|string|max:255',
'job_url' => 'nullable|url|max:255',
'location' => 'nullable|string|max:255',
'employment_type' => 'nullable|in:full_time,part_time,internship,contract,freelance',
'salary_range' => 'nullable|string|max:255',
'source' => 'nullable|in:linkedin,company_website,job_board,referral,other',
'resume_version' => 'nullable|string|max:255',
'cover_letter_version' => 'nullable|string|max:255',
'notes' => 'nullable|string',
'applied_at' => 'required|date',
'status' => 'in:applied,screening,interviewing,offer,rejected,withdrawn,ghosted',
```

### 1.10 Create ActivityController

**New file:** `app/Http/Controllers/ActivityController.php`

```php
class ActivityController extends Controller
{
    // store() — create activity for an application
    //   - Validate: type, title, description (optional), activity_date
    //   - Set user_id from auth
    //   - After create, update application.last_activity_at = activity.activity_date (or now() if today)
    //   - Return back with success flash

    // destroy() — delete activity (with authorization)
}
```

### 1.11 Create DocumentController

**New file:** `app/Http/Controllers/DocumentController.php`

```php
class DocumentController extends Controller
{
    // index() — list user's documents
    // store() — create document (name, type, notes)
    // destroy() — delete document (detach from applications first)
}
```

### 1.12 Update routes

**File:** `routes/web.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ApplicationController::class, 'dashboard'])->name('dashboard');

    Route::resource('applications', ApplicationController::class);
    Route::post('applications/{application}/activities', [ActivityController::class, 'store'])
        ->name('applications.activities.store');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])
        ->name('activities.destroy');

    Route::resource('documents', DocumentController::class)->except(['show', 'edit', 'update']);
});
```

### 1.13 Frontend — Application List page (rewrite)

**File:** `resources/js/Pages/Applications/Index.jsx` — **PROVIDED BY USER**

Rewrite using daisyUI components (after Phase 7 UI migration). For now, build with Tailwind utility classes + daisyUI class names.

**Layout:** Use the authenticated layout with sidebar.

**Table columns:**
- Company + Role (linked to detail page)
- Status (daisyUI badge, neutral colors per PRD 10.2)
- Health (daisyUI badge with color — will be wired in Phase 2)
- Applied Date
- Last Activity
- Actions (View, Edit, Delete)

**Filters bar (top):**
- Search input (free-text, searches company + role)
- Status dropdown filter
- Source dropdown filter

### 1.14 Frontend — Application Create page (new)

**New file:** `resources/js/Pages/Applications/Create.jsx` — **PROVIDED BY USER**

Form fields:
- Company Name (required)
- Role Title (required)
- Job URL (optional)
- Location (optional)
- Employment Type (select)
- Salary Range (optional)
- Application Date (required, date input)
- Source (select)
- Resume Version (text input)
- Cover Letter Version (text input)
- Notes (textarea)

Use daisyUI `form-control`, `input`, `select`, `textarea` classes.

### 1.15 Frontend — Application Edit page (new)

**New file:** `resources/js/Pages/Applications/Edit.jsx` — **PROVIDED BY USER**

Same form as Create, pre-filled with existing data.

### 1.16 Frontend — Application Detail page (new)

**New file:** `resources/js/Pages/Applications/Show.jsx` — **PROVIDED BY USER**

This is the central page of the product (PRD 4.12). Must answer:
1. What happened? (Timeline)
2. Where am I? (Status / health)
3. What should I do next? (Next action)

**Sections:**
- **Header:** Company name, role title, current status badge, health badge, Edit button, Add Activity button
- **Next Action bar:** Derived message (e.g., "Follow up in 2 days" — wired in Phase 2, placeholder for now)
- **Application Details card:** Applied date, source, location, salary, employment type
- **Documents card:** Resume version, cover letter version
- **Activity Timeline:** Vertical timeline of all activities, ordered by date descending. Each entry shows: type icon, title, date, description. "Add Activity" button opens a modal.

### 1.17 Frontend — Activity modal (new)

**New file:** `resources/js/Pages/Applications/Components/ActivityModal.jsx` — **PROVIDED BY USER**

daisyUI `modal` with form:
- Activity Type (select from enum)
- Title (text)
- Description (textarea, optional)
- Date (date input, defaults to today)

### 1.18 Frontend — Authenticated Layout (rewrite)

**File:** `resources/js/Layouts/AuthenticatedLayout.jsx` — **PROVIDED BY USER**

- Remove all shadcn/SidebarProvider code
- Remove commented-out Breeze layout code
- Build clean layout with daisyUI: sidebar (`drawer` + `drawer-side`) + main content area
- Sidebar nav links: Dashboard, Applications, Documents
- Top bar: Search input, user dropdown (Profile, Logout)

### 1.19 Update ApplicationFactory

**File:** `database/factories/ApplicationFactory.php`

```php
return [
    'user_id' => User::factory(),
    'company_name' => fake()->company(),
    'role_title' => fake()->jobTitle(),
    'job_url' => fake()->url(),
    'status' => fake()->randomElement(['applied', 'screening', 'interviewing', 'offer', 'rejected', 'withdrawn', 'ghosted']),
    'applied_at' => fake()->dateTimeBetween('-60 days', 'now'),
    'last_activity_at' => now(),
    'location' => fake()->city(),
    'employment_type' => fake()->randomElement(['full_time', 'part_time', 'contract']),
    'salary_range' => fake()->randomElement(['$60k-$80k', '$80k-$100k', '$100k-$120k', null]),
    'source' => fake()->randomElement(['linkedin', 'company_website', 'job_board', 'referral', 'other']),
];
```

---

## Phase 2 — Intelligence Layer

**Goal:** Implement the follow-up engine, ghosting detection, application health states, and the Dashboard/Action Center.

### 2.1 Create `follow_up_policies` config

**File:** `config/followup.php` (new)

```php
return [
    'windows' => [
        'applied' => 7,       // days of inactivity before follow-up
        'screening' => 5,
        'interviewing' => 3,
        'offer' => null,      // no automated follow-up
        'rejected' => null,
        'withdrawn' => null,
        'ghosted' => null,
    ],
    'ghosting' => [
        'inactivity_days' => 21,   // days of total inactivity
        'min_follow_ups' => 2,     // follow-ups sent before ghosting
    ],
];
```

### 2.2 Create Action classes

These are plain PHP classes invoked from controllers — no service layer.

**New file:** `app/Actions/DetermineNextAction.php`

```php
class DetermineNextAction
{
    public function execute(Application $application): ?string
    {
        // Returns a human-readable next action string:
        // - "Follow up — no response in X days"
        // - "Interview scheduled in X days"
        // - "Awaiting response"
        // - null (no action needed)
        //
        // Logic:
        // 1. If status is terminal (rejected/withdrawn/ghosted), return null
        // 2. If interview upcoming, return "Interview in X days"
        // 3. Calculate days since last_activity_at
        // 4. If days >= follow_up_window for current status, return "Follow up needed"
        // 5. Otherwise return "Awaiting response"
    }
}
```

**New file:** `app/Actions/EvaluateGhostingStatus.php`

```php
class EvaluateGhostingStatus
{
    public function execute(Application $application): bool
    {
        // Returns true if application should be considered ghosted.
        // Logic (from PRD 4.4):
        //   IF application is active (not already terminal)
        //   AND no activity for X days (from config)
        //   AND follow_up attempts >= Y (count activities where type = 'follow_up_sent')
        //   THEN return true
        //
        // Does NOT auto-change status — only returns a recommendation.
        // The caller decides whether to prompt the user.
    }
}
```

**New file:** `app/Actions/DetermineApplicationHealth.php`

```php
class DetermineApplicationHealth
{
    public function execute(Application $application): string
    {
        // Returns one of: 'healthy', 'needs_attention', 'stale', 'ghosted'
        //
        // Logic (maps to PRD 4.10):
        // - ghosted: status is ghosted OR EvaluateGhostingStatus returns true
        // - stale: inactivity >= 14 days OR multiple follow-ups with no response
        // - needs_attention: follow-up is due (days since last_activity >= window for status)
        // - healthy: none of the above
    }
}
```

**New file:** `app/Actions/CalculateFollowUpDueDate.php`

```php
class CalculateFollowUpDueDate
{
    public function execute(Application $application): ?Carbon
    {
        // Returns the date when a follow-up is next due, or null.
        // Formula: last_activity_at + follow_up_window_days (from config/followup.php)
        // If status has no window (null), returns null.
        // If result is in the past, returns today (follow-up is due now).
    }
}
```

### 2.3 Update ApplicationController for Phase 2

**File:** `app/Http/Controllers/ApplicationController.php`

Add `dashboard()` method:
```php
public function dashboard()
{
    $applications = Application::where('user_id', auth()->id())->get();

    // Enrich each application with health + next action
    $enriched = $applications->map(function ($app) {
        $app->health = (new DetermineApplicationHealth)->execute($app);
        $app->next_action = (new DetermineNextAction)->execute($app);
        $app->follow_up_due = (new CalculateFollowUpDueDate)->execute($app);
        return $app;
    });

    return Inertia::render('Dashboard', [
        'followUpsDue' => $enriched->filter(fn ($a) => $a->follow_up_due && $a->follow_up_due->isPast()),
        'upcomingInterviews' => $this->getUpcomingInterviews(), // Phase 4
        'summary' => [
            'active' => $enriched->where('health', '!=', 'ghosted')->count(),
            'needs_attention' => $enriched->where('health', 'needs_attention')->count(),
            'stale' => $enriched->where('health', 'stale')->count(),
            'ghosted' => $enriched->where('health', 'ghosted')->count(),
        ],
    ]);
}
```

Also enrich `show()` with health and next action data.

### 2.4 Frontend — Dashboard (new, replacing Breeze placeholder)

**File:** `resources/js/Pages/Dashboard.jsx` — **PROVIDED BY USER**

**Sections (PRD 4.5):**
1. **Follow-ups Due Today** — list of cards with company/role, days since last activity, [Follow up] [Snooze] buttons
2. **Upcoming Interviews** — list of cards with company/role, date/time, [View application] button
3. **Summary counts** — N active, N waiting, N needs attention, N ghosted

This is the homepage. Use daisyUI `card` components with health-colored left-border accents (not full colored backgrounds — per PRD 10.1 calm-by-default principle).

### 2.5 Follow-up actions (frontend)

**New file:** `resources/js/Pages/Applications/Components/FollowUpActions.jsx` — **PROVIDED BY USER**

When user clicks [Follow up]:
- Open modal to log a "follow_up_sent" activity

When user clicks [Snooze]:
- Set `follow_up_at` to today + 3 days (hardcoded for MVP)
- Log a "note" activity: "Follow-up snoozed"

### 2.6 Status change confirmation modal (new)

**New file:** `resources/js/Pages/Applications/Components/StatusTransitionModal.jsx` — **PROVIDED BY USER**

Per PRD 4.1: "Status changes are deliberate, irreversible actions."

When the user selects a new status from the dropdown on the application form:
1. A confirmation alert modal appears (e.g. "Move this application from Applied to Interview? This can't be undone.")
2. If confirmed, the status update is committed — treated as irreversible (moving "backward" is a new forward action, not an undo)
3. If cancelled, the status reverts to its previous value
4. The confirmed status change is automatically logged as a **milestone** entry in the Activity Timeline (§4.2) — visually distinct from regular user-logged activities

### 2.7 Update routes for Phase 2

```php
Route::patch('/applications/{application}/snooze', [ApplicationController::class, 'snooze'])
    ->name('applications.snooze');
```

Note: status changes go through the standard `PATCH /applications/{application}` update route — no separate status route needed.

### 2.8 Auto-expire follow-ups

**New file:** `app/Actions/ExpireFollowUps.php`

```php
class ExpireFollowUps
{
    public function execute(User $user): void
    {
        // For all active applications where follow_up_at < today
        // and status is terminal: clear follow_up_at
        // This runs on every dashboard visit (lightweight) and via scheduler (Phase 3).
    }
}
```

---

## Phase 3 — Automation

**Goal:** Scheduled follow-up processing, email reminders, automatic health/status updates.

### 3.1 Schedule the `SendFollowUpReminders` command

**File:** `routes/console.php`

```php
use App\Console\Commands\SendFollowUpReminders;

Schedule::daily()->at('09:00')->command(SendFollowUpReminders::class);
```

### 3.2 Enhance `SendFollowUpReminders` command

**File:** `app/Console/Commands/SendFollowUpReminders.php`

Rewrite to use the intelligence layer:
```php
// For each user:
//   For each active application:
//     1. Evaluate ghosting status
//     2. If ghosted and not yet marked, prompt user (email)
//     3. Calculate follow-up due date
//     4. If follow-up is due today, send reminder
//     5. Update application health
```

### 3.3 Enhance FollowUpReminder mailable

**File:** `app/Mail/FollowUpReminder.php`

Update to include:
- Application details (company, role, status)
- Days since last activity
- Health status
- Quick action links (optional — deep links to the app)

### 3.4 Update email template

**File:** `resources/views/email/follow-up-reminder.blade.php`

Redesign with application context, not just a generic reminder.

### 3.5 Stale application detection job

**New file:** `app/Jobs/DetectStaleApplications.php`

```php
// Runs daily via scheduler
// For each active application with no activity in 14+ days:
//   Set health to 'stale'
//   (Future: send "this application may be going cold" notification)
```

### 3.6 Ghosting auto-detection job

**New file:** `app/Jobs/DetectGhostedApplications.php`

```php
// Runs daily via scheduler
// For each active application:
//   Run EvaluateGhostingStatus
//   If ghosted: notify user that application may be ghosted (prompt to update status via dropdown)
```

---

## Phase 4 — Interview Management

**Goal:** Structured interview records linked to applications.

### 4.1 Database — Create `interviews` table

**New migration:** `database/migrations/xxxx_create_interviews_table.php`

```
id              — bigint, auto-increment
application_id  — foreign ID, cascade delete
user_id         — foreign ID, cascade delete
type            — enum('technical','behavioral','screening','system_design','other')
title           — string, nullable (e.g., "Round 2 - System Design")
interviewer_name — string, nullable
interviewer_email — string, nullable
scheduled_at    — datetime (date and time of interview)
duration_minutes — integer, nullable (default 60)
meeting_url     — string, nullable
notes           — text, nullable
preparation_checklist — json, nullable (array of {item, checked} objects)
created_at      — timestamp
updated_at      — timestamp
```

**Index:** `scheduled_at` for querying upcoming interviews.

### 4.2 Create Interview model

**New file:** `app/Models/Interview.php`

```php
class Interview extends Model
{
    protected $fillable = [
        'application_id', 'user_id', 'type', 'title',
        'interviewer_name', 'interviewer_email', 'scheduled_at',
        'duration_minutes', 'meeting_url', 'notes',
        'preparation_checklist',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'preparation_checklist' => 'array',
        'duration_minutes' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 4.3 Add relationship to Application model

```php
public function interviews()
{
    return $this->hasMany(Interview::class)->orderBy('scheduled_at', 'desc');
}
```

### 4.4 Create InterviewController

**New file:** `app/Http/Controllers/InterviewController.php`

```php
class InterviewController extends Controller
{
    // store() — create interview for an application
    //   - Automatically log "Interview scheduled" activity
    // update() — edit interview details
    // destroy() — delete interview
}
```

### 4.5 Update routes

```php
Route::resource('applications.interviews', InterviewController::class)
    ->except(['index', 'show']);
```

### 4.6 Frontend — Interview form modal

**New file:** `resources/js/Pages/Applications/Components/InterviewModal.jsx` — **PROVIDED BY USER**

daisyUI modal with fields:
- Type (select)
- Title (text)
- Interviewer Name (text)
- Interviewer Email (text)
- Date & Time (datetime-local input)
- Duration (number, minutes)
- Meeting URL (text)
- Notes (textarea)
- Preparation Checklist (dynamic list of items with add/remove)

### 4.7 Frontend — Interview cards on Application Detail

**File:** `resources/js/Pages/Applications/Show.jsx`

Add "Interviews" section below the activity timeline:
- Cards for each interview with type badge, date/time, interviewer, meeting link
- Past interviews grayed out, upcoming highlighted
- [Edit] [Delete] actions

### 4.8 Frontend — Upcoming interviews on Dashboard

**File:** `resources/js/Pages/Dashboard.jsx`

Wire up the "Upcoming Interviews" section with real data from `Interview` model.

---

## Phase 5 — Insights & Analytics

**Goal:** Decision-supporting metrics beyond raw counts.

### 5.1 Create AnalyticsService

**New file:** `app/Services/AnalyticsService.php`

```php
class AnalyticsService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function funnel(): array
    {
        // Count applications at each stage:
        // Applied -> Screening -> Interviewing -> Offer
        // Returns: ['applied' => N, 'screening' => N, ...]
    }

    public function responseRate(): float
    {
        // Responses (screening + interviewing + offer + rejected) / total applications
    }

    public function interviewConversionRate(): float
    {
        // Interviews / responses
    }

    public function sourceEffectiveness(): array
    {
        // Per source: total applications, interviews generated, interview rate
        // Returns: ['linkedin' => ['total' => N, 'interviews' => N, 'rate' => N%], ...]
    }

    public function resumeEffectiveness(): array
    {
        // Per resume version: total applications, interviews generated, interview rate
        // Returns: ['v3' => ['total' => N, 'interviews' => N, 'rate' => N%], ...]
    }
}
```

### 5.2 Create AnalyticsController

**New file:** `app/Http/Controllers/AnalyticsController.php`

```php
class AnalyticsController extends Controller
{
    public function index()
    {
        $service = new AnalyticsService(auth()->user());
        return Inertia::render('Analytics/Index', [
            'funnel' => $service->funnel(),
            'responseRate' => $service->responseRate(),
            'interviewConversion' => $service->interviewConversionRate(),
            'sourceEffectiveness' => $service->sourceEffectiveness(),
            'resumeEffectiveness' => $service->resumeEffectiveness(),
        ]);
    }
}
```

### 5.3 Frontend — Analytics page

**New file:** `resources/js/Pages/Analytics/Index.jsx` — **PROVIDED BY USER**

Sections:
- **Funnel visualization** — horizontal bar or funnel chart (source/resume effectiveness)
- **Key metrics cards** — response rate, interview conversion rate
- **Source breakdown** — table or bar chart of applications and interview rate per source
- **Resume effectiveness** — table of interview rate per resume version

Use daisyUI `stat` components for metric cards. For charts, use a lightweight library (e.g., `recharts` or CSS-based bars — avoid heavy charting libraries per the "calm by default" principle).

### 5.4 Update routes

```php
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
```

### 5.5 Update sidebar navigation

**File:** `resources/js/Layouts/AuthenticatedLayout.jsx`

Add "Analytics" link to sidebar nav.

---

## UI Overhaul — daisyUI Migration

**Goal:** Replace all shadcn/Radix components with daisyUI. This is done incrementally during Phases 1-5 but has a dedicated migration step.

### M.1 Install daisyUI

```bash
npm install daisyui
```

### M.2 Update Tailwind config

**File:** `tailwind.config.js`

- Remove all shadcn/ui CSS variable themes
- Remove `tailwindcss-animate` plugin
- Add `daisyui` plugin
- Configure daisyUI themes — use M3-inspired calm blue theme

```js
// tailwind.config.js
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('daisyui'),
    ],
    daisyui: {
        themes: ['light', 'dark'],
        darkTheme: 'dark',
    },
};
```

### M.3 Update CSS

**File:** `resources/css/app.css`

Replace all shadcn CSS variables with:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
```

### M.4 Replace components per page

Replace shadcn components with daisyUI equivalents during page rewrites:

| shadcn component | daisyUI equivalent |
|-----------------|-------------------|
| `<Button>` | `<button class="btn btn-primary">` |
| `<Dialog>` | `<dialog class="modal">` |
| `<DropdownMenu>` | `<div class="dropdown">` |
| `<Input>` | `<input class="input input-bordered">` |
| `<Select>` | `<select class="select select-bordered">` |
| `<Textarea>` | `<textarea class="textarea textarea-bordered">` |
| `<Table>` | `<table class="table">` |
| `<Badge>` | `<span class="badge">` |
| `<Separator>` | `<div class="divider">` |
| `<Skeleton>` | `<div class="skeleton">` |
| `<Tooltip>` | `<div class="tooltip">` |
| `<Popover>` | `<div class="dropdown">` or custom |
| `<Sheet>` | `<div class="drawer drawer-end">` |

### M.5 Remove npm dependencies no longer needed

```bash
npm uninstall @radix-ui/react-dialog @radix-ui/react-dropdown-menu \
  @radix-ui/react-label @radix-ui/react-popover @radix-ui/react-select \
  @radix-ui/react-separator @radix-ui/react-slot @radix-ui/react-tooltip \
  class-variance-authority clsx tailwind-merge tailwindcss-animate \
  react-day-picker
```

### M.6 M3-inspired theme customization

For daisyUI's theme system, create a custom theme in `tailwind.config.js`:

```js
daisyui: {
    themes: [
        {
            stride: {
                "primary": "#4f46e5",        // Indigo — calm, focused
                "primary-content": "#ffffff",
                "secondary": "#f59e0b",       // Amber — attention without alarm
                "secondary-content": "#1f2937",
                "accent": "#10b981",          // Green — healthy/active
                "accent-content": "#ffffff",
                "neutral": "#374151",
                "neutral-content": "#f3f4f6",
                "base-100": "#f9fafb",        // Very light gray — calm surface
                "base-200": "#f3f4f6",
                "base-300": "#e5e7eb",
                "base-content": "#1f2937",
                "info": "#3b82f6",
                "success": "#10b981",
                "warning": "#f59e0b",
                "error": "#ef4444",
            },
        },
    ],
},
```

Health color mapping in the UI:
- Healthy/Active: `badge-success`
- Needs Attention: `badge-warning`
- Stale/At Risk: `badge-error badge-outline` (lower emphasis)
- Likely Ghosted: `badge-error`

---

## Testing Strategy

### Unit tests (Phase 1+)

| Test file | Tests |
|-----------|-------|
| `tests/Unit/Actions/DetermineNextActionTest.php` | Test each status path |
| `tests/Unit/Actions/EvaluateGhostingStatusTest.php` | Test ghosting thresholds |
| `tests/Unit/Actions/DetermineApplicationHealthTest.php` | Test health state derivation |
| `tests/Unit/Actions/CalculateFollowUpDueDateTest.php` | Test date calculations |
| `tests/Unit/Models/ApplicationTest.php` | Relationships, scopes, casts |
| `tests/Unit/Models/ActivityTest.php` | Relationships, casts |
| `tests/Unit/Models/DocumentTest.php` | Relationships |
| `tests/Unit/Models/InterviewTest.php` | Relationships, casts |
| `tests/Unit/Services/AnalyticsServiceTest.php` | All analytics methods |

### Feature tests (Phase 1+)

| Test file | Tests |
|-----------|-------|
| `tests/Feature/ApplicationCrudTest.php` | Create, read, update, delete applications with auth |
| `tests/Feature/ActivityManagementTest.php` | Log activities, verify timeline |
| `tests/Feature/StatusTransitionTest.php` | Status changes with confirmation |
| `tests/Feature/DocumentManagementTest.php` | CRUD documents, attach to applications |
| `tests/Feature/InterviewManagementTest.php` | CRUD interviews, linked to applications |
| `tests/Feature/DashboardTest.php` | Dashboard renders with correct data |
| `tests/Feature/AnalyticsTest.php` | Analytics page renders with computed data |

### Update existing test

**File:** `tests/Feature/ExampleTest.php`

Update or remove — the default `GET /` test hits the welcome page which is being deleted.

---

## File Reference

### Files to DELETE

```
resources/views/dashboard.blade.php
resources/views/layouts/app.blade.php
resources/views/layouts/guest.blade.php
resources/views/profile/ (entire directory)
resources/js/Pages/Welcome.jsx
resources/js/Pages/Dashboard.jsx (replaced by new implementation)
resources/js/Components/ApplicationLogo.jsx
resources/js/Components/Checkbox.jsx
resources/js/Components/DangerButton.jsx
resources/js/Components/Dropdown.jsx
resources/js/Components/InputError.jsx
resources/js/Components/InputLabel.jsx
resources/js/Components/NavLink.jsx
resources/js/Components/PrimaryButton.jsx
resources/js/Components/ResponsiveNavLink.jsx
resources/js/Components/SecondaryButton.jsx
resources/js/Components/TextInput.jsx
resources/js/Components/ui/ (entire directory — 16 files)
resources/js/hooks/use-mobile.jsx
resources/js/Pages/FollowUp/ (empty directory)
resources/js/Pages/Applications/Components/date-picker-apply.jsx
resources/js/Pages/Applications/Components/date-picker-follUp.jsx
resources/js/lib/utils.js
components.json
```

### Files to CREATE

```
app/Models/Activity.php
app/Models/Document.php
app/Models/Interview.php
app/Http/Controllers/ActivityController.php
app/Http/Controllers/DocumentController.php
app/Http/Controllers/InterviewController.php
app/Http/Controllers/AnalyticsController.php
app/Actions/DetermineNextAction.php
app/Actions/EvaluateGhostingStatus.php
app/Actions/DetermineApplicationHealth.php
app/Actions/CalculateFollowUpDueDate.php
app/Actions/ExpireFollowUps.php
app/Jobs/DetectStaleApplications.php
app/Jobs/DetectGhostedApplications.php
app/Services/AnalyticsService.php
config/followup.php
resources/js/Pages/Applications/Create.jsx
resources/js/Pages/Applications/Edit.jsx
resources/js/Pages/Applications/Show.jsx
resources/js/Pages/Applications/Components/ActivityModal.jsx
resources/js/Pages/Applications/Components/InterviewModal.jsx
resources/js/Pages/Applications/Components/FollowUpActions.jsx
resources/js/Pages/Applications/Components/StatusTransitionModal.jsx
resources/js/Pages/Analytics/Index.jsx
database/migrations/xxxx_add_details_to_applications_table.php
database/migrations/xxxx_create_activities_table.php
database/migrations/xxxx_create_documents_table.php
database/migrations/xxxx_create_application_document_table.php
database/migrations/xxxx_create_interviews_table.php
tests/Unit/Actions/DetermineNextActionTest.php
tests/Unit/Actions/EvaluateGhostingStatusTest.php
tests/Unit/Actions/DetermineApplicationHealthTest.php
tests/Unit/Actions/CalculateFollowUpDueDateTest.php
tests/Unit/Models/ApplicationTest.php
tests/Unit/Models/ActivityTest.php
tests/Unit/Models/DocumentTest.php
tests/Unit/Models/InterviewTest.php
tests/Unit/Services/AnalyticsServiceTest.php
tests/Feature/ApplicationCrudTest.php
tests/Feature/ActivityManagementTest.php
tests/Feature/StatusTransitionTest.php
tests/Feature/DocumentManagementTest.php
tests/Feature/InterviewManagementTest.php
tests/Feature/DashboardTest.php
tests/Feature/AnalyticsTest.php
```

### Files to REWRITE (major changes)

```
app/Http/Controllers/ApplicationController.php
app/Models/Application.php
app/Models/User.php
resources/js/Pages/Applications/Index.jsx
resources/js/Layouts/AuthenticatedLayout.jsx
resources/css/app.css
tailwind.config.js
package.json
routes/web.php
routes/console.php
database/factories/ApplicationFactory.php
resources/js/Pages/Dashboard.jsx (entirely new content)
```

### Files to EDIT (minor changes)

```
app/Console/Commands/SendFollowUpReminders.php
app/Mail/FollowUpReminder.php
resources/views/email/follow-up-reminder.blade.php
resources/js/app.jsx (update layout import if needed)
routes/api.php (remove test route)
.gitignore (ensure .env is listed)
```

---

## Execution Order

Follow this sequence to avoid breaking changes:

1. **Phase 0** — Clean up first. Remove dead code, fix bugs, establish clean base.
2. **M.1-M.3** — Install daisyUI and update Tailwind config + CSS (do this early so all new code uses it).
3. **Phase 1** — Database migrations, models, controllers, routes. Build all pages with daisyUI.
4. **M.4-M.6** — Complete the daisyUI migration across all remaining pages. Remove shadcn deps.
5. **Phase 2** — Intelligence layer (actions, dashboard, health).
6. **Phase 3** — Automation (scheduler, jobs, emails).
7. **Phase 4** — Interview management.
8. **Phase 5** — Analytics.
9. **Tests** — Write tests throughout, but bulk up coverage after Phase 2.

**Run `php artisan migrate` after each database migration.**
**Run `npm run build` after each frontend change to verify compilation.**
**Run `php artisan test` after each backend change to verify no regressions.**
