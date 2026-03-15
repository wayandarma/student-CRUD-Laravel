# RBAC Overhaul Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement role-based access control with three roles (student/admin/super_admin), student self-service profiles, and a super admin user management page.

**Architecture:** Laravel Policies handle authorization (registered in AppServiceProvider). StudentController gains `authorize()` calls. Two new controllers handle student self-service profiles and super admin user management. Routes are grouped by role.

**Tech Stack:** Laravel 12, PHP 8.2+, PHPUnit 11, Bootstrap 5

**Spec:** `docs/superpowers/specs/2026-03-15-rbac-overhaul-design.md`

---

## File Map

**New files:**
- `database/migrations/*_add_last_login_at_to_users_table.php`
- `app/Policies/StudentPolicy.php`
- `app/Policies/UserPolicy.php`
- `app/Http/Controllers/StudentProfileController.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Requests/StudentProfileStoreRequest.php`
- `app/Http/Requests/StudentProfileUpdateRequest.php`
- `app/Http/Requests/UserRoleUpdateRequest.php`
- `resources/views/students/show.blade.php`
- `resources/views/profile/show.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/users/index.blade.php`
- `tests/Feature/StudentPolicyTest.php`
- `tests/Feature/UserPolicyTest.php`

**Modified files:**
- `app/Models/User.php` — add `last_login_at` to `$fillable` + `casts()`
- `app/Http/Controllers/AuthController.php` — set `last_login_at` on successful login
- `app/Http/Controllers/StudentController.php` — add `$this->authorize()` calls
- `app/Providers/AppServiceProvider.php` — register `StudentPolicy` + `UserPolicy`
- `routes/web.php` — add new route groups, remove `->except('show')`
- `resources/views/dashboard.blade.php` — wrap delete button in `@can`
- `resources/views/student/dashboard.blade.php` — add edit/create profile CTA
- `tests/Feature/RoleAccessTest.php` — add new test cases

---

## Chunk 1: Database Foundation

Verify existing migrations are run, create `last_login_at` migration, update `User` model, and record login time.

### Pre-flight: verify existing migrations

- [ ] **Step 1: Check migration status**

```bash
php artisan migrate:status
```

Confirm `add_role_to_users_table` and `add_user_id_to_students_table` show **Ran**. If either shows **Pending**, run `php artisan migrate` before continuing.

---

### Task 0: Verify `DashboardController` role-based redirect

**Files:**
- Modify if needed: `app/Http/Controllers/DashboardController.php`

- [ ] **Step 1: Read the current `DashboardController`**

Open `app/Http/Controllers/DashboardController.php` and check `index()`:
- If student (`$user->isStudent()`) → renders `student.dashboard` view ✓
- Otherwise → redirects to `students.index` ✓

The existing implementation already handles this correctly. **No change required.** Existing test `test_admin_dashboard_redirects_to_student_management_index` confirms this behavior.

- [ ] **Step 2: Confirm existing tests still pass**

```bash
php artisan test tests/Feature/RoleAccessTest.php
```

Expected: All 4 existing tests PASS.

---

### Task 1: Create `last_login_at` migration

**Files:**
- Create: `database/migrations/*_add_last_login_at_to_users_table.php`

- [ ] **Step 1: Generate migration**

```bash
php artisan make:migration add_last_login_at_to_users_table --table=users
```

- [ ] **Step 2: Fill in migration**

Open the generated file and replace the `up()` and `down()` methods:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table): void {
        $table->timestamp('last_login_at')->nullable()->after('remember_token');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table): void {
        $table->dropColumn('last_login_at');
    });
}
```

- [ ] **Step 3: Run migration**

```bash
php artisan migrate
```

Expected: `2026_..._add_last_login_at_to_users_table ........ DONE`

---

### Task 2: Update `User` model

**Files:**
- Modify: `app/Models/User.php`

- [ ] **Step 1: Add `last_login_at` to `$fillable`**

In `app/Models/User.php`, add `'last_login_at'` to the `$fillable` array:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'last_login_at',
];
```

- [ ] **Step 2: Add `last_login_at` to `casts()`**

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'last_login_at'     => 'datetime',
    ];
}
```

---

### Task 3: Record login time in `AuthController`

**Files:**
- Modify: `app/Http/Controllers/AuthController.php`

- [ ] **Step 1: Update `login()` method**

After `Auth::attempt()` succeeds, add one line before `session()->regenerate()`:

```php
public function login(LoginRequest $request): RedirectResponse
{
    $credentials = $request->validated();

    if (!Auth::attempt($credentials)) {
        return back()
            ->withInput($request->except('password'))
            ->with('error', 'Email atau password salah.');
    }

    Auth::user()->update(['last_login_at' => now()]);

    $request->session()->regenerate();

    return redirect()
        ->route('dashboard')
        ->with('success', 'Login berhasil.');
}
```

- [ ] **Step 2: Commit**

```bash
git add database/migrations app/Models/User.php app/Http/Controllers/AuthController.php
git commit -m "feat: add last_login_at tracking on user login"
```

---

## Chunk 2: Policies

Write failing policy tests first, then implement both policies and register them.

### Task 4: Write failing `StudentPolicyTest`

**Files:**
- Create: `tests/Feature/StudentPolicyTest.php`

- [ ] **Step 1: Create test file**

```php
<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private StudentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new StudentPolicy();
    }

    // ── viewAny ─────────────────────────────────────────────

    public function test_student_cannot_view_any(): void
    {
        $student = User::factory()->student()->create();
        $this->assertFalse($this->policy->viewAny($student));
    }

    public function test_admin_can_view_any(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertTrue($this->policy->viewAny($admin));
    }

    public function test_super_admin_can_view_any(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertTrue($this->policy->viewAny($superAdmin));
    }

    // ── view ────────────────────────────────────────────────

    public function test_student_can_view_own_record(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($this->policy->view($user, $student));
    }

    public function test_student_cannot_view_other_students_record(): void
    {
        $user    = User::factory()->student()->create();
        $other   = Student::factory()->create(); // no user_id link to $user
        $this->assertFalse($this->policy->view($user, $other));
    }

    public function test_admin_can_view_any_student_record(): void
    {
        $admin   = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $this->assertTrue($this->policy->view($admin, $student));
    }

    // ── create ──────────────────────────────────────────────

    public function test_student_cannot_create(): void
    {
        $user = User::factory()->student()->create();
        $this->assertFalse($this->policy->create($user));
    }

    public function test_admin_can_create(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertTrue($this->policy->create($admin));
    }

    public function test_super_admin_can_create(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertTrue($this->policy->create($superAdmin));
    }

    // ── update ──────────────────────────────────────────────

    public function test_admin_can_update_any_student(): void
    {
        $admin   = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $this->assertTrue($this->policy->update($admin, $student));
    }

    public function test_student_can_update_own_record(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($this->policy->update($user, $student));
    }

    public function test_student_cannot_update_another_students_record(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create();
        $this->assertFalse($this->policy->update($user, $student));
    }

    // ── delete ──────────────────────────────────────────────

    public function test_student_cannot_delete(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create();
        $this->assertFalse($this->policy->delete($user, $student));
    }

    public function test_admin_cannot_delete(): void
    {
        $admin   = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $this->assertFalse($this->policy->delete($admin, $student));
    }

    public function test_super_admin_can_delete(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $student    = Student::factory()->create();
        $this->assertTrue($this->policy->delete($superAdmin, $student));
    }
}
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
php artisan test tests/Feature/StudentPolicyTest.php
```

Expected: FAIL — `App\Policies\StudentPolicy` not found.

---

### Task 5: Write failing `UserPolicyTest`

**Files:**
- Create: `tests/Feature/UserPolicyTest.php`

- [ ] **Step 1: Create test file**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    // ── viewAny ─────────────────────────────────────────────

    public function test_student_cannot_view_any_users(): void
    {
        $student = User::factory()->student()->create();
        $target  = User::factory()->student()->create();
        $this->assertFalse($this->policy->viewAny($student));
    }

    public function test_admin_cannot_view_any_users(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertFalse($this->policy->viewAny($admin));
    }

    public function test_super_admin_can_view_any_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertTrue($this->policy->viewAny($superAdmin));
    }

    // ── updateRole ──────────────────────────────────────────

    public function test_super_admin_can_change_student_to_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->student()->create();
        $this->assertTrue($this->policy->updateRole($superAdmin, $target));
    }

    public function test_super_admin_can_change_admin_to_student(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->admin()->create();
        $this->assertTrue($this->policy->updateRole($superAdmin, $target));
    }

    public function test_super_admin_cannot_change_another_super_admins_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->superAdmin()->create();
        $this->assertFalse($this->policy->updateRole($superAdmin, $target));
    }

    public function test_super_admin_cannot_change_own_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertFalse($this->policy->updateRole($superAdmin, $superAdmin));
    }

    public function test_admin_cannot_update_any_role(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->student()->create();
        $this->assertFalse($this->policy->updateRole($admin, $target));
    }

    public function test_student_cannot_update_any_role(): void
    {
        $student = User::factory()->student()->create();
        $target  = User::factory()->admin()->create();
        $this->assertFalse($this->policy->updateRole($student, $target));
    }
}
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test tests/Feature/UserPolicyTest.php
```

Expected: FAIL — `App\Policies\UserPolicy` not found.

---

### Task 6: Implement `StudentPolicy`

**Files:**
- Create: `app/Policies/StudentPolicy.php`

- [ ] **Step 1: Create the policy**

```bash
php artisan make:policy StudentPolicy --model=Student
```

- [ ] **Step 2: Replace the generated content**

```php
<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminLevel();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isAdminLevel()) {
            return true;
        }

        return $student->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminLevel();
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->isAdminLevel()) {
            return true;
        }

        return $student->user_id === $user->id;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isSuperAdmin();
    }
}
```

---

### Task 7: Implement `UserPolicy`

**Files:**
- Create: `app/Policies/UserPolicy.php`

- [ ] **Step 1: Create the policy**

```bash
php artisan make:policy UserPolicy --model=User
```

- [ ] **Step 2: Replace the generated content**

```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function updateRole(User $user, User $target): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        // Cannot change own role
        if ($user->id === $target->id) {
            return false;
        }

        // Cannot change another super_admin's role
        if ($target->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
```

---

### Task 8: Register policies in `AppServiceProvider`

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Open `AppServiceProvider.php` and update `boot()`**

```php
<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
```

---

### Task 9: Run policy tests — confirm green

- [ ] **Step 1: Run both policy tests**

```bash
php artisan test tests/Feature/StudentPolicyTest.php tests/Feature/UserPolicyTest.php
```

Expected: All tests PASS.

- [ ] **Step 2: Commit**

```bash
git add app/Policies/ tests/Feature/StudentPolicyTest.php tests/Feature/UserPolicyTest.php app/Providers/AppServiceProvider.php
git commit -m "feat: add StudentPolicy and UserPolicy with full test coverage"
```

---

## Chunk 3: StudentController Authorization

Add `authorize()` calls to all `StudentController` methods. This is the enforcement layer for admin vs super_admin access.

### Task 10: Add authorize() to `StudentController`

**Files:**
- Modify: `app/Http/Controllers/StudentController.php`

- [ ] **Step 1: Write failing test first**

Add to `tests/Feature/RoleAccessTest.php`:

```php
public function test_admin_cannot_delete_a_student(): void
{
    $admin   = User::factory()->admin()->create();
    $student = \App\Models\Student::factory()->create();

    $response = $this->actingAs($admin)->delete(route('students.destroy', $student));

    $response->assertForbidden();
}
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --filter=test_admin_cannot_delete_a_student
```

Expected: FAIL — admin can currently delete (no policy check yet), so returns 302, not 403.

- [ ] **Step 3: Update `StudentController` with authorize() calls**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $search = trim((string) $request->input('search', ''));
        $status = $request->string('status')->toString();
        $major  = $request->string('major')->toString();

        $studentQuery = Student::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($major !== '', fn (Builder $query) => $query->where('major', $major))
            ->latest();

        $students = (clone $studentQuery)
            ->paginate(15)
            ->withQueryString();

        $majors = Student::query()
            ->whereNotNull('major')
            ->orderBy('major')
            ->distinct()
            ->pluck('major')
            ->values();

        $stats = [
            'total'    => Student::query()->count(),
            'active'   => Student::query()->where('status', 'active')->count(),
            'inactive' => Student::query()->where('status', 'inactive')->count(),
            'majors'   => Student::query()->whereNotNull('major')->distinct()->count('major'),
        ];

        return view('dashboard', compact('students', 'majors', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', Student::class);

        return view('students.create');
    }

    public function store(StudentStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        Student::query()->create($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        return view('students.edit', compact('student'));
    }

    public function update(StudentUpdateRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $student->update($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
```

- [ ] **Step 4: Run the failing test again — confirm it now passes**

```bash
php artisan test --filter=test_admin_cannot_delete_a_student
```

Expected: PASS.

- [ ] **Step 5: Run full test suite to confirm no regressions**

```bash
php artisan test
```

Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/StudentController.php tests/Feature/RoleAccessTest.php
git commit -m "feat: enforce StudentPolicy authorization in StudentController"
```

---

## Chunk 4: Student Self-Service Profile

New controller, form requests, routes, and views for student self-service profile management.

### Task 11: Create form requests

**Files:**
- Create: `app/Http/Requests/StudentProfileStoreRequest.php`
- Create: `app/Http/Requests/StudentProfileUpdateRequest.php`

- [ ] **Step 1: Generate requests**

```bash
php artisan make:request StudentProfileStoreRequest
php artisan make:request StudentProfileUpdateRequest
```

- [ ] **Step 2: Fill `StudentProfileStoreRequest`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'major'           => 'required|string|max:255',
            'enrollment_year' => 'required|integer|min:2000|max:' . now()->year,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Nama wajib diisi.',
            'name.max'                 => 'Nama maksimal :max karakter.',
            'major.required'           => 'Jurusan wajib diisi.',
            'major.max'                => 'Jurusan maksimal :max karakter.',
            'enrollment_year.required' => 'Tahun masuk wajib diisi.',
            'enrollment_year.integer'  => 'Tahun masuk harus berupa angka.',
            'enrollment_year.min'      => 'Tahun masuk minimal :min.',
            'enrollment_year.max'      => 'Tahun masuk tidak boleh melebihi tahun ini.',
        ];
    }
}
```

- [ ] **Step 3: Fill `StudentProfileUpdateRequest`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'major'           => 'required|string|max:255',
            'enrollment_year' => 'required|integer|min:2000|max:' . now()->year,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Nama wajib diisi.',
            'name.max'                 => 'Nama maksimal :max karakter.',
            'major.required'           => 'Jurusan wajib diisi.',
            'major.max'                => 'Jurusan maksimal :max karakter.',
            'enrollment_year.required' => 'Tahun masuk wajib diisi.',
            'enrollment_year.integer'  => 'Tahun masuk harus berupa angka.',
            'enrollment_year.min'      => 'Tahun masuk minimal :min.',
            'enrollment_year.max'      => 'Tahun masuk tidak boleh melebihi tahun ini.',
        ];
    }
}
```

---

### Task 12: Create `StudentProfileController`

**Files:**
- Create: `app/Http/Controllers/StudentProfileController.php`

- [ ] **Step 1: Generate controller**

```bash
php artisan make:controller StudentProfileController
```

- [ ] **Step 2: Replace generated content**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentProfileStoreRequest;
use App\Http\Requests\StudentProfileUpdateRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentProfileController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $student = Student::query()
            ->where('user_id', auth()->id())
            ->first();

        if ($student === null) {
            return redirect()->route('profile.edit');
        }

        return view('profile.show', compact('student'));
    }

    public function edit(): View
    {
        $student    = Student::query()->where('user_id', auth()->id())->first();
        $hasProfile = $student !== null;

        return view('profile.edit', compact('student', 'hasProfile'));
    }

    public function store(StudentProfileStoreRequest $request): RedirectResponse
    {
        if (Student::query()->where('user_id', auth()->id())->exists()) {
            return redirect()
                ->route('profile.show')
                ->with('info', 'Profil kamu sudah ada.');
        }

        Student::query()->create([
            ...$request->validated(),
            'email'   => auth()->user()->email,
            'user_id' => auth()->id(),
            'status'  => 'active',
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil berhasil dibuat.');
    }

    public function update(StudentProfileUpdateRequest $request): RedirectResponse
    {
        $student = Student::query()->where('user_id', auth()->id())->firstOrFail();

        abort_if($student->user_id !== auth()->id(), 403);

        $student->update($request->validated());

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
```

---

### Task 13: Add profile routes

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add imports and new route groups to `web.php`**

Add `StudentProfileController` and `UserController` to the use statements, add the new route groups, and remove `->except('show')`:

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student self-service profile
    Route::middleware('role:student')->group(function (): void {
        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [StudentProfileController::class, 'store'])->name('profile.store');
        Route::get('/profile/edit', [StudentProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
    });

    // Admin + Super Admin: student management (->except('show') removed)
    Route::middleware('role:super_admin,admin')->group(function (): void {
        Route::resource('students', StudentController::class);
    });

    // Super Admin: user management
    Route::middleware('role:super_admin')->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});
```

---

### Task 14: Create profile views

**Files:**
- Create: `resources/views/profile/show.blade.php`
- Create: `resources/views/profile/edit.blade.php`

- [ ] **Step 1: Create `profile/show.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-semibold">Profil Akademik</h5>
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Profil
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Nama</dt>
                        <dd class="col-sm-8">{{ $student->name }}</dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $student->email }}</dd>

                        <dt class="col-sm-4 text-muted">Jurusan</dt>
                        <dd class="col-sm-8">{{ $student->major }}</dd>

                        <dt class="col-sm-4 text-muted">Tahun Masuk</dt>
                        <dd class="col-sm-8 font-monospace">{{ $student->enrollment_year }}</dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Create `profile/edit.blade.php`**

```blade
@extends('layouts.app')

@section('title', $hasProfile ? 'Edit Profil' : 'Buat Profil')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">
                        {{ $hasProfile ? 'Edit Profil Akademik' : 'Buat Profil Akademik' }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($hasProfile)
                        <form action="{{ route('profile.update') }}" method="POST">
                            @method('PUT')
                    @else
                        <form action="{{ route('profile.store') }}" method="POST">
                    @endif
                        @csrf

                        {{-- Email: read-only, not submitted --}}
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <input type="text" class="form-control bg-light" value="{{ auth()->user()->email }}" disabled>
                            <div class="form-text">Email tidak dapat diubah.</div>
                        </div>

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $student?->name) }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Major --}}
                        <div class="mb-3">
                            <label for="major" class="form-label">Jurusan <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="major"
                                name="major"
                                class="form-control @error('major') is-invalid @enderror"
                                value="{{ old('major', $student?->major) }}"
                                required
                            >
                            @error('major')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Enrollment Year --}}
                        <div class="mb-3">
                            <label for="enrollment_year" class="form-label">Tahun Masuk <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                id="enrollment_year"
                                name="enrollment_year"
                                class="form-control font-monospace @error('enrollment_year') is-invalid @enderror"
                                value="{{ old('enrollment_year', $student?->enrollment_year) }}"
                                min="2000"
                                max="{{ now()->year }}"
                                required
                            >
                            @error('enrollment_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($hasProfile)
                            {{-- Status: read-only display, only admin can change --}}
                            <div class="mb-3">
                                <label class="form-label text-muted">Status Akademik</label>
                                <div>
                                    <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                    <small class="text-muted ms-2">Diatur oleh admin.</small>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            @if ($hasProfile)
                                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            @else
                                <button type="submit" class="btn btn-primary">Buat Profil</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 3: Run existing tests — confirm no regressions**

```bash
php artisan test
```

Expected: All existing tests pass.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/StudentProfileController.php \
        app/Http/Requests/StudentProfileStoreRequest.php \
        app/Http/Requests/StudentProfileUpdateRequest.php \
        resources/views/profile/ \
        routes/web.php
git commit -m "feat: add student self-service profile controller, routes, and views"
```

---

## Chunk 5: User Management (Super Admin)

New controller, form request, route, and view for super admin user management.

### Task 15: Create `UserRoleUpdateRequest`

**Files:**
- Create: `app/Http/Requests/UserRoleUpdateRequest.php`

- [ ] **Step 1: Generate request**

```bash
php artisan make:request UserRoleUpdateRequest
```

- [ ] **Step 2: Fill in request**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => 'required|in:student,admin',
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role wajib dipilih.',
            'role.in'       => 'Role yang dipilih tidak valid.',
        ];
    }
}
```

---

### Task 16: Create `UserController`

**Files:**
- Create: `app/Http/Controllers/UserController.php`

- [ ] **Step 1: Generate controller**

```bash
php artisan make:controller UserController
```

- [ ] **Step 2: Replace generated content**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRoleUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function updateRole(UserRoleUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('updateRole', $user);

        $user->update(['role' => $request->validated()['role']]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Role pengguna berhasil diubah.');
    }
}
```

---

### Task 17: Create `users/index` view

**Files:**
- Create: `resources/views/users/index.blade.php`

- [ ] **Step 1: Create the view**

```blade
@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-semibold mb-0">Manajemen Pengguna</h4>
        <span class="badge bg-secondary">{{ $users->count() }} pengguna</span>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Login Terakhir</th>
                        <th class="text-end">Ubah Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-chip">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: $user->name, 1, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-medium">
                                        {{ $user->name }}
                                        @if ($user->id === auth()->id())
                                            <span class="text-muted small">(Anda)</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleClass = match($user->role) {
                                    'super_admin' => 'bg-primary',
                                    'admin'       => 'bg-warning text-dark',
                                    default       => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $roleClass }}">{{ $user->roleLabel() }}</span>
                        </td>
                        <td class="text-muted small font-monospace">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah login' }}
                        </td>
                        <td class="text-end">
                            @if ($user->id !== auth()->id() && !$user->isSuperAdmin())
                                <form action="{{ route('users.update-role', $user) }}" method="POST" class="d-inline-flex gap-2 align-items-center">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" class="form-select form-select-sm" style="width:auto;">
                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Student</option>
                                        <option value="admin"   {{ $user->role === 'admin'   ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Run tests**

```bash
php artisan test
```

Expected: All pass.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/UserController.php \
        app/Http/Requests/UserRoleUpdateRequest.php \
        resources/views/users/
git commit -m "feat: add super admin user management with role change"
```

---

## Chunk 6: View Updates & `students.show`

Update existing views and create the missing `students/show.blade.php`.

### Task 18: Update `dashboard.blade.php` — gate delete button

**Files:**
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Find the delete form in the view**

Search for the delete button in `resources/views/dashboard.blade.php`. It will look like a `<form>` with `@method('DELETE')`.

- [ ] **Step 2: Wrap it in `@can`**

Replace the delete form block with:

```blade
@can('delete', $student)
<form action="{{ route('students.destroy', $student) }}" method="POST"
      data-confirm
      data-confirm-title="Hapus Mahasiswa"
      data-confirm-body="Yakin ingin menghapus data {{ $student->name }}? Tindakan ini tidak dapat dibatalkan."
      data-confirm-variant="danger">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-trash"></i>
    </button>
</form>
@endcan
```

---

### Task 19: Update `student/dashboard.blade.php` — add profile CTA

**Files:**
- Modify: `resources/views/student/dashboard.blade.php`

- [ ] **Step 1: Find the no-profile empty state block**

Find the `@else` branch (no `studentProfile` linked). Replace the message with:

```blade
@else
    <div class="text-center py-5">
        <i class="bi bi-person-circle display-4 text-muted mb-3 d-block"></i>
        <p class="text-muted mb-3">Profil akademikmu belum dibuat.</p>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Buat Profil
        </a>
    </div>
@endif
```

- [ ] **Step 2: Find the existing profile display block (`@if ($studentProfile)`)**

Add an "Edit Profil" button at the top of the profile card. Find the section that shows student profile data and add:

```blade
<a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary ms-auto">
    <i class="bi bi-pencil me-1"></i> Edit Profil
</a>
```

---

### Task 20: Create `students/show.blade.php`

**Files:**
- Create: `resources/views/students/show.blade.php`

- [ ] **Step 1: Create the view**

```blade
@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="fw-semibold mb-0">Detail Mahasiswa</h4>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-chip">
                            {{ strtoupper(substr($student->name, 0, 1)) }}{{ strtoupper(substr(strstr($student->name, ' ') ?: $student->name, 1, 1)) }}
                        </div>
                        <span class="fw-semibold">{{ $student->name }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        @can('delete', $student)
                        <form action="{{ route('students.destroy', $student) }}" method="POST"
                              data-confirm
                              data-confirm-title="Hapus Mahasiswa"
                              data-confirm-body="Yakin ingin menghapus data {{ $student->name }}?"
                              data-confirm-variant="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Nama</dt>
                        <dd class="col-sm-8">{{ $student->name }}</dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8">{{ $student->email }}</dd>

                        <dt class="col-sm-4 text-muted">Jurusan</dt>
                        <dd class="col-sm-8">{{ $student->major }}</dd>

                        <dt class="col-sm-4 text-muted">Tahun Masuk</dt>
                        <dd class="col-sm-8 font-monospace">{{ $student->enrollment_year }}</dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $student->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </dd>

                        @if ($student->user)
                        <dt class="col-sm-4 text-muted">Akun Terhubung</dt>
                        <dd class="col-sm-8">
                            <span class="font-monospace">{{ $student->user->email }}</span>
                            <span class="badge bg-secondary ms-1">{{ $student->user->roleLabel() }}</span>
                        </dd>
                        @endif

                        <dt class="col-sm-4 text-muted">Ditambahkan</dt>
                        <dd class="col-sm-8 text-muted small">{{ $student->created_at->diffForHumans() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Run tests**

```bash
php artisan test
```

Expected: All pass.

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php \
        resources/views/student/dashboard.blade.php \
        resources/views/students/show.blade.php
git commit -m "feat: gate delete button, add profile CTA, create students show view"
```

---

## Chunk 7: Integration Tests

Add remaining test cases to `RoleAccessTest` and verify the full test suite is green.

### Task 21: Add remaining test cases to `RoleAccessTest`

**Files:**
- Modify: `tests/Feature/RoleAccessTest.php`

- [ ] **Step 1: Add new test cases**

Append to the existing `RoleAccessTest` class:

```php
use App\Models\Student;

// -- Profile routes ---------------------------------------------------

public function test_student_can_access_own_profile(): void
{
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->get(route('profile.show'));

    // StudentProfileController@show redirects to profile.edit when no profile
    $response->assertRedirect(route('profile.edit'));
}

public function test_student_can_access_profile_edit(): void
{
    $student = User::factory()->student()->create();

    $response = $this->actingAs($student)->get(route('profile.edit'));

    $response->assertOk();
}

public function test_admin_cannot_access_profile_routes(): void
{
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('profile.show'));

    $response->assertRedirect(route('dashboard'));
}

// -- User management --------------------------------------------------

public function test_super_admin_can_access_user_management(): void
{
    $superAdmin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($superAdmin)->get(route('users.index'));

    $response->assertOk();
}

public function test_admin_cannot_access_user_management(): void
{
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertRedirect(route('dashboard'));
}

public function test_super_admin_can_change_student_role_to_admin(): void
{
    $superAdmin = User::factory()->superAdmin()->create();
    $target     = User::factory()->student()->create();

    $response = $this->actingAs($superAdmin)
        ->put(route('users.update-role', $target), ['role' => 'admin']);

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'admin']);
}

public function test_super_admin_cannot_change_another_super_admins_role(): void
{
    $superAdmin = User::factory()->superAdmin()->create();
    $target     = User::factory()->superAdmin()->create();

    $response = $this->actingAs($superAdmin)
        ->put(route('users.update-role', $target), ['role' => 'admin']);

    $response->assertForbidden();
}

// -- students.show ----------------------------------------------------

public function test_admin_can_access_students_show(): void
{
    $admin   = User::factory()->admin()->create();
    $student = Student::factory()->create();

    $response = $this->actingAs($admin)->get(route('students.show', $student));

    $response->assertOk();
}
```

- [ ] **Step 2: Run all tests**

```bash
php artisan test
```

Expected: All tests PASS. If any fail, debug before proceeding.

- [ ] **Step 3: Final commit**

```bash
git add tests/Feature/RoleAccessTest.php
git commit -m "test: add integration tests for role access, profile routes, and user management"
```

---

## Final Verification

- [ ] **Run complete test suite**

```bash
php artisan test --coverage
```

Expected: All tests green.

- [ ] **Smoke test manually**
  1. Login as `student@studentms.test` → verify redirect to student portal, profile CTA visible
  2. Login as `admin@studentms.test` → verify student list visible, delete button hidden
  3. Login as `superadmin@studentms.test` → verify delete button visible, `/users` page accessible, role change works

```bash
php artisan serve
```
