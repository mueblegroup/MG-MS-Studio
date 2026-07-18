#!/usr/bin/env bash
set -euo pipefail

PROJECT_PATH="${PROJECT_PATH:-/var/www/Studio-Management-System-Laravel}"
APP_USER="${APP_USER:-www-data}"
PHP_BIN="${PHP_BIN:-$(command -v php)}"
SUPERVISOR_NAME="mueble-lms-worker"

if [[ $EUID -ne 0 ]]; then
    echo "Run this installer with sudo or as root." >&2
    exit 1
fi

if [[ ! -f "${PROJECT_PATH}/artisan" ]]; then
    echo "Laravel artisan file not found at ${PROJECT_PATH}/artisan" >&2
    exit 1
fi

if [[ -z "${PHP_BIN}" || ! -x "${PHP_BIN}" ]]; then
    echo "PHP binary was not found. Set PHP_BIN explicitly." >&2
    exit 1
fi

if ! id "${APP_USER}" >/dev/null 2>&1; then
    echo "Linux user ${APP_USER} does not exist." >&2
    exit 1
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y supervisor cron
systemctl enable --now supervisor cron

render_template() {
    local source="$1"
    local destination="$2"

    sed \
        -e "s|{{PROJECT_PATH}}|${PROJECT_PATH}|g" \
        -e "s|{{APP_USER}}|${APP_USER}|g" \
        -e "s|{{PHP_BIN}}|${PHP_BIN}|g" \
        "${source}" > "${destination}"
}

render_template \
    "${PROJECT_PATH}/deployment/supervisor/mueble-lms-worker.conf.example" \
    "/etc/supervisor/conf.d/${SUPERVISOR_NAME}.conf"

render_template \
    "${PROJECT_PATH}/deployment/cron/mueble-lms" \
    "/etc/cron.d/mueble-lms"

chmod 0644 "/etc/supervisor/conf.d/${SUPERVISOR_NAME}.conf"
chmod 0644 /etc/cron.d/mueble-lms

supervisorctl reread
supervisorctl update
supervisorctl restart "${SUPERVISOR_NAME}:*" || supervisorctl start "${SUPERVISOR_NAME}:*"

sudo -u "${APP_USER}" "${PHP_BIN}" "${PROJECT_PATH}/artisan" queue:restart || true

echo
echo "Installed Mueble LMS production services."
echo "Project: ${PROJECT_PATH}"
echo "User: ${APP_USER}"
echo "PHP: ${PHP_BIN}"
supervisorctl status "${SUPERVISOR_NAME}:*"
