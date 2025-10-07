<?php

/**
 * @file
 * Enables modules and site configuration for the DU Core profile.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function ducore_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // We'll add custom alterations to the site configuration form here.
}


/**
* Give users a default role if they are on
* the system or campus support lists.
*/
function ducore_user_presave(UserInterface $user) {
  // IMPRORTANT! Because of change to using CammelCase in usernames at DU wasn't 
  // retoactively applied, we are evaluating the match in lowercase.
  $support_eas = ['kevin.reynen','kent.hogue','joshua.mcgehee','tj.sheu', 'alexander.finnarn'];
  $support_ur =  array('mac.whitney','nathan.boorom','staci.striegnitz','sherry.liang','anastasia.vylegzhanina','james.e.thomas','derek.vonschulz');
  $support_ba = array('rosi.hull');
  // @TODO - These arrays should be YML files or API endpoint that can be 
  // easily editted outside the PHP
  // Check to see if this user is on the list of campus or system support users
  if (in_array(strtolower($user->getAccountName()), $support_eas)) {
    $user->addRole('administrator');
  }
  if (in_array(strtolower($user->getAccountName()), $support_ba)) {
    $user->addRole('user_admin');
  }
  if (in_array(strtolower($user->getAccountName()), $support_ur)) {
    //check to see if the Pantheon environment is live
    if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
      if ($_ENV['PANTHEON_ENVIRONMENT'] != 'live') {
        $user->addRole('administrator');
      } else {
        $user->addRole('site_admin');
      }
    }
  }
  // @TODO - Remove user if no longer in original array 
}

/**
 * Implements hook_google_tag_snippets_alter().
 */
function ducore_google_tag_snippets_alter(array &$snippets) {

  if (!defined('PANTHEON_ENVIRONMENT')){
    // if it is a local DDEV instance, null out the gtm file contents
    $snippets['script'] = ' ';
    unset($snippets['noscript']);
    unset($snippets['data_layer']);
  } else {
    if ($_ENV['PANTHEON_ENVIRONMENT'] == 'test' || $_ENV['PANTHEON_ENVIRONMENT'] == 'live') {
      // do nothing
    } else {
      // if it isn't the test or live, null out the gtm file contents
      $snippets['script'] = ' ';
      unset($snippets['noscript']);
      unset($snippets['data_layer']);
    }
  }  
}