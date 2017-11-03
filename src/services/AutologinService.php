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
    // Public Methods
    // =========================================================================

    public function shouldLogin ()
    {
        $settings = Autologin::$plugin->getSettings();

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
                    $this->_loginByUsername($settings->basicAuth[ $currentAuthUser ]);
                }
            }
        }

        if ( $currentIp && !empty($settings->ipWhitelist) ) {
            if ( $craftUsername = $this->_matchIp($currentIp) ) {
                $this->_loginByUsername($craftUsername);
            }
        }
    }

    private function _matchIp ($currentIp)
    {
        $settings = Autologin::$plugin->getSettings();

        foreach ($settings->ipWhitelist as $craftUsername => $ips) {
            $filteredIps = array_filter($ips, function ($ip) { return filter_var($ip, FILTER_VALIDATE_IP); });

            if ( in_array($currentIp, $filteredIps) ) {
                return $craftUsername;
            }
        }
    }

    private function _loginByUsername ($username)
    {
        $craftUser = Craft::$app->users->getUserByUsernameOrEmail($username);

        if ( $craftUser ) {
            $success = Craft::$app->user->loginByUserId($craftUser->id);

            if ( $success ) {
                $this->_afterLogin();

                return $success;
            }
        }
    }

    private function _afterLogin ()
    {
        $settings = Autologin::$plugin->getSettings();

        if ( $url = $settings->redirectUrl && !empty($url) ) {
            Craft::$app->response->redirect($url);
            Craft::$app->end();
        }
    }
}
