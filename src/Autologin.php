<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP or a key
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\autologin;

use superbig\autologin\services\AutologinService as AutologinServiceService;
use superbig\autologin\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class Autologin
 *
 * @author    Superbig
 * @package   Autologin
 * @since     1.0.0
 *
 * @property  AutologinServiceService $autologinService
 * @method   Settings                getSettings();
 */
class Autologin extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Autologin
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init ()
    {
        parent::init();
        self::$plugin = $this;

        $this->autologinService->shouldLogin();

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['autologin'] = 'autologin/default';
            }
        );

        Craft::info(
            Craft::t(
                'autologin',
                '{name} plugin loaded',
                [ 'name' => $this->name ]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel ()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml (): string
    {
        return Craft::$app->view->renderTemplate(
            'autologin/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
