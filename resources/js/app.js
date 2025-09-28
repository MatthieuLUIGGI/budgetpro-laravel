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
    const formData = new FormData();
    formData.append('description', document.getElementById('description').value);
    formData.append('amount', document.getElementById('amount').value);
    formData.append('type', document.getElementById('type').value);
    formData.append('category', document.getElementById('category').value);
    formData.append('date', document.getElementById('date').value);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    try {
        const response = await fetch('/api/transactions', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error('Erreur lors de l\'ajout');

        document.getElementById('add-transaction').reset();
        document.getElementById('date').valueAsDate = new Date();
        
        const transactions = await loadTransactions();
        renderTransactions(transactions);
        updateDashboard(transactions);
        updateCharts(transactions);
        
        const button = document.querySelector('#add-transaction button');
        button.innerHTML = 'Ajouté ! <i class="fas fa-check"></i>';
        button.style.background = 'var(--success)';
        
        setTimeout(() => {
            button.innerHTML = 'Ajouter <i class="fas fa-plus-circle"></i>';
            button.style.background = 'var(--primary)';
        }, 2000);
    } catch (e) {
        console.error("Erreur d'ajout", e);
        alert("Erreur lors de l'ajout de la transaction");
    }
}

async function deleteTransaction(id) {
    if (!confirm('Supprimer cette transaction ?')) return;
    
    try {
        const response = await fetch(`/api/transactions/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) throw new Error('Erreur de suppression');

        const transactions = await loadTransactions();
        renderTransactions(transactions);
        updateDashboard(transactions);
        updateCharts(transactions);
    } catch (e) {
        console.error('Erreur suppression', e);
        alert('Erreur lors de la suppression');
    }
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
    const totalIncome = transactions
        .filter(t => t.amount > 0)
        .reduce((sum, t) => sum + t.amount, 0);
    
    const totalExpenses = transactions
        .filter(t => t.amount < 0)
        .reduce((sum, t) => sum + Math.abs(t.amount), 0);
    
    const totalBalance = totalIncome - totalExpenses;
    
    
    const totalBalanceElement = document.getElementById('total-balance');
        document.getElementById('total-income').textContent = `${Number(totalIncome).toFixed(2)} €`;
        document.getElementById('total-expenses').textContent = `${Number(totalExpenses).toFixed(2)} €`;
        totalBalanceElement.textContent = `${Number(totalBalance).toFixed(2)} €`;
        totalBalanceElement.className = `amount ${Number(totalBalance) >= 0 ? 'positive' : 'negative'}`;
}

function updateCharts(transactions) {
    updateMonthlyChart(transactions);
}

function calculateMonthTotal(transactions) {
    return transactions.reduce((total, t) => total + Number(t.amount), 0);
}

function initFilters(transactions) {
    // Remplir les années disponibles
    const yearFilter = document.getElementById('year-filter');
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
        transactions = transactions.filter(t => { const d = new Date(t.date); return (d.getMonth()+1) === parseInt(monthFilter); });
    }
    if (yearFilter !== 'all') {
        transactions = transactions.filter(t => { const d = new Date(t.date); return d.getFullYear() === parseInt(yearFilter); });
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
    
    // Initialiser les 12 mois
    for (let month = 1; month <= 12; month++) {
        monthlyData[month] = 0;
    }
    
    // Ajouter les transactions de l'année en cours
    transactions.forEach(transaction => {
        const date = new Date(transaction.date);
        if (date.getFullYear() === currentYear) {
            const month = date.getMonth() + 1;
            monthlyData[month] += transaction.amount;
        }
    });
    
    // Trouver le montant maximum pour l'échelle
    const maxAmount = Math.max(...Object.values(monthlyData).map(Math.abs), 100);
    
    // Créer un barre pour chaque mois
    for (let month = 1; month <= 12; month++) {
        const amount = monthlyData[month];
        const barHeight = Math.abs(amount) / maxAmount * 100;
        
        const bar = document.createElement('div');
        bar.className = 'bar';
        bar.style.height = `${barHeight}%`;
        bar.style.background = amount >= 0 ? 'var(--income)' : 'var(--expense)';
        
        bar.innerHTML = `
            <div class="bar-value">${Number(amount).toFixed(0)}€</div>
            <div class="bar-label">${['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D'][month-1]}</div>
        `;
        
        monthlyChart.appendChild(bar);
    }
}

// Exposer les fonctions globales
window.deleteTransaction = deleteTransaction;