#!/bin/bash

set -e

trap exit SIGTERM
touch /tmp/shop.log

# If we are in Github plugin repo CI environment
CI_REPO_URL=${GITHUB_SERVER_URL}/${GITHUB_REPOSITORY}
if [[ ${CI_REPO_URL} == ${PLUGIN_URL//.git/} ]]; then
  PLUGIN_VERSION=${GITHUB_SHA}
  CI='true'
fi

if [[ -z ${OPENMAGE_BASEURL} ]]; then
  echo "OPENMAGE_BASEURL not specified."
  if [[ -n ${NGROK_TOKEN} ]]; then 
    echo "Launching ngrok to get temporary URL"
    OPENMAGE_BASEURL=$(ngrok.sh ${NGROK_TOKEN})
  else
    echo "No NGROK_TOKEN specified. Using localhost as URL"
    OPENMAGE_BASEURL=localhost
  fi
fi

echo "Waiting for DB host ${OPENMAGE_DB_HOST}"

while ! mysqladmin ping -h"${OPENMAGE_DB_HOST}" --silent; do
  sleep 10
done

function create_db() {
  echo "Creating Database"
}

function install_core() {
  echo "Downgrade dependencies for PHP 7"
  composer update
  echo "Install Core"
  composer install
}

function switch_version() {
  echo "Switchting to Magento-LTS ${OPENMAGE_VERSION}"
  cd /var/www/html
  git fetch --all
  git checkout ${OPENMAGE_VERSION} || echo "Invalid OPENMAGE_VERSION specified"
}

function install_sample_data() {
  echo "Installing Sample Data"
  cd /tmp
  7zr x demo-data.tar.7z >& /dev/null
  rm demo-data.tar.7z
  mkdir demo-data
  tar xf demo-data.tar --strip-components=1 -C demo-data
  rm demo-data.tar
  mysql -u ${OPENMAGE_DB_USER} -p${OPENMAGE_DB_PASS} -h ${OPENMAGE_DB_HOST} ${OPENMAGE_DB_NAME} < demo-data/*.sql
  rm demo-data/*.sql
  cp -r demo-data/* /var/www/html/
}

function install_language_pack() {
  echo "Installing Language Packs"
  local LANG_DIR='/tmp/translations'
  git clone https://github.com/luigifab/openmage-translations ${LANG_DIR}
  cp -r ${LANG_DIR}/locales /var/www/html/
}

function install_plugin() {
  echo "Installing Extension"
  local PLUGIN_DIR=/tmp/plugin/
  if [[ -n ${PLUGIN_URL} && ${PLUGIN_URL} != 'local' ]]; then
    PLUGIN_DIR=$(mktemp -d)
    if [[ -z ${PLUGIN_VERSION} || ${PLUGIN_VERSION} == 'latest' ]]; then
      git clone -b ${PLUGIN_VERSION} ${PLUGIN_URL} ${PLUGIN_DIR}
    else
      git clone ${PLUGIN_URL} ${PLUGIN_DIR}
    fi
  fi
  cd /var/www/html
  cp -r ${PLUGIN_DIR}/app ${PLUGIN_DIR}/skin .
  n98-magerun cache:flush
}

function run_periodic_flush() {
  local INTERVAL=${1:-60}
  while sleep ${INTERVAL}; do n98-magerun cache:clean >& /dev/null & done &
}

function setup_store() {
  cd /var/www/html
  php -f install.php -- \
    --license_agreement_accepted 'yes' \
    --locale 'de_AT' \
    --timezone 'Europe/Vienna' \
    --db_host ${OPENMAGE_DB_HOST} \
    --db_name ${OPENMAGE_DB_NAME} \
    --db_user ${OPENMAGE_DB_USER} \
    --db_pass ${OPENMAGE_DB_PASS} \
    --url ${OPENMAGE_BASEURL} \
    --use_rewrites 'yes' \
    --use_secure 'yes' \
    --secure_base_url ${OPENMAGE_BASEURL} \
    --use_secure_admin 'yes' \
    --admin_username "${OPENMAGE_ADMIN_USER}" \
    --admin_lastname "Page" \
    --admin_firstname "QENTA Checkout" \
    --admin_email "sp-magento1-p@qenta.com" \
    --admin_password "${OPENMAGE_ADMIN_PASS}" \
    --session_save 'files' \
    --admin_frontname 'admin_qenta' \
    --default_currency 'EUR' \
    --skip_url_validation 'yes'
}

function print_info() {
  echo
  echo '####################################'
  echo
  echo "Shop: https://${OPENMAGE_BASEURL}"
  echo "Admin Panel: https://${OPENMAGE_BASEURL}/admin_qenta/"
  echo "User: ${OPENMAGE_ADMIN_USER}"
  echo "Password: ${OPENMAGE_ADMIN_PASS}"
  echo
  echo '####################################'
  echo
}

function _log() {
  echo "${@}" >> /tmp/shop.log
}

if [[ -e wp-config.php ]]; then
  echo "Shop detected. Skipping installations"
  OPENMAGE_BASEURL=$(echo "BLABLABLA")
else
  switch_version ${OPENMAGE_VERSION}
  _log "Magento2 version set to: ${OPENMAGE_VERSION}"

  install_core
  _log "Shop installed"

  install_language_pack
  _log "installed 3rd party language pack de_DE"
  
  install_sample_data
  _log "Sample data installed"

  setup_store
  _log "store set up"
  
  if [[ -n ${PLUGIN_URL} ]]; then
    install_plugin
    _log "plugin installed"
  fi
  if [[ -n ${OVERRIDE_api_uri} ]]; then
    change_api_uri "${OVERRIDE_api_uri}" &&
    _log "changed API URL to ${OVERRIDE_api_uri}" &&
    _api_uri_changed=true
  fi
fi
if [[ ${CI} != 'true' ]]; then
  (sleep 1; print_info) &
fi

run_periodic_flush 3m

_log "url=https://${OPENMAGE_BASEURL}"
_log "ready"

echo "ready" > /tmp/debug.log

mkdir -p /var/www/magento2/log
touch /var/www/magento2/log/exception.log

apache2-foreground "$@" &
tail -f /var/www/magento2/log/exception.log
