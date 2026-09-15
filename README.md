# Architecture Applicative 3-Tier "TLS"

Une solution complète pour déployer et exécuter une application Web **3-Tier sécurisée en TLS** avec :
1. **Frontend (Tier 1)** : Client React (Vite, Lucide Icons, Fetch API, Rapport d'erreurs automatique, JWT Auth Modal)
2. **Backend (Tier 2)** : API REST avec **Symfony 7**, **API Platform 4** (Swagger/OpenAPI), **LexikJWTAuthenticationBundle**, et Dashboard SuperAdmin **EasyAdmin 5**
3. **Base de données & Proxy (Tier 3)** : Base PostgreSQL / SQLite et Reverse Proxy **Caddy** (Gestion TLS interne à la terminaison Caddy sur `https://localhost`)

---

## 🔒 Terminaison TLS / HTTPS

Le chiffrement TLS est assuré par le reverse proxy **Caddy** sur le port `443` (`https://localhost`). Caddy termine la connexion TLS de manière transparente et redirige le trafic réseau interne aux conteneurs `backend:8000` et `frontend:5173`.

---

## 🚀 Fonctionnalités principales

- 🔒 **Sécurité TLS / HTTPS globale** : Le reverse proxy Caddy gère le TLS de bout en bout (`https://localhost`).
- 🔑 **Authentification JWT** : Endpoint `/api/login_check` générant un Bearer token pour les requêtes sécurisées de l'API.
- ⚡ **API REST & Swagger UI** : Fourni nativement via API Platform sur `/api` (Extension GraphQL disponible via `composer require webonyx/graphql-php`).
- 👑 **SuperAdmin Dashboard (EasyAdmin)** : Administration des utilisateurs, des produits et des logs d'erreurs du client sur `/admin`.
- ⚠️ **Centralisation des Erreurs Frontend** : Remontée automatique des erreurs JS du frontend vers le backend (`/api/client_logs`).
- ⚛️ **Frontend React Moderne** : Interface réactive avec édition de produits, création, suppression et modal de connexion JWT.
- 📦 **Docker & Docker Compose** : Environnement conteneurisé prêt pour la production et le développement.

---

## 🛠️ Structure du Projet

```text
.
├── AGENTS.md              # Recommandations et consignes pour les agents IA
├── backend/               # Application Symfony 7 (API Platform + EasyAdmin + JWT)
│   ├── config/            # Configuration de sécurité, CORS, JWT
│   ├── src/
│   │   ├── Controller/    # Dashboard EasyAdmin & Security
│   │   ├── DataFixtures/  # Seeders (Fixtures d'utilisateurs et produits)
│   │   ├── Entity/        # Entités JPA/Doctrine (User, Product, ClientLog)
│   │   └── Repository/    # Repositories Doctrine
│   └── tests/             # Tests unitaires et d'intégration (PHPUnit)
├── frontend/              # Application React (Vite)
│   ├── src/               # Composants React, logger d'erreurs, JWT Auth Modal
│   └── package.json
├── docker/                # Configurations Docker & Caddyfile
│   ├── Caddyfile          # Reverse Proxy TLS
│   ├── backend.Dockerfile
│   └── frontend.Dockerfile
├── Makefile               # Gestion automatisée des conteneurs et tests
├── docker-compose.yml     # Orchestration Multi-conteneurs
├── docker-compose.test.yml# Environnement de tests CI/CD
├── setup.sh               # Script d'installation rapide
└── README.md
```

---

## 🚦 Démarrage Rapide

### Option 1 : Avec Makefile / Docker Compose (Recommandé avec TLS)

1. Lancez les conteneurs Docker :
   ```bash
   make up
   ```

2. Effectuez les migrations et chargez les données initiales (Fixtures) :
   ```bash
   make fixtures
   ```

3. Accédez aux services sécurisés :
   - 🌐 **Frontend React** : [https://localhost](https://localhost)
   - 📖 **Documentation Swagger API** : [https://localhost/api](https://localhost/api)
   - 🛡️ **SuperAdmin EasyAdmin** : [https://localhost/admin](https://localhost/admin)

---

### Option 2 : Exécution Locale sans Docker

1. **Backend Symfony** :
   ```bash
   cd backend
   composer install
   php bin/console lexik:jwt:generate-keypair --skip-if-exists
   DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console doctrine:schema:create --force
   DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console doctrine:fixtures:load --no-interaction
   php -S 127.0.0.1:8000 -t public
   ```

2. **Frontend React** :
   ```bash
   cd frontend
   npm install
   npm run dev
   ```

---

## 🔑 Identifiants d'Accès par Défaut

Les données de test suivantes sont créées automatiquement par les fixtures (`AppFixtures.php`) :

- **SuperAdmin EasyAdmin** (`/admin`) & API JWT :
  - **Email** : `admin@example.com`
  - **Mot de passe** : `admin123`

- **Utilisateur Standard** :
  - **Email** : `user@example.com`
  - **Mot de passe** : `user123`

---

## 🧪 Exécution des Tests

### Backend (PHPUnit)

Pour lancer la suite de tests PHPUnit du Backend Symfony :

```bash
make test-backend
```

---

## 📜 Licences & Crédits

Projet initialisé avec Symfony, API Platform, LexikJWTAuthenticationBundle, EasyAdmin et Vite React.
