# DU Functional Testing Module

## INTRODUCTION

The DU Functional Testing module provides Drush commands and utilities to support functional testing with Playwright.

The primary use cases for this module are:

- Creating test users for Playwright tests
- Managing test user credentials and roles
- Verifying test environment setup
- Cleaning up test data

## REQUIREMENTS

- Drupal 10 or 11
- Drush 12+
- DU Core installation profile

## INSTALLATION

This module is part of the DU Core installation profile and is located at:
`web/profiles/custom/ducore/modules/custom/du_functional_testing`

Enable the module:
```bash
ddev drush en du_functional_testing -y
```

## DRUSH COMMANDS

### Create Test Users

Create all test users defined for Playwright tests:

```bash
ddev drush du-test:create-users
# or
ddev drush dutcu
```

This creates the following users:
- `du_test_admin` - Administrator user
- `du_test_editor` - Editor user
- `du_test_auth` - Basic authenticated user
- `du_test_creator` - Content creator user

### List Test Users

View all test users and their status:

```bash
ddev drush du-test:list-users
# or
ddev drush dutlu
```

### Delete Test Users

Remove all test users:

```bash
ddev drush du-test:delete-users
# or
ddev drush dutdu
```

### Reset Passwords

Reset all test user passwords to default values:

```bash
ddev drush du-test:reset-passwords
# or
ddev drush dutrp
```

### Verify Test Users

Check that all test users exist and have correct configuration:

```bash
ddev drush du-test:verify-users
# or
ddev drush dutvu
```

## USAGE WITH PLAYWRIGHT

After creating test users with Drush, they can be used in Playwright tests via the authentication manager:

```javascript
import { authManager } from '../../support/auth-manager.js'
import testUsers from '../../support/test-users.json' assert { type: 'json' }

test('Test as admin', async ({ page, context }) => {
  await authManager.switchToUser(page, context, 'admin')
  // Test admin functionality
})
```

## CONFIGURATION

Test users are defined in `tests/playwright/support/test-users.json` in the project root.

To modify test users:
1. Update `test-users.json`
2. Update the `TEST_USERS` constant in `src/Commands/DuTestingCommands.php`
3. Run `ddev drush du-test:create-users` to create the new users

## MAINTAINERS

Current maintainers:

- DU University Relations Development Team

