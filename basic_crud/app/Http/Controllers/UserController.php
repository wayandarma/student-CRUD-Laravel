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
