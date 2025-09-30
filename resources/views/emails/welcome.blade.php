<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur BudgetPro</title>
    <style>
        /* Reset CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .logo {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .email-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .email-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .email-content {
            padding: 40px 30px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-icon {
            font-size: 4rem;
            color: #4361ee;
            margin-bottom: 20px;
        }

        .welcome-section h2 {
            font-size: 1.8rem;
            color: #2d3748;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .welcome-text {
            font-size: 1.1rem;
            color: #4a5568;
            margin-bottom: 25px;
        }

        .user-name {
            color: #4361ee;
            font-weight: 600;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin: 30px 0;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #4361ee;
        }

        .feature-icon {
            font-size: 1.5rem;
            color: #4361ee;
            flex-shrink: 0;
        }

        .feature-content h3 {
            font-size: 1.1rem;
            color: #2d3748;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .feature-content p {
            color: #4a5568;
            font-size: 0.95rem;
        }

        .cta-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(67, 97, 238, 0.4);
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 30px 0;
        }

        .stat {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4361ee;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #4a5568;
        }

        .next-steps {
            background: #fff8f0;
            border: 1px solid #fed7aa;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }

        .next-steps h3 {
            color: #c05621;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .steps-list {
            list-style: none;
        }

        .steps-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            color: #4a5568;
        }

        .steps-list li:before {
            content: '✓';
            color: #48bb78;
            font-weight: bold;
            background: #f0fff4;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .email-footer {
            background: #1a202c;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .footer-link {
            color: #cbd5e0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: #4361ee;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #4361ee;
            transform: translateY(-2px);
        }

        .copyright {
            color: #a0aec0;
            font-size: 0.8rem;
            margin-top: 20px;
        }

        .support-contact {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }

        .support-contact p {
            color: #2b6cb0;
            margin-bottom: 10px;
        }

        .support-email {
            color: #4361ee;
            font-weight: 600;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .email-container {
                border-radius: 10px;
                margin: 10px;
            }

            .email-header {
                padding: 30px 20px;
            }

            .email-content {
                padding: 30px 20px;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
                gap: 10px;
            }

            .feature {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- En-tête -->
        <div class="email-header">
            <div class="logo">
                <i class="fas fa-chart-pie"></i>
            </div>
            <h1>Bienvenue sur BudgetPro</h1>
            <p>Votre voyage vers la liberté financière commence ici</p>
        </div>

        <!-- Contenu principal -->
        <div class="email-content">
            <div class="welcome-section">
                <div class="welcome-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Bienvenue, <span class="user-name">{{ $user->name }} !</span></h2>
                <p class="welcome-text">
                    Félicitations ! Votre compte BudgetPro a été créé avec succès. 
                    Nous sommes ravis de vous accompagner dans la gestion de vos finances.
                </p>
            </div>

            <!-- Statistiques -->
            <div class="stats-section">
                <div class="stat">
                    <div class="stat-number">+30%</div>
                    <div class="stat-label">d'économies en moyenne</div>
                </div>
                <div class="stat">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Disponible partout</div>
                </div>
                <div class="stat">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Sécurisé</div>
                </div>
            </div>

            <!-- Fonctionnalités -->
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Suivi en temps réel</h3>
                        <p>Visualisez l'évolution de vos finances avec des graphiques interactifs et des rapports détaillés.</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Multi-appareils</h3>
                        <p>Accédez à votre budget depuis votre ordinateur, tablette ou smartphone.</p>
                    </div>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Sécurité bancaire</h3>
                        <p>Vos données sont chiffrées et protégées selon les standards les plus stricts.</p>
                    </div>
                </div>
            </div>

            <!-- Étapes suivantes -->
            <div class="next-steps">
                <h3>Pour bien commencer :</h3>
                <ul class="steps-list">
                    <li>Complétez votre profil utilisateur</li>
                    <li>Ajoutez vos premières transactions</li>
                    <li>Configurez vos catégories de dépenses</li>
                    <li>Définissez vos objectifs budgétaires</li>
                    <li>Explorez les rapports et analyses</li>
                </ul>
            </div>

            <!-- Support -->
            <div class="support-contact">
                <p>Besoin d'aide ? Notre équipe support est là pour vous accompagner :</p>
                <a href="mailto:support@budgetpro.fr" class="support-email">support@budgetpro.fr</a>
            </div>

            <!-- Call to Action -->
            <div class="cta-section">
                <h3 style="margin-bottom: 20px; color: #2d3748;">Prêt à prendre le contrôle de vos finances ?</h3>
                <a href="{{ url('/') }}" class="cta-button">
                    Commencer maintenant <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
                </a>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="email-footer">
            <div class="social-links">
                <a href="#" class="social-link" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-link" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-link" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="#" class="social-link" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
            
            <div class="footer-links">
                <a href="#" class="footer-link">Centre d'aide</a>
                <a href="#" class="footer-link">Conditions d'utilisation</a>
                <a href="#" class="footer-link">Politique de confidentialité</a>
                <a href="#" class="footer-link">Contact</a>
            </div>
            
            <div class="copyright">
                &copy; 2025 BudgetPro. Tous droits réservés.<br>
                Cet email a été envoyé à {{ $user->email }}
            </div>
        </div>
    </div>

    <!-- Font Awesome pour les icônes -->
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>