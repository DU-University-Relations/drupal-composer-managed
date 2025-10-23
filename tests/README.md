# DU Core Profile Tests

These are the tests for the DU Core Profile written using Drupal 10.

## Testing Docs

Some documentation about testing is available on the DU Confluence wiki and some documentation 
within this repository. You should place general testing documentation on the Confluence wiki 
and docs specifically for writing tests within this repository.

General testing documentation:
- [Testing Introduction & Background](https://ducloudwiki.atlassian.net/wiki/spaces/DS/pages/1168900125/Testing+-+Introduction+Background)

Project-specific testing documentation:
- [Writing Tests](docs/writing-tests.md)

## Testing Setup

The `./scripts/bootstrap-local.sh` script will setup the environment for running the tests, but 
you can look at the commands in the script and run them manually if you need to.

```bash
# Install testing dependencies.
npm install
ddev drush en du_functional_testing -y

# Generate test user data.
npm run generate-roles
```

## Running Tests

The tests are set to run using a `baseURL` in the Playwright config file, but you can also pass 
in the base URL as an argument to the `npm run test` command in order to run the tests against 
a different environment, like on Pantheon.

```bash
# Run tests against a local environment.
npm run test

# Run tests against a Pantheon environment.
PLAYWRIGHT_BASE_URL="https://test-site.pantheonsite.io/" npm run test 
```

## Structure

The tests' directoy is structured into separate subdirectories for various purposes.

### Assets

You will need to upload images and other assets to the Drupal site where users interact with 
file inputs. Place any assets used in the tests in the appropriate subdirectory.

- `assets` - Generic assets to be used by the tests.
  - `images` - Any images used by the tests.

### Data

The tests will use shared data for things like users and content. Try not to hardcode any data 
within the tests and consider if it can be parameterized and shared.

- `test-roles.ts` - User data shared across tests.

### Docs

Documentation about writing code for the tests should be stored here. It will be helpful to any 
AI assistant or agent to read the documentation and reference it for assistance in writing and 
maintaining the test suite.

### e2e

Playwright tests are stored in this directory and broken down into test cases and test plans.

### scripts

Any scripts used to create files, like the test data, should be stored here.

- `generate-role-data.ts` - Generates the `data/test-roles.ts` file via user role information 
  from drush.

### support

Any support files used by the tests should be stored here.

- `files.ts` - Support for working with files.
- `users.ts` - Use this for working with users in tests including login/logout methods.

## Todos

These would be good things to add to the tests:

- [ ] Implement cookie-based login https://medium.com/automated-monotony/using-playwright-cookies-to-bypass-authentication-b5eb29b35c73
- [ ] Possibly move bootstrap scripts to DDEV commands
- [ ] Add the ability to run drush commands via the support helper files
- [ ] Stop CI run on first test failure
