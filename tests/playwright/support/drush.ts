import { execSync } from 'child_process';

/**
 * Run a drush command via ddev.
 *
 * @param command - The drush command to run (without 'drush' prefix)
 * @returns The output of the command as a string
 *
 * @example
 * // Enable a module
 * runDrushCommand('en a_module -y');
 *
 * @example
 * // Clear cache
 * runDrushCommand('cr');
 *
 * @example
 * // Get config value
 * const output = runDrushCommand('config:get system.site name');
 */
export function runDrushCommand(command: string): string {
  const fullCommand = `ddev drush ${command}`;

  try {
    const output = execSync(fullCommand, { encoding: 'utf8' }).toString();
    return output.trim();
  } catch (error) {
    console.error(`Error running drush command: ${fullCommand}`);
    throw error;
  }
}