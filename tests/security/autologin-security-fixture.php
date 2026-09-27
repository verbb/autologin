<?php

use craft\elements\User;
use yii\base\Action;
use yii\base\Module;
use yii\web\HttpException;
use yii\web\Response;

$vendorPath = getenv('AUTOLOGIN_CRAFT_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

if (!is_file($vendorPath . '/autoload.php')) {
    throw new RuntimeException('Set AUTOLOGIN_CRAFT_VENDOR to a Craft 5 vendor directory.');
}

require $vendorPath . '/autoload.php';
require $vendorPath . '/yiisoft/yii2/Yii.php';
require $vendorPath . '/craftcms/cms/src/Craft.php';
$baseSettingsController = getenv('VERBB_BASE_SETTINGS_CONTROLLER') ?: $vendorPath . '/verbb/base/src/controllers/SettingsController.php';
require $baseSettingsController;
require dirname(__DIR__, 2) . '/src/models/Settings.php';
require dirname(__DIR__, 2) . '/src/services/Service.php';
require dirname(__DIR__, 2) . '/src/controllers/PluginController.php';
require dirname(__DIR__, 2) . '/src/controllers/SettingsController.php';

final class SecurityFixtureRequest extends craft\web\Request
{
    public bool $console = false;
    public bool $cp = false;
    public bool $csrfValid = true;
    public bool $post = true;
    public ?string $authUser = null;
    public ?string $userIp = null;

    public function init(): void
    {
    }

    public function getIsConsoleRequest(): bool
    {
        return $this->console;
    }

    public function getIsCpRequest(): bool
    {
        return $this->cp;
    }

    public function getIsPost(): bool
    {
        return $this->post;
    }

    public function getAuthUser(): ?string
    {
        return $this->authUser;
    }

    public function getUserIP(int $filterOptions = 0): ?string
    {
        return $this->userIp;
    }

    public function getIsLivePreview(): bool
    {
        return false;
    }

    public function hasValidSiteToken(): bool
    {
        return false;
    }

    public function validateCsrfToken($clientSuppliedToken = null): bool
    {
        return $this->csrfValid;
    }
}

final class SecurityFixtureSession
{
    public bool $admin = false;
    public bool $guest = true;
    public int $loginAttempts = 0;
    public ?int $identityId = null;
    public array $permissions = [];

    public function checkPermission(string $permission): bool
    {
        return $this->admin || in_array($permission, $this->permissions, true);
    }

    public function getIsAdmin(): bool
    {
        return $this->admin;
    }

    public function getIsGuest(): bool
    {
        return $this->guest;
    }

    public function loginByUserId(int $userId): bool
    {
        $this->loginAttempts++;
        $this->identityId = $userId;
        $this->guest = false;

        return true;
    }

    public function loginRequired(): void
    {
        throw new yii\web\ForbiddenHttpException('Fixture guest denied.');
    }
}

final class SecurityFixtureUser extends User
{
    public function init(): void
    {
    }
}

final class SecurityFixtureUsers
{
    public function __construct(public ?SecurityFixtureUser $user)
    {
    }

    public function getUserByUsernameOrEmail(string $usernameOrEmail): ?SecurityFixtureUser
    {
        return $this->user;
    }
}

final class SecurityFixtureApp extends yii\base\Component
{
    public bool $allowAdminChanges = true;
    public string $charset = 'UTF-8';
    public string $language = 'en';
    public string $sourceLanguage = 'en';
    public SecurityFixtureRequest $request;
    public Response $response;
    public SecurityFixtureSession $session;
    public SecurityFixtureUsers $users;

    public function getConfig(): object
    {
        return new class($this->allowAdminChanges) {
            public function __construct(private bool $allowAdminChanges)
            {
            }

            public function getGeneral(): object
            {
                return (object)['allowAdminChanges' => $this->allowAdminChanges];
            }
        };
    }

    public function getErrorHandler(): object
    {
        return (object)['exception' => null];
    }

    public function getI18n(): yii\i18n\I18N
    {
        return new yii\i18n\I18N();
    }

    public function getIsLive(): bool
    {
        return true;
    }

    public function getRequest(): SecurityFixtureRequest
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getUser(): SecurityFixtureSession
    {
        return $this->session;
    }

    public function getUsers(): SecurityFixtureUsers
    {
        return $this->users;
    }
}

function fixtureAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function fixtureApp(?SecurityFixtureUser $user = null): SecurityFixtureApp
{
    $app = new SecurityFixtureApp();
    $app->request = new SecurityFixtureRequest();
    $app->session = new SecurityFixtureSession();
    $app->users = new SecurityFixtureUsers($user);
    Craft::$app = $app;
    $app->response = new Response();

    return $app;
}

function fixtureService(verbb\autologin\models\Settings $settings): verbb\autologin\services\Service
{
    $service = new verbb\autologin\services\Service();
    $reflection = new ReflectionProperty($service, '_settings');
    $reflection->setValue($service, $settings);

    return $service;
}

function fixtureUser(string $state = 'active', bool $admin = false): SecurityFixtureUser
{
    $user = new SecurityFixtureUser();
    $user->id = 42;
    $user->admin = $admin;
    $user->enabled = $state !== 'disabled';
    $user->active = !in_array($state, ['pending', 'inactive'], true);
    $user->pending = $state === 'pending';
    $user->suspended = $state === 'suspended';
    $user->locked = $state === 'locked';

    return $user;
}

function runLoginMethod(string $method, verbb\autologin\models\Settings $settings, SecurityFixtureUser $user): array
{
    $app = fixtureApp($user);
    $service = fixtureService($settings);

    $result = match ($method) {
        'url-key' => $service->loginByKey('fixture-key'),
        'basic-auth' => (function() use ($app, $service) {
            $app->request->authUser = 'fixture-upstream';
            return $service->shouldLogin();
        })(),
        'ip' => (function() use ($app, $service) {
            $app->request->userIp = '192.0.2.10';
            return $service->shouldLogin();
        })(),
    };

    return [$result, $app->session];
}

$settings = new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'fixture-key'],
    'basicAuth' => ['administrator' => 'fixture-upstream'],
    'ipWhitelist' => ['administrator' => ['192.0.2.10']],
]);

$deniedStates = ['disabled', 'suspended', 'pending', 'locked', 'inactive'];

foreach (['url-key', 'basic-auth', 'ip'] as $method) {
    foreach ($deniedStates as $state) {
        [$result, $session] = runLoginMethod($method, $settings, fixtureUser($state));
        fixtureAssert($result === false, "$method must deny a $state account.");
        fixtureAssert($session->loginAttempts === 0, "$method must not attempt a session for a $state account.");
    }
}

$settings->enabled = false;

foreach (['url-key', 'basic-auth', 'ip'] as $method) {
    [$result, $session] = runLoginMethod($method, $settings, fixtureUser());
    fixtureAssert($result === false, "$method must be disabled by the plugin setting.");
    fixtureAssert($session->loginAttempts === 0, "$method must not attempt a session while disabled.");
}

$settings->enabled = true;
[$result, $session] = runLoginMethod('url-key', $settings, fixtureUser(admin: true));
fixtureAssert($result === true, 'An active administrator must still be able to log in with a deliberate URL key.');
fixtureAssert($session->loginAttempts === 1 && $session->identityId === 42, 'The administrator URL key must create the intended session.');

foreach (['basic-auth', 'ip'] as $method) {
    [$result, $session] = runLoginMethod($method, $settings, fixtureUser());
    fixtureAssert($result === true, "An active mapped user must still be able to log in by $method.");
    fixtureAssert($session->loginAttempts === 1 && $session->identityId === 42, "$method must create the intended session.");
}

function dispatchSettingsAction(string $actionId, bool $admin, bool $allowAdminChanges, bool $csrfValid = true, bool $cp = true): string
{
    $app = fixtureApp();
    $app->allowAdminChanges = $allowAdminChanges;
    $app->request->cp = $cp;
    $app->request->csrfValid = $csrfValid;
    $app->session->admin = $admin;
    $app->session->guest = false;
    $app->session->permissions = ['accessCp'];

    $controllerClass = $actionId === 'settings'
        ? verbb\autologin\controllers\PluginController::class
        : verbb\autologin\controllers\SettingsController::class;
    $controller = new $controllerClass('fixture', new Module('fixture'), [
        'request' => $app->request,
        'response' => $app->response,
    ]);

    try {
        return $controller->beforeAction(new Action($actionId, $controller)) ? 'reachable' : 'stopped';
    } catch (HttpException $exception) {
        return (string)$exception->statusCode;
    }
}

fixtureAssert(dispatchSettingsAction('settings', false, true) === '403', 'A lower-privilege CP user must not read URL keys.');
fixtureAssert(dispatchSettingsAction('save-settings', false, true) === '403', 'A lower-privilege CP user must not mutate settings.');
fixtureAssert(dispatchSettingsAction('settings', true, false) === 'reachable', 'A read-only administrator may inspect settings.');
fixtureAssert(dispatchSettingsAction('save-settings', true, true) === 'reachable', 'A writable administrator may submit settings.');
fixtureAssert(dispatchSettingsAction('save-settings', true, false) === '403', 'A read-only administrator must not mutate settings.');
fixtureAssert(dispatchSettingsAction('save-settings', true, true, false) === '400', 'Settings mutation must reject an invalid CSRF token.');
fixtureAssert(dispatchSettingsAction('save-settings', true, true, true, false) === '400', 'Settings mutation must require a control panel request.');

$app = fixtureApp();
$app->allowAdminChanges = true;
$app->request->cp = true;
$app->session->admin = true;
$app->session->guest = false;
$controller = new verbb\autologin\controllers\SettingsController('fixture', new Module('fixture'), [
    'request' => $app->request,
    'response' => $app->response,
]);

$app->request->post = false;

try {
    $controller->actionSaveSettings();
    throw new RuntimeException('Settings mutation must require POST.');
} catch (yii\web\MethodNotAllowedHttpException) {
}

echo "Autologin security fixture passed.\n";
