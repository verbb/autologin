<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP or a key
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

/**
 * Autologin config.php
 *
 * This file exists only as a template for the Autologin settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'autologin.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [

    // Enable the Autologin plugin
    'enabled'           => true,

    // A list of usernames mapped to IPs
    'ipWhitelist'       => [],

    // A list of Craft usernames mapped to basic auth usernames
    'basicAuth'         => [],

    // Set this to a username if you want to automatically login on localhost
    'localhostUsername' => '',

    // Redirect after logging in automatically
    'redirectUrl'       => '',

];
