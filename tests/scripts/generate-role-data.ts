#!/usr/bin/env ts-node

/**
 * Generate test-roles.ts from Drupal role data
 *
 * Usage:
 *   ts-node tests/support/generate-role-data.ts
 *   or add to package.json: "generate-roles": "ts-node tests/support/generate-role-data.ts"
 */

import {execSync} from 'child_process';
import {writeFileSync} from 'fs';
import {join, dirname} from 'path';
import {fileURLToPath} from 'url';

const OUTPUT_FILE = join(dirname(__filename), '../data/test-roles.ts');

try {
  // Execute drush command
  const output = execSync('ddev drush role:list', {encoding: 'utf-8'});

  // Extract roles (lines with no indentation)
  const roles = output
    .split('\n')
    .filter(line => /^[a-z]/.test(line))
    .map(line => line.replace(/:$/, '').trim())
    .filter(Boolean);

  if (roles.length === 0) {
    throw new Error('No roles found from drush command');
  }

  // Always remove the 'anonymous' role.
  roles.splice(roles.indexOf('anonymous'), 1);

  // Generate TypeScript file content
  const roleEntries = roles.map(role =>
    `  ${role}: {\n    name: '${role}',\n    test_user: 'qa_${role}',\n  },`
  ).join('\n');

  const fileContent = `/**
 * Test role definitions for Playwright tests
 * 
 * Auto-generated from Drupal via: npm run generate-roles
 * DO NOT EDIT MANUALLY - your changes will be overwritten
 */

export interface Role {
  name: string;
  test_user: string;
  // Add more properties as needed:
  // permissions?: string[];
  // displayName?: string;
  // description?: string;
}

export const roles = {
${roleEntries}
} as const satisfies Record<string, Role>;
// 'as const' = make this immutable and preserve exact string values as types
// 'satisfies Record<string, Role>' = verify each role has 'name' and 'test_user' properties

export type RoleName = keyof typeof roles;
// 'typeof roles' = get the type of the roles object
// 'keyof' = extract all keys as a union type: 'anonymous' | 'authenticated' | 'user_admin' | ...
`;

  writeFileSync(OUTPUT_FILE, fileContent, 'utf-8');

  console.log('✅ Roles written to:', OUTPUT_FILE);
  console.log('Roles:', roles.join(', '));
  console.log(`\nTotal: ${roles.length} roles`);

} catch (error) {
  console.error('❌ Error generating roles:', error);
  process.exit(1);
}