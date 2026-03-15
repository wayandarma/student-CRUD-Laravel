# RBAC Overhaul Design Spec
**Date:** 2026-03-15
**Project:** StudentMS — Laravel 12 Student Management System
**Status:** Approved by user

---

## 1. Overview

Implement a comprehensive role-based access control (RBAC) system with three distinct roles: `student`, `admin`, and `super_admin`. Each role has clearly defined capabilities. Authorization is enforced via Laravel Policies, registered in `AppServiceProvider` (Laravel 12 convention — no `AuthServiceProvider`).

---

## 2. Role Capabilities

### Student
- View and edit their own profile (name, major, enrollment_year)
- Create their own profile if not yet linked (self-service onboarding)
- Cannot access student list or other users' data

### Admin
- View all students
- Create new students (admin-managed, not the same as student self-service)
- Edit any student
- **Cannot delete** students

### Super Admin
- All admin capabilities
- Delete students
- View all registered users (user management page)
- Change user roles between `student` ↔ `admin` only
  - Cannot promote/demote `super_admin` accounts
  - Cannot change their own role
- View last login time for all users

---

## 3. Database Changes

### Migration: `add_role_to_users_table` *(already exists as untracked file)*
- Adds `role` column (string, default: `'student'`, not nullable) to the `users` table
- This migration file already exists at `database/migrations/2026_03_15_063729_add_role_to_users_table.php`
- **No changes needed** — verify it has been run (`php artisan migrate:status`) before proceeding

### Migration: `add_last_login_at_to_users_table` *(new)*
- Add `last_login_at` (timestamp, nullable) to `users` table; stored in UTC (Laravel default), displayed as relative time in views
- Updated in `AuthController@login` upon **successful form login only** — not on remember-me token re-auth or session resume. A failed login attempt preserves the existing `last_login_at` value.

### Model Change: `User` model
- Add `last_login_at` to `$fillable` array (required if `update()` mass assignment is used in `AuthController`)
- Add `'last_login_at' => 'datetime'` to `casts()` method (required for Carbon relative time rendering in views — e.g., `$user->last_login_at->diffForHumans()`)

### Migration: `add_user_id_to_students_table` *(already exists as untracked file)*
- Adds nullable `user_id` FK on `students` table referencing `users.id`
- This migration file already exists at `database/migrations/2026_03_15_063729_add_user_id_to_students_table.php`
- **No changes needed** — verify it has been run (`php artisan migrate:status`) before proceeding. If not run, `$student->user_id` will be `null` and the inline ownership check in `StudentProfileController` will silently 403 all updates.

---

## 4. Authorization Layer — Laravel Policies

Policies are registered via `Gate::policy()` in `AppServiceProvider::boot()`.

### `StudentPolicy`

`StudentPolicy::create` applies **only** to admin-managed creation via `StudentController`. Student self-service creation via `StudentProfileController` uses a dedicated gate check (`Gate::allows('create-own-profile')` or inline ownership check), not `StudentPolicy::create`.

`StudentPolicy::view` for students handles ownership checks in shared contexts (e.g., reusable authorization helpers). Students access their own data exclusively via `/profile` routes — they never reach `StudentController@show`.

`StudentPolicy::update` "own only" for students is **never triggered via `StudentController`** (students are blocked by middleware). The student self-service update path uses `StudentProfileController::update`, which performs a direct ownership check (`abort_if($student->user_id !== auth()->id(), 403)`) rather than calling `$this->authorize('update', $student)`. The policy row is kept for completeness and potential future use.

| Method | Student | Admin | Super Admin | Notes |
|--------|---------|-------|-------------|-------|
| `viewAny` | ✗ | ✓ | ✓ | |
| `view` | own only (`student.user_id === auth()->id()`) | ✓ | ✓ | Students never reach `StudentController@show` |
| `create` | ✗ (admin-managed only) | ✓ | ✓ | Student self-service uses inline check, not this policy method |
| `update` | own only | ✓ | ✓ | Students use `StudentProfileController` with inline ownership check |
| `delete` | ✗ | ✗ | ✓ | |

### Delete enforcement note
`DELETE /students/{student}` is inside the `role:super_admin,admin` middleware group (both roles pass the route). `StudentPolicy::delete` is the sole enforcement layer — it returns `false` for `admin`, `true` for `super_admin`. No separate route-level guard for delete is needed or created.

### `UserPolicy`

| Method | Student | Admin | Super Admin |
|--------|---------|-------|-------------|
| `viewAny` | ✗ | ✗ | ✓ |
| `updateRole` | ✗ | ✗ | ✓ (with guards below) |

**Guards for `updateRole`:**
- Target user must not have role `super_admin`
- Target user must not be the authenticated user themselves
- New role must be one of: `student`, `admin`

---

## 5. Controllers

### Modified: `StudentController`
- All methods use `$this->authorize()` via `StudentPolicy`
- `destroy` restricted by policy (super_admin only; middleware allows both roles to reach the route)
- Index view receives auth context to conditionally show/hide Delete button

### Modified: `DashboardController`
Post-login redirect behavior by role:
- `student` → `student/dashboard` (student portal)
- `admin` → `students.index` (student management list)
- `super_admin` → `students.index` (student management list)

No change to existing redirect logic needed if current implementation already handles this — verify during implementation.

### New: `StudentProfileController`
Handles student self-service profile management.

| Method | Route | Description |
|--------|-------|-------------|
| `show` | `GET /profile` | Show own profile; redirect to `profile.edit` if no profile linked yet |
| `store` | `POST /profile` | Create own student profile for the first time |
| `edit` | `GET /profile/edit` | Edit form (create or update path) |
| `update` | `PUT /profile` | Update own profile (name, major, enrollment_year only) |

**`edit` — view branching variables:**
Controller always passes to `profile/edit.blade.php`:
- `$student`: the linked Student model, or `null` if no profile exists
- `$hasProfile`: `true` if student has a profile, `false` otherwise

View uses `$hasProfile` to determine form action and method: `true` → `PUT /profile`; `false` → `POST /profile`.

**`store` guard — duplicate profile prevention:**
If the authenticated user already has a linked student record (`Student::where('user_id', auth()->id())->exists()`), redirect to `profile.show` with flash `info`: `"Profil kamu sudah ada."` No second record is created.

**`store` — email assignment:**
`email` is **not** submitted by the user. The controller sets `$data['email'] = auth()->user()->email` and `$data['user_id'] = auth()->id()` before persisting. `StudentProfileStoreRequest` does not include an `email` field.

**Fields student can self-edit:** `name`, `major`, `enrollment_year`
**Fields only admin can set:** `status` (active/inactive), `email`

### New: `UserController`
Handles super admin user management.

| Method | Route | Description |
|--------|-------|-------------|
| `index` | `GET /users` | List all users with name, email, role, last_login_at (relative time) |
| `updateRole` | `PUT /users/{user}/role` | Change a user's role (student ↔ admin); authorized via `UserPolicy` |

**`updateRole` — response behavior:**
- Success: redirect to `users.index` with flash `success`: `"Role pengguna berhasil diubah."`
- Policy denial: Laravel automatically throws `AuthorizationException` → 403
- Validation failure: redirect back with errors (handled by `UserRoleUpdateRequest`)

---

## 6. Routes

**Note on existing routes:** The route groups below are additions/replacements within `routes/web.php`. The existing `/dashboard` (GET) and `/logout` (POST) routes — currently inside the outer `auth` middleware group — are **preserved unchanged**. Only the `students` resource route and the new groups below are modified or added.

```php
// Student self-service (auth + student role)
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [StudentProfileController::class, 'store'])->name('profile.store');
    Route::get('/profile/edit', [StudentProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
});

// Admin + Super Admin (existing group, extended)
// Note: DELETE is reachable by both roles; StudentPolicy::delete is the sole enforcement layer.
// Note: Remove ->except('show') from the existing resource route registration to enable students.show.
Route::middleware(['auth', 'role:super_admin,admin'])->group(function () {
    Route::resource('students', StudentController::class); // no exclusions
});

// Super Admin only
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
});
```

---

## 7. Views

### Modified: `resources/views/dashboard.blade.php` *(this IS the student list view — `StudentController@index` returns this file)*
- Delete button rendered conditionally using `@can('delete', $student)` directive (delegates to `StudentPolicy::delete`, avoids raw role string check)

### Modified: `resources/views/student/dashboard.blade.php`
*(This file uses singular `student/` — intentional, separate from the admin-facing `students/` namespace.)*
- If profile exists: show data + "Edit Profil" button linking to `profile.edit`
- If no profile: show empty state with "Buat Profil" CTA linking to `profile.edit`

### New: `resources/views/students/show.blade.php`
- Detail view for a single student (admin/super_admin only)
- Shows all fields: name, email, major, enrollment_year, status, linked user account (if any)

### New: `resources/views/profile/show.blade.php`
- Student's own profile view (always has a profile when rendered — `StudentProfileController@show` redirects to `profile.edit` before reaching this view if no profile exists)
- Displays: name, major, enrollment_year, status, email (all read-only)
- "Edit Profil" button linking to `profile.edit`

### New: `resources/views/profile/edit.blade.php`
- Combined create/edit form using `$hasProfile` and `$student` passed from controller
- Editable fields: name, major, enrollment_year
- Non-editable display: email (from `auth()->user()->email`), status (set by admin only, shown read-only)
- Form action: `PUT /profile` (with `@method('PUT')`) when `$hasProfile === true`; `POST /profile` when `$hasProfile === false`

### New: `resources/views/users/index.blade.php`
- Table: avatar (2-letter initials), name, email, role pill, last login (relative time from UTC; display `"Belum pernah login"` when `last_login_at` is `null`), change role button
- Role change via inline form (PUT /users/{user}/role) with select (student/admin)
- Super admin accounts: role pill shown, no change button
- Authenticated user: "(Anda)" label appended to name, no change button

---

## 8. Form Requests

### New: `StudentProfileStoreRequest`
- `name`: required, string, max 255
- `major`: required, string, max 255
- `enrollment_year`: required, integer, between 2000 and current year (`date('Y')`)
- *(email is NOT a user-submitted field — set programmatically in controller)*

### New: `StudentProfileUpdateRequest`
*(Kept separate from StoreRequest to allow future rule divergence, e.g., enrollment_year may become read-only post-creation.)*
- `name`: required, string, max 255
- `major`: required, string, max 255
- `enrollment_year`: required, integer, between 2000 and current year (`date('Y')`)

### New: `UserRoleUpdateRequest`
- `role`: required, in:student,admin

All validation messages in Indonesian (`messages()` method). Example for `UserRoleUpdateRequest`: `'role.in' => 'Role yang dipilih tidak valid.'`

---

## 9. Testing

### `EnsureUserHasRole` middleware failure behavior
When a user's role does not match the required role(s), the middleware redirects to `route('dashboard')` with flash `error`: `"Anda tidak memiliki izin untuk mengakses halaman tersebut."` (not a 403 abort). Test assertions for role-blocked routes should expect an HTTP redirect (302), not 403.

### Existing: `RegistrationRoleTest` *(already exists as untracked file)*
- Verifies that newly registered users are assigned the `student` role by default
- No changes needed unless registration flow changes

### New: `RoleAccessTest` *(new file, extends existing test patterns)*
- Admin cannot delete a student (expects 403 — policy abort, not middleware redirect)
- Student cannot access `/students` (expects 302 redirect to dashboard — middleware redirect)
- Student can access `/profile` (expects 200)
- Super admin can access `/users` (expects 200)
- Super admin can change a user's role (expects 302 redirect to users.index)

### New: `StudentPolicyTest`
- Tests all 5 policy methods across all 3 roles
- Edge case: student can only view/update their own record, not another student's

### New: `UserPolicyTest`
- Super admin can change student→admin and admin→student
- Super admin cannot change another super_admin's role
- Super admin cannot change their own role
- Admin cannot access role change

---

## 10. Implementation Order

1. Migration: `last_login_at` on users
2. Update `AuthController@login` to set `last_login_at` on successful form login
3. Create `StudentPolicy` + register in `AppServiceProvider::boot()`
4. Apply `authorize()` in `StudentController`, gate delete to super_admin via policy
5. Create `UserPolicy` + register in `AppServiceProvider::boot()`
6. Create `UserController` + routes + `UserRoleUpdateRequest` + `users/index` view
7. Create `StudentProfileController` + routes + `StudentProfileStoreRequest` + `StudentProfileUpdateRequest` + profile views
8. Create `students/show.blade.php`
9. Update `student/dashboard.blade.php` for self-service CTA
10. Update `dashboard.blade.php` — replace raw role check with `@can('delete', $student)`
11. Verify/update `DashboardController` role-based redirect behavior
12. Write/update tests
