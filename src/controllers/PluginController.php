<?php
namespace verbb\autologin\controllers;

use verbb\autologin\Autologin;
use verbb\autologin\models\Settings;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class PluginController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings(): Response
    {
        /* @var Settings $settings */
        $settings = Autologin::$plugin->getSettings();

        return $this->renderTemplate('autologin/settings', [
            'settings' => $settings,
        ]);
    }

    public function actionSaveSettings(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        /* @var Settings $settings */
        $settings = Autologin::$plugin->getSettings();
        $settingsConfig = $request->getParam('settings', []);
        $settingsConfig['ipWhitelist'] = $this->_normalizeIpWhitelist($settingsConfig['ipWhitelist'] ?? '');
        $settingsConfig['basicAuth'] = $this->_normalizeMappedLines($settingsConfig['basicAuth'] ?? '');
        $settingsConfig['urlKeys'] = $this->_normalizeMappedLines($settingsConfig['urlKeys'] ?? '');

        $settings->setAttributes($settingsConfig, false);

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('autologin', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings,
            ]);

            return null;
        }

        $pluginSettingsSaved = Craft::$app->getPlugins()->savePluginSettings(Autologin::$plugin, $settings->toArray());

        if (!$pluginSettingsSaved) {
            Craft::$app->getSession()->setError(Craft::t('autologin', 'Couldn’t save settings.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'settings' => $settings,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('autologin', 'Settings saved.'));

        return $this->redirect('autologin/settings');
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
