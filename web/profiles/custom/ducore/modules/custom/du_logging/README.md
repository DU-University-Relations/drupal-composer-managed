## Introduction

The DU Logging module reduces database log entries by filtering out noisy messages when `dblog` 
is enabled. It uses the **service decorator pattern** to wrap Drupal's database logger and 
filter messages based on configurable criteria.

Key features:
- Filter logs by severity level (Emergency, Alert, Critical, Error, Warning, Notice, Info, Debug)
- Block messages from specific channels (e.g., 'php', 'cron', 'page not found')
- Use regex patterns to filter messages matching specific text
- Reduces database bloat from high-volume, low-value log entries

## Requirements

This module requires:
- Drupal core's `dblog` module to be enabled
- Drupal 10 or 11

## Installation

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.

## Configuration

1. Navigate to `/admin/config/development/du_logging` (Configuration > Development > DU Logging)
2. **Enable filtered logging**: Check this box to activate message filtering
3. **Allowed log levels**: Select which severity levels should be logged. If none are selected, 
   all levels will be logged. Unselected levels will be filtered out.
4. **Message types to filter**: Enter channel names to block, one per line. Common examples:
   ```
   php
   cron
   page not found
   ```

5. **Message patterns to filter**: Enter regex patterns to match against log messages, one per 
   line. Examples:
   ```
   /deprecated/i
   /notice.*undefined/i
   /user warning/i
   ```
   **Important**: Patterns are matched against the final rendered message (as displayed in the 
   database log UI at `/admin/reports/dblog`), not the raw message template.

6. Save the configuration form

### How Filtering Works

The module evaluates messages in this order:
1. **Is filtering enabled?** If not, all messages pass through
2. **Log level check**: Does the message severity match allowed levels?
3. **Channel check**: Is the message channel in the blocked list?
4. **Pattern check**: Does the rendered message match any filter patterns?

If any filter condition matches (levels excluded, channel blocked, or pattern matched), the 
message is suppressed and not written to the database.

## Technical Details

The module uses Drupal's service decoration to wrap the `logger.dblog` service with priority 100.
The `FilteredLogger` class intercepts log messages before they reach the database logger and 
applies filtering logic.

Service definition: `du_logging.services.yml`
Logger class: `src/Logger/FilteredLogger.php`
Configuration form: `src/Form/FilteredLoggerSettingsForm.php`

