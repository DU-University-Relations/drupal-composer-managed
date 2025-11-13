import { test, expect } from '@du_pw/test';
import { faker } from '@faker-js/faker';

import {createAnonSession, getRole, logIn} from "@du_pw/support/users";
// import {getAssetPath} from "@du_pw/support/files";
import {runDrushCommand} from "@du_pw/support/drush";

test.describe('@smoke - Basic Page Tests', () => {
  const site_admin = getRole('site_admin');
  const page_title = faker.lorem.words(3);
  const h1_text = faker.lorem.words(3);
  const body_text = faker.lorem.paragraphs(1);

  test('Create basic page', async ({ page, context }) => {
    await logIn(page, context, site_admin);
    await page.goto('/node/add/page');

    await page.getByLabel('Title', {exact: true}).fill(page_title);
    await page.getByLabel('Alternative H1').fill(h1_text);

    await page.getByRole('button', { name: 'Add Body Text', exact: true }).click();
    // await fillParagraphField(page, 0, 'field-body-text', body_text);

    // The contenteditable div that appears when CKEditor is active
    const editor = page.locator('.ck-editor__editable[role="textbox"]').first();
    await editor.fill(body_text);

    // // Open hero image paragraph.
    // await page.getByRole('button', { name: 'Add Hero Media' }).click();
    // // Open the hero image file field.
    // await page.getByRole('button', { name: 'Hero Image' }).click();
    // // Open media library.
    // await page.locator('.field--name-field-hero-media-header')
    //   .getByRole('button', { name: 'Select files' })
    //   .click();
    // const iframe = page.frameLocator('iframe[name="entity_browser_iframe_browse_files_modal"]');
    // // The button has onclick="event.preventDefault()" so just clicking should work.
    // await iframe.locator('a.button:has-text("Select file")').click();
    // //.setInputFiles('tests/e2e/fixtures/test.jpg');

    await page.getByRole('button', { name: 'Save' }).click();
    // await page.waitForURL('/node/*');

    // End up on admin/content where we can test the page title, which is different from the H1 text.
    await page.getByRole('link', { name: page_title, exact: true }).click({ force: true });
    // await page.waitForURL('/node/*');

    // Expect alternate H1 to be visible.
    await expect(page.getByRole('heading', { name: h1_text })).toBeVisible();

    // Expect body text to be what was entered in the field.
    await expect(page.getByText(body_text)).toBeVisible();

    // Test for anon user.
    const anonPage = await createAnonSession(context);
    await anonPage.goto(page.url());
    await expect(anonPage.getByRole('heading', { name: h1_text })).toBeVisible();
    await expect(anonPage.getByText(body_text)).toBeVisible();
  });

  test.afterAll(async () => {
    // Delete test content.
    runDrushCommand(`du:delete-content --title="${page_title}"`);
  });
});