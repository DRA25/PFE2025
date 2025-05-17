<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleUserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get()->map(function ($user) {
            $user->roles = $user->roles->pluck('name')->toArray();
            return $user;
        });

        $roles = Role::all();

        return inertia('Roles/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function assignRoles(Request $request, User $user)
    {
        //$validated=$request->valivate
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('roles.index')->with('success', 'Roles updated successfully.');
    }

    public function removeRole(Request $request, User $user)
    {
        $user->removeRole($request->role);
        $user->load('roles');

        return redirect()->route('roles.index')->with('success', 'Role removed successfully.');
    }
}
