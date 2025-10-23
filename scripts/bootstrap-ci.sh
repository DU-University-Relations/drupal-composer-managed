#!/usr/bin/env bash
set -euxo pipefail

# Install dependencies and set up the Drupal site.
# DDEV already started in CI via DDEV setup action.
ddev composer install --no-interaction --prefer-dist
ddev drush si ducore -y

# Install Node.js dependencies and set up functional testing.
cd tests
npm ci
ddev drush en du_functional_testing -y
npm run generate-roles
