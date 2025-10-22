# DU Core Profile Tests

These are the tests for the DU Core Profile written using Drupal 10.

## Testing Docs

Some documentation about testing is available on the DU Confluence wiki abd some documentation 
within this repository. You should place general testing documentation on the Confluence wiki 
and docs specifically for writing tests within this repository.

General testing documentation:
- [Testing Introduction & Background](https://ducloudwiki.atlassian.net/wiki/spaces/DS/pages/1168900125/Testing+-+Introduction+Background)

Project-specific testing documentation:
- [Writing Tests](docs/writing-tests.md)

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
- `login.ts` - Helper methods for logging users in and out of Drupal
- `user-roles.ts` - Use this for working with users in tests.
