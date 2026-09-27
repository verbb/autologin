<?php
namespace verbb\autologin\controllers;

use verbb\autologin\Autologin;
use verbb\autologin\models\Settings;

use craft\web\Controller;

use yii\web\Response;

class PluginController extends Controller
{
    // Public Methods
    // =========================================================================

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requireCpRequest();
        $this->requireAdmin($action->id !== 'settings');

        return true;
    }

    public function actionSettings(): Response
    {
        /* @var Settings $settings */
        $settings = Autologin::$plugin->getSettings();

        return $this->renderTemplate('autologin/settings', [
            'settings' => $settings,
        ]);
    }

}
