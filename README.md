# Architecture Architecture Applicative 3-Tier "TLS"

Une solution complète pour déployer et exécuter une application Web **3-Tier sécurisée en TLS** avec :
1. **Frontend (Tier 1)** : Client React (Vite, Lucide Icons, Fetch API)
2. **Backend (Tier 2)** : API REST & GraphQL avec **Symfony 7**, **API Platform 4** (Swagger/OpenAPI), et Dashboard SuperAdmin **EasyAdmin 5**
3. **Base de données & Proxy (Tier 3)** : Base PostgreSQL / SQLite et Reverse Proxy **Caddy** (Gestion automatique des certificats SSL/TLS)

---

## 🚀 Fonctionnalités principales

- 🔒 **Sécurité TLS / HTTPS globale** : Le reverse proxy Caddy gère le TLS de bout en bout (`https://localhost`).
- ⚡ **API REST / JSON-LD & Swagger UI** : Fourni nativement via API Platform sur `/api`.
- 👑 **SuperAdmin Dashboard (EasyAdmin)** : Administration des utilisateurs et des produits sur `/admin`.
- ⚛️ **Frontend React Moderne** : Interface réactive connectée aux endpoints de l'API.
- 📦 **Docker & Docker Compose** : Environnement conteneurisé prêt pour la production et le développement.

---

## 🛠️ Structure du Projet

```text
.
├── backend/               # Application Symfony 7 (API Platform + EasyAdmin)
│   ├── config/            # Configuration de sécurité, CORS, bundles
│   ├── src/
│   │   ├── Controller/    # Dashboard EasyAdmin & Controller de sécurité
│   │   ├── DataFixtures/  # Seeders (Fixtures d'utilisateurs et produits)
│   │   ├── Entity/        # Entités JPA/Doctrine (User, Product)
│   │   └── Repository/    # Repositories Doctrine
│   └── tests/             # Tests d'intégration et d'API (PHPUnit)
├── frontend/              # Application React (Vite)
│   ├── src/               # Composants React et intégration API
│   └── package.json
├── docker/                # Configurations Docker & Caddyfile
│   ├── Caddyfile          # Reverse Proxy TLS
│   ├── backend.Dockerfile
│   └── frontend.Dockerfile
├── docker-compose.yml     # Orchestration Multi-conteneurs
├── setup.sh               # Script d'installation rapide
└── README.md
```

---

## 🚦 Démarrage Rapide

### Option 1 : Avec Docker Compose (Recommandé avec TLS)

1. Lancez les conteneurs Docker :
   ```bash
   docker compose up --build -d
   ```

2. Effectuez les migrations et chargez les données initiales (Fixtures) :
   ```bash
   docker compose exec backend php bin/console doctrine:schema:create
   docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction
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
   DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db" php bin/console doctrine:schema:create
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

## 🔑 Identifiants d'Accès par Défaut (SuperAdmin)

Les données de test suivantes sont créées automatiquement par les fixtures (`AppFixtures.php`) :

- **SuperAdmin EasyAdmin** (`/admin`) :
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
cd backend
./bin/phpunit
```

---

## 📜 Licences & Crédits

Projet initialisé avec Symfony, API Platform, EasyAdmin et Vite React.
