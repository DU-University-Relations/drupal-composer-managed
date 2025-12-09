# Pantheon Quicksilver Automation Scripts

Pantheon has specific rules on what directories are kept private to the outside world, and there
is no reason for the scripts to be accessible outside the Pantheon server environment.

`web` is the docroot, and normally we would place the scripts in `web/private/scripts`. However,
since Composer manages the web directory, we have to store the scripts in `private/scripts` and
have a post install command copy the scripts to `web/private/scripts`. We keep Pantheon-specific
scripts in `private/scripts/pantheon`.

## DU Core Profile Scripts vs. Site Configuration

Since these scripts can be used for any site, we put the main scripts and logic in the shared
Drupal core profiles. However, the scripts need to be triggered by site-specific pantheon.yml files.

## Pantheon Secrets

The automation scripts need access to Pantheon secrets, which are stored in the environment. See
[Pantheon Secrets documentation](https://ducloudwiki.atlassian.net/wiki/spaces/DS/pages/1070104590/Pantheon+Secrets#Using-Pantheon-Secrets-in-DDEV)
for more information on how to work with secrets.

## Site Metadata File

Some scripts need to know the site's metadata, which is stored in `sites/default/site_meta.json`.
Please add that to your site repository to use these scripts.

Example site metadata file:
```json
{
  "critical_paths": [
    "/about",
    "/contact"
    ],
  "domains":  {
    "live": "https://www.du.edu"
  }
}
```

## Automation Workflows

There are several workflows that can be triggered by configuring `pantheon.yml` files in the
site codebases.

## Prepare Testing Environment

It is useful to prepare the test environment before running functional tests, and the database 
clone operation is a good web operation to target.

Actions performed:
- Enables functional testing modules
- Sanitizes the database

Pre-requisites:
- ...possibly add tables to be sanitized here

```yaml
workflows:
  clone_database:
    after:
      - type: webphp
        description: Prepare testing environment
        script: private/scripts/pantheon/prepare_testing_environment.php
```

## Send Autopilot Notification

You can send a notification to MS Teams after an autopilot operation to let the team know that
Autopilot ran and either succeeded or failed.

Actions performed:
- Sends a notification to MS Teams

Pre-requisites:
- `$_ENV['AUTOPILOT_NOTIFICATION_URL']` needs to be set via Terminus.

```yaml
workflows:
  autopilot_vrt:
    after:
      - type: webphp
        description: Send Autopilot run notification
        script: private/scripts/pantheon/send_autopilot_notification.php
```

## Warm Critical Paths

After web operations like clearing the cache, it is useful to warm the cache for critical pages 
so users don't experience slow load times.

Actions performed:
- Warms cache for critical pages

Pre-requisites:
- `site_meta.json` needs to be present in the codebase and include an array of critical pages.
  - Critical Paths: `$site_meta['critical_paths'] = ['/about', '/contact'];`
  - Live domain: `$site_meta['domains']['live'] = 'https://www.du.edu'`

```yaml
workflows:
  autopilot_vrt:
    after:
      - type: webphp
        description: Warm critical paths cache
        script: private/scripts/pantheon/warm_critical_paths.php
```
