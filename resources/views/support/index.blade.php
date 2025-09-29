@extends('layouts.app')
@section('title','Support')
@section('content')
<div class="support-page">
    <h2>Support & Contact</h2>
    <p>Besoin d'aide ? Envoyez-nous un message.</p>

    @if (session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('support.send') }}" class="support-form">
        @csrf
        <div class="form-row">
            <label for="subject">Sujet</label>
            <input type="text" id="subject" name="subject" required>
        </div>
        <div class="form-row">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" required></textarea>
        </div>
        <button type="submit" class="support-submit">Envoyer</button>
    </form>
</div>
@push('styles')
<style>
.legal-page, .support-page { background: var(--card-bg); padding: 32px; border-radius: 15px; box-shadow: 0 10px 20px var(--shadow); animation: fadeIn .5s ease; max-width: 900px; margin: 0 auto; }
.legal-page h2, .support-page h2 { color: var(--primary); margin-bottom: 16px; }
.legal-page h3 { margin-top: 18px; font-size: 1rem; color: var(--secondary); }
.legal-page ul { margin: 8px 0 16px 20px; }
.support-form .form-row { margin-bottom: 18px; display:flex; flex-direction:column; }
.support-form label { font-size:.8rem; font-weight:600; text-transform: uppercase; letter-spacing:.5px; color: var(--gray); margin-bottom:6px; }
.support-form input, .support-form textarea { padding: 12px 14px; border:1px solid #ddd; border-radius:10px; font-family: inherit; font-size:.9rem; }
.support-form textarea { resize: vertical; }
.support-form input:focus, .support-form textarea:focus { outline:none; border-color: var(--primary); box-shadow:0 0 0 3px rgba(67,97,238,.15); }
.support-submit { background: var(--primary); color:#fff; border:none; padding:14px 20px; width:100%; border-radius:10px; font-weight:600; letter-spacing:.5px; cursor:pointer; transition: background .3s ease; }
.support-submit:hover { background: var(--secondary); }
.alert.success { background: rgba(46, 204, 113, 0.12); color: var(--income); border:1px solid rgba(46,204,113,.3); padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:.85rem; }
@media (max-width:600px){ .legal-page, .support-page { padding:22px; } }
</style>
@endpush
@endsection
