# GitHub Copilot Instructions for DU Drupal Composer Managed

## Project Overview

This is a Composer-managed Drupal 10 project configured for Pantheon hosting, serving as a template for DU (University of Denver) sites. It uses DU Core configuration and is built with Pantheon's Integrated Composer build process.

## Technology Stack

- **PHP**: Drupal 10 CMS with Composer for dependency management
- **Testing**: Playwright (TypeScript) for end-to-end tests
- **Hosting**: Pantheon platform
- **Local Development**: DDEV for local environment
- **CI/CD**: GitHub Actions workflows

## Key Documentation

- DU Core Features: https://ducloudwiki.atlassian.net/wiki/spaces/DS/pages/564690946/DUCore+-+Features+Used+by+All+Sites
- Pantheon Integrated Composer: https://pantheon.io/docs/integrated-composer

## Repository Structure

- `web/` - Drupal web root (profiles, sites directories)
- `config/` - Drupal configuration files
- `scripts/` - Bootstrap and deployment scripts
- `tests/playwright/` - Playwright end-to-end tests
- `upstream-configuration/` - DU upstream configuration packages
- `composer.json` - PHP dependencies and project configuration
- `package.json` - Node.js dependencies for testing

## Development Guidelines

### Dependency Management

- **Always use Composer** for PHP dependencies (Drupal modules, libraries)
- Run `composer validate --strict` before committing changes to composer.json
- Keep composer.lock in version control
- For JavaScript dependencies (testing only), use npm/package.json

### Testing

The repository has three main test suites:

1. **Composer Build Check** (`.github/workflows/php.yml`)
   - Validates composer.json and composer.lock
   - Installs dependencies to ensure build works
   - Run: `composer validate --strict && composer install`

2. **Drush Tests** (`.github/workflows/drush.yml`)
   - Tests Drupal installation and DU Core profile
   - Run via GitHub Actions

3. **Playwright E2E Tests** (`.github/workflows/playwright.yml`)
   - Located in `tests/playwright/e2e/`
   - Tests authentication, content creation, and user workflows
   - Run: `npm test` (requires Drupal site running)
   - See `tests/playwright/docs/writing-tests.md` for test writing guidelines

### Code Modifications

- **Minimal changes**: Make surgical, focused changes
- **Drupal best practices**: Follow Drupal coding standards
- **Test existing functionality**: Run relevant test suites after changes
- **Configuration**: Drupal configuration should go in `config/` directory
- **Custom code**: Add to appropriate directories under `web/profiles/` or as Composer packages

### Composer.json Structure

- Contains Drupal package repositories
- Includes custom library packages for Webform (CodeMirror, Select2, etc.)
- Uses path repository for `upstream-configuration`
- Patches are tracked in `patches.lock.json`

### Common Commands

```bash
# Validate composer files
composer validate --strict

# Install dependencies
composer install --no-interaction

# Generate test role data (Playwright)
npm run generate-roles

# Run Playwright tests (requires running Drupal site)
npm test
```

### Workflow Integration

- All PRs must pass Composer validation
- Changes to Drupal code should not break existing tests
- New features should include appropriate tests
- Bootstrap scripts (`scripts/bootstrap-ci.sh`, `scripts/bootstrap-local.sh`) prepare environments

## Conventions

- **Branch**: Main branch is `master`
- **Licensing**: MIT license
- **Package Name**: `du/drupal-composer-managed`
- **Drupal Version**: 10.x

## When Making Changes

1. Understand the impact on Drupal configuration
2. Validate composer.json if dependencies change
3. Test locally if possible using DDEV
4. Run appropriate test suites
5. Document breaking changes or new features
6. Keep changes focused and minimal
