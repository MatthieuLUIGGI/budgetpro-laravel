<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetPro - Gestion de Budget</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        @include('layouts.header')

        <!-- Le reste du code HTML reste identique à votre version index.html -->
        <div class="dashboard">
            <div class="balance-card">
                <h2><i class="fas fa-wallet" aria-hidden="true"></i> Solde Total</h2>
                <div class="amount" id="total-balance">0,00 €</div>
                <div class="summary">
                    <div class="income">
                        <span><i class="fas fa-arrow-trend-up" aria-hidden="true"></i> Revenus</span>
                        <span id="total-income">0,00 €</span>
                    </div>
                    <div class="expenses">
                        <span><i class="fas fa-arrow-trend-down" aria-hidden="true"></i> Dépenses</span>
                        <span id="total-expenses">0,00 €</span>
                    </div>
                </div>
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label for="month-filter"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Mois:</label>
                    <select id="month-filter">
                        <option value="all">Tous les mois</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="year-filter"><i class="fas fa-calendar" aria-hidden="true"></i> Année:</label>
                    <select id="year-filter"></select>
                </div>
                <div class="filter-group">
                    <label for="category-filter"><i class="fas fa-tags" aria-hidden="true"></i> Catégorie:</label>
                    <select id="category-filter">
                        <option value="all">Toutes catégories</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="transaction-form">
                <h2><i class="fas fa-plus-circle" aria-hidden="true"></i> Nouvelle Transaction</h2>
                <form id="add-transaction">
                    @csrf
                    <div class="form-group">
                        <label for="description"><i class="fas fa-file-lines" aria-hidden="true"></i> Description</label>
                        <input type="text" id="description" required>
                    </div>
                    <div class="form-group">
                        <label for="amount"><i class="fas fa-euro-sign" aria-hidden="true"></i> Montant (€)</label>
                        <input type="number" id="amount" step="0.01" required>
                    </div>
                    <div class="form-row-duo">
                        <div class="form-group">
                            <label for="type"><i class="fas fa-exchange-alt" aria-hidden="true"></i> Type</label>
                            <select id="type" required>
                                <option value="income">Revenu</option>
                                <option value="expense">Dépense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recurring"><i class="fas fa-sync" aria-hidden="true"></i> Récurrence</label>
                            <select id="recurring" required>
                                <option value="no" selected>Non</option>
                                <option value="yes">Oui</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="recurrence-day-wrapper" style="display:none;">
                        <label for="recurrence-day"><i class="fas fa-calendar-day" aria-hidden="true"></i> Jour du mois</label>
                        <select id="recurrence-day">
                            <!-- Options 1..31 générées côté client si besoin -->
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{$d}}">{{$d}}</option>
                            @endfor
                        </select>
                        <small style="display:block;margin-top:4px;color:#888;font-size:12px;">La transaction sera automatiquement ajoutée chaque mois à cette date (ajustée si le mois est plus court).</small>
                    </div>
                    <div class="form-group">
                        <label for="category"><i class="fas fa-tag" aria-hidden="true"></i> Catégorie</label>
                        <select id="category" required>
                            <option value="Salaire">Salaire</option>
                            <option value="Argent de poche">Argent de poche</option>
                            <option value="Investissement">Investissement</option>
                            <option value="Frais carte">Frais carte</option>
                            <option value="Alimentation">Courses</option>
                            <option value="Logement">Logement</option>
                            <option value="Factures">Factures</option>
                            <option value="Restaurant">Restaurants</option>
                            <option value="Transport">Transport</option>
                            <option value="Loisirs">Loisirs</option>
                            <option value="Bien-être">Bien-être</option>
                            <option value="Santé">Santé</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date"><i class="fas fa-calendar-day" aria-hidden="true"></i> Date</label>
                        <input type="date" id="date" required>
                    </div>
                    <button type="submit">Ajouter <i class="fas fa-plus-circle"></i></button>
                </form>
            </div>

            <div class="transactions">
                <h2><i class="fas fa-list-ul" aria-hidden="true"></i> Historique des Transactions</h2>
                <div class="transaction-list" id="transaction-list"></div>
            </div>
        </div>

        <div class="charts">
            <div class="chart-container">
                <h2><i class="fas fa-chart-line" aria-hidden="true"></i> Évolution Mensuelle</h2>
                <div class="chart" id="monthly-chart"></div>
            </div>
        </div>

        @include('layouts.footer')
    </div>

    <!-- Container pour les notifications -->
    <div class="notification-container" id="notification-container"></div>

    <!-- Script JS chargé via Vite -->
</body>
</html>