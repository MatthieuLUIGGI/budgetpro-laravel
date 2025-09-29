import './bootstrap';

// Version Laravel avec API AJAX
document.addEventListener('DOMContentLoaded', async function() {
    await initApp();
});

async function initApp() {
    // Initialiser la date du jour
    document.getElementById('date').valueAsDate = new Date();

    // Charger les transactions depuis l'API
    window.allTransactions = await loadTransactions();

    // Initialiser les filtres
    initFilters(window.allTransactions);

    // Affichage initial
    renderTransactions(window.allTransactions);
    updateDashboard(window.allTransactions);
    updateCharts(window.allTransactions);

    // Gestion du formulaire
    document.getElementById('add-transaction').addEventListener('submit', function(e) {
        e.preventDefault();
        addTransaction();
    });

    // Gestion des filtres
    document.getElementById('month-filter').addEventListener('change', filterTransactions);
    document.getElementById('year-filter').addEventListener('change', filterTransactions);
    document.getElementById('category-filter').addEventListener('change', filterTransactions);
}

async function loadTransactions() {
    try {
        const response = await fetch('/api/transactions', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) throw new Error('Erreur de chargement');
        
        let transactions = await response.json();
        transactions.sort((a, b) => new Date(b.date) - new Date(a.date));
        return transactions;
    } catch (e) {
        console.error('Erreur chargement transactions', e);
        return [];
    }
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
        
        // Recharger toutes les transactions
        window.allTransactions = await loadTransactions();
        renderTransactions(window.allTransactions);
        updateDashboard(window.allTransactions);
        updateCharts(window.allTransactions);
        
        // Notification de succès
        const finalDescription = (result && result.description) ? result.description : descriptionValue;
        const apiAmount = (result && typeof result.amount !== 'undefined') ? result.amount : rawAmountValue;
        let numericAmount = parseFloat(apiAmount);
        const formattedAmount = isNaN(numericAmount) ? '' : ` ${numericAmount >= 0 ? '+' : ''}${numericAmount.toFixed(2)} €`;
        showNotification(
            'success', 
            'Transaction ajoutée !', 
            `Ajout de "${finalDescription || 'Sans description'}"${formattedAmount} avec succès.`,
            3000
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
            "Impossible d'ajouter la transaction. Veuillez réessayer.",
            5000
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
        0 // Durée infinie jusqu'à action utilisateur
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

                // Recharger toutes les transactions
                window.allTransactions = await loadTransactions();
                renderTransactions(window.allTransactions);
                updateDashboard(window.allTransactions);
                updateCharts(window.allTransactions);
                
                // Notification de succès
                showNotification(
                    'success',
                    'Transaction supprimée',
                    `"${transaction.description}" a été supprimé avec succès.`,
                    3000
                );
                
            } catch (e) {
                console.error('Erreur suppression', e);
                
                // Notification d'erreur
                showNotification(
                    'error',
                    'Erreur',
                    'Impossible de supprimer la transaction. Veuillez réessayer.',
                    5000
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

function showNotification(type, title, message, duration = 4000) {
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
    showNotification('error', 'Erreur', message, 5000);
}

// Fonction utilitaire pour les succès
function showSuccess(message, title = 'Succès') {
    showNotification('success', title, message, 3000);
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
            transactionItem.innerHTML = `
                <div class="transaction-details">
                    <h3>${transaction.description}</h3>
                    <p>${formattedDate} • ${transaction.category}</p>
                </div>
                <div class="transaction-amount ${Number(transaction.amount) >= 0 ? 'income-amount' : 'expense-amount'}">
                    ${Number(transaction.amount) >= 0 ? '+' : ''}${Number(transaction.amount).toFixed(2)} €
                </div>
                <div class="transaction-actions">
                    <button onclick="deleteTransaction(${transaction.id})"><i class="fas fa-trash"></i></button>
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

// Exposer les fonctions globales
window.deleteTransaction = deleteTransaction;