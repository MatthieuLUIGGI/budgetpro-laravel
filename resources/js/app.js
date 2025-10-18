import './bootstrap';

// Durée par défaut (en ms) pour toutes les notifications
const DEFAULT_NOTIFICATION_DURATION = 7000; // 7 secondes

// Version Laravel avec API AJAX
document.addEventListener('DOMContentLoaded', async function() {
    await initApp();
});

async function initApp() {
    // Initialiser la date du jour si le champ existe (page dashboard)
    const dateEl = document.getElementById('date');
    if (dateEl) {
        dateEl.valueAsDate = new Date();
    }

    // Initialiser bascule thème
    initThemeToggle();
    enhanceSelectsWithIcons();

    // Gestion affichage récurrence
    const recurringSelect = document.getElementById('recurring');
    const recurrenceFields = document.getElementById('recurrence-fields');
    const recurrenceDayWrapper = document.getElementById('recurrence-day-wrapper');
    const recurrenceFrequency = document.getElementById('recurrence_frequency');
    if (recurringSelect && recurrenceFields && recurrenceFrequency) {
        const updateVisibility = () => {
            const isRecurring = recurringSelect.value === 'yes';
            recurrenceFields.style.display = isRecurring ? 'block' : 'none';
            const freq = recurrenceFrequency.value;
            if (isRecurring && (freq === 'monthly' || freq === 'yearly')) {
                recurrenceDayWrapper.style.display = 'block';
            } else {
                recurrenceDayWrapper.style.display = 'none';
            }
        };
        recurringSelect.addEventListener('change', updateVisibility);
        recurrenceFrequency.addEventListener('change', updateVisibility);
        updateVisibility();
    }

    // Si on est sur la page dashboard (éléments présents)
    const hasTransactionsUI = document.getElementById('transaction-list');
    if (hasTransactionsUI) {
        // Initialiser les filtres (liste locale le temps du premier chargement)
        initFilters([]);

        // Charger la 1ère page via API paginée
        await applyFilters(1);

        // Gestion du formulaire
        const addForm = document.getElementById('add-transaction');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                addTransaction();
            });
        }

        // Gestion des filtres
        const monthFilter = document.getElementById('month-filter');
        const yearFilter = document.getElementById('year-filter');
        const categoryFilter = document.getElementById('category-filter');
        if (monthFilter) monthFilter.addEventListener('change', async () => await applyFilters(1));
        if (yearFilter) yearFilter.addEventListener('change', async () => await applyFilters(1));
        if (categoryFilter) categoryFilter.addEventListener('change', async () => await applyFilters(1));

        // Pagination
        const prev = document.getElementById('prev-page');
        const next = document.getElementById('next-page');
        if (prev) prev.addEventListener('click', async () => await changePage(-1));
        if (next) next.addEventListener('click', async () => await changePage(1));
    }
}

function initThemeToggle() {
    const toggleBtn = document.getElementById('theme-toggle');
    if (!toggleBtn) return;
    const saved = localStorage.getItem('bp_theme');
    if (saved === 'dark') {
        document.body.classList.add('theme-dark');
        toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
    }
    toggleBtn.addEventListener('click', () => {
        const dark = document.body.classList.toggle('theme-dark');
        localStorage.setItem('bp_theme', dark ? 'dark' : 'light');
        toggleBtn.innerHTML = dark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        showNotification('info', 'Thème', dark ? 'Thème sombre activé' : 'Thème clair activé');
    });
}

// Amélioration visuelle des selects avec pseudo icônes (préfixe Unicode)
function enhanceSelectsWithIcons(includeFilters = false) {
    const map = {
        'income': '💰 ',
        'expense': '🧾 ',
        'Salaire': '💼 ',
        'Argent de poche': '🪙 ',
        'Investissement': '📈 ',
        'Frais carte': '💳 ',
        'Alimentation': '🛒 ',
        'Courses': '🛒 ',
        'Logement': '🏠 ',
        'Factures': '🧾 ',
        'Restaurant': '🍽️ ',
        'Transport': '🚌 ',
        'Loisirs': '🎮 ',
        'Bien-être': '💆 ',
        'Santé': '🩺 ',
        'Autre': '📁 '
    };
    // Type select
    const typeSel = document.getElementById('type');
    if (typeSel) {
        for (const opt of typeSel.options) {
            const base = opt.value === 'income' ? 'Revenu' : opt.value === 'expense' ? 'Dépense' : opt.textContent;
            opt.textContent = (map[opt.value] || '') + base;
        }
    }
    // Category select
    const catSel = document.getElementById('category');
    if (catSel) {
        for (const opt of catSel.options) {
            const label = opt.textContent.trim();
            // Éviter double icône
            if (!/^\p{Extended_Pictographic}/u.test(label)) {
                opt.textContent = (map[label] || '') + label;
            }
        }
    }

    if (includeFilters) {
        // Filtres
        const catFilter = document.getElementById('category-filter');
        if (catFilter) {
            for (const opt of catFilter.options) {
                const label = opt.textContent.trim();
                if (opt.value === 'all') {
                    if (!/^\p{Extended_Pictographic}/u.test(label)) opt.textContent = '🌐 ' + label;
                } else if (!/^\p{Extended_Pictographic}/u.test(label)) {
                    opt.textContent = (map[label] || '') + label;
                }
            }
        }
        const monthFilter = document.getElementById('month-filter');
        if (monthFilter) {
            for (const opt of monthFilter.options) {
                const label = opt.textContent.trim();
                if (!/^\p{Extended_Pictographic}/u.test(label)) {
                    opt.textContent = (opt.value === 'all' ? '📅 ' : '📅 ') + label;
                }
            }
        }
        const yearFilter = document.getElementById('year-filter');
        if (yearFilter) {
            for (const opt of yearFilter.options) {
                const label = opt.textContent.trim();
                if (!/^\p{Extended_Pictographic}/u.test(label)) {
                    opt.textContent = (opt.value === 'all' ? '🗓️ ' : '🗓️ ') + label;
                }
            }
        }
    }
}

function buildQuery(page = 1) {
    const month = document.getElementById('month-filter')?.value;
    const year = document.getElementById('year-filter')?.value;
    const category = document.getElementById('category-filter')?.value;
    const params = new URLSearchParams();
    if (month && month !== 'all') params.set('month', month);
    if (year && year !== 'all') params.set('year', year);
    if (category && category !== 'all') params.set('category', category);
    params.set('per_page', '25');
    params.set('page', String(page));
    return params;
}

async function applyFilters(page = 1) {
    try {
        const params = buildQuery(page);
        const response = await fetch(`/api/transactions?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) throw new Error('Erreur de chargement');
        const payload = await response.json();
        let list = payload;
        if (payload && payload.data) {
            list = payload.data;
            updatePaginationUI(payload);
        } else {
            const pag = document.getElementById('pagination');
            if (pag) pag.style.display = 'none';
        }
        list.sort((a, b) => new Date(b.date) - new Date(a.date));
        window.allTransactions = list;
        renderTransactions(window.allTransactions);
        updateDashboard(window.allTransactions);
        updateCharts(window.allTransactions);
    } catch (e) {
        console.error('Erreur chargement transactions', e);
        window.allTransactions = [];
        renderTransactions([]);
        updateDashboard([]);
        updateCharts([]);
    }
}

function updatePaginationUI(paginated) {
    const p = document.getElementById('pagination');
    if (!p) return;
    p.style.display = 'flex';
    const info = document.getElementById('page-info');
    if (info) info.textContent = `Page ${paginated.current_page} / ${paginated.last_page}`;
    const prev = document.getElementById('prev-page');
    const next = document.getElementById('next-page');
    if (prev) prev.disabled = paginated.current_page <= 1;
    if (next) next.disabled = paginated.current_page >= paginated.last_page;
    p.dataset.current = String(paginated.current_page);
}

async function changePage(delta) {
    const p = document.getElementById('pagination');
    const cur = parseInt(p?.dataset.current || '1', 10);
    const next = Math.max(1, cur + delta);
    await applyFilters(next);
}

async function addTransaction() {
    // Système de notifications - helpers intégrés ci-dessous si non présents
    const formData = new FormData();
    const descriptionInput = document.getElementById('description');
    const descriptionValue = descriptionInput.value.trim();
    formData.append('description', descriptionValue);
    const amountInput = document.getElementById('amount');
    const rawAmountValue = amountInput.value.trim();
    formData.append('amount', rawAmountValue);
    formData.append('type', document.getElementById('type').value);
    formData.append('category', document.getElementById('category').value);
    formData.append('date', document.getElementById('date').value);
    // Champs de récurrence
    const uiRecurring = document.getElementById('recurring');
    if (uiRecurring && uiRecurring.value === 'yes') {
        formData.append('is_recurring', '1');
        const freq = document.getElementById('recurrence_frequency').value;
        formData.append('recurrence_frequency', freq);
        const interval = document.getElementById('recurrence_interval').value || '1';
        formData.append('recurrence_interval', interval);
        if (freq === 'monthly' || freq === 'yearly') {
            const day = document.getElementById('recurrence_day').value;
            if (day) formData.append('recurrence_day', day);
        }
        const end = document.getElementById('recurrence_end_date').value;
        if (end) formData.append('recurrence_end_date', end);
    }
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    const button = document.querySelector('#add-transaction button');
    const originalText = button.innerHTML;
    
    // État de chargement
    button.innerHTML = 'Ajout en cours... <i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    try {
        const response = await fetch('/api/transactions', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Erreur lors de l\'ajout');

        const result = await response.json();
        
    document.getElementById('add-transaction').reset();
    document.getElementById('date').valueAsDate = new Date();
        
    // Recharger la page courante
    const cur = parseInt(document.getElementById('pagination')?.dataset.current || '1', 10);
    await applyFilters(cur || 1);
        
        // Notification de succès
        const finalDescription = (result && result.description) ? result.description : descriptionValue;
        const apiAmount = (result && typeof result.amount !== 'undefined') ? result.amount : rawAmountValue;
        let numericAmount = parseFloat(apiAmount);
        const formattedAmount = isNaN(numericAmount) ? '' : ` ${numericAmount >= 0 ? '+' : ''}${numericAmount.toFixed(2)} €`;
        showNotification(
            'success', 
            'Transaction ajoutée !', 
            `Ajout de "${finalDescription || 'Sans description'}"${formattedAmount} avec succès.`
        );
        
        // Animation du bouton
        button.innerHTML = 'Ajouté ! <i class="fas fa-check"></i>';
        button.style.background = 'var(--success)';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.style.background = 'var(--primary)';
            button.disabled = false;
        }, 2000);
        
    } catch (e) {
        console.error("Erreur d'ajout", e);
        
        // Notification d'erreur
        showNotification(
            'error',
            'Erreur',
            "Impossible d'ajouter la transaction. Veuillez réessayer."
        );
        
        // Réinitialiser le bouton
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

async function deleteTransaction(id) {
    // Notification de confirmation améliorée
    const transaction = window.allTransactions.find(t => t.id === id);
    if (!transaction) return;
    
    const confirmationNotification = showNotification(
        'warning',
        'Confirmer la suppression',
        `Supprimer "${transaction.description}" de "${transaction.amount}"€ ? Cette action est irréversible.`,
        0 // Durée infinie jusqu'à action utilisateur (ne pas changer)
    );
    
    // Ajouter des boutons d'action à la notification
    const actionButtons = document.createElement('div');
    actionButtons.className = 'notification-actions';
    actionButtons.style.marginTop = '10px';
    actionButtons.style.display = 'flex';
    actionButtons.style.gap = '10px';
    
    actionButtons.innerHTML = `
        <button class="confirm-btn" style="
            background: var(--expense);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
        ">Oui, supprimer</button>
        <button class="cancel-btn" style="
            background: var(--gray);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
        ">Annuler</button>
    `;
    
    confirmationNotification.querySelector('.notification-content').appendChild(actionButtons);
    
    // Gérer les clics
    return new Promise((resolve) => {
        confirmationNotification.querySelector('.confirm-btn').onclick = async () => {
            hideNotification(confirmationNotification);
            
            try {
                const response = await fetch(`/api/transactions/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) throw new Error('Erreur de suppression');

                // Recharger la page courante
                const cur = parseInt(document.getElementById('pagination')?.dataset.current || '1', 10);
                await applyFilters(cur || 1);
                
                // Notification de succès
                showNotification(
                    'success',
                    'Transaction supprimée',
                    `"${transaction.description}" a été supprimé avec succès.`
                );
                
            } catch (e) {
                console.error('Erreur suppression', e);
                
                // Notification d'erreur
                showNotification(
                    'error',
                    'Erreur',
                    'Impossible de supprimer la transaction. Veuillez réessayer.'
                );
            }
            
            resolve(true);
        };
        
        confirmationNotification.querySelector('.cancel-btn').onclick = () => {
            hideNotification(confirmationNotification);
            resolve(false);
        };
        
        // Fermer la notification si on clique sur la croix
        confirmationNotification.querySelector('.notification-close').onclick = () => {
            hideNotification(confirmationNotification);
            resolve(false);
        };
    });
}

// ================== Système de notifications ==================
function ensureNotificationContainer() {
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '12px';
        document.body.appendChild(container);
    }
    return container;
}

function showNotification(type, title, message, duration = DEFAULT_NOTIFICATION_DURATION) {
    const container = ensureNotificationContainer();
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="${icons[type]}"></i>
        </div>
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" style="background:none;border:none;color:inherit;cursor:pointer;font-size:14px;padding:4px;" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </button>
        <div class="notification-progress" style="height:3px;background:rgba(255,255,255,0.4);position:absolute;left:0;bottom:0;width:100%;overflow:hidden;">
            <div class="progress-bar" style="height:100%;background:currentColor;transform-origin:left;animation: notification-progress ${duration}ms linear forwards;"></div>
        </div>
    `;
    
    // Styles de base inline pour éviter dépendance CSS si non ajoutée
    Object.assign(notification.style, {
        position: 'relative',
        display: 'flex',
        gap: '12px',
        alignItems: 'flex-start',
        padding: '14px 16px',
        borderRadius: '10px',
        background: 'var(--card-bg, #2d2f39)',
        color: 'white',
        boxShadow: '0 8px 24px -4px rgba(0,0,0,0.35)',
        transform: 'translateX(120%)',
        opacity: '0',
        transition: 'transform .45s cubic-bezier(.34,1.56,.64,1), opacity .3s ease',
        minWidth: '280px',
        maxWidth: '340px',
        fontSize: '14px'
    });
    
    // Couleurs par type
    const typeColors = {
        success: '#16a34a',
        error: '#dc2626',
        warning: '#d97706',
        info: '#2563eb'
    };
    notification.style.borderLeft = `6px solid ${typeColors[type] || '#2563eb'}`;
    notification.style.color = typeColors[type] || '#2563eb';
    
    container.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.classList.add('show');
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 100);
    
    // Bouton fermer
    notification.querySelector('.notification-close').onclick = () => hideNotification(notification);
    
    // Auto-suppression après la durée spécifiée
    if (duration > 0) {
        setTimeout(() => hideNotification(notification), duration);
    } else {
        // Retirer la barre de progression si durée infinie
        const progress = notification.querySelector('.notification-progress');
        if (progress) progress.remove();
    }
    
    return notification;
}

function hideNotification(notification) {
    notification.classList.remove('show');
    notification.classList.add('hide');
    notification.style.transform = 'translateX(120%)';
    notification.style.opacity = '0';
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 400);
}

// Fonction utilitaire pour les erreurs
function showError(message) {
    showNotification('error', 'Erreur', message);
}

// Fonction utilitaire pour les succès
function showSuccess(message, title = 'Succès') {
    showNotification('success', title, message);
}

function renderTransactions(transactions) {
    const transactionList = document.getElementById('transaction-list');
    transactionList.innerHTML = '';
    
    if (transactions.length === 0) {
        transactionList.innerHTML = '<p class="no-transactions">Aucune transaction enregistrée</p>';
        return;
    }
    
    // Grouper les transactions par mois-année
    const groupedTransactions = {};
    const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    
    transactions.forEach(transaction => {
        const date = new Date(transaction.date);
        const monthYear = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
        const monthYearKey = `${date.getFullYear()}-${date.getMonth()}`;
        
        if (!groupedTransactions[monthYearKey]) {
            groupedTransactions[monthYearKey] = {
                monthYear: monthYear,
                transactions: []
            };
        }
        groupedTransactions[monthYearKey].transactions.push(transaction);
    });
    
    // Trier les groupes (plus récent d'abord)
    const sortedGroups = Object.keys(groupedTransactions)
        .sort((a, b) => b.localeCompare(a))
        .map(key => groupedTransactions[key]);
    
    // Affichage des groupes
    sortedGroups.forEach(group => {
        const monthHeader = document.createElement('div');
        monthHeader.className = 'month-header';
        const monthTotal = calculateMonthTotal(group.transactions);
        monthHeader.innerHTML = `
            <h3>${group.monthYear}</h3>
            <div class="month-total ${monthTotal >= 0 ? 'income-amount' : 'expense-amount'}">
                ${monthTotal >= 0 ? '+' : ''}${monthTotal.toFixed(2)} €
            </div>
        `;
        transactionList.appendChild(monthHeader);
        
        group.transactions.forEach(transaction => {
            const transactionItem = document.createElement('div');
            transactionItem.className = 'transaction-item';
            const date = new Date(transaction.date);
            const formattedDate = `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
            const isTemplate = !!transaction.is_recurring && !transaction.parent_id;
            const isOccurrence = !!transaction.parent_id;
            const badgesHtml = isTemplate
                ? '<i title="Modèle récurrent" class="fas fa-sync-alt" style="font-size:0.75rem;color:var(--primary);"></i>'
                : (isOccurrence ? '<i title="Occurrence récurrente" class="fas fa-sync-alt" style="font-size:0.75rem;color:var(--gray);"></i>' : '');
            transactionItem.innerHTML = `
                <div class="transaction-details">
                    <h3>${transaction.description} ${badgesHtml}</h3>
                    <p>${formattedDate} • ${transaction.category}${isTemplate && transaction.recurrence_day ? ' • J'+transaction.recurrence_day : ''}</p>
                </div>
                <div class="transaction-amount ${Number(transaction.amount) >= 0 ? 'income-amount' : 'expense-amount'}">
                    ${Number(transaction.amount) >= 0 ? '+' : ''}${Number(transaction.amount).toFixed(2)} €
                </div>
                <div class="transaction-actions" style="display:flex; gap:8px;">
                    <button onclick="editTransaction(${transaction.id})" title="Modifier"><i class="fas fa-pen"></i></button>
                    <button onclick="deleteTransaction(${transaction.id})" title="Supprimer"><i class="fas fa-trash"></i></button>
                </div>
            `;
            transactionList.appendChild(transactionItem);
        });
    });
}

function updateDashboard(transactions) {
    // Convertir tous les montants en nombres et s'assurer qu'ils sont valides
    const validTransactions = transactions.filter(t => !isNaN(parseFloat(t.amount)));
    
    const totalIncome = validTransactions
        .filter(t => parseFloat(t.amount) > 0)
        .reduce((sum, t) => sum + parseFloat(t.amount), 0);
    
    const totalExpenses = validTransactions
        .filter(t => parseFloat(t.amount) < 0)
        .reduce((sum, t) => sum + Math.abs(parseFloat(t.amount)), 0);
    
    const totalBalance = totalIncome - totalExpenses;
    
    const totalBalanceElement = document.getElementById('total-balance');
    document.getElementById('total-income').textContent = `${totalIncome.toFixed(2)} €`;
    document.getElementById('total-expenses').textContent = `${totalExpenses.toFixed(2)} €`;
    totalBalanceElement.textContent = `${totalBalance.toFixed(2)} €`;
    totalBalanceElement.className = `amount ${totalBalance >= 0 ? 'positive' : 'negative'}`;
}

function updateCharts(transactions) {
    updateMonthlyChart(transactions);
}

function calculateMonthTotal(transactions) {
    return transactions.reduce((total, t) => {
        const amount = parseFloat(t.amount);
        return total + (isNaN(amount) ? 0 : amount);
    }, 0);
}

function initFilters(transactions) {
    // Remplir les années disponibles
    const yearFilter = document.getElementById('year-filter');
    yearFilter.innerHTML = '<option value="all">Toutes les années</option>';
    const currentYear = new Date().getFullYear();
    
    // Ajouter les 5 dernières années et les 5 prochaines
    for (let year = currentYear - 5; year <= currentYear + 5; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === currentYear) option.selected = true;
        yearFilter.appendChild(option);
    }
    
    // Remplir les mois disponibles
    const monthFilter = document.getElementById('month-filter');
    monthFilter.innerHTML = '<option value="all">Tous les mois</option>';
    const months = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    
    months.forEach((month, index) => {
        const option = document.createElement('option');
        option.value = index + 1;
        option.textContent = month;
        monthFilter.appendChild(option);
    });
    
    // Remplir les catégories disponibles
    const categoryFilter = document.getElementById('category-filter');
    categoryFilter.innerHTML = '<option value="all">Toutes catégories</option>';
    const categories = [
        'Salaire', 'Argent de poche', 'Investissement', 'Frais carte', 'Courses',
        'Logement', 'Factures', 'Restaurant', 'Transport', 'Loisirs', 'Bien-être', 'Santé', 'Autre'
    ];
    
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });

    // Ajouter les icônes après population
    enhanceSelectsWithIcons(true);
}

async function filterTransactions() {
    const monthFilter = document.getElementById('month-filter').value;
    const yearFilter = document.getElementById('year-filter').value;
    const categoryFilter = document.getElementById('category-filter').value;
    
    let transactions = window.allTransactions.slice();
    
    if (monthFilter !== 'all') {
        transactions = transactions.filter(t => { 
            const d = new Date(t.date); 
            return (d.getMonth() + 1) === parseInt(monthFilter); 
        });
    }
    if (yearFilter !== 'all') {
        transactions = transactions.filter(t => { 
            const d = new Date(t.date); 
            return d.getFullYear() === parseInt(yearFilter); 
        });
    }
    if (categoryFilter !== 'all') {
        transactions = transactions.filter(t => t.category === categoryFilter);
    }
    
    renderTransactions(transactions);
    updateDashboard(transactions);
    updateCharts(transactions);
}

function updateMonthlyChart(transactions) {
    const monthlyChart = document.getElementById('monthly-chart');
    monthlyChart.innerHTML = '';
    
    // Regrouper par mois
    const monthlyData = {};
    const currentYear = new Date().getFullYear();
    
    // Initialiser les 12 mois avec 0
    for (let month = 1; month <= 12; month++) {
        monthlyData[month] = 0;
    }
    
    // Ajouter les transactions de l'année en cours
    transactions.forEach(transaction => {
        const date = new Date(transaction.date);
        if (date.getFullYear() === currentYear) {
            const month = date.getMonth() + 1;
            const amount = parseFloat(transaction.amount);
            if (!isNaN(amount)) {
                monthlyData[month] += amount;
            }
        }
    });
    
    // Trouver le montant maximum pour l'échelle (au moins 100 pour éviter division par 0)
    const amounts = Object.values(monthlyData).map(Math.abs);
    const maxAmount = Math.max(...amounts, 100);
    
    // Créer une barre pour chaque mois
    for (let month = 1; month <= 12; month++) {
        const amount = monthlyData[month];
        const barHeight = Math.abs(amount) / maxAmount * 100;
        
        const bar = document.createElement('div');
        bar.className = 'bar';
        bar.style.height = `${barHeight}%`;
        bar.style.background = amount >= 0 ? 'var(--income)' : 'var(--expense)';
        
        bar.innerHTML = `
            <div class="bar-value">${amount.toFixed(0)}€</div>
            <div class="bar-label">${['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'][month-1]}</div>
        `;
        
        monthlyChart.appendChild(bar);
    }
}

// =============== Édition de transaction =================
window.editTransaction = function(id) {
    const t = (window.allTransactions || []).find(x => x.id === id);
    if (!t) return;

    document.getElementById('edit-id').value = t.id;
    document.getElementById('edit-description').value = t.description || '';
    document.getElementById('edit-amount').value = Math.abs(parseFloat(t.amount || 0)).toFixed(2);
    document.getElementById('edit-type').value = (parseFloat(t.amount) >= 0 ? 'income' : 'expense');
    document.getElementById('edit-category').value = t.category || 'Autre';
    document.getElementById('edit-date').value = (t.date ? new Date(t.date).toISOString().split('T')[0] : '');

    // Récurrence: n'afficher la section que si c'est un modèle (pas d'édition de récurrence sur une occurrence)
    const isBase = !t.parent_id;
    const details = document.getElementById('edit-recurrence-details');
    if (details) details.style.display = isBase ? 'block' : 'none';
    if (isBase) {
        document.getElementById('edit-is-recurring').value = t.is_recurring ? '1' : '0';
        document.getElementById('edit-recurrence-frequency').value = t.recurrence_frequency || 'monthly';
        document.getElementById('edit-recurrence-interval').value = t.recurrence_interval || 1;
        const freq = document.getElementById('edit-recurrence-frequency').value;
        const dayWrap = document.getElementById('edit-recurrence-day-wrapper');
        if (freq === 'monthly' || freq === 'yearly') {
            dayWrap.style.display = 'block';
            document.getElementById('edit-recurrence-day').value = t.recurrence_day || '';
        } else {
            dayWrap.style.display = 'none';
            document.getElementById('edit-recurrence-day').value = '';
        }
        document.getElementById('edit-recurrence-end-date').value = t.recurrence_end_date ? new Date(t.recurrence_end_date).toISOString().split('T')[0] : '';
    }

    openEditModal();
};

function openEditModal() {
    const m = document.getElementById('edit-modal');
    if (!m) return;
    m.style.display = 'flex';
}

function closeEditModal() {
    const m = document.getElementById('edit-modal');
    if (!m) return;
    m.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => {
    const cancelBtn = document.getElementById('edit-cancel');
    const modal = document.getElementById('edit-modal');
    const form = document.getElementById('edit-transaction-form');
    if (cancelBtn) cancelBtn.addEventListener('click', closeEditModal);
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeEditModal(); });
    if (form) form.addEventListener('submit', submitEditForm);
    const freqSel = document.getElementById('edit-recurrence-frequency');
    if (freqSel) freqSel.addEventListener('change', () => {
        const dayWrap = document.getElementById('edit-recurrence-day-wrapper');
        const freq = freqSel.value;
        dayWrap.style.display = (freq === 'monthly' || freq === 'yearly') ? 'block' : 'none';
    });
});

async function submitEditForm(e) {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const description = document.getElementById('edit-description').value.trim();
    const amount = document.getElementById('edit-amount').value.trim();
    const type = document.getElementById('edit-type').value;
    const category = document.getElementById('edit-category').value;
    const date = document.getElementById('edit-date').value;

    const payload = new FormData();
    payload.append('description', description);
    payload.append('amount', amount);
    payload.append('type', type);
    payload.append('category', category);
    payload.append('date', date);
    payload.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    payload.append('_method', 'PUT');
    // Ajouter les champs de récurrence si base
    const details = document.getElementById('edit-recurrence-details');
    if (details && details.style.display !== 'none') {
        const isRecurring = document.getElementById('edit-is-recurring').value;
        payload.append('is_recurring', isRecurring);
        payload.append('recurrence_frequency', document.getElementById('edit-recurrence-frequency').value);
        payload.append('recurrence_interval', document.getElementById('edit-recurrence-interval').value || '1');
        const freq = document.getElementById('edit-recurrence-frequency').value;
        if (freq === 'monthly' || freq === 'yearly') {
            const day = document.getElementById('edit-recurrence-day').value;
            if (day) payload.append('recurrence_day', day);
        }
        const end = document.getElementById('edit-recurrence-end-date').value;
        if (end) payload.append('recurrence_end_date', end);
    }

    try {
        const resp = await fetch(`/api/transactions/${id}`, { method: 'POST', body: payload });
        if (!resp.ok) throw new Error('Erreur update');
        closeEditModal();

        // Recharger
        window.allTransactions = await loadTransactions();
        renderTransactions(window.allTransactions);
        updateDashboard(window.allTransactions);
        updateCharts(window.allTransactions);

        showNotification('success', 'Transaction modifiée', 'La transaction a été mise à jour.');
    } catch (err) {
        console.error(err);
        showNotification('error', 'Erreur', "Impossible de modifier la transaction.");
    }
}

// Exposer les fonctions globales
window.deleteTransaction = deleteTransaction;