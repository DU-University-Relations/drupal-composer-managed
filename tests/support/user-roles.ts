/**
 * Drupal Role Management Helper
 *
 * Provides type-safe access to Drupal user roles for testing.
 *
 * Usage:
 *   import { getRole, getAllRoles, hasRole } from '../support/roles';
 *
 *   const adminRole = getRole('administrator');
 *   const allRoles = getAllRoles();
 */

import { roles, Role, RoleName } from '../data/test-roles';

/**
 * Type guard to check if a string is a valid role name
 */
export function isValidRoleName(name: string): name is RoleName {
  return name in roles;
}

/**
 * Get a specific role by name
 *
 * @param roleName - The name of the role
 * @returns Role object
 * @throws Error if role doesn't exist
 *
 * @example
 * const adminRole = getRole('administrator');
 * console.log(adminRole.name); // 'administrator'
 */
export function getRole(roleName: RoleName): Role {
  const role = roles[roleName];
  if (!role) {
    throw new Error(`Role "${roleName}" not found in test-roles.json`);
  }
  return role;
}


/**
 * Get all roles as an array
 *
 * @returns Array of all Role objects
 *
 * @example
 * const allRoles = getAllRoles();
 * allRoles.forEach(role => console.log(role.name));
 */
export function getAllRoles(): Role[] {
  return Object.values(roles);
}

/**
 * Get all role names as an array
 *
 * @returns Array of role name strings
 *
 * @example
 * const names = getRoleNames();
 * // ['administrator', 'content_editor', ...]
 */
export function getRoleNames(): string[] {
  return Object.keys(roles);
}

/**
 * Check if a role exists
 *
 * @param roleName - The name to check
 * @returns true if the role exists
 *
 * @example
 * if (hasRole('administrator')) {
 *   // Role exists
 * }
 */
export function hasRole(roleName: string): boolean {
  return isValidRoleName(roleName);
}

/**
 * Filter roles by a predicate function
 *
 * @param predicate - Function to test each role
 * @returns Array of roles that match the predicate
 *
 * @example
 * // Get all roles that start with 'content'
 * const contentRoles = filterRoles(role => role.name.startsWith('content'));
 */
export function filterRoles(predicate: (role: Role) => boolean): Role[] {
  return getAllRoles().filter(predicate);
}

/**
 * Get the count of available roles
 *
 * @returns Number of roles
 *
 * @example
 * console.log(`Testing ${getRoleCount()} roles`);
 */
export function getRoleCount(): number {
  return getRoleNames().length;
}

/**
 * Export the raw roles object for advanced use cases
 */
export const ROLES = roles as Record<string, Role>;

/**
 * Default export for convenience
 */
export default {
  getRole,
  getAllRoles,
  getRoleNames,
  hasRole,
  filterRoles,
  getRoleCount,
  isValidRoleName,
  ROLES,
};