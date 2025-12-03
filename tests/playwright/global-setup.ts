import { rmSync } from 'node:fs';
import { getDataPath } from '@du_pw/support/files';

async function globalSetup() {

  // Clear any existing authentication cookies.
  // Otherwise, the tests will fail with stale session cookies.
  const cookiesDir = getDataPath('cookies');
  try {
    rmSync(cookiesDir, { recursive: true, force: true });
    console.log('Cleared authentication cookies');
  } catch (error) {
    console.log('No existing auth cookies to clear');
  }
}

export default globalSetup;