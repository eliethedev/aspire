# Plan: School Head Observation — EPOC Replaces COT + Admin EPOC + 3-Term Calendar

## Context

Currently, when a Supervisor schedules a School Head observation:
1. The co-observation section is always shown (even though school head observations don't use co-observation)
2. The template selector shows COT/PPSSH templates for the observation stage
3. The EPOC form is only accessible as a separate post-conference evaluation step
4. Admin can only view observation details, no EPOC visibility
5. Calendar uses 4 quarters (Q1-4), but DepEd new curriculum uses a **3-term trimester calendar**

The user wants:
- **Hide co-observation** when the observee is a School Head
- **Replace the COT rating form with the EPOC form** in the observation stage for school head observations
- **Admin can view/manage EPOC** for school head observations
- **Update calendar to 3-term trimester system**

## DepEd 3-Term Trimester Calendar (2026-2027)

| Term | Dates |
|------|-------|
| TERM 1 | June 8, 2026 → September 15, 2026 |
| TERM 2 | September 16, 2026 → December 18, 2026 |
| TERM 3 | January 4, 2027 → April 8, 2027 |

### Scope decision for the DB field

The observation table has `quarter` (integer, 1-4) which is meaningful historical data in existing observations and reports. Changing it to 1-3 requires a **migration** and touches reports (`post-observation`, `cot-document`, `FinalReportService`, `ObservationReportService`, `PDFReportService`) and `school-head/reports/index.blade.php` (quarterly chart).

**Decision:** Since the backend constraint explicitly says "Keep existing database columns unchanged," and this is a UI/UX refactor, I will:
- **Keep the DB `quarter` integer column as-is** (values 1-4)
- **Change the UI layer** to display it as "Term 1/2/3" (values 1-3)
- **Change `getCurrentQuarter()`** to return 1/2/3 based on the trimester boundaries
- Update the create form dropdowns to show 3 terms instead of 4 quarters

This keeps the DB schema/intent intact (the field stores an ordinal) while presenting the 3-term calendar to users.

---

## Step 1: Supervisor Create Form — Hide Co-Observation for School Heads

**File:** `resources/views/supervisor/observations/create.blade.php`

Add `x-show="selectedType !== 'school_head_observation'"` to the co-observation section outer `<div>` (Step 4, around line 538). This hides the entire co-observation section when the observation type is `school_head_observation`.

Also add a brief explanatory note when school head is selected: "Co-observation is not applicable for School Head observations."

## Step 2: Supervisor Create Form — Replace Template Selector with EPOC Info for School Heads

**File:** `resources/views/supervisor/observations/create.blade.php`

In Step 2 (template selection, lines 167-236):
- Wrap the existing template card grid with `x-show="selectedType !== 'school_head_observation'"`
- Add a new section for school head observations: an informational card stating "This observation will use the EPOC form as the rating instrument"
- Auto-set `selectedCotTemplateId` to `null` and `requiresPostConference` appropriately when school head is selected

**File:** `app/Http/Controllers/SupervisorController.php`

In `storeObservation()`:
- When `observation_type === 'school_head_observation'`, skip the `cot_indicator_version_id` validation and role resolution (lines 820-900). Store `cot_indicator_version_id` as `null`.
- Keep the existing `requiresPostConference`/post-conference auto-creation logic (school head observations still go through post-conference).

## Step 3: Create EPOC Form Partial

**New file:** `resources/views/supervisor/observations/partials/epoc-form.blade.php`

Extract the EPOC rating form from `resources/views/supervisor/observations/epoc.blade.php` into a reusable partial:
- Accept `$observation`, `$epocEvaluation`, `$schoolHead` variables
- Use the same vanilla JS for rating selection (`selectRating`, `toggleComment`)
- Use the same 6 domains / 23 indicators / 1-5 rating scale
- Use the same narrative observation and agreement textareas
- **Not** include its own form tag (the parent view provides the `<form>`)

## Step 4: Observation Stage View — Conditional Rendering

**File:** `resources/views/supervisor/observations/observation.blade.php`

- When `$observation->isSchoolHeadObservation()`:
  - Load EPOC data in the controller: `$epocEvaluation = $observation->epocEvaluation; $schoolHead = $observation->schoolHead;`
  - Render `@include('supervisor.observations.partials.epoc-form', ...)` instead of the COT rating table
  - Keep same form action (`storeObservationData`), autosave setup, and submit button
- When teacher observation: render the existing COT rating table (no change)

**File:** `app/Http/Controllers/SupervisorController.php`

In `observation()` method (lines 1511-1531): Load `epocEvaluation` and `schoolHead` when it's a school head observation and pass to view.

## Step 5: Modify `storeObservationData` to Handle EPOC Data

**File:** `app/Http/Controllers/SupervisorController.php`

In `storeObservationData()` (lines 1536-1667), branch based on `$observation->observation_type`:
- **Teacher observation** (current logic): Delete/create `CotRating` records, dispatch AI feedback jobs.
- **School head observation** (new): Delete existing `EpocEvaluation`, create new `EpocEvaluation` + `EpocRating` records (domain, indicator, rating, comments), set `school_head_name` and `observation_date` fallbacks, calculate `overall_score` as average of non-null ratings.
- Stage transition logic stays identical for both paths (status → `cot_completed`, advance to `post_conference`).
- EPOC path does NOT dispatch AI feedback jobs.

## Step 6: Handle Autosave for EPOC

**File:** `app/Http/Controllers/SupervisorController.php`

Modify `autosave()` to detect school head observation and save EPOC fields (ratings, narrative, agreement).

**File:** `resources/views/supervisor/observations/partials/epoc-form.blade.php`

Add `data-autosave-field` attributes to EPOC form fields.

## Step 7: Admin EPOC Visibility

### 7a. Admin Observation Controller — Load EPOC Data

**File:** `app/Http/Controllers/Admin/ObservationController.php`

In `show()` method (lines 49-62): Add `'epocEvaluation.ratings'` and `'schoolHead.user'` to eager-loaded relationships.

### 7b. Admin Show Page — Display EPOC Data

**File:** `resources/views/admin/observations/show.blade.php`

Add an EPOC section (after COT Ratings section) rendering EPOC ratings, narrative, agreement, and overall score, conditionally when `$observation->isSchoolHeadObservation() && $observation->epocEvaluation`.

### 7c. Admin EPOC Download

**File:** `app/Http/Controllers/Admin/ObservationController.php`

Add a `downloadEpocDocument()` method reusing the EPOC document generation logic (from `SupervisorController::downloadEpoc()` / `CotDocumentService::generateEpocDocument()`).

**File:** `routes/web.php`

Add admin route:
```
GET /admin/observations/{observation}/epoc-document → Admin\ObservationController@downloadEpocDocument
```
Named: `admin.observations.epoc-document`

**File:** `resources/views/admin/observations/show.blade.php`

Add a "Download EPOC Document" button when EPOC evaluation exists.

## Step 8: Update to 3-Term Trimester Calendar

### 8a. getCurrentQuarter → getCurrentTerm

**File:** `app/Http/Controllers/SupervisorController.php`

Change `getCurrentQuarter()` (lines 1165-1178) to `getCurrentTerm()` using trimester boundaries:

```php
private function getCurrentTerm(): int
{
    $now = now();

    // DepEd new curriculum 3-term calendar
    if ($now->between('2026-06-08 00:00:00', '2026-09-15 23:59:59')) return 1;
    if ($now->between('2026-09-16 00:00:00', '2026-12-18 23:59:59')) return 2;
    if ($now->between('2027-01-04 00:00:00', '2027-04-08 23:59:59')) return 3;

    // Fallback: term 2 (current 2026-27 SY middle)
    return 2;
}
```

Update callers at lines 917 and 1083 (`$this->getCurrentQuarter()` → `$this->getCurrentTerm()`).

**File:** `app/Http/Controllers/SchoolHead/ObservationController.php`

Apply the same change to `getCurrentQuarter()` (lines 1297-1310) and its caller at line 248.

### 8b. Create Form — Update Quarter Dropdowns to Terms

**File:** `resources/views/supervisor/observations/create.blade.php`

- Change label "Quarter" → "Term"
- Change dropdown options from 4 quarters to 3 terms:
  - `value="1"` → "1st Term"
  - `value="2"` → "2nd Term"
  - `value="3"` → "3rd Term"
- Update the summary sidebar label (line 793): `'Term ' + form.term` — but the hidden field name must stay `quarter` (DB column).

**File:** `resources/views/school-head/observations/create.blade.php`

Apply the same term-ization to lines 207-216 and 408.

---

## Files to Modify

| File | Change |
|------|--------|
| `resources/views/supervisor/observations/create.blade.php` | Hide co-observation + template selector for school heads; quarter→term |
| `resources/views/supervisor/observations/observation.blade.php` | Conditional EPOC form rendering |
| `resources/views/supervisor/observations/partials/epoc-form.blade.php` | **NEW** — Extracted EPOC form partial |
| `resources/views/admin/observations/show.blade.php` | Display EPOC data + download button |
| `resources/views/school-head/observations/create.blade.php` | Quarter→Term dropdown |
| `app/Http/Controllers/SupervisorController.php` | storeObservation() skip COT for SH; observation() load EPOC; storeObservationData() handle EPOC; autosave() handle EPOC; getCurrentQuarter()→getCurrentTerm() + callers |
| `app/Http/Controllers/SchoolHead/ObservationController.php` | getCurrentQuarter()→getCurrentTerm() + caller |
| `app/Http/Controllers/Admin/ObservationController.php` | Load EPOC in show(); add downloadEpocDocument() |
| `routes/web.php` | Add admin EPOC document download route |

## Files NOT Modified (keep intact)

- `app/Models/Observation.php` — keep `quarter` column
- `app/Models/EpocEvaluation.php`, `EpocRating.php`
- `resources/views/supervisor/observations/epoc.blade.php` — kept as-is (still used from show page)
- No migrations

## Notes / Open Questions

- Changing the internal method name `getCurrentQuarter` → `getCurrentTerm` is safe (private method, only called in same file). 
- The DB `quarter` column keeps storing 1-3 (terms). Existing observations storing 4 would still render "4th Quarter" if displayed raw — but I'll map display to Term labels in the create form only. Existing report views (post-observation, cot-document) still show "Quarter {{ $quarter }}" — those are included in the "backend relevant files" but I'll update them to say "Term" since the DB value now holds a term ordinal. (Report display changes are minimal and user-facing.)

## Verification

1. `php artisan route:list --name=admin.observations` — new EPOC route present
2. Schedule a school head observation → co-observation hidden, template shows EPOC info, term dropdown shows 3 terms
3. Complete pre-obs + pre-conference → observation stage shows EPOC form
4. Fill EPOC ratings + narrative → save → transition to post-conference with `cot_completed`
5. Autosave works for EPOC fields
6. Teacher observations unchanged (COT form, no regression)
7. Admin show page shows EPOC data + download button for school head obs
8. `getCurrentTerm()` returns 2 for today (Sep 4, 2026 is in Term 2 boundary Sep 16–Dec 18? No — Sep 4 is before Sep 16, so it falls in Term 1 per boundaries. Verify against boundaries.)
9. `git diff --stat -- app/ routes/ database/` — no migration changes
