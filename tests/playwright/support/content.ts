// These are a work-in-progress for helping with content creation, but not used on any test yet.
import { Page, Locator, expect } from '@playwright/test';

/**
 * Get the CKEditor editable area for a field
 */
export function getCKEditorByLabel(page: Page, label: string): Locator {
  // The contenteditable div that appears when CKEditor is active
  return page.locator('.ck-editor__editable[role="textbox"]').first();
}

/**
 * Fast-fill a paragraph CKEditor field using the hidden textarea
 */
export async function fillParagraphField(
  page: Page,
  paragraphIndex: number,
  fieldName: string,
  content: string
): Promise<void> {
  const selector = `[data-drupal-selector="edit-field-page-overview-${paragraphIndex}-subform-${fieldName}-0-value"]`;
  await page.locator(selector).fill(content, { force: true });
}

export async function verifyCKEditorPluginsVisible(
  page: Page,
  fieldSelector: string,
  expectedPlugins: string[]
): Promise<void> {
  const fieldWrapper = page.locator(`[data-drupal-selector="${fieldSelector}"]`);
  const toolbar = fieldWrapper.getByRole('toolbar', { name: 'Editor toolbar' });

  await expect(toolbar).toBeVisible();

  for (const plugin of expectedPlugins) {
    await expect(
      toolbar.getByRole('button', { name: plugin }),
      `Expected plugin "${plugin}" in field "${fieldSelector}"`
    ).toBeVisible();
  }
}
