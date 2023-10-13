<?php
namespace verbb\autologin\base;

use verbb\autologin\Autologin;
use verbb\autologin\services\Service;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

trait PluginTrait
{
    // Properties
    // =========================================================================

    public static ?Autologin $plugin = null;


    // Traits
    // =========================================================================

    use LogTrait;


    // Static Methods
    // =========================================================================

    public static function config(): array
    {
        Plugin::bootstrapPlugin('autologin');

        return [
            'components' => [
                'service' => Service::class,
            ],
        ];
    }
    

    // Public Methods
    // =========================================================================

    public function getService(): Service
    {
        return $this->get('service');
    }

}