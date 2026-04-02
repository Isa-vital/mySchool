<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        if ($request->filled('roles')) {
            $roleNames = Role::whereIn('id', $request->roles)->pluck('name');
            $user->syncRoles($roleNames);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $userRoleIds = $user->roles->pluck('id')->toArray();
        return view('users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        if ($request->has('roles')) {
            $roleNames = Role::whereIn('id', $request->roles ?? [])->pluck('name');
            $user->syncRoles($roleNames);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        if ($user->hasRole('Super Admin')) {
            return redirect()->route('users.index')->with('error', 'The Super Admin account cannot be deleted.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);
        return redirect()->route('users.index')->with('success', $user->name . ' has been approved.');
    }

    public function reject(User $user)
    {
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('users.index')->with('error', 'The Super Admin account cannot be rejected.');
        }

        $user->update(['is_approved' => false]);
        return redirect()->route('users.index')->with('success', $user->name . ' has been rejected.');
    }
}
