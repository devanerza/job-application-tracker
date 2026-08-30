# Product Requirements Document
## Job Application Tracker — "Action-Oriented" Rebuild

**Status:** Draft v1
**Author:** Devan
**Date:** August 28, 2026

---

## 1. Overview

### 1.1 Problem Statement
Job seekers currently track applications in spreadsheets or generic tools (Notion, Excel). These tools are excellent at storage but provide no intelligence: they don't tell the user what needs attention, when to follow up, or whether an application has likely gone cold. As a result, users lose track of active opportunities, miss follow-up windows, and have no visibility into what's actually working in their job search.

### 1.2 Product Vision
> The app continuously interprets the state of the user's job applications and tells them what needs attention next.

This is not a CRUD table for job applications. It is a system that turns raw application data into decisions and next actions.

### 1.3 North Star Test
For any proposed feature, ask:
> Does this feature help the user decide or take action on their job search?

If yes — consider it. If it's just another way to store or display information a spreadsheet could hold — cut it.

### 1.4 Goals
- Give users a single place to see what needs action *today*.
- Automatically surface follow-ups based on real inactivity, not manual reminders.
- Detect likely-ghosted applications and stop pointless automation on them.
- Provide a reliable history (timeline) of every application.
- Surface insights (funnel, response rate, source effectiveness) once enough data exists.

### 1.5 Non-Goals
The following are explicitly out of scope, to prevent the product from becoming a bloated Notion/CRM/calendar clone:
- Generic, freely customizable Kanban boards
- Arbitrary/custom user-defined fields ("Add property")
- A general-purpose notes/pages/blocks system
- A full calendar product (only interview + follow-up dates are needed)
- A job board / job discovery product
- A full CRM for recruiter relationship management

---

## 2. Target User

- Primary user: an individual actively job hunting, managing multiple applications in parallel (assumed persona based on the source material: a developer/tech job seeker, though the model generalizes).
- Single-user usage per account (no team/collaboration requirement implied by the source material).

---

## 3. Core Product Loop

```
Find job → Save application → Track progress → Know what needs attention
→ Take action → Record activity → System determines next action
→ Eventually close / archive application
```

Every feature in this PRD should map back to a step in this loop.

---

## 4. Functional Requirements

### 4.1 Application Management (Core)

**Create Application**
Captured fields (kept deliberately minimal — only what affects decisions/actions):
- Company
- Job title
- Job URL
- Location
- Employment type (Full-time / Part-time / Internship / Contract / Freelance)
- Salary range (optional)
- Application date
- Source (LinkedIn / Company website / Job board / Referral / Other)
- Resume version used
- Cover letter version used
- Notes

Design principle: do not add metadata fields just because a spreadsheet could hold them. Only store information that affects a decision or an action.

**Edit / Delete / List Applications**
- Standard edit and delete.
- List view with filtering/search (see 4.9).

**Application Status — Controlled Lifecycle**
Primary path:
```
Saved → Applied → Screening → Interview → Offer → Accepted
```
Terminal/exception outcomes:
```
Rejected
Withdrawn
Ghosted
```
Requirement: status is not a freely editable dropdown. Transitions should represent real events in the process (e.g., moving to "Interview" should be tied to an interview being scheduled/recorded, not an arbitrary click).

**Transition model: user-confirmed.** The system can *suggest* a status transition based on activity (e.g., logging an "Interview scheduled" activity suggests moving status to Interview), but the user must explicitly confirm the transition. Status never changes silently/automatically in the background.

---

### 4.2 Activity Timeline

Every meaningful interaction on an application is logged as an **activity**, building a chronological history rather than a single "last activity" date.

Activity types include (not exhaustive):
- Application submitted
- Email sent / Email received
- Recruiter contacted / Recruiter response
- Screening
- Interview scheduled / Interview completed
- Technical test
- Offer received
- Rejected
- Follow-up sent
- Note

Requirements:
- Every application has a visible, ordered timeline of its activities.
- `last_activity_at` is derived from the most recent activity, not manually set.

---

### 4.3 Follow-up Engine

The system determines when a follow-up is needed based on inactivity, not a manually stored date.

**Formula:**
```
last_activity_at + follow-up policy (based on current status) = next follow-up
```

**Status-based follow-up windows (example defaults, tunable):**
| Status | Follow-up after inactivity of |
|---|---|
| Applied | 7 days |
| Screening | 5 days |
| Interview | 3 days |
| Offer | No automated follow-up |
| Rejected | No follow-up |

**Configurability plan:** For MVP, these windows are fixed placeholders (hardcoded defaults, not exposed in the UI). Post-MVP, they should become user-configurable — either globally (a per-user default per status) or, later, per-application override. This is a Phase 2+ enhancement, not part of the initial follow-up engine build.

**User actions on a suggested follow-up:**
- Send follow-up (logs an activity)
- Mark as not needed
- Snooze

---

### 4.4 Ghosting Detection

The system distinguishes between "no response yet" and "likely ghosted" rather than requiring the user to manually flag it.

**Derivation logic (conceptual):**
```
IF application is active
AND no activity for X days
AND follow-up attempts >= Y
THEN ghosted = true (user can override)
```

**State progression:**
```
Active → Inactive → Follow-up needed → No response → Likely ghosted → Automation stops
```

Requirement: once an application is marked ghosted (system-derived or user override), the follow-up engine stops generating further follow-up prompts for it.

**Configurability plan:** For MVP, the inactivity threshold (X days) and follow-up attempt count (Y) are fixed placeholders. Post-MVP, these become user-configurable, following the same approach as the follow-up windows above.

---

### 4.5 Dashboard / Action Center

The homepage answers "What should I do today?" rather than displaying stats.

**Contents:**
- Follow-ups due today, each with company/role, days since last activity, and [Follow up] / [Snooze] actions
- Upcoming interviews, each with company/role, date/time, and [View application]
- Summary of applications needing attention (e.g., N active, N waiting for response, N likely ghosted)

Aggregate statistics (totals, counts) are secondary and can appear below the action items, not as the primary content.

---

### 4.6 Reminders & Notifications

Expands beyond follow-ups into a general reminder engine. Reminder types:
- Follow-up due
- Upcoming interview
- Application going stale (no update in N days)
- Ghosting warning (after N follow-ups with no response)

Delivery: email reminders (building on the existing scheduled-command pattern already in place: a scheduled follow-up job). Exact channel/scheduling mechanics to be defined during design — this PRD specifies behavior, not implementation.

---

### 4.7 Interview Management

Once an application reaches the Interview stage, it should support structured interview records:
- Interview type (e.g., Technical, Behavioral, Screening call)
- Date and time
- Interviewer name
- Meeting URL
- Notes
- Preparation checklist (freeform checklist items)

Upcoming interviews automatically surface on the Dashboard (4.5).

---

### 4.8 Application Documents (Versioned)

Rather than storing a single filename per document, the system tracks **versions** of resumes/cover letters/portfolios and which applications used which version.

Example:
```
Resume v3 — used for 12 applications
Resume v2 — used for 8 applications
```

When creating/editing an application, the user selects which resume/cover letter version was used. This enables the effectiveness analysis in 4.11 (source/resume effectiveness).

---

### 4.9 Search & Filtering

Standard, necessary functionality — not a differentiator, but required for usability.

**Filters:**
- Status
- Company
- Job title
- Date applied
- Source
- Location
- Follow-up due
- Interview upcoming
- Ghosted
- Active / inactive

**Search:** free-text search across company, job title, etc.

---

### 4.10 Application Health

Each active application is assigned a derived health state, converting raw data into an at-a-glance decision:

| State | Example condition |
|---|---|
| 🟢 Healthy / Active | Recent activity, no pending action |
| 🟡 Needs attention | Follow-up due |
| 🟠 Stale / At risk | Extended inactivity, multiple follow-ups sent |
| 🔴 Likely ghosted | Long inactivity, follow-up attempts exhausted |

Health state should be visible in list views, the application detail page, and the dashboard.

---

### 4.11 Analytics / Insights

Available once sufficient application volume exists. Moves beyond raw counts into decision-supporting metrics:

- **Funnel:** Applied → Screening → Interview → Offer, with counts at each stage
- **Response rate:** responses ÷ total applications
- **Interview conversion rate:** interviews ÷ responses
- **Source effectiveness:** applications and interviews generated per source (LinkedIn, company website, referral, etc.)
- **Resume/document effectiveness:** interview rate per resume version (enabled by 4.8)

---

### 4.12 Application Detail Page

The central page of the product. Must answer three questions at a glance:
1. What happened? (Timeline)
2. Where am I? (Status / health)
3. What should I do next? (Next action)

**Sections:**
- Header: company, role, current status/health, [Edit] / [Add activity] actions
- Next action (e.g., "Follow up in 2 days")
- Application details (applied date, source, location, salary, etc.)
- Documents used (resume/cover letter version)
- Full activity timeline

---

## 5. Explicitly Out of Scope (Guardrails)

To prevent scope creep into a generic productivity tool, the following will **not** be built:
- Freely draggable/customizable Kanban boards — the workflow already has meaningful, defined states
- Arbitrary custom fields / "Add property" functionality
- General-purpose notes system (pages, sub-pages, databases, blocks) — only application notes and activity notes are needed
- A full calendar product — only interview dates and follow-up dates are needed
- A job board / job discovery feature — the app tracks applications, it does not source them
- A full CRM for recruiters — basic recruiter name/email/LinkedIn is acceptable, but not a Salesforce-style system

---

## 6. Phased Rollout / MVP Definition

**Phase 1 — Application Lifecycle (MVP core)**
- Authentication
- Create / edit / delete / list applications
- Application status (controlled lifecycle)
- Application detail page
- Activity timeline
- Status transitions

**Phase 2 — Intelligence**
- `last_activity_at` derivation
- Follow-up rules engine
- Follow-up due detection
- Follow-up snooze
- Ghosting detection
- Next-action calculation
- Application health states
- *(Follow-up windows and ghosting thresholds ship as fixed placeholders in this phase; user-configurable settings are a later enhancement, not required for MVP)*

**Phase 3 — Automation**
- Scheduled follow-up processing
- Email reminders
- Upcoming interview reminders
- Automatic status/health updates

**Phase 4 — Interview Management**
- Interview records
- Interview date/time tracking
- Interview notes / preparation checklist
- Upcoming interviews on dashboard

**Phase 5 — Insights**
- Application funnel
- Response rate
- Interview conversion
- Source effectiveness
- Resume/document effectiveness

---

## 7. Success Metrics (proposed — to discuss)

- % of applications with at least one recorded activity within 3 days of creation
- % of due follow-ups actioned (sent/snoozed/dismissed) within 48 hours of surfacing
- Reduction in applications reaching "likely ghosted" without any follow-up attempt
- User return rate to the dashboard (daily/weekly active use)

---

## 8. Decisions Made

- **Status transitions:** user-confirmed. The system may suggest a transition based on activity, but the user must confirm it — no silent automatic status changes.
- **Follow-up windows & ghosting thresholds:** fixed placeholders for MVP; user-configurable in a later phase.

## 9. Open Questions

- What are the final default placeholder values for follow-up windows and ghosting thresholds (days/attempt counts) to ship with MVP?
- Should configurability (when built) be global per-user, or per-application override, or both?
- Single-user only, or should multi-device sync / sharing be considered later?
- What's the retention/archiving behavior for closed applications (Rejected/Withdrawn/Accepted/Ghosted)?

---

## 9. Tech Stack

- **Backend:** Laravel, Eloquent ORM, Laravel Breeze (authentication), MySQL
- **Domain logic:** kept lean — plain Action classes (e.g. `DetermineNextAction`, `EvaluateGhostingStatus`) invoked directly from controllers, rather than a heavy service/repository layer. Laravel's built-in scheduler/jobs/notifications are used for automation (Phase 3) rather than additional packages.
- **Frontend:** Inertia.js + React
- **UI components:** daisyUI (Tailwind-based), chosen over shadcn/Radix for simplicity — this app's UI is mostly cards, badges, timelines, and forms, which daisyUI covers directly with less code to own/maintain. A raw Radix primitive may be dropped in later for the rare case that needs it (e.g. a company/location combobox), but daisyUI end-to-end is the default for MVP.

*Note: this is a restructure/refinement of a previously abandoned project — the backend framework and database choice carry over from that version; the UI library is being switched from shadcn to daisyUI as part of this rebuild.*

**UI Workflow:** All `.jsx` frontend UI files (pages, components, layouts) are provided by the developer (Devan). The AI agent integrates, re-touches, and adjusts these files as needed — wiring up Inertia props, fixing imports, ensuring daisyUI class usage, and connecting to backend routes/controllers. The agent does not write UI from scratch; it waits for the developer to provide each `.jsx` file, then adapts it to fit the backend and data layer.

## 10. UI Design Guideline

Design reference: **Material Design 3 (M3)**. This section adapts M3's system (color roles, type scale, elevation, shape, motion) to this app's specific domain — an action-oriented tracker, not a generic CRUD admin panel. The guideline is written to be implemented with daisyUI (Tailwind) rather than Google's MD3 component library directly — treat M3 as the design token/system reference, daisyUI as the implementation layer.

### 10.1 Design Principles for This App

- **Status and health are the primary visual language.** Color, more than any other element, is what tells the user "this needs you" vs. "this is fine." M3's color-role system (not just raw brand colors) is used deliberately for this.
- **The dashboard is the face of the product.** Its visual hierarchy should read, at a glance: what's urgent → what's upcoming → what's just informational. Everything else (list views, detail pages) is secondary in visual weight.
- **Calm by default, alert when it matters.** Most of the UI (application lists, forms, documents) should be quiet and neutral. Color/emphasis is reserved for things that map to an action (follow-up due, ghosted, interview tomorrow) — not decoration.
- **No unnecessary chrome.** Consistent with the "don't build a Notion clone" principle in this PRD — the UI should avoid dense toolbars, sidebars-within-sidebars, or customization panels. Keep it closer to a focused task app than a database admin tool.

### 10.2 Color System

Follow M3's role-based color approach rather than hardcoded hex usage throughout the app: define **primary, secondary, tertiary, error**, plus **surface** and **surface-container** tiers, and derive on-* (text/icon) colors from each. Generate the full M3 tonal palette from one seed color rather than picking colors ad hoc.

**Seed color:** a calm, focused blue or blue-violet (M3 default-adjacent) — deliberately *not* an energetic/startup color, since this app's job is to reduce anxiety around a stressful process (job hunting), not to hype the user.

**Domain-specific mapping — Application Health (§4.10 of this PRD):**
| Health state | M3 role used | Rationale |
|---|---|---|
| 🟢 Healthy/Active | `tertiary` (calm green-leaning tone) | Positive, low-alert |
| 🟡 Needs attention | `secondary` or a warm amber container tone | Draws the eye without alarming |
| 🟠 Stale/At risk | `error` container (lower emphasis variant) | Signals real risk, not yet critical |
| 🔴 Likely ghosted | `error` (full emphasis) | Clearest signal, reserved for this state only |

Reserve full-saturation `error` strictly for "likely ghosted" and validation errors — if it's used anywhere else, its meaning as a signal gets diluted.

**Status badges (§4.1)** use neutral `surface-container` + `on-surface-variant` tones (not the health colors) — status is informational ("where am I"), health is actionable ("what do I do"). Keeping these visually distinct avoids the two systems blurring together.

### 10.3 Typography

Follow M3's type scale (display / headline / title / body / label, each with large/medium/small) rather than ad hoc font sizes.

- **Body/UI face:** a clean, highly legible grotesque (system font stack or Inter-equivalent) — this is a data/task tool used daily, legibility beats personality.
- **Numerals:** use tabular figures where numbers appear in lists/timelines (days since activity, counts) so they align cleanly in scans.
- Avoid a separate display/serif face — unlike a marketing site, this product has no "hero moment" to justify one; consistency across dashboard, lists, and detail pages matters more than a memorable type pairing.

### 10.4 Shape, Elevation & Layout

- **Shape:** M3's rounded-corner language (`shape.medium`/`shape.large` tokens) applies well to daisyUI's card/badge components — use consistent, moderate corner radius across cards, inputs, and modals. Avoid mixing sharp and heavily rounded elements.
- **Elevation:** use M3's elevation levels sparingly and purposefully — the dashboard's action cards (follow-ups due, upcoming interviews) can sit at a slightly higher elevation than passive content (stats, archived applications) to reinforce "this needs interaction."
- **Layout:** the Application Detail Page (§4.12) should follow M3's list/detail density guidance — comfortable spacing for the timeline (it's read sequentially), denser spacing for metadata fields (scanned, not read).
- **Density:** default to M3's "default" density, not "compact" — this isn't a power-user admin tool, occasional daily use favors more breathing room over information density.

### 10.5 Motion

Use motion only where M3 recommends it for state change and hierarchy, not for decoration:
- Status/health transitions (e.g., a card moving from "Needs attention" to resolved) can use a brief M3 emphasized-easing transition to reinforce that something changed.
- List reordering/filtering: standard M3 shared-axis transitions.
- Avoid animating the dashboard on every load — respect `prefers-reduced-motion`, and keep first-load fast and static; motion is for *changes in state*, not entrances.

### 10.6 Component Notes (daisyUI implementation)

- **Cards** (`card`): used for dashboard action items, application list rows in card view, and interview records. Health color applies to a left-border accent or a small status dot — not the full card background, to keep the UI calm (see 10.1).
- **Badges** (`badge`): used for status (neutral) and health (colored, per 10.2).
- **Timeline:** no native daisyUI timeline component maps perfectly to M3 — build a custom vertical timeline using M3 spacing/color tokens (a divider line + dot per M3's list guidance), since the Activity Timeline (§4.2) is a core, frequently-viewed element and deserves a bespoke treatment rather than a generic list.
- **Modals** (`modal`): used for status transition confirmation (§4.1) and follow-up actions (send/snooze/dismiss) — keep these short and single-purpose, consistent with M3 dialog guidance (one clear action, no multi-step forms inside a modal).
- **Forms:** M3 filled or outlined text field style, applied via daisyUI `input`/`select` variants — pick one variant and use it consistently across Create Application, Interview records, and Documents.

### 10.7 Accessibility Baseline

- All color-coded health/status states must have a non-color indicator too (icon or label) — color alone cannot be the only signal, both for accessibility and because health colors (10.2) carry real decision weight.
- Maintain M3/WCAG AA contrast minimums for all text-on-surface combinations, especially within colored health badges.
- Visible keyboard focus states throughout (M3 focus indicator token), particularly for dashboard quick actions (Follow up / Snooze) which should be fully operable without a mouse.
