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

The `./private/scripts/bootstrap-local.sh` script will setup the environment for running the tests,
but you can look at the commands in the script and run them manually if you need to.

```bash
# Install testing dependencies.
npm install
ddev drush en du_functional_testing -y

# Generate test user data.
npm run generate-roles
```

## Running Tests

The tests are set to run using a `baseURL` in the Playwright config file, but you can also pass 
in the base URL as an argument to the `npx playwright test` command in order to run the tests 
against a different environment, like on Pantheon.

```bash
# Run tests against a local environment.
npx playwright test

# Run tests against a Pantheon environment.
BASE_URL="https://test-site.pantheonsite.io/" npx playwright test

# Run tests on a specific tag.
npx playwright test --grep @tag-name
```

## Structure

The `tests/playwright` directory is structured into separate subdirectories for various purposes.

- `test.ts` - This extends Playwright's `test` function and is the entry point for the tests.
  - Also excludes certain routes from loading during tests, like Termly.

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

## Testing Live Pantheon Sites

In order to help with testing on Pantheon, you can use this test suite to target runs against 
a Pantheon site. 

Tests tagged with `@d9` will run against a D9 site, and since CKEditor 5 is enabled on the D9 
sites, the same test helpers can be used for testing the D10 profile.

You can run the tests against a Pantheon site by setting the `BASE_URL` environment variable to 
the site's URL.

```bash
BASE_URL="https://www-du-core.ddev.site/" npx playwright test --grep @d9
```

### Run Tests Against All Dev/Test Sites

Before a deployment, it can be useful to run the tests against all the Dev or Test sites.


