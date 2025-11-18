import { test, expect } from '@du_pw/test';
import { getAllRoles, logInViaForm, logOutViaUi, logIn } from "@du_pw/support/users";

const testUsers = getAllRoles();

test.describe('@smoke - Login and out Tests', () => {
  testUsers.forEach((role) => {
    test(`log in and out as ${role.name}`, async ({ page, context }) => {
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

test.describe('@smoke - Login and save cookies', () => {
  testUsers.forEach((role) => {
    test(`log in as ${role.name}`, async ({ page, context }) => {
      await logIn(page, context, role);
    });
  });
});