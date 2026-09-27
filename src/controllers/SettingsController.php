<?php
namespace verbb\autologin\controllers;

use verbb\base\controllers\SettingsController as BaseSettingsController;

class SettingsController extends BaseSettingsController
{
    // Protected Methods
    // =========================================================================

    protected function prepareSubmittedSettings(array $settings): array
    {
        $settings['ipWhitelist'] = $this->_normalizeIpWhitelist($settings['ipWhitelist'] ?? '');
        $settings['basicAuth'] = $this->_normalizeMappedLines($settings['basicAuth'] ?? '');
        $settings['urlKeys'] = $this->_normalizeMappedLines($settings['urlKeys'] ?? '');

        return $settings;
    }


    // Private Methods
    // =========================================================================

    private function _normalizeIpWhitelist(string $value): array
    {
        $ipWhitelist = [];

        foreach (preg_split('/\R/', $value) ?: [] as $line) {
            [$username, $ipsValue] = $this->_splitMappingLine($line);

            if (!$username) {
                continue;
            }

            $ips = preg_split('/,/', $ipsValue) ?: [];
            $ips = array_values(array_filter(array_map('trim', $ips)));

            if ($ips) {
                $ipWhitelist[$username] = $ips;
            }
        }

        return $ipWhitelist;
    }

    private function _normalizeMappedLines(string $value): array
    {
        $mappedLines = [];

        foreach (preg_split('/\R/', $value) ?: [] as $line) {
            [$username, $mappedValue] = $this->_splitMappingLine($line);

            if ($username && $mappedValue) {
                $mappedLines[$username] = $mappedValue;
            }
        }

        return $mappedLines;
    }

    private function _splitMappingLine(string $line): array
    {
        $line = trim($line);

        if (!$line) {
            return ['', ''];
        }

        foreach (['=>', ':', '='] as $separator) {
            if (str_contains($line, $separator)) {
                $parts = explode($separator, $line, 2);

                return [trim($parts[0]), trim($parts[1])];
            }
        }

        return [$line, ''];
    }
}
