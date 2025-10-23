import { test, expect } from '@playwright/test';
import { faker } from '@faker-js/faker';

import { getRole , logInViaForm, logOutViaUi} from "../../support/users";
import {getAssetPath} from "../../support/files";

test.describe('Basic Page Tests', () => {
  const site_admin = getRole('site_admin');
  const page_title = faker.lorem.words(3);
  const h1_text = faker.lorem.words(3);

  test.skip('Create basic page', async ({ page, context }) => {
    await logInViaForm(page, context, site_admin);
    await page.goto('/node/add/page');

    await page.getByLabel('Title', {exact: true}).fill(page_title);
    await page.getByLabel('Alternative H1').fill(h1_text);

    // Open hero image paragraph.
    await page.getByRole('button', { name: 'Add Hero Media' }).click();
    // Open hero image file input.
    await page.getByRole('button', { name: 'Hero Image' }).click();
    // Open media library.
    await page.locator('.field--name-field-hero-media-header')
      .getByRole('button', { name: 'Select files' })
      .click();

    const iframe = page.frameLocator('iframe[name="entity_browser_iframe_browse_files_modal"]');
    // The button has onclick="event.preventDefault()" so just clicking should work
    await iframe.locator('a.button:has-text("Select file")').click();

    //.setInputFiles('tests/e2e/fixtures/test.jpg');


    await page.getByRole('button', { name: 'Save' }).click();
    await page.waitForURL('/node/*');
    // Expect title to be the h1 on the page.
    await expect(page.getByRole('heading', { name: page_title })).toBeVisible();
    await logOutViaUi(page);
  });
});