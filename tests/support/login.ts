import type { Page, BrowserContext } from '@playwright/test'
import { expect } from '@playwright/test'
import {Role} from "../data/test-roles";
import {URLPattern} from "node:url";

/**
 * Log in via the login form (SLOWEST method - ~2-5 seconds).
 * Use ONLY for testing the actual login flow.
 *
 * @param {object} page - Playwright Page object
 * @param {object} context - Playwright Context object
 * @param {object} role - User account with userName and userPassword
 * @returns {Promise<void>}
 */
async function logInViaForm(page: Page, context: BrowserContext, role: Role): Promise<void> {
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
async function logOutViaUi(page: Page): Promise<void> {
  await page.goto(`/user/logout`)
  await page.getByRole('button', { name: 'Log out' }).click()

  // Should redirect to the front page.
  await expect(page).toHaveURL('/')
}

export {
  logInViaForm,
  logOutViaUi,
}