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
        $student = Student::query()
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $student->update($request->validated());

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
