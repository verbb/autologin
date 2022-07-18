<?php
namespace verbb\autologin\services;

use verbb\autologin\Autologin;
use verbb\autologin\models\Settings;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

use yii\base\ExitException;

class Service extends Component
{
    // Constants
    // =========================================================================

    const REDIRECT_MODE_SITE = 'site';
    const REDIRECT_MODE_CP = 'cp';


    // Properties
    // =========================================================================

    protected $_settings;


    // Public Methods
    // =========================================================================

    public function loginByKey($key = null, $cp = false)
    {
        $settings = $this->_getSettings();
        $redirectMode = $cp ? self::REDIRECT_MODE_CP : self::REDIRECT_MODE_SITE;

        if (!Craft::$app->getUser()->getIsGuest()) {
            return $this->_afterLogin($redirectMode);
        }

        if ($key) {
            foreach ($settings->urlKeys as $craftUsername => $matchKey) {
                if (trim($key) === $matchKey) {
                    return $this->_loginByUsername($craftUsername, $redirectMode);
                }
            }
        }

        return false;
    }

    public function shouldLogin()
    {
        $settings = $this->_getSettings();
        $request = Craft::$app->getRequest();

        if (!$settings->enabled || $request->getIsConsoleRequest() || !Craft::$app->getUser()->getIsGuest()) {
            return false;
        }

        $currentIp = $request->getUserIP();
        $currentAuthUser = $request->getAuthUser();

        if ($currentAuthUser && !empty($settings->basicAuth)) {
            foreach ($settings->basicAuth as $craftUsername => $authUsername) {
                if ($currentAuthUser === $authUsername) {
                    return $this->_loginByUsername($settings->basicAuth[$currentAuthUser]);
                }
            }
        }

        if ($currentIp && !empty($settings->ipWhitelist)) {
            if ($craftUsername = $this->_matchIp($currentIp)) {
                return $this->_loginByUsername($craftUsername);
            }
        }

        return false;
    }


    // Private Methods
    // =========================================================================

    private function _matchIp($currentIp)
    {
        $settings = $this->_getSettings();

        foreach ($settings->ipWhitelist as $craftUsername => $ips) {
            $filteredIps = array_filter($ips, function($ip) {
                return filter_var($ip, FILTER_VALIDATE_IP);
            });

            if (in_array($currentIp, $filteredIps)) {
                return $craftUsername;
            }
        }

        return false;
    }

    private function _loginByUsername(string $username, string $redirectMode = self::REDIRECT_MODE_SITE): void
    {
        $craftUser = Craft::$app->getUser()->getUserByUsernameOrEmail($username);

        if ($craftUser) {
            $success = Craft::$app->getUser()->loginByUserId($craftUser->id);

            if ($success) {
                $this->_afterLogin($redirectMode);
            }
        }
    }

    private function _afterLogin($redirectMode): void
    {
        $settings = $this->_getSettings();

        if ($redirectMode === self::REDIRECT_MODE_SITE && ($url = $settings->redirectUrl)) {
            Craft::$app->getResponse()->redirect($url);

            Craft::$app->end();
        }

        if ($redirectMode === self::REDIRECT_MODE_CP) {
            Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('/'));

            Craft::$app->end();
        }
    }

    private function _getSettings(): Settings
    {
        if (!$this->_settings) {
            $this->_settings = Autologin::$plugin->getSettings();
        }

        return $this->_settings;
    }
}
