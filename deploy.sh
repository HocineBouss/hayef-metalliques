#!/usr/bin/env bash
set -e

AD_USER="hayef-metalliques"
AD_HOST="ssh-1.alwaysdata.com"
AD_PATH="/home/$AD_USER/apps/hayef"

echo "[1/4] rsync fichiers…"
rsync -avz --delete \
  --exclude=".git" \
  --exclude="var/*" \
  --exclude="vendor/*" \
  --exclude="node_modules/*" \
  . "$AD_USER@$AD_HOST:$AD_PATH"

echo "[2/4] composer install…"
ssh $AD_USER@$AD_HOST "cd $AD_PATH && composer install --no-dev --optimize-autoloader"

echo "[3/4] migrations & cache…"
ssh $AD_USER@$AD_HOST "cd $AD_PATH && \
  php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true && \
  php bin/console cache:clear --env=prod && \
  php bin/console cache:warmup --env=prod"

echo "[4/4] purge Cloudflare (optionnel)"
# -> Tu peux appeler l’API Cloudflare ici si tu as un token, sinon purge manuelle dans le dashboard.

echo "✅ Déploiement terminé."
