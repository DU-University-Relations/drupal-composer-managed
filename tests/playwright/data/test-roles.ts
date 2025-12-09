/**
 * Test role definitions for Playwright tests
 * 
 * Auto-generated from Drupal via: npm run generate-roles
 * DO NOT EDIT MANUALLY - your changes will be overwritten
 */

export interface Role {
  name: string;
  test_user: string;
  // Add more properties as needed:
  // permissions?: string[];
  // displayName?: string;
  // description?: string;
}

export const roles = {
  authenticated: {
    name: 'authenticated',
    test_user: 'qa_authenticated',
  },
  user_admin: {
    name: 'user_admin',
    test_user: 'qa_user_admin',
  },
  edit_own: {
    name: 'edit_own',
    test_user: 'qa_edit_own',
  },
  content_editor: {
    name: 'content_editor',
    test_user: 'qa_content_editor',
  },
  site_admin: {
    name: 'site_admin',
    test_user: 'qa_site_admin',
  },
  administrator: {
    name: 'administrator',
    test_user: 'qa_administrator',
  },
} as const satisfies Record<string, Role>;
// 'as const' = make this immutable and preserve exact string values as types
// 'satisfies Record<string, Role>' = verify each role has 'name' and 'test_user' properties

export type RoleName = keyof typeof roles;
// 'typeof roles' = get the type of the roles object
// 'keyof' = extract all keys as a union type: 'anonymous' | 'authenticated' | 'user_admin' | ...
