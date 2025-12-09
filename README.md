# Composer-enabled Drupal template

This "hard fork" of Pantheon's recommended starting point for forking new [Drupal](https://www.drupal.org/) upstreams
that work with the Platform's Integrated Composer build process.

For more information and detailed installation guides, please visit the
Integrated Composer Pantheon documentation: https://pantheon.io/docs/integrated-composer

## Documentation

https://ducloudwiki.atlassian.net/wiki/spaces/DS/pages/564690946/DUCore+-+Features+Used+by+All+Sites

## Testing Badges

[![ComposerBuildCheck](https://github.com/DU-University-Relations/drupal-composer-managed/actions/workflows/php.yml/badge.svg)](https://github.com/DU-University-Relations/drupal-composer-managed/actions/workflows/php.yml)

[![Install and DU Core install](https://github.com/DU-University-Relations/drupal-composer-managed/actions/workflows/drush.yml/badge.svg)](https://github.com/DU-University-Relations/drupal-composer-managed/actions/workflows/drush.yml)

[![e2e](https://github.com/DU-University-Relations/drupal-composer-managed/actions/workflows/playwright.yml/badge.svg)](https://github.com/DU-University-Relations/drupal-composer-managed/actions/workflows/playwright.yml)

[![DU Bootstrap Theme Test](https://github.com/DU-University-Relations/du_bootstrap/actions/workflows/theme-test.yml/badge.svg)](https://github.com/DU-University-Relations/du_bootstrap/actions/workflows/theme-test.yml)

## Local Setup

DDEV is used to manage the local development environment. You can start it up with the following
commands, or by running `./private/scripts/bootstrap-local.sh`:

```shell
# Install dependencies and set up the Drupal site.
ddev start
ddev composer install --no-interaction --no-ansi --no-progress
ddev drush si ducore -y
```

## Functional Testing Setup

Functional testing is done using Playwright and you can read more in the
[Playwright Testing Documentation](tests/playwright/README.md).



