import { test, expect } from '@du_pw/test';
import { faker } from '@faker-js/faker';

import { getRole , logInViaForm, logOutViaUi} from "@du_pw/support/users";

test.describe('@du_logging - Log filtering tests', () => {
  const site_admin = getRole('site_admin');

  test.beforeEach(async ({ page }) => {

  });

  test('Filter logs, then disable filtering', async ({ page, context }) => {
    await logInViaForm(page, context, site_admin);

  });
});