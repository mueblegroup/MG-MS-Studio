#!/usr/bin/env bash
set -euo pipefail

PROJECT_PATH="${PROJECT_PATH:-/var/www/Studio-Management-System-Laravel}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-$(command -v php)}"
COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer)}"
NPM_BIN="${NPM_BIN:-$(command -v npm)}"

cd "${PROJECT_PATH}"

if [[ ! -f artisan ]]; then
    echo "artisan not found in ${PROJECT_PATH}" >&2
    exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Deployment stopped: the working tree contains local changes." >&2
    git status --short
    echo "Review, commit, discard, or stash these changes before deploying." >&2
    exit 1
fi

echo "Enabling maintenance mode..."
"${PHP_BIN}" artisan down --retry=60 || true

restore_application() {
    "${PHP_BIN}" artisan up || true
}
trap restore_application EXIT

echo "Fetching ${BRANCH}..."
git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git reset --hard "origin/${BRANCH}"

if [[ -n "${COMPOSER_BIN}" ]]; then
    "${COMPOSER_BIN}" install --no-dev --prefer-dist --no-interaction --optimize-autoloader
else
    echo "Composer was not found." >&2
    exit 1
fi

"${PHP_BIN}" artisan migrate --force
"${PHP_BIN}" artisan optimize:clear

if [[ -n "${NPM_BIN}" ]]; then
    "${NPM_BIN}" ci
    "${NPM_BIN}" run build
else
    echo "npm was not found." >&2
    exit 1
fi

"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache
"${PHP_BIN}" artisan queue:restart

restore_application
trap - EXIT

echo "Deployment completed successfully."
git log -1 --oneline
