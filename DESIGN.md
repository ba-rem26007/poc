# DESIGN.md - Design System & UI Specification for Figma / Figma Make

Ce document définit les spécifications complètes du **Design System** et de l'interface utilisateur pour la génération et l'export depuis **Figma**, **Figma Make**, **Stitch**, ou **Anima** vers notre Frontend React PWA.

---

## 1. Vision & Charte Graphique

* **Style Visuel :** Clean, Moderne, SaaS Enterprise & PWA Responsive.
* **Thème :** Dark Navbar / Light Body Container avec cartes ombragées (`shadow-sm`).
* **Framework CSS cible :** Bootstrap 5 / Tailwind / CSS Modules.
* **Bibliothèque d'icônes :** FontAwesome 6 Free Solid (`fa-solid`, `fa-brands`).

---

## 2. Palette de Couleurs (Design Tokens)

| Token | Couleur Hex | Usage |
| :--- | :--- | :--- |
| `--color-primary` | `#0d6efd` (Bleu Royal) | Boutons d'action principaux, liens, bordures actives |
| `--color-secondary` | `#6c757d` (Gris Slate) | Textes secondaires, badges d'état neutres |
| `--color-success` | `#198754` (Vert Émeraude) | Statut "In Stock", Badges TLS, Succès Toasts |
| `--color-warning` | `#ffc107` (Jaune Ambre) | Assistant IA RAG Mistral, Boutons de réinitialisation |
| `--color-danger` | `#dc3545` (Rouge Crimson) | Boutons de suppression, Erreurs Toasts, Logout |
| `--color-dark` | `#212529` (Anthracite) | Barres de navigation Header, Modales dark background |
| `--color-bg-light` | `#f8f9fa` (Gris Clair) | Fond d'écran global de l'application |

---

## 3. Typographie

* **Font Family Principal :** System UI, `-apple-system`, `BlinkMacSystemFont`, `"Segoe UI"`, `Roboto`, `"Helvetica Neue"`, `sans-serif`.
* **Échelle Typographique :**
  * **H1 / App Title :** 24px (Bold)
  * **H2 / Section Title :** 20px (Bold)
  * **H3 / Card Header :** 16px (Semi-Bold)
  * **Body Text :** 14px (Regular)
  * **Caption / Badges :** 12px (Medium)

---

## 4. Spécification des Composants UI à Dessiner dans Figma

### A. Barre de Navigation (Navbar)
* **Composants :**
  * `LogoBrand` : Icône `fa-cubes` + Titre "3-Tier TLS Platform (PWA)".
  * `BadgeTLS` : Badge vert avec icône `fa-lock` ("TLS Encrypted").
  * `BtnRagAI` : Bouton Ambre `fa-robot` ("AI RAG Mistral").
  * `BtnProfile` : Bouton bleu d'information `fa-user-circle` ("Mon Profil").
  * `BtnAdmin` : Bouton `fa-user-shield` ("EasyAdmin").
  * `BtnDoc` : Bouton `fa-sitemap` ("Arch Doc").
  * `DropdownNotifications` : Icône `fa-bell` avec badge rouge réactif.

### B. Carte Produit (Product Card Component)
* **Composants :**
  * `Title` : Nom du produit (ex: "Wireless Mouse").
  * `BadgeStock` : Badge vert "In Stock" (`fa-check-circle`) ou rouge "Out of Stock" (`fa-times-circle`).
  * `Description` : Texte descriptif du produit.
  * `PriceTag` : Affichage du prix en grand (ex: "$29.99").
  * `Actions` : Bouton Modifier (`fa-edit`) et Bouton Supprimer (`fa-trash`).

### C. Modale Assistant IA RAG Mistral (RAG AI Widget Modal)
* **Composants :**
  * `Header` : Fond jaune/ambre avec icône `fa-robot` ("Assistant IA Mistral Proxy RAG").
  * `InputQuestion` : Champ texte avec placeholder "ex: Quels produits sont en stock sous 50$ ?".
  * `AnswerBox` : Zone de réponse ombragée avec badge de source ("Mistral AI" ou "Local RAG Engine").

### D. Modale "Mon Profil" & Sécurité
* **Composants :**
  * `InputEmail` : Lecture seule.
  * `InputFullName` : Champ texte modifiable.
  * `Switch2FA` : Commutateur toggle ("Activer Sécurité 2FA").
  * `FormPassword` : Champs "Mot de passe actuel" et "Nouveau mot de passe".

---

## 5. Instructions d'Export depuis Figma vers React

Lors de l'export avec **Figma Make / Anima / Locofy** :
1. Conservez les noms de propriétés (`props`) identiques : `name`, `description`, `price`, `isAvailable`, `onEdit`, `onDelete`.
2. Exportez en composants React fonctionnels JSX (`.jsx`).
3. Placez les composants générés dans `frontend/src/components/figma/`.
