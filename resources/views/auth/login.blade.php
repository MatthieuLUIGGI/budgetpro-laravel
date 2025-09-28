<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - BudgetPro</title>
    @vite(['resources/css/auth.css'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-background">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-chart-pie"></i>
                    <h1>BudgetPro</h1>
                </div>
                <p>Reprenez le contrôle de vos finances</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="form-group animated-input">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        <span>Adresse email</span>
                    </label>
                    @error('email')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group animated-input">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                           name="password" required autocomplete="current-password">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        <span>Mot de passe</span>
                    </label>
                    @error('password')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Se souvenir de moi
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password">
                            Mot de passe oublié ?
                        </a>
                    @endif
                </div>

                <button type="submit" class="auth-button">
                    <span class="button-text">Se connecter</span>
                    <span class="button-icon">
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </button>

                <div class="auth-footer">
                    <p>Pas encore de compte ? 
                        <a href="{{ route('register') }}" class="auth-link">S'inscrire</a>
                    </p>
                </div>
            </form>

            <div class="auth-features">
                <div class="feature">
                    <i class="fas fa-sync-alt"></i>
                    <span>Synchronisation multi-appareils</span>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Sécurité bancaire</span>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Analyses détaillées</span>
                </div>
            </div>
        </div>

        <div class="auth-welcome">
            <div class="welcome-content">
                <h2>Bienvenue sur BudgetPro</h2>
                <p>La solution complète pour gérer votre budget en toute simplicité</p>
                <div class="welcome-stats">
                    <div class="stat">
                        <div class="stat-number">+5k</div>
                        <div class="stat-label">Utilisateurs satisfaits</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">€</div>
                        <div class="stat-label">Économies moyennes</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Disponible</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animation pour les inputs
        document.querySelectorAll('.animated-input input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });

            // Pré-remplissage au chargement
            if (input.value !== '') {
                input.parentElement.classList.add('focused');
            }
        });

        // Animation du bouton
        const authButton = document.querySelector('.auth-button');
        authButton.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        authButton.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });

        // Animation des formes flottantes
        function animateShapes() {
            const shapes = document.querySelectorAll('.shape');
            shapes.forEach((shape, index) => {
                shape.style.animation = `float ${3 + index}s ease-in-out infinite`;
            });
        }

        animateShapes();
    </script>
</body>
</html>