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
        $search = trim((string) $request->input('search', ''));
        $status = $request->string('status')->toString();
        $major = $request->string('major')->toString();

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
            'total' => Student::query()->count(),
            'active' => Student::query()->where('status', 'active')->count(),
            'inactive' => Student::query()->where('status', 'inactive')->count(),
            'majors' => Student::query()->whereNotNull('major')->distinct()->count('major'),
        ];

        return view('dashboard', compact('students', 'majors', 'stats'));
    }

    public function create(): View
    {
        return view('students.create');
    }

    public function store(StudentStoreRequest $request): RedirectResponse
    {
        Student::query()->create($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function show(Student $student): View
    {
        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    public function update(StudentUpdateRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
