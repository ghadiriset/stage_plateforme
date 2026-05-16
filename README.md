# ISET Djerba — Plateforme Stages (PHP)

Projet migré de Node.js vers **PHP pur + PostgreSQL**, déployable sur Render via Docker.

## Structure

```
├── index.php          # Routeur principal
├── includes/
│   ├── db.php         # Connexion PDO (PostgreSQL/MySQL)
│   ├── auth.php       # Sessions et protection des routes
│   └── layout.php     # Header, footer, head HTML
├── pages/             # Pages PHP (vues)
│   ├── admin/         # Actions admin
│   ├── home.php
│   ├── login.php / register.php
│   ├── dashboard.php  # Admin + étudiant + professeur
│   └── chat.php
├── api/
│   ├── messages.php   # GET messages (polling)
│   └── send.php       # POST envoi message
├── public/
│   ├── css/style.css
│   ├── js/chat.js     # Chat polling (toutes les 3s)
│   └── uploads/
├── Dockerfile
└── .htaccess
```

## Déploiement sur Render

### 1. Créer une base PostgreSQL sur Render
Dashboard Render → New → PostgreSQL → Free plan  
Copier la **DATABASE_URL**

### 2. Créer un Web Service sur Render
- **Environment** : Docker
- **Build Command** : (automatique via Dockerfile)
- **Variables d'environnement** :
  ```
  DATABASE_URL=postgresql://user:pass@host:5432/dbname
  ```

### 3. Pousser le code
```bash
git init
git add .
git commit -m "Initial PHP version"
git remote add origin https://github.com/ton-repo.git
git push -u origin main
```

Render déploiera automatiquement.

## Compte admin par défaut
- Email : `admin@isetdjerba.tn`
- Mot de passe : `admin123`

## Chat
Le chat utilise le **polling HTTP** (requêtes toutes les 3 secondes) — compatible avec Render sans WebSockets.

## Développement local (MySQL)
```bash
# Créer la base MySQL
mysql -u root -e "CREATE DATABASE iset_stages;"

# Variables d'environnement
export DB_HOST=localhost
export DB_NAME=iset_stages
export DB_USER=root
export DB_PASS=

# Lancer PHP
php -S localhost:8000
```
