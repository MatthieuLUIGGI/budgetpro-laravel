<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - BudgetPro</title>
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
                <div class="shape shape-4"></div>
            </div>
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-chart-pie"></i>
                    <h1>BudgetPro</h1>
                </div>
                <p>Commencez votre voyage financier</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="form-group animated-input">
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                           name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    <label for="name">
                        <i class="fas fa-user"></i>
                        <span>Nom complet</span>
                    </label>
                    @error('name')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group animated-input">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" required autocomplete="email">
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
                           name="password" required autocomplete="new-password">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        <span>Mot de passe</span>
                    </label>
                    <div class="password-strength">
                        <div class="strength-bar"></div>
                    </div>
                    @error('password')
                        <span class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group animated-input">
                    <input id="password-confirm" type="password" class="form-control" 
                           name="password_confirmation" required autocomplete="new-password">
                    <label for="password-confirm">
                        <i class="fas fa-lock-check"></i>
                        <span>Confirmer le mot de passe</span>
                    </label>
                    <div class="password-match">
                        <i class="fas fa-check"></i>
                        <span>Les mots de passe correspondent</span>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" id="terms" required>
                        <span class="checkmark"></span>
                        J'accepte les <a href="#" class="terms-link">conditions d'utilisation</a>
                    </label>
                </div>

                <button type="submit" class="auth-button">
                    <span class="button-text">Créer mon compte</span>
                    <span class="button-icon">
                        <i class="fas fa-rocket"></i>
                    </span>
                </button>

                <div class="auth-footer">
                    <p>Déjà un compte ? 
                        <a href="{{ route('login') }}" class="auth-link">Se connecter</a>
                    </p>
                </div>
            </form>

            <div class="auth-features">
                <div class="feature">
                    <i class="fas fa-cloud"></i>
                    <span>Sauvegarde cloud sécurisée</span>
                </div>
                <div class="feature">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Accès multi-appareils</span>
                </div>
                <div class="feature">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analyses personnalisées</span>
                </div>
            </div>
        </div>

        <div class="auth-welcome">
            <div class="welcome-content">
                <h2>Rejoignez BudgetPro</h2>
                <p>Et prenez le contrôle de vos finances dès aujourd'hui</p>
                
                <div class="benefits-list">
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Suivi en temps réel de vos dépenses</span>
                    </div>
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Graphiques et rapports détaillés</span>
                    </div>
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Alertes de budget intelligentes</span>
                    </div>
                    <div class="benefit">
                        <i class="fas fa-check-circle"></i>
                        <span>Synchronisation automatique</span>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="quote">
                        <i class="fas fa-quote-left"></i>
                        "BudgetPro m'a aidé à économiser 30% de plus chaque mois !"
                    </div>
                    <div class="author">- Marie D., utilisatrice depuis 2023</div>
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

        // Vérification de la force du mot de passe
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.strength-bar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            
            strengthBar.style.width = strength + '%';
            strengthBar.style.background = strength < 50 ? 'var(--expense)' : 
                                          strength < 75 ? 'var(--warning)' : 'var(--income)';
        });

        // Vérification de la correspondance des mots de passe
        const confirmPassword = document.getElementById('password-confirm');
        const passwordMatch = document.querySelector('.password-match');
        
        confirmPassword.addEventListener('input', function() {
            const match = this.value === passwordInput.value && this.value !== '';
            passwordMatch.style.display = match ? 'flex' : 'none';
        });

        // Animation du bouton
        const authButton = document.querySelector('.auth-button');
        authButton.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        authButton.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });

        // Animation des formes flottantes
        function animateShapes() {
            const shapes = document.querySelectorAll('.shape');
            shapes.forEach((shape, index) => {
                shape.style.animation = `float ${4 + index}s ease-in-out infinite`;
                shape.style.animationDelay = `${index}s`;
            });
        }

        animateShapes();

        // Validation des conditions
        const termsCheckbox = document.getElementById('terms');
        termsCheckbox.addEventListener('change', function() {
            authButton.disabled = !this.checked;
            authButton.style.opacity = this.checked ? '1' : '0.6';
        });

        // Désactiver le bouton initialement
        authButton.disabled = true;
        authButton.style.opacity = '0.6';
    </script>
</body>
</html>
