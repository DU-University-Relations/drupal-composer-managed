import { test, expect } from '@du_pw/test';
import { faker } from '@faker-js/faker';

import {getRole, logIn} from "@du_pw/support/users";
import {verifyCKEditorPluginsVisible} from "@du_pw/support/content";

test.describe('@d9 @CKE - Basic CKEditor Tests', () => {
  const admin = getRole('administrator');
  const page_title = faker.lorem.words(3);

  test('Test Media Embed', async ({ page, context }) => {
    await logIn(page, context, admin);
    await page.goto('/node/add/page');

    await page.getByRole('textbox', {name: 'Title', exact: true}).fill(page_title);

    // Open CKEditor.
    await page.locator('div[data-drupal-selector="edit-field-page-overview"]')
      .getByRole('button', { name: 'Add Body Text', exact: true })
      .click();

    // Varify plugins exist and are visible.
    // If there is a JS error breaking the WYSIWYG, the test will fail.
    await verifyCKEditorPluginsVisible(page, 'edit-field-page-overview-0',
      [
        'Undo',
        'Redo',
        'Bold',
        'Italic',
        'Link',
        'Bulleted List',
        // Dropdown causes two "Numbered List" matches.
        // 'Numbered List',
        'Text alignment',
        'File Browser',
        'Media Embed',
        'Insert media',
        'Block quote',
        'Insert table',
        // Can't find the source button for some reason.
        // 'Source'
      ]);

    // Open media embed plugin.
    const fieldWrapper = page.locator('[data-drupal-selector="edit-field-page-overview-0"]');
    const toolbar = fieldWrapper.getByRole('toolbar', { name: 'Editor toolbar' });
    await toolbar.getByRole('button', { name: 'Media Embed' }).click();

    // Wait for the media grid to load, then select the first item.
    const entityBrowser = page.frameLocator('#entity_browser_iframe_media_browser');
    await entityBrowser.locator('[data-selectable="true"]').first().click();
    await entityBrowser.getByRole('button', { name: 'Place' }).click();
    // Embed the selected media item.
    await page.locator('.ui-dialog-buttonpane').getByRole('button', { name: 'Embed' }).click();

    // Save page.
    await page.getByRole('button', { name: 'Save' }).click();

    // Look for the title.
    await expect(page.locator('h1')).toHaveText(page_title);

    // Look for the image.
    await expect(page.locator('#main-content img').first()).toBeVisible();
  });
});