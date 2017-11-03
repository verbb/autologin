<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP or a key
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\autologin\models;

use superbig\autologin\Autologin;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Autologin
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Enable the Autologin plugin
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * A list of Craft usernames mapped to IPs
     *
     * @var array
     */
    public $ipWhitelist = [];

    /**
     * A list of Craft usernames mapped to basic auth usernames
     * @var array
     */
    public $basicAuth = [];

    /**
     * Set this to a username if you want to automatically login on localhost
     *
     * @var string
     */
    public $localhostUsername = '';

    /**
     * Redirect after logging in automatically
     *
     * @var string
     */
    public $redirectUrl = '';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules ()
    {
        return [
            [ 'enabled', 'boolean' ],
            [ 'localhostUsername', 'string' ],
            [ 'redirectUrl', 'string' ],

            [ 'enabled', 'default', 'value' => true ],
            [ 'localhostUsername', 'default', 'value' => '' ],
            [ 'ipWhitelist', 'default', 'value' => [] ],
            [ 'basicAuth', 'default', 'value' => [] ],
        ];
    }
}
