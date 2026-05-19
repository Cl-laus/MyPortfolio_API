# MyPortfolio API

API REST du backend de mon portfolio personnel. Elle expose les données affichées sur le site (projets, technologies, informations, réseaux sociaux) et permet de les gérer via une interface d'administration sécurisée.

**Stack :** Symfony 8 · PHP 8.4 · MySQL 8 · Docker · JWT

**En production :** [api.lucasluisetti.me](https://api.lucasluisetti.me)

---

## Lancer le projet en local

### Prérequis

- Docker Desktop installé et démarré

### Démarrage

```bash
git clone https://github.com/Cl-laus/MyPortfolio_API.git
cd MyPortfolio_API

# Copier les variables d'environnement
cp .env .env.local
# → Remplir les vraies valeurs dans .env.local (APP_SECRET, JWT_PASSPHRASE)

# Générer les clés JWT
mkdir -p config/jwt
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096 -aes256
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

# Lancer les containers
docker compose up -d

# Créer la base de données et appliquer les migrations
docker exec mon-portfolio-app-1 php bin/console doctrine:migrations:migrate --no-interaction

# (Optionnel) Charger les données de test
docker exec mon-portfolio-app-1 php bin/console doctrine:fixtures:load --no-interaction
```

L'API est disponible sur `http://localhost:8000`.

---

## Structure des routes

### Publiques (sans authentification)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/projects` | 3 projets principaux |
| GET | `/api/projects/all` | Tous les projets |
| GET | `/api/projects/{id}` | Détail d'un projet |
| GET | `/api/technologies` | Toutes les technologies |
| GET | `/api/informations` | Informations personnelles |
| GET | `/api/social-networks` | Réseaux sociaux |

### Admin (JWT requis)

Se connecter d'abord via `POST /api/batcave` avec `{ "username": "...", "password": "..." }` pour obtenir un token JWT, puis l'inclure dans le header `Authorization: Bearer <token>`.

Toutes les routes admin suivent le schéma `/api/admin/{ressource}` avec les méthodes standard : `POST`, `PATCH`, `DELETE`.

---

## Lancer les tests

```bash
# Créer le schéma de test (SQLite, pas besoin de MySQL)
docker exec mon-portfolio-app-1 php bin/console doctrine:schema:create --env=test

# Charger les fixtures de test
docker exec mon-portfolio-app-1 php bin/console doctrine:fixtures:load --env=test --no-interaction

# Lancer les tests
docker exec mon-portfolio-app-1 vendor/bin/phpunit --testdox
```

7 tests au total : 3 unitaires, 4 fonctionnels.

---

## Variables d'environnement

| Variable | Description |
|----------|-------------|
| `APP_SECRET` | Clé secrète Symfony (générer une valeur aléatoire) |
| `DATABASE_URL` | URL de connexion MySQL |
| `JWT_PASSPHRASE` | Passphrase pour chiffrer la clé privée JWT |
| `CORS_ALLOW_ORIGIN` | Regex des domaines autorisés à appeler l'API |

Les vraies valeurs vont dans `.env.local` (gitignore — ne jamais commiter).

---

## Déploiement

Le déploiement est automatisé via **GitHub Actions** :
- Chaque push sur `main` lance les tests
- Si les tests passent, le code est déployé automatiquement sur le VPS

Pour déclencher un déploiement manuellement :

```bash
ssh root@<VPS_IP> "cd /var/www/mon-portfolio && git pull origin main && \
  docker exec mon-portfolio-app-1 php bin/console cache:clear --env=prod && \
  docker exec mon-portfolio-app-1 php bin/console cache:warmup --env=prod"
```
