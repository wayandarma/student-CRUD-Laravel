<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\StudentUpdateRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('major'),  fn ($q) => $q->where('major', $request->string('major')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $majors = Student::distinct()->orderBy('major')->pluck('major')->filter()->values();

        return view('dashboard', compact('students', 'majors'));
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
