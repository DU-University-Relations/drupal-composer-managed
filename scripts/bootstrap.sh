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

if [ "$IS_CI" = true ]; then
  echo "Installing profile..."
  ddev drush si ducore -y
  npm ci
else
  npm install
fi

ddev drush en du_functional_testing -y
npm run generate-roles
