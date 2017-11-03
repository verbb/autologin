<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP or a key
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\autologin\services;

use craft\helpers\UrlHelper;
use superbig\autologin\Autologin;

use Craft;
use craft\base\Component;

/**
 * @author    Superbig
 * @package   Autologin
 * @since     1.0.0
 */
class AutologinService extends Component
{
    const REDIRECT_MODE_SITE = 'site';
    const REDIRECT_MODE_CP   = 'cp';

    protected $_settings;

    // Public Methods
    // =========================================================================

    public function loginByKey ($key = null, $cp = false)
    {
        $settings     = $this->_getSettings();
        $redirectMode = $cp ? self::REDIRECT_MODE_CP : self::REDIRECT_MODE_SITE;

        if ( !Craft::$app->user->getIsGuest() ) {
            return $this->_afterLogin($redirectMode);
        }

        if ( $key ) {
            foreach ($settings->urlKeys as $craftUsername => $matchKey) {
                if ( trim($key) === $matchKey ) {
                    return $this->_loginByUsername($craftUsername, $redirectMode);
                }
            }
        }
    }

    public function shouldLogin ()
    {
        $settings = $this->_getSettings();

        if ( !$settings->enabled ) {
            return false;
        }

        if ( !Craft::$app->user->getIsGuest() ) {
            return false;
        }

        $currentIp       = Craft::$app->request->getUserIP();
        $currentAuthUser = Craft::$app->request->getAuthUser();

        if ( $currentAuthUser && !empty($settings->basicAuth) ) {
            foreach ($settings->basicAuth as $craftUsername => $authUsername) {
                if ( $currentAuthUser === $authUsername ) {
                    return $this->_loginByUsername($settings->basicAuth[ $currentAuthUser ]);
                }
            }
        }

        if ( $currentIp && !empty($settings->ipWhitelist) ) {
            if ( $craftUsername = $this->_matchIp($currentIp) ) {
                return $this->_loginByUsername($craftUsername);
            }
        }
    }

    /**
     * Match IP against whitelist
     *
     * @param $currentIp
     *
     * @return int|string
     */
    private function _matchIp ($currentIp)
    {
        $settings = $this->_getSettings();

        foreach ($settings->ipWhitelist as $craftUsername => $ips) {
            $filteredIps = array_filter($ips, function ($ip) { return filter_var($ip, FILTER_VALIDATE_IP); });

            if ( in_array($currentIp, $filteredIps) ) {
                return $craftUsername;
            }
        }
    }

    /**
     * Login by username or email
     *
     * @param string $username
     * @param string $redirectMode
     */
    private function _loginByUsername ($username, $redirectMode = self::REDIRECT_MODE_SITE)
    {
        $craftUser = Craft::$app->users->getUserByUsernameOrEmail($username);

        if ( $craftUser ) {
            $success = Craft::$app->user->loginByUserId($craftUser->id);

            if ( $success ) {
                return $this->_afterLogin($redirectMode);
            }
        }
    }

    /**
     * @param $redirectMode
     */
    private function _afterLogin ($redirectMode)
    {
        $settings = $this->_getSettings();

        if ( $redirectMode === self::REDIRECT_MODE_SITE && $url = $settings->redirectUrl && !empty($url) ) {
            Craft::$app->response->redirect($url);

            return Craft::$app->end();
        }

        if ( $redirectMode === self::REDIRECT_MODE_CP ) {
            Craft::$app->response->redirect(UrlHelper::cpUrl('/'));

            return Craft::$app->end();
        }
    }

    /**
     * @return \superbig\autologin\models\Settings
     */
    private function _getSettings ()
    {
        if ( !$this->_settings ) {
            $this->_settings = Autologin::$plugin->getSettings();
        }

        return $this->_settings;
    }
}
