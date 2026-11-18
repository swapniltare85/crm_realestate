<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserWebController extends Controller
{
    public function index()
    {
        $users = User::withCount(['assignedLeads', 'followups'])
            ->latest('id')
            ->get();

        $totalUsers = $users->count();
        $totalAgents = $users->where('role', 'sales_agent')->count();
        $activeUsers = $users->where('status', 'active')->count();

        return view('users.index', compact('users', 'totalUsers', 'totalAgents', 'activeUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,sales_agent,telecaller',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', "Team member {$validated['name']} added successfully!");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,sales_agent,telecaller',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Prevent admin from removing their own admin role or deactivating themselves
        if ($user->id === Auth::id()) {
            $validated['role'] = 'admin';
            $validated['status'] = 'active';
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', "User {$user->name} updated successfully!");
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot deactivate your own administrative account.']);
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return redirect()->route('users.index')->with('success', "User {$user->name} is now {$newStatus}.");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own administrative account.']);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$userName} removed from system.");
    }
}
