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
}
