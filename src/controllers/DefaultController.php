<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP, basic auth username or URL keys
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\autologin\controllers;

use craft\helpers\UrlHelper;
use superbig\autologin\Autologin;

use Craft;
use craft\web\Controller;

/**
 * @author    Superbig
 * @package   Autologin
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [ 'index' ];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex ()
    {
        $key = Craft::$app->request->getRequiredParam('key');
        $cp  = Craft::$app->request->getParam('cp');

        $success = Autologin::$plugin->autologinService->loginByKey($key, $cp);

        if ( !$success ) {
            return $this->redirect('/');
        }
        elseif ( $success && !$cp ) {

        }
        elseif ( $success && $cp ) {
            return $this->redirect(UrlHelper::cpUrl('/'));
        }
    }
}
