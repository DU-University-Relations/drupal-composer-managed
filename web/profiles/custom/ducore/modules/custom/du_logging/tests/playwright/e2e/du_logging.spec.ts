import { test, expect } from '@du_pw/test';

import { getRole , logIn, createAnonSession} from "@du_pw/support/users";
import {drush} from "@du_pw/support/drush";

test.describe('@du_logging - Log filtering tests', () => {
  const administrator = getRole('administrator');
  const dblog_page = '/admin/reports/dblog';

  test.beforeAll(async () => {
    drush('en du_logging -y');
  });

  test('Filter logs, then disable filtering', async ({ page, context }) => {
    await logIn(page, context, administrator);
    const anonPage = await createAnonSession(context);
    drush('watchdog:delete all -y');

    // Logging should be enabled, so go to two routes to test.
    await anonPage.goto(dblog_page);
    await anonPage.goto('/foo/bar/baz');

    // Admin user should not see messages.
    await page.goto(dblog_page);
    await expect(page.getByText('No log messages available.')).toBeVisible();

    // Disable filtering and see messages again.
    drush('cset du_logging.settings enabled 0 -y');
    drush('watchdog:delete all -y');

    // Have anon user go to two routes to test.
    await anonPage.goto(dblog_page);
    await anonPage.goto('/foo/bar/baz');

    // Admin user should see messages.
    await page.goto(dblog_page);
    await expect(
      page.getByRole('table').getByRole('cell', { name: 'access denied' }).first()
    ).toBeVisible();
    await expect(
      page.getByRole('table').getByRole('cell', { name: 'page not found' }).first()
    ).toBeVisible();
  });

  test.afterAll(async () => {
    drush('pmu du_logging -y');
  })
});