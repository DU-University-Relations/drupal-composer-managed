import { execSync } from 'child_process';

/**
 * Run a drush command via ddev.
 *
 * @param command - The drush command to run (without 'drush' prefix)
 * @returns The output of the command as a string
 *
 * @example
 * // Enable a module
 * drush('en a_module -y');
 *
 * @example
 * // Clear cache
 * drush('cr');
 *
 * @example
 * // Get config value
 * const output = drush('config:get system.site name');
 */
export function drush(command: string): string {
  const fullCommand = `ddev drush ${command}`;

  try {
    const output = execSync(fullCommand, { encoding: 'utf8' }).toString();
    return output.trim();
  } catch (error) {
    console.error(`Error running drush command: ${fullCommand}\nError details:`, error && (error.message || error));
    throw error;
  }
}