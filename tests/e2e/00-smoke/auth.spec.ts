import { test, expect } from '@playwright/test';
import { getAllRoles, logInViaForm, logOutViaUi } from "@support/users";

test.describe('@smoke - Login and out Tests', () => {
  const testUsers = getAllRoles();

  testUsers.forEach((role) => {
    test(`login as ${role.name}`, async ({ page, context }) => {
      await logInViaForm(page, context, role);
      await logOutViaUi(page);
    });
  });
});

test.describe('@smoke - Admin paths protected from anon', () => {
  const paths = [
    '/admin/config',
    '/admin/content',
    '/admin/modules',
    '/admin/people',
    '/admin/structure'
  ]

  paths.forEach((path) => {
    test(`anon access denied for: ${path}`, async ({ page, context }) => {
      const response = await page.goto(path);
      // Expect a 403 status code.
      expect(response).not.toBeNull();
      expect(response!.status()).toBe(403);
    });
  });
});