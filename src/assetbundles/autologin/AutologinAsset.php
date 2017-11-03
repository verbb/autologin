<?php
/**
 * Autologin plugin for Craft CMS 3.x
 *
 * Automatically login based on whitelisted IP or a key
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\autologin\assetbundles\Autologin;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Superbig
 * @package   Autologin
 * @since     1.0.0
 */
class AutologinAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@superbig/autologin/assetbundles/autologin/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Autologin.js',
        ];

        $this->css = [
            'css/Autologin.css',
        ];

        parent::init();
    }
}
