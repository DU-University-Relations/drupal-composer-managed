/**
 * Drupal User Management Helper
 *
 * Provides type-safe access to Drupal users for testing.
 */

import fs from 'node:fs/promises';
import path from 'node:path';
import {Role, RoleName, roles} from '../data/users/test-roles';
import {BrowserContext, expect, Page} from "@playwright/test";
import {getDataPath} from "@du_pw/support/files";

const COOKIE_DIR = getDataPath('cookies');

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
 * Log in via the login form (SLOWEST method - ~2-5 seconds).
 * Use ONLY for testing the actual login flow.
 *
 * @param {object} page - Playwright Page object
 * @param {object} context - Playwright Context object
 * @param {object} role - User account with userName and userPassword
 * @returns {Promise<void>}
 */
export async function logInViaForm(page: Page, context: BrowserContext, role: Role): Promise<void> {
  await context.clearCookies()
  await page.goto('/user/login')

  // Toggle login form visibility.
  // Have to use ".toggle-user-login-state.icon-du-down-arrow" since no better selector exists.
  await page.locator('.toggle-user-login-state.icon-du-down-arrow').click()

  await page.getByLabel('Username').fill(role.test_user)
  await page.getByLabel('Password').fill(role.test_user)
  await page.getByRole('button', { name: 'Log in' }).click()

  await page.waitForLoadState('domcontentloaded')

  // Verify login success.
  const textContent = await page.textContent('body')
  expect(textContent).toContain('Member for')
}

/**
 * Log out via the UI.
 *
 * @param {object} page - Playwright Page object
 * @returns {Promise<void>}
 */
export async function logOutViaUi(page: Page): Promise<void> {
  await page.goto(`/user/logout`)
  await page.getByRole('button', { name: 'Log out' }).click()

  // Should redirect to the front page.
  await expect(page).toHaveURL('/')
}

/**
 * Gets the cookie file path for a specific role
 */
function getCookiePath(role: Role): string {
  return path.join(COOKIE_DIR, `${role.name}-cookies.json`);
}

/**
 * Loads cookies from file and adds them to the context
 */
async function loadCookies(context: BrowserContext, role: Role): Promise<boolean> {
  const cookiePath = getCookiePath(role);

  try {
    const cookiesString = await fs.readFile(cookiePath, 'utf-8');
    const cookies = JSON.parse(cookiesString);
    await context.addCookies(cookies);
    return true;
  } catch (error) {
    return false;
  }
}

/**
 * Saves cookies to file for future use
 */
async function saveCookies(context: BrowserContext, role: Role): Promise<void> {
  await fs.mkdir(COOKIE_DIR, { recursive: true });
  const cookies = await context.cookies();
  await fs.writeFile(
    getCookiePath(role),
    JSON.stringify(cookies, null, 2),
    'utf-8'
  );
}

/**
 * Logs in using saved cookies, falls back to form login if needed
 */
export async function logIn(page: Page, context: BrowserContext, role: Role): Promise<void> {
  const cookiesLoaded = await loadCookies(context, role);

  // No saved cookies, login via form and then save cookies for future use.
  if (!cookiesLoaded) {
    await logInViaForm(page, context, role);
    await saveCookies(context, role);
  }
}

/**
 * Create a fresh anonymous Page (new isolated BrowserContext).
 * @param sourceContext Existing test BrowserContext.
 * @returns Anonymous Page in its own new BrowserContext.
 */
export async function createAnonSession(sourceContext: BrowserContext): Promise<Page> {
  const browser = sourceContext.browser();
  if (!browser) throw new Error('Browser not available from source context.');
  const anonContext = await browser.newContext();
  return await anonContext.newPage();
}

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
  logInViaForm,
  logOutViaUi,
  logIn,
  createAnonSession
};