#!/usr/bin/env bash
set -euxo pipefail

if [ "${GITHUB_ACTIONS:-}" = "true" ]; then
  echo "Detected GitHub CI environment"
  IS_CI=true
else
  echo "Detected local environment"
  IS_CI=false
fi

# DDEV already started in CI.
if [ "$IS_CI" != true ]; then
  ddev start
fi

ddev composer install --no-interaction --prefer-dist
ddev drush si ducore -y

if [ "$IS_CI" = true ]; then
  echo "Installing npm CI..."
  npm ci
else
  echo "Installing npm locally..."
  npm install
fi

ddev drush en du_functional_testing -y
npm run generate-roles
