<?php

declare(strict_types=1);

namespace Drupal\du_functional_testing\Drush\Commands;

use Drupal\user\Entity\Role;
use Drush\Commands\DrushCommands;
use Drush\Attributes as CLI;

/**
 * Drush commands for DU functional testing.
 *
 * Provides commands to create, manage, and clean up test users
 * for Playwright functional tests.
 */
final class DuTestingCommands extends DrushCommands {

  /**
   * Output role data as JSON for test generation.
   */
  #[CLI\Command(name: 'du:roles-json', aliases: ['du:rj'])]
  #[CLI\Usage(name: 'drush du:roles-json', description: 'Output all roles as JSON for Playwright test data generation.')]
  public function rolesJson(): void {
    $roles = Role::loadMultiple();
    $role_data = [];

    foreach ($roles as $role_id => $role) {
      // Skip anonymous role
      if ($role_id === 'anonymous') {
        continue;
      }

      $role_data[$role_id] = [
        'id' => $role_id,
        'label' => $role->label(),
        'test_user' => 'qa_' . $role_id,
//        'permissions' => $role->getPermissions(),
      ];
    }

    $this->output()->writeln(json_encode($role_data, JSON_PRETTY_PRINT));
  }
}
