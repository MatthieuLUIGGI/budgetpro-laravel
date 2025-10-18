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
                            <option value="Restaurant">Restaurant</option>
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

                    <div class="form-group">
                        <label for="recurring"><i class="fas fa-redo" aria-hidden="true"></i> Récurrente ?</label>
                        <select id="recurring">
                            <option value="no">Non</option>
                            <option value="yes">Oui</option>
                        </select>
                    </div>

                    <div id="recurrence-fields" style="display:none;">
                        <div class="form-row-duo">
                            <div class="form-group">
                                <label for="recurrence_frequency"><i class="fas fa-clock" aria-hidden="true"></i> Fréquence</label>
                                <select id="recurrence_frequency">
                                    <option value="monthly">Mensuelle</option>
                                    <option value="weekly">Hebdomadaire</option>
                                    <option value="daily">Quotidienne</option>
                                    <option value="yearly">Annuelle</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="recurrence_interval"><i class="fas fa-repeat" aria-hidden="true"></i> Intervalle</label>
                                <input type="number" id="recurrence_interval" min="1" value="1" placeholder="Tous les N cycles">
                            </div>
                        </div>

                        <div class="form-row-duo">
                            <div class="form-group" id="recurrence-day-wrapper" style="display:none;">
                                <label for="recurrence_day"><i class="fas fa-calendar-day" aria-hidden="true"></i> Jour du mois</label>
                                <input type="number" id="recurrence_day" min="1" max="31" placeholder="1-31">
                                <small>Par défaut: le jour de la date choisie</small>
                            </div>
                            <div class="form-group">
                                <label for="recurrence_end_date"><i class="fas fa-flag-checkered" aria-hidden="true"></i> Fin (optionnel)</label>
                                <input type="date" id="recurrence_end_date">
                            </div>
                        </div>
                    </div>
                    <button type="submit">Ajouter <i class="fas fa-plus-circle"></i></button>
                </form>
            </div>

            <div class="transactions">
                <h2><i class="fas fa-list-ul" aria-hidden="true"></i> Historique des Transactions</h2>
                <div class="transaction-list" id="transaction-list"></div>
                <div id="pagination" style="display:flex; align-items:center; justify-content:center; gap:10px; margin-top:12px;">
                    <button id="prev-page" style="width:auto; padding:8px 12px;">« Précédent</button>
                    <span id="page-info" style="min-width:140px; text-align:center; color:var(--gray)">Page 1</span>
                    <button id="next-page" style="width:auto; padding:8px 12px;">Suivant »</button>
                </div>
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

    <!-- Modale d'édition de transaction -->
    <div id="edit-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:10000; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#1f2230; color:#fff; padding:16px; border-radius:12px; width:min(420px, 92vw); max-height:80vh; overflow-y:auto; overscroll-behavior:contain; box-shadow:0 10px 30px rgba(0,0,0,0.4);">
            <h3 style="margin-top:0;">Modifier la transaction</h3>
            <form id="edit-transaction-form" style="display:flex; flex-direction:column; gap:12px;">
                <input type="hidden" id="edit-id">
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <input type="text" id="edit-description" required>
                </div>
                <div class="form-row-duo">
                    <div class="form-group">
                        <label for="edit-amount">Montant (€)</label>
                        <input type="number" id="edit-amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-type">Type</label>
                        <select id="edit-type" required>
                            <option value="income">Revenu</option>
                            <option value="expense">Dépense</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-category">Catégorie</label>
                    <select id="edit-category" required>
                        <option value="Salaire">Salaire</option>
                        <option value="Argent de poche">Argent de poche</option>
                        <option value="Investissement">Investissement</option>
                        <option value="Frais carte">Frais carte</option>
                        <option value="Alimentation">Courses</option>
                        <option value="Logement">Logement</option>
                        <option value="Factures">Factures</option>
                        <option value="Restaurant">Restaurant</option>
                        <option value="Transport">Transport</option>
                        <option value="Loisirs">Loisirs</option>
                        <option value="Bien-être">Bien-être</option>
                        <option value="Santé">Santé</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-date">Date</label>
                    <input type="date" id="edit-date" required>
                </div>
                <details id="edit-recurrence-details" style="background:#26293a; padding:10px; border-radius:8px;">
                    <summary style="cursor:pointer;">Options de récurrence</summary>
                    <div class="form-row-duo" style="margin-top:10px;">
                        <div class="form-group">
                            <label for="edit-is-recurring">Récurrente ?</label>
                            <select id="edit-is-recurring">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-recurrence-frequency">Fréquence</label>
                            <select id="edit-recurrence-frequency">
                                <option value="monthly">Mensuelle</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="daily">Quotidienne</option>
                                <option value="yearly">Annuelle</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-duo">
                        <div class="form-group">
                            <label for="edit-recurrence-interval">Intervalle</label>
                            <input type="number" id="edit-recurrence-interval" min="1" value="1">
                        </div>
                        <div class="form-group" id="edit-recurrence-day-wrapper">
                            <label for="edit-recurrence-day">Jour du mois</label>
                            <input type="number" id="edit-recurrence-day" min="1" max="31">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-recurrence-end-date">Fin (optionnel)</label>
                        <input type="date" id="edit-recurrence-end-date">
                    </div>
                </details>
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:8px;">
                    <button type="button" id="edit-cancel" style="background:var(--gray); color:#fff; padding:8px 14px; border:none; border-radius:8px;">Annuler</button>
                    <button type="submit" style="background:var(--primary); color:#fff; padding:8px 14px; border:none; border-radius:8px;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script JS chargé via Vite -->
</body>
</html>