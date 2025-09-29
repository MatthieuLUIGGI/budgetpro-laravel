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
    </div>
</div>
@push('styles')
    @vite('resources/css/profile.css')
@endpush
@endsection
