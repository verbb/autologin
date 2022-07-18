<?php
namespace verbb\autologin\controllers;

use verbb\autologin\Autologin;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;

use yii\web\BadRequestHttpException;
use yii\web\Response;

class BaseController extends Controller
{
    // Properties
    // =========================================================================

    protected $allowAnonymous = ['index'];


    // Public Methods
    // =========================================================================

    public function actionIndex(): ?Response
    {
        $key = Craft::$app->getRequest()->getRequiredParam('key');
        $cp = Craft::$app->getRequest()->getParam('cp');

        $success = Autologin::$plugin->getService()->loginByKey($key, $cp);

        if (!$success) {
            return $this->redirect('/');
        }

        if ($success && $cp) {
            return $this->redirect(UrlHelper::cpUrl('/'));
        }

        return null;
    }
}
