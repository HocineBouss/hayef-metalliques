#!/usr/bin/env bash
set -Eeuo pipefail

# === Paramètres à adapter au besoin ===
REMOTE_USER="hayef-metalliques"
REMOTE_HOST="ssh-hayef-metalliques.alwaysdata.net"
PROJECT_DIR="~/www/ton-projet"   # <-- remplace par le chemin réel sur le serveur
BRANCH="main"

# Options SSH : accepte automatiquement la nouvelle empreinte si jamais elle change
SSH_OPTS="-o StrictHostKeyChecking=accept-new"

echo "Connexion à ${REMOTE_USER}@${REMOTE_HOST} ..."
# -t alloue un pseudo-TTY pour bien gérer la saisie du mot de passe si nécessaire
ssh -t ${SSH_OPTS} "${REMOTE_USER}@${REMOTE_HOST}" bash <<'EOSSH'
set -Eeuo pipefail

# --- À ADAPTER : même chemin que PROJECT_DIR ci-dessus ---
cd ~/www/ton-projet

echo "→ Git pull..."
git pull origin main

echo "→ Composer install (prod, sans dev)..."
# Détermine la meilleure commande composer disponible
if command -v composer >/dev/null 2>&1; then
  COMPOSER="composer"
elif [ -f "./composer.phar" ]; then
  COMPOSER="php ./composer.phar"
else
  echo "Composer introuvable. Installe-le ou place un composer.phar à la racine du projet."
  exit 1
fi

$COMPOSER install --no-dev --prefer-dist --no-progress --optimize-autoloader --no-interaction

echo "→ Symfony cache clear (prod)..."
php bin/console cache:clear --env=prod

echo "→ Symfony cache warmup (prod)..."
php bin/console cache:warmup --env=prod

echo "✅ Déploiement terminé."
EOSSH
