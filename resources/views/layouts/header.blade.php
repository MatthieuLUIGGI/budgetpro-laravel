<header class="site-header">
    <div class="branding">
        <h1><i class="fas fa-chart-pie"></i> BudgetPro</h1>
        <p class="tagline">Gestion professionnelle de vos finances</p>
    </div>

    <nav class="main-nav">
        <ul>
            <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}"><i class="fas fa-gauge"></i> Tableau de bord</a></li>
            <li><a href="{{ route('profile.edit') }}" class="{{ request()->is('profile') ? 'active' : '' }}"><i class="fas fa-user"></i> Profil</a></li>
        </ul>
    </nav>

    <div class="user-menu">
        @auth
            <span class="username">Bonjour, {{ Auth::user()->name }}</span>
            <a href="{{ route('logout') }}" class="logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Déconnexion <i class="fas fa-sign-out-alt"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        @endauth
    </div>
</header>