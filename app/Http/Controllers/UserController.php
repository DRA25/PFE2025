<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'centre')->get();
        return inertia('Users/Index', compact('users'));
    }

    public function create()
    {
        $centres = Centre::all()->pluck('id_centre', 'id_centre');
        return inertia('Users/Create', compact('centres'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'id_centre' => 'nullable|exists:centres,id_centre',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'id_centre' => $request->id_centre,
            ]);

            return redirect()->route('users.index')->with('success', 'User created successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create user');
        }
    }

    public function show(User $user)
    {
        return inertia('Users/Show', compact('user'));
    }

    public function edit(User $user)
    {
        $centres = Centre::all()->pluck('id_centre', 'id_centre');
        return inertia('Users/Edit', [
            'user' => $user->only(['id', 'name', 'email', 'id_centre']),
            'centres' => $centres,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', Rules\Password::defaults()],
            'id_centre' => 'nullable|exists:centres,id_centre',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'id_centre' => $request->id_centre,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->route('users.index')->with('success', 'User updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update user');
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete user');
        }
    }
}
