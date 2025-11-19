import { test, expect } from '@du_pw/test';
import { faker } from '@faker-js/faker';

import { getRole , logIn, createAnonSession, logInViaForm} from "@du_pw/support/users";
import {runDrushCommand} from "@du_pw/support/drush";
import {run} from "node:test";

test.describe('@du_logging - Log filtering tests', () => {
  const administrator = getRole('administrator');
  const dblog_page = '/admin/reports/dblog';

  test.beforeAll(async () => {
    runDrushCommand('en du_logging -y');
  });

  test('Filter logs, then disable filtering', async ({ page, context }) => {
    await logIn(page, context, administrator);
    const anonPage = await createAnonSession(context);
    runDrushCommand('watchdog:delete all -y');

    // Logging should be enabled, so go to two routes to test.
    await anonPage.goto(dblog_page);
    await anonPage.goto('/foo/bar/baz');

    // Admin user should not see messages.
    await page.goto(dblog_page);
    await expect(page.getByText('No log messages available.')).toBeVisible();

    // Disable filtering and see messages again.
    runDrushCommand('cset du_logging.settings enabled 0 -y');
    runDrushCommand('watchdog:delete all -y');

    // Have anon user go to two routes to test.
    await anonPage.goto(dblog_page);
    await anonPage.goto('/foo/bar/baz');

    // dmin user should see messages.
    await page.goto(dblog_page);
    await expect(
      page.getByRole('table').getByRole('cell', { name: 'access denied' }).first()
    ).toBeVisible();
    await expect(
      page.getByRole('table').getByRole('cell', { name: 'page not found' }).first()
    ).toBeVisible();
  });

  test.afterAll(async () => {
    runDrushCommand('pmu du_logging -y');
  })
});