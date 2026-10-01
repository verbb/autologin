<?php
namespace verbb\autologin\services;

use verbb\autologin\Autologin;
use verbb\autologin\models\Settings;

use Craft;
use craft\base\Component;
use craft\elements\User;
use craft\helpers\UrlHelper;

class Service extends Component
{
    // Constants
    // =========================================================================

    public const REDIRECT_MODE_SITE = 'site';
    public const REDIRECT_MODE_CP = 'cp';

    private const LOGIN_METHOD_BASIC_AUTH = 'basicAuth';
    private const LOGIN_METHOD_IP_WHITELIST = 'ipWhitelist';
    private const LOGIN_METHOD_URL_KEY = 'urlKeys';
    private const MINIMUM_URL_KEY_LENGTH = 3;


    // Properties
    // =========================================================================

    protected ?Settings $_settings = null;


    // Public Methods
    // =========================================================================

    public function loginByKey($key = null, $cp = false): bool
    {
        $settings = $this->_getSettings();
        $redirectMode = $cp ? self::REDIRECT_MODE_CP : self::REDIRECT_MODE_SITE;

        if (!$settings->enabled) {
            return false;
        }

        if (!Craft::$app->getUser()->getIsGuest()) {
            return $this->_afterLogin($redirectMode);
        }

        $key = $this->_normalizeUrlKey($key);

        if ($key === null) {
            return false;
        }

        foreach ($settings->urlKeys as $craftUsername => $matchKey) {
            if (!is_string($matchKey) || $this->_normalizeUrlKey($matchKey) === null) {
                continue;
            }

            if (hash_equals($matchKey, $key)) {
                return $this->_loginByUsername($craftUsername, self::LOGIN_METHOD_URL_KEY, $redirectMode);
            }
        }

        return false;
    }

    public function shouldLogin(): bool
    {
        $settings = $this->_getSettings();
        $request = Craft::$app->getRequest();

        if (!$settings->enabled || $request->getIsConsoleRequest() || !Craft::$app->getUser()->getIsGuest()) {
            return false;
        }

        $currentIp = $request->getRemoteIP();
        $currentAuthUser = $_SERVER['REMOTE_USER'] ?? null;

        if (is_string($currentAuthUser) && $currentAuthUser !== '' && !empty($settings->basicAuth)) {
            foreach ($settings->basicAuth as $craftUsername => $authUsername) {
                if ($currentAuthUser === $authUsername) {
                    return $this->_loginByUsername($craftUsername, self::LOGIN_METHOD_BASIC_AUTH);
                }
            }
        }

        if ($currentIp && !empty($settings->ipWhitelist)) {
            if ($craftUsername = $this->_matchIp($currentIp)) {
                return $this->_loginByUsername($craftUsername, self::LOGIN_METHOD_IP_WHITELIST);
            }
        }

        return false;
    }


    // Private Methods
    // =========================================================================

    private function _normalizeUrlKey(mixed $key): ?string
    {
        if (is_int($key) || is_float($key)) {
            $key = (string)$key;
        }

        if (!is_string($key)) {
            return null;
        }

        // Include Unicode separators so multibyte whitespace cannot satisfy the minimum length.
        $key = preg_replace('/^[\s\p{Z}\x00]+|[\s\p{Z}\x00]+$/u', '', $key);

        if ($key === null || mb_strlen($key, 'UTF-8') < self::MINIMUM_URL_KEY_LENGTH) {
            return null;
        }

        return $key;
    }

    private function _matchIp($currentIp): bool|int|string
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

    private function _loginByUsername(string $username, string $loginMethod, string $redirectMode = self::REDIRECT_MODE_SITE): bool
    {
        if (!$this->_getSettings()->enabled) {
            return false;
        }

        $craftUser = Craft::$app->getUsers()->getUserByUsernameOrEmail($username);

        if (!$craftUser || !$this->_isUserEligible($craftUser, $loginMethod)) {
            return false;
        }

        $success = Craft::$app->getUser()->loginByUserId($craftUser->id);

        if ($success) {
            $this->_afterLogin($redirectMode);

            return true;
        }

        return false;
    }

    private function _isUserEligible(User $user, string $loginMethod): bool
    {
        if ($user->getStatus() !== User::STATUS_ACTIVE || $user->locked) {
            return false;
        }

        if (in_array($loginMethod, $this->_getSettings()->mfaAssuredMethods, true)) {
            return true;
        }

        return !Craft::$app->getAuth()->hasActiveMethod($user);
    }

    private function _afterLogin($redirectMode): bool
    {
        $settings = $this->_getSettings();

        if ($redirectMode === self::REDIRECT_MODE_SITE && ($url = $settings->redirectUrl)) {
            Craft::$app->getResponse()->redirect($url);

            Craft::$app->end();

            return true;
        }

        if ($redirectMode === self::REDIRECT_MODE_CP) {
            Craft::$app->getResponse()->redirect(UrlHelper::cpUrl());

            Craft::$app->end();

            return true;
        }

        return false;
    }

    private function _getSettings(): Settings
    {
        if (!$this->_settings) {
            $this->_settings = Autologin::$plugin->getSettings();
        }

        return $this->_settings;
    }
}
