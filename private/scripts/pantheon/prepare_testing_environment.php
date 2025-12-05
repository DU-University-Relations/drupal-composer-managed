<?php

/**
 * @file
 * Operations to run after a database clone.
 *
 * This code is configured to run via the clone_database operation in site-specific pantheon.yml files.
 */

// The clone_database may be triggered on any environment, but we only want
// to performn these operations on non-live environments.
if (isset($_POST['environment']) && $_POST['environment'] != 'live') {

  echo "Enabling functional testing module.\n";
  passthru('drush pm-enable -y du_functional_testing');

  echo "Sanitizing the database.\n";
  passthru('drush sql-sanitize -y');
}
