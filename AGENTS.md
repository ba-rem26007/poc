# Instructions pour les Agents IA & Développeurs (AGENTS.md)

Ce fichier fournit les consignes, l'architecture et les règles à respecter pour les agents IA et développeurs travaillant sur ce dépôt.

---

## 🏗️ Architecture Globale (3-Tier TLS)

1. **Tier 1 (Frontend)** :
   - Développé avec **React** (Vite), situé dans `frontend/`.
   - Utilise Lucide Icons et l'API Fetch avec en-têtes `Authorization: Bearer <jwt_token>`.
   - Gère la remontée automatique des erreurs JavaScript vers le backend (`frontend/src/logger.js`).

2. **Tier 2 (Backend)** :
   - Application **Symfony 7** avec PHP 8.3+, située dans `backend/`.
   - **API Platform 4** pour les API REST/JSON-LD (`/api`).
   - **LexikJWTAuthenticationBundle** pour l'émission et la vérification des tokens JWT (`/api/login_check`).
   - **EasyAdmin 5** pour le dashboard d'administration des produits, utilisateurs et logs d'erreur (`/admin`).
   - En cas d'extension vers **GraphQL**, API Platform prend en charge la bascule via le package `webonyx/graphql-php`.

3. **Tier 3 (Infrastructure & TLS)** :
   - Reverse Proxy **Caddy** (port `80` et `443`), chiffrant tout le trafic avec certificats TLS internes (`https://localhost`).
   - Base de données **PostgreSQL 16** (ou SQLite local pour le dev/test rapide).

---

## 🛠️ Commandes Principales à Respecter

- **Lancement des services** :
  ```bash
  make up
  ```
- **Tests unitaires et d'intégration** :
  ```bash
  cd backend && ./bin/phpunit
  ```
- **Build du Frontend React** :
  ```bash
  cd frontend && npm run build
  ```
- **Purge de la base de données (si demandé explicitement)** :
  ```bash
  make clean
  ```

---

## 🔒 Règles de Sécurité

- Les opérations d'écriture sur l'API (`POST`, `PUT`, `PATCH`, `DELETE`) sont sécurisées et nécessitent un token JWT (`ROLE_USER` ou `ROLE_ADMIN`).
- Le dashboard `/admin` requiert un accès de niveau `ROLE_ADMIN`.
- Ne pas commiter de vraies clés privées SSH/JWT en production (utiliser des variables d'environnement).
