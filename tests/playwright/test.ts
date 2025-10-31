import { test as base } from '@playwright/test';

export const test = base.extend({
  context: async ({ context }, use) => {
    // Block the termly cookie banner script for all pages in this context.
    await context.route('**/*termly.io/**', route => route.abort());

    await use(context);
  },
});

export { expect } from '@playwright/test';
