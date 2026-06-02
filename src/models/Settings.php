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

    public function getIpWhitelistText(): string
    {
        $lines = [];

        foreach ($this->ipWhitelist as $username => $ips) {
            $lines[] = $username . ': ' . implode(', ', (array)$ips);
        }

        return implode(PHP_EOL, $lines);
    }

    public function getBasicAuthText(): string
    {
        $lines = [];

        foreach ($this->basicAuth as $username => $authUsername) {
            $lines[] = $username . ': ' . $authUsername;
        }

        return implode(PHP_EOL, $lines);
    }

    public function getUrlKeyText(): string
    {
        $lines = [];

        foreach ($this->urlKeys as $username => $key) {
            $lines[] = $username . ': ' . $key;
        }

        return implode(PHP_EOL, $lines);
    }
}
