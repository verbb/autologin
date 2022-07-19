<?php
namespace verbb\autologin\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public bool $enabled = true;
    public array $ipWhitelist = [];
    public array $basicAuth = [];
    public array $urlKeys = [];
    public string $redirectUrl = '';


    // Public Methods
    // =========================================================================

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = ['enabled', 'boolean'];
        $rules[] = ['redirectUrl', 'string'];

        $rules[] = ['enabled', 'default', 'value' => true];
        $rules[] = ['ipWhitelist', 'default', 'value' => []];
        $rules[] = ['basicAuth', 'default', 'value' => []];
        $rules[] = ['urlKeys', 'default', 'value' => []];

        return $rules;
    }
}
