<?php

declare(strict_types=1);

namespace Drupal\du_functional_testing\Drush\Commands;

use Drupal\node\Entity\Node;
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

  /**
   * Delete all content created by a specific user.
   */
  #[CLI\Command(name: 'du:delete-content', aliases: ['du:dc'])]
  #[CLI\Option(name: 'user', description: 'The username of the user whose content should be deleted')]
  #[CLI\Usage(name: 'drush du:delete-content --user=qa_site_admin', description: 'Delete all content created by qa_site_admin user.')]
  public function deleteContent(array $options = ['user' => NULL]): void {
    $username = $options['user'];

    if (empty($username)) {
      $this->logger()->error('Username is required. Use --user=username');
      return;
    }

    // Load the user by username.
    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['name' => $username]);

    if (empty($users)) {
      $this->logger()->error("User '{$username}' not found.");
      return;
    }

    $user = reset($users);
    $uid = $user->id();

    // Find all nodes created by this user.
    $query = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('uid', $uid)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    if (empty($nids)) {
      $this->logger()->success("No content found for user '{$username}'.");
      return;
    }

    $count = count($nids);
    $this->logger()->notice("Found {$count} content item(s) for user '{$username}'.");

    // Delete the nodes.
    $nodes = Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $node->delete();
    }

    $this->logger()->success("Successfully deleted {$count} content item(s) for user '{$username}'.");
  }
}
