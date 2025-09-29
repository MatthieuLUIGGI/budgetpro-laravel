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
        <header>
            <h1><i class="fas fa-chart-pie"></i> BudgetPro</h1>
            <p>Gestion professionnelle de vos finances</p>
            <div class="user-menu">
                <span>Bonjour, {{ Auth::user()->name }}</span>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Déconnexion <i class="fas fa-sign-out-alt"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </header>

        <!-- Le reste du code HTML reste identique à votre version index.html -->
        <div class="dashboard">
            <div class="balance-card">
                <h2>Solde Total</h2>
                <div class="amount" id="total-balance">0,00 €</div>
                <div class="summary">
                    <div class="income">
                        <span>Revenus</span>
                        <span id="total-income">0,00 €</span>
                    </div>
                    <div class="expenses">
                        <span>Dépenses</span>
                        <span id="total-expenses">0,00 €</span>
                    </div>
                </div>
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label for="month-filter">Mois:</label>
                    <select id="month-filter">
                        <option value="all">Tous les mois</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="year-filter">Année:</label>
                    <select id="year-filter"></select>
                </div>
                <div class="filter-group">
                    <label for="category-filter">Catégorie:</label>
                    <select id="category-filter">
                        <option value="all">Toutes catégories</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="transaction-form">
                <h2>Nouvelle Transaction</h2>
                <form id="add-transaction">
                    @csrf
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Montant (€)</label>
                        <input type="number" id="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" required>
                            <option value="income">Revenu</option>
                            <option value="expense">Dépense</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category">Catégorie</label>
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
                        <label for="date">Date</label>
                        <input type="date" id="date" required>
                    </div>
                    <button type="submit">Ajouter <i class="fas fa-plus-circle"></i></button>
                </form>
            </div>

            <div class="transactions">
                <h2>Historique des Transactions</h2>
                <div class="transaction-list" id="transaction-list"></div>
            </div>
        </div>

        <div class="charts">
            <div class="chart-container">
                <h2>Évolution Mensuelle</h2>
                <div class="chart" id="monthly-chart"></div>
            </div>
        </div>
    </div>

    <!-- Container pour les notifications -->
    <div class="notification-container" id="notification-container"></div>

    <!-- Script JS chargé via Vite -->
</body>
</html>