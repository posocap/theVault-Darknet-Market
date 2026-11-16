#!/usr/bin/env bash
# =============================================================================
# deploy/update.sh - update an existing theVault install WITHOUT restarting
# anything. Run as root on a host previously set up with deploy/provision.sh.
#
#   sudo bash deploy/update.sh            # update to the latest of REPO_REF
#
# What it does
#   * fetches REPO_REF into SRC_DIR and syncs the app into APP_DIR
#   * keeps .env, uploaded files (storage/app), logs and the sessions/cache
#   * re-creates the public/storage symlink and copies any new demo images
#   * clears Laravel caches and does a graceful *reload* of php-fpm and nginx
#     (Tor, MariaDB and the onion service are never touched, so there is no
#     downtime and the .onion address stays reachable)
#
# Environment variables (all optional): REPO_REF (default main), APP_DIR
# (/var/www/thevault), SRC_DIR (/opt/thevault-src), WEB_USER (nginx|www-data).
# =============================================================================
set -euo pipefail

REPO_REF="${REPO_REF:-main}"
APP_DIR="${APP_DIR:-/var/www/thevault}"
SRC_DIR="${SRC_DIR:-/opt/thevault-src}"
CE_SUBDIR="app/theVault_community_edition"
if [[ -z "${WEB_USER:-}" ]]; then
  if id nginx >/dev/null 2>&1; then WEB_USER=nginx; else WEB_USER=www-data; fi
fi

log() { printf '\n\033[1;32m[thevault-update]\033[0m %s\n' "$*"; }

[[ $EUID -eq 0 ]] || { echo "Run as root." >&2; exit 1; }
[[ -d "$SRC_DIR/.git" ]] || { echo "No checkout at $SRC_DIR - run deploy/provision.sh first." >&2; exit 1; }

log "Fetching $REPO_REF..."
git -C "$SRC_DIR" fetch -q origin "$REPO_REF"
git -C "$SRC_DIR" checkout -q -B deploy FETCH_HEAD
log "Now at: $(git -C "$SRC_DIR" log --oneline -1)"

log "Syncing application files..."
rsync -a --delete \
  --exclude '/.env' --exclude '/storage/app' --exclude '/storage/logs' --exclude '/storage/framework' \
  "$SRC_DIR/$CE_SUBDIR/" "$APP_DIR/"

mkdir -p "$APP_DIR/storage/app/public"
if [[ -d "$SRC_DIR/app/example_files/demo_images" ]]; then
  cp -n "$SRC_DIR/app/example_files/demo_images/"* "$APP_DIR/storage/app/public/" 2>/dev/null || true
fi
chown -R "$WEB_USER":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
[[ -f "$APP_DIR/.env" ]] && { chown root:"$WEB_USER" "$APP_DIR/.env"; chmod 640 "$APP_DIR/.env"; }
command -v chcon >/dev/null 2>&1 && chcon -R -t httpd_sys_rw_content_t "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

(cd "$APP_DIR" && php artisan storage:link >/dev/null 2>&1 || true)
(cd "$APP_DIR" && php artisan optimize:clear >/dev/null 2>&1 || true)

log "Graceful reload (no restarts)..."
FPM="$(systemctl list-unit-files --no-legend 2>/dev/null | awk '/^php[0-9.]*-fpm\.service|^php-fpm\.service/{print $1; exit}')"
[[ -n "$FPM" ]] && systemctl reload "$FPM" || true
nginx -t >/dev/null 2>&1 && systemctl reload nginx || true

log "Smoke test:"
for u in / /login; do printf '  %s -> HTTP %s\n' "$u" "$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:${NGINX_PORT:-8080}$u")"; done
log "Done."
