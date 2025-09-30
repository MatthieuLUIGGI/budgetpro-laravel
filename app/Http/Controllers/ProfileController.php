<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable','confirmed','min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if(!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('profile.edit')->with('status','Profil mis à jour avec succès');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password_confirm' => ['required','string'],
        ]);

        if(!\Hash::check($request->input('password_confirm'), $user->password)) {
            return back()->withErrors(['password_confirm' => 'Mot de passe incorrect'])->withInput();
        }

        Auth::logout();

        // Suppression en cascade: transactions liées (clé étrangère onDelete cascade)
        $user->delete();

        // Invalidation de la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status','Compte supprimé avec succès.');
    }
}
