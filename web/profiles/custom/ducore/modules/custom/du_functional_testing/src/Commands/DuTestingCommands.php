<?php

declare(strict_types=1);

namespace Drupal\du_functional_testing\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\user\Entity\User;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for DU functional testing.
 *
 * Provides commands to create, manage, and clean up test users
 * for Playwright functional tests.
 */
final class DuTestingCommands extends DrushCommands {

  /**
   * Test users configuration from test-users.json.
   */
  private const TEST_USERS = [
    'admin' => [
      'name' => 'du_test_admin',
      'mail' => 'admin@du-test.local',
      'pass' => 'TestAdmin123!',
      'roles' => ['administrator'],
    ],
    'editor' => [
      'name' => 'du_test_editor',
      'mail' => 'editor@du-test.local',
      'pass' => 'TestEditor123!',
      'roles' => ['editor'],
    ],
    'authenticated' => [
      'name' => 'du_test_auth',
      'mail' => 'authenticated@du-test.local',
      'pass' => 'TestAuth123!',
      'roles' => ['authenticated'],
    ],
    'content_creator' => [
      'name' => 'du_test_creator',
      'mail' => 'creator@du-test.local',
      'pass' => 'TestCreator123!',
      'roles' => ['content_creator'],
    ],
  ];

  /**
   * Create all test users for Playwright tests.
   */
  #[CLI\Command(name: 'du-test:create-users', aliases: ['dutcu'])]
  #[CLI\Usage(name: 'du-test:create-users', description: 'Create all test users for Playwright tests')]
  public function createTestUsers(): void {
    $created = 0;
    $skipped = 0;

    foreach (self::TEST_USERS as $key => $userInfo) {
      $existing = user_load_by_name($userInfo['name']);

      if ($existing) {
        $this->io()->warning(dt('User @name already exists, skipping.', ['@name' => $userInfo['name']]));
        $skipped++;
        continue;
      }

      try {
        $user = User::create([
          'name' => $userInfo['name'],
          'mail' => $userInfo['mail'],
          'pass' => $userInfo['pass'],
          'status' => 1,
        ]);

        // Add roles
        foreach ($userInfo['roles'] as $role) {
          if ($role !== 'authenticated') {
            $user->addRole($role);
          }
        }

        $user->save();

        $this->io()->success(dt('Created test user: @name (UID: @uid)', [
          '@name' => $userInfo['name'],
          '@uid' => $user->id(),
        ]));
        $created++;
      }
      catch (\Exception $e) {
        $this->io()->error(dt('Failed to create user @name: @error', [
          '@name' => $userInfo['name'],
          '@error' => $e->getMessage(),
        ]));
      }
    }

    $this->io()->writeln('');
    $this->io()->success(dt('Summary: @created created, @skipped skipped', [
      '@created' => $created,
      '@skipped' => $skipped,
    ]));
  }

  /**
   * Delete all test users.
   */
  #[CLI\Command(name: 'du-test:delete-users', aliases: ['dutdu'])]
  #[CLI\Usage(name: 'du-test:delete-users', description: 'Delete all test users created for Playwright tests')]
  public function deleteTestUsers(): void {
    $deleted = 0;
    $notFound = 0;

    foreach (self::TEST_USERS as $key => $userInfo) {
      $user = user_load_by_name($userInfo['name']);

      if (!$user) {
        $this->io()->warning(dt('User @name not found, skipping.', ['@name' => $userInfo['name']]));
        $notFound++;
        continue;
      }

      try {
        $user->delete();
        $this->io()->success(dt('Deleted test user: @name', ['@name' => $userInfo['name']]));
        $deleted++;
      }
      catch (\Exception $e) {
        $this->io()->error(dt('Failed to delete user @name: @error', [
          '@name' => $userInfo['name'],
          '@error' => $e->getMessage(),
        ]));
      }
    }

    $this->io()->writeln('');
    $this->io()->success(dt('Summary: @deleted deleted, @notfound not found', [
      '@deleted' => $deleted,
      '@notfound' => $notFound,
    ]));
  }

  /**
   * List all test users.
   */
  #[CLI\Command(name: 'du-test:list-users', aliases: ['dutlu'])]
  #[CLI\Usage(name: 'du-test:list-users', description: 'List all test users for Playwright tests')]
  #[CLI\FieldLabels(labels: [
    'name' => 'Username',
    'uid' => 'UID',
    'mail' => 'Email',
    'status' => 'Status',
    'roles' => 'Roles',
  ])]
  public function listTestUsers(): RowsOfFields {
    $rows = [];

    foreach (self::TEST_USERS as $key => $userInfo) {
      $user = user_load_by_name($userInfo['name']);

      if ($user) {
        $roles = array_values(array_diff($user->getRoles(), ['authenticated']));
        $rows[] = [
          'name' => $user->getAccountName(),
          'uid' => $user->id(),
          'mail' => $user->getEmail(),
          'status' => $user->isActive() ? 'Active' : 'Blocked',
          'roles' => implode(', ', $roles),
        ];
      }
      else {
        $rows[] = [
          'name' => $userInfo['name'],
          'uid' => 'N/A',
          'mail' => $userInfo['mail'],
          'status' => 'Not created',
          'roles' => implode(', ', array_diff($userInfo['roles'], ['authenticated'])),
        ];
      }
    }

    return new RowsOfFields($rows);
  }

  /**
   * Reset passwords for all test users.
   */
  #[CLI\Command(name: 'du-test:reset-passwords', aliases: ['dutrp'])]
  #[CLI\Usage(name: 'du-test:reset-passwords', description: 'Reset passwords for all test users to default values')]
  public function resetTestUserPasswords(): void {
    $reset = 0;
    $notFound = 0;

    foreach (self::TEST_USERS as $key => $userInfo) {
      $user = user_load_by_name($userInfo['name']);

      if (!$user) {
        $this->io()->warning(dt('User @name not found, skipping.', ['@name' => $userInfo['name']]));
        $notFound++;
        continue;
      }

      try {
        $user->setPassword($userInfo['pass']);
        $user->save();
        $this->io()->success(dt('Reset password for: @name', ['@name' => $userInfo['name']]));
        $reset++;
      }
      catch (\Exception $e) {
        $this->io()->error(dt('Failed to reset password for @name: @error', [
          '@name' => $userInfo['name'],
          '@error' => $e->getMessage(),
        ]));
      }
    }

    $this->io()->writeln('');
    $this->io()->success(dt('Summary: @reset passwords reset, @notfound users not found', [
      '@reset' => $reset,
      '@notfound' => $notFound,
    ]));
  }

  /**
   * Verify test users exist and have correct configuration.
   */
  #[CLI\Command(name: 'du-test:verify-users', aliases: ['dutvu'])]
  #[CLI\Usage(name: 'du-test:verify-users', description: 'Verify all test users exist and have correct roles')]
  public function verifyTestUsers(): void {
    $allValid = TRUE;

    foreach (self::TEST_USERS as $key => $userInfo) {
      $user = user_load_by_name($userInfo['name']);

      if (!$user) {
        $this->io()->error(dt('❌ User @name does not exist', ['@name' => $userInfo['name']]));
        $allValid = FALSE;
        continue;
      }

      // Check email
      if ($user->getEmail() !== $userInfo['mail']) {
        $this->io()->error(dt('❌ User @name has incorrect email: @actual (expected @expected)', [
          '@name' => $userInfo['name'],
          '@actual' => $user->getEmail(),
          '@expected' => $userInfo['mail'],
        ]));
        $allValid = FALSE;
      }

      // Check roles
      $userRoles = array_values(array_diff($user->getRoles(), ['authenticated']));
      $expectedRoles = array_values(array_diff($userInfo['roles'], ['authenticated']));

      $missingRoles = array_diff($expectedRoles, $userRoles);
      if (!empty($missingRoles)) {
        $this->io()->error(dt('❌ User @name is missing roles: @roles', [
          '@name' => $userInfo['name'],
          '@roles' => implode(', ', $missingRoles),
        ]));
        $allValid = FALSE;
      }

      if ($allValid) {
        $this->io()->success(dt('✓ User @name is correctly configured', ['@name' => $userInfo['name']]));
      }
    }

    $this->io()->writeln('');
    if ($allValid) {
      $this->io()->success('✓ All test users are correctly configured!');
    }
    else {
      $this->io()->error('❌ Some test users need attention. Run "drush du-test:create-users" to fix.');
    }
  }

}
