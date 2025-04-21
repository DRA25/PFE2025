<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get()->map(function ($user) {
            $user->roles = $user->roles->pluck('name')->toArray(); // Only pass role names
            return $user;
        });

        $roles = Role::all();

        return inertia('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function assignRoles(Request $request, User $user)
    {
        // Validate the incoming role data
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name', // Ensure roles exist in the roles table
        ]);

        // Sync the roles with the user (this will replace any existing roles with the new ones)
        $user->syncRoles($request->roles);

        // After roles are updated, return the updated users list with roles
        $users = User::with('roles')->get();
        $roles = Role::all();

        // Pass the updated data to Inertia
        return inertia('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'success' => 'Roles updated successfully!', // Optional: show success message
        ]);
    }


    public function destroyRole(Role $role)
    {
        // Optional: prevent deleting essential roles
        if (in_array($role->name, ['admin'])) {
            return back()->withErrors(['error' => 'Cannot delete admin role.']);
        }

        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function removeRole(Request $request, User $user)
    {
        // Remove role from user
        $user->removeRole($request->role);

        // Reload user roles after removal
        $user->load('roles');

        return inertia('Users/Index', [
            'users' => User::with('roles')->get(),
            'roles' => Role::all(),
            'success' => 'Role removed successfully!'
        ]);
    }

}
