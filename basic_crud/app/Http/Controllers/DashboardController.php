<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->isStudent()) {
            $user->loadMissing('studentProfile');

            return view('student.dashboard', [
                'studentProfile' => $user->studentProfile,
            ]);
        }

        return redirect()->route('students.index');
    }
}
