# BudgetPro Laravel

Application de gestion de budget développée avec le framework Laravel.

## Prérequis

-   PHP >= 8.2
-   Composer
-   Node.js & npm
-   SQLite (ou autre SGBD compatible Laravel)

## Installation

1. **Cloner le projet :**

    ```pwsh
    git clone https://github.com/MatthieuLUIGGI/budgetpro-laravel.git
    cd budgetpro-laravel
    ```

2. **Installer les dépendances PHP :**

    ```pwsh
    composer install
    ```

3. **Installer les dépendances front-end :**

    ```pwsh
    npm install
    ```

4. **Configurer l'environnement :**

    - Copier `.env.example` en `.env` et adapter les variables (base de données, etc.).
    - Générer la clé d'application :
        ```pwsh
        php artisan key:generate
        ```

5. **Migrer la base de données :**
    ```pwsh
    php artisan migrate
    ```

## Lancement

-   **Serveur de développement Laravel :**
    ```pwsh
    php artisan serve
    ```
-   **Serveur front-end (Vite) :**
    ```pwsh
    npm run dev
    ```

## Scripts utiles

-   `npm run dev` : Lance le serveur Vite pour le développement front-end.
-   `npm run build` : Compile les assets pour la production.

## Principales dépendances

-   Laravel 12.x
-   Bootstrap 5
-   Tailwind CSS
-   Axios
-   Vite

## Structure du projet

-   `app/` : Code métier Laravel (contrôleurs, modèles…)
-   `resources/views/` : Vues Blade
-   `resources/js/` et `resources/css/` : Front-end
-   `database/` : Migrations, seeders, factories
-   `routes/` : Fichiers de routes

## Tests

Lancer les tests unitaires :

```pwsh
php artisan test
```

## Licence

Ce projet est sous licence MIT.
