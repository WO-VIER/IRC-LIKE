#  IRC-LIKE

Un projet de chat en temps réel construit avec Laravel, Inertia.js, et Vue.js.

##  Stack Technique

* **Backend :** Laravel 11 (PHP 8.4)
* **Frontend :** Vue.js 3
* **Connexion :** Inertia.js
* **Base de données :** SQLite
* **Temps réel :** Pusher
* **Styling :** Tailwind CSS

---

##  Démarrage Rapide (en local)

Suivez ces étapes pour lancer le projet sur votre machine.

1.  **Cloner le repo**
    ```bash
    git clone [https://github.com/WO-VIER/IRC-LIKE.git](https://github.com/WO-VIER/IRC-LIKE.git)
    cd IRC-LIKE
    ```

2.  **Installer les dépendances**
    ```bash
    composer install
    npm install
    ```

3.  **Configurer l'environnement**
    ```bash
    # Copier le fichier d'exemple
    cp .env.example .env
    
    # Générer la clé de l'application
    php artisan key:generate
    
    # Ajouter vos clés Pusher (PUSHER_APP_ID, PUSHER_APP_KEY, etc.) dans le .env
    nano .env 
    ```

4.  **Préparer la base de données (SQLite)**
    ```bash
    # Créer le fichier de base de données vide
    touch database/database.sqlite
    
    # Lancer les migrations
    php artisan migrate
    ```

5.  **Lancer l'application**
    ```bash
    # Lancer le serveur de développement (Vite)
    npm run dev
    
    # Dans un autre terminal, lancer le serveur PHP
    php artisan serve
    ```

L'application est maintenant disponible sur `http://127.0.0.1:8000`.

---

##  Déploiement & CI/CD

Ce projet est hébergé sur un **Droplet DigitalOcean (VPS)** pour garantir la persistance de la base de données SQLite.

Un pipeline **CI/CD** est configuré avec **GitHub Actions** (`.github/workflows/main.yml`). À chaque `push` sur la branche `main` :

1.  **Job 1 (Test) :** Les tests (Pest) sont lancés pour valider le code.
2.  **Job 2 (Deploy) :** Si les tests passent, le workflow se connecte en SSH au Droplet et exécute automatiquement :
    * `git pull origin main`
    * `composer install --no-dev` (Mode production)
    * `npm run build` (Compilation des assets)
    * `php artisan migrate --force`
    * `php artisan optimize:clear`
    * `sudo supervisorctl restart laravel-worker:*` (Redémarrage du worker)
