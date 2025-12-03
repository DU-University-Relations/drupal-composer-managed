# Pantheon Quicksilver Automation Scripts

Pantheon has specific rules on what directories are kept private to the outside world, and there
is no reason for the scripts to be accessible outside of the Pantheon server environment.

`web` is the docroot, and normally we would place the scripts in `web/private/scripts`. However,
since Composer manages the web directory, we have to store the scripts in `private/scripts` and
have a post install command copy the scripts to `web/private/scripts`.

## DU Core Profile Scripts vs. Site Configuration

Since these scripts can be used for any site, we put the main scripts and logic in the shared
Drupal core profiles. However, the scripts need to be triggered by site-specific pantheon.yml files.

## Pantheon Secrets

The automation scripts need access to Pantheon secrets, which are stored in the environment. See
[Pantheon Secrets documentation](https://ducloudwiki.atlassian.net/wiki/spaces/DS/pages/1070104590/Pantheon+Secrets#Using-Pantheon-Secrets-in-DDEV)
for more information on how to work with secrets.

## Automation Workflows

There are several workflows that can be triggered by configuring `pantheon.yml` files in the
site codebases.

## After Database Clone Operations

It is useful to target the database clone operation to enable testing modules amongst other
things.

Actions performed:
- Enables functional testing modules
- Sanitizes the database

Pre-requisites:
- None

```yaml
workflows:
  clone_database:
    after:
      - type: webphp
        description: Prepare testing environment
        script: private/scripts/pantheon/after_database_clone_actions.php
```

## After Autopilot Notification

You can send a notification to MS Teams after an autopilot operation to let the team know that
Autopilot ran and either succeeded or failed.

Actions performed:
- Sends a notification to MS Teams

Pre-requisites:
- `$_ENV['AUTOPILOT_WEBHOOK_URL']` needs to be set via Terminus.

```yaml
workflows:
  autopilot_vrt:
    after:
      - type: webphp
        description: Autopilot after
        script: private/scripts/pantheon/after_autopilot_notification.php
```

## After Clear Cache Actions

The `cache_clear` hook runs after several Pantheon web operations, and it is useful to do things
like warm the cache for critical pages.

Actions performed:
- Warms cache of critical pages

Pre-requisites:
- `$_ENV['VANITY_DOMAIN']` needs to be set via Terminus so the live site can be targeted.

```yaml
workflows:
  autopilot_vrt:
    after:
      - type: webphp
        description: Autopilot after
        script: private/scripts/pantheon/after_autopilot_notification.php
```
