# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Module Overview

`du_logging` is a custom Drupal module that reduces database log entries by filtering out noisy messages when `dblog` is enabled. It uses the **service decorator pattern** to wrap Drupal's database logger.

## Architecture

### Service Decoration Pattern
The module decorates the `logger.dblog` service (see `du_logging.services.yml`):
- Service: `du_logging.dblog_filter`
- Decorates: `logger.dblog`
- Decoration priority: 100
- The `FilteredLogger` class wraps the original dblog logger and filters messages before passing them through

### Filtering Logic (src/Logger/FilteredLogger.php)
Messages are filtered based on three criteria (all applied via `shouldFilter()` method):
1. **Log level filtering**: Only allows specified RFC log levels (Emergency, Alert, Critical, Error, Warning, Notice, Info, Debug)
2. **Channel/message type filtering**: Blocks messages from specific channels (e.g., 'cron', 'php')
3. **Pattern matching**: Applies regex patterns to the **rendered message** (after token replacement)

**Important**: Pattern filtering operates on the final rendered message as it appears in the dblog UI, not the raw template string. This matches what admins see in `/admin/reports/dblog`.

### Configuration
- Route: `/admin/config/development/du_logging`
- Form: `FilteredLoggerSettingsForm`
- Config key: `du_logging.settings`
- Settings: `enabled`, `log_levels`, `message_types`, `patterns`

## Testing

### Unit Tests
Run PHPUnit tests from the Drupal root directory:
```bash
cd /Users/alexander.finnarn/Sites/du/drupal-composer-managed/web
../vendor/bin/phpunit -c core profiles/custom/ducore/modules/custom/du_logging/tests/src/Unit/
```

Test file: `tests/src/Unit/FilteredLoggerTest.php`
- Uses mock objects for `LoggerInterface`, `ConfigFactory`, and `TranslationInterface`
- Tests filtering behavior for levels, channels, and patterns
- Tests both enabled/disabled states

### Playwright E2E Tests
Run from repository root:
```bash
cd /Users/alexander.finnarn/Sites/du/drupal-composer-managed
npx playwright test --grep @du_logging
```

Test file: `tests/playwright/e2e/du_logging.spec.ts`
- Uses custom Playwright helpers from `@du_pw/test` (getRole, logIn, createAnonSession)
- Uses `@du_pw/support/drush` for running Drush commands
- Tests filtering in action by checking dblog UI visibility

### Running a Single Test
```bash
# Single unit test class
../vendor/bin/phpunit -c core profiles/custom/ducore/modules/custom/du_logging/tests/src/Unit/FilteredLoggerTest.php

# Single Playwright test
npx playwright test tests/playwright/e2e/du_logging.spec.ts
```

## Drush Commands Used in Tests
```bash
# Enable module
drush en du_logging -y

# Disable module
drush pmu du_logging -y

# Clear watchdog logs
drush watchdog:delete all -y

# Update config
drush cset du_logging.settings enabled 0 -y
```

## Key Implementation Details

1. **Token Replacement**: The `FilteredLogger` uses `TranslationInterface::translate()` to render messages with context tokens before pattern matching. This simulates how dblog displays messages.

2. **Performance**: Level filtering is checked first as the most efficient check before more expensive channel and pattern checks.

3. **Empty Criteria Handling**: If no filtering criteria are configured, messages pass through (fail-safe behavior).
