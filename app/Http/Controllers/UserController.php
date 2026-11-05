<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        // Sesuaikan 'role' dengan struktur database Anda (misal role = 'customer' atau is_admin = false)
        $users = User::where('role', 'customer')
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', "Kata sandi untuk pengguna {$user->name} berhasil di-reset.");
    }
}