<?php
namespace verbb\autologin\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
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
     *
     * @var array
     */
    public $basicAuth = [];

    /**
     * A list of Craft usernames mapped to url keys
     *
     * @var array
     */
    public $urlKeys = [];

    /**
     * Redirect after logging in automatically
     *
     * @var string
     */
    public $redirectUrl = '';


    // Public Methods
    // =========================================================================

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = ['enabled', 'boolean'];
        $rules[] = ['redirectUrl', 'string'];

        $rules[] = ['enabled', 'default', 'value' => true];
        $rules[] = ['ipWhitelist', 'default', 'value' => []];
        $rules[] = ['basicAuth', 'default', 'value' => []];
        $rules[] = ['urlKeys', 'default', 'value' => []];

        return $rules;
    }
}
