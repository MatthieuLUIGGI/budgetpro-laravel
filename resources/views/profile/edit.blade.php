@extends('layouts.app')

@section('title','Profil')

@section('content')
<div class="profile-wrapper">
    <div class="profile-card">
        <h2><i class="fas fa-user-circle"></i> Mon Profil</h2>

        @if (session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
            @csrf
            @method('PUT')
            <div class="form-row">
                <label>Nom</label>
                <input type="text" name="name" value="{{ old('name',$user->name) }}" required>
            </div>
            <div class="form-row">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email',$user->email) }}" required>
            </div>
            <hr>
            <div class="form-row">
                <label>Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small></label>
                <input type="password" name="password" autocomplete="new-password">
            </div>
            <div class="form-row">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </div>
            <button type="submit" class="save-profile">Enregistrer les modifications</button>
        </form>

        <div class="danger-zone" style="margin-top:2rem; padding:1.25rem; border:1px solid #dc2626; border-radius:8px; background:#fff5f5;">
            <h3 style="color:#dc2626; margin-top:0;">Zone dangereuse</h3>
            <p style="margin-bottom:1rem;">La suppression de votre compte est définitive et effacera toutes vos transactions.</p>
            <button type="button" onclick="document.getElementById('deleteAccountModal').style.display='block'" style="background:#dc2626; color:#fff; border:none; padding:0.75rem 1rem; border-radius:6px; cursor:pointer;">Supprimer mon compte</button>
        </div>

        <div id="deleteAccountModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:1000;">
            <div style="max-width:460px; margin:6% auto; background:#fff; padding:1.5rem 1.75rem; border-radius:10px; position:relative;">
                <h3 style="margin-top:0;">Confirmer la suppression</h3>
                <p>Veuillez confirmer votre mot de passe pour supprimer votre compte. Cette action est irréversible.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Êtes-vous sûr ? Cette action est irréversible.');">
                    @csrf
                    @method('DELETE')
                    <div class="form-row">
                        <label>Mot de passe</label>
                        <input type="password" name="password_confirm" required autocomplete="current-password">
                        @error('password_confirm')
                            <div class="alert error" style="margin-top:0.5rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                        <button type="submit" style="background:#dc2626; color:#fff; border:none; padding:0.6rem 1rem; border-radius:6px; cursor:pointer; flex:1;">Oui, supprimer</button>
                        <button type="button" onclick="document.getElementById('deleteAccountModal').style.display='none'" style="background:#6b7280; color:#fff; border:none; padding:0.6rem 1rem; border-radius:6px; cursor:pointer; flex:1;">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('styles')
    @vite('resources/css/profile.css')
@endpush
@endsection
