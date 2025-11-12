// ckeditor.helpers.ts

import { Page, Locator } from '@playwright/test';

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
