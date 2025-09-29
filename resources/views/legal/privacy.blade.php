@extends('layouts.app')
@section('title','Confidentialité')
@section('content')
<div class="legal-page">
    <h2>Politique de Confidentialité</h2>
    <p>Dernière mise à jour : {{ date('d/m/Y') }}</p>
    <p>Cette politique explique quelles données nous collectons, pourquoi et comment elles sont utilisées.</p>
    <h3>1. Données collectées</h3>
    <ul>
        <li>Données de compte (nom, email)</li>
        <li>Données d'utilisation de l'application (transactions enregistrées)</li>
    </ul>
    <h3>2. Utilisation des données</h3>
    <p>Les données servent exclusivement à fournir les fonctionnalités de gestion budgétaire.</p>
    <h3>3. Sécurité</h3>
    <p>Les mots de passe sont stockés sous forme hachée. Aucune donnée n'est vendue ou partagée à des tiers.</p>
    <h3>4. Vos droits</h3>
    <p>Vous pouvez demander la suppression ou l'export de vos données via la page Support.</p>
    <h3>5. Contact</h3>
    <p>Pour toute question : <a href="{{ route('support') }}">Support</a></p>
</div>
@endsection
