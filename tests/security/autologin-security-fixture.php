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
    public ?string $remoteIp = null;
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

    public function getRemoteIP(int $filterOptions = 0): ?string
    {
        return $this->remoteIp;
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
    public bool $hasActiveMfa = false;

    public function init(): void
    {
    }
}

final class SecurityFixtureAuth
{
    public function hasActiveMethod(?User $user = null): bool
    {
        return $user instanceof SecurityFixtureUser && $user->hasActiveMfa;
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

final class SecurityFixtureCache
{
    public array $entries = [];
    public bool $failWrites = false;

    public function get(string $key): mixed
    {
        return $this->entries[$key] ?? false;
    }

    public function set(string $key, mixed $value, int $duration = 0): bool
    {
        if ($this->failWrites) {
            return false;
        }

        $this->entries[$key] = $value;

        return true;
    }
}

final class SecurityFixtureMutex
{
    public array $acquiredNames = [];
    public array $heldNames = [];
    public bool $failAcquires = false;

    public function acquire(string $name, int $timeout = 0): bool
    {
        $this->acquiredNames[] = $name;

        if ($this->failAcquires || isset($this->heldNames[$name])) {
            return false;
        }

        $this->heldNames[$name] = true;

        return true;
    }

    public function release(string $name): bool
    {
        unset($this->heldNames[$name]);

        return true;
    }
}

final class SecurityFixtureApp extends yii\base\Component
{
    public bool $allowAdminChanges = true;
    public string $charset = 'UTF-8';
    public string $language = 'en';
    public string $sourceLanguage = 'en';
    public SecurityFixtureAuth $auth;
    public SecurityFixtureCache $cache;
    public SecurityFixtureMutex $mutex;
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

    public function getAuth(): SecurityFixtureAuth
    {
        return $this->auth;
    }

    public function getCache(): SecurityFixtureCache
    {
        return $this->cache;
    }

    public function getErrorHandler(): object
    {
        return (object)['exception' => null];
    }

    public function getI18n(): yii\i18n\I18N
    {
        return new yii\i18n\I18N([
            'translations' => [
                'autologin' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => dirname(__DIR__, 2) . '/src/translations',
                ],
            ],
        ]);
    }

    public function getIsLive(): bool
    {
        return true;
    }

    public function getMutex(): SecurityFixtureMutex
    {
        return $this->mutex;
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
    $app->auth = new SecurityFixtureAuth();
    $app->cache = new SecurityFixtureCache();
    $app->mutex = new SecurityFixtureMutex();
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

function fixtureUser(string $state = 'active', bool $admin = false, bool $hasActiveMfa = false): SecurityFixtureUser
{
    $user = new SecurityFixtureUser();
    $user->id = 42;
    $user->admin = $admin;
    $user->enabled = $state !== 'disabled';
    $user->active = !in_array($state, ['pending', 'inactive'], true);
    $user->pending = $state === 'pending';
    $user->suspended = $state === 'suspended';
    $user->locked = $state === 'locked';
    $user->hasActiveMfa = $hasActiveMfa;

    return $user;
}

function runLoginMethod(string $method, verbb\autologin\models\Settings $settings, SecurityFixtureUser $user, bool $trustedSource = true): array
{
    $app = fixtureApp($user);
    $service = fixtureService($settings);
    $hadRemoteUser = array_key_exists('REMOTE_USER', $_SERVER);
    $previousRemoteUser = $_SERVER['REMOTE_USER'] ?? null;
    $hadPhpAuthUser = array_key_exists('PHP_AUTH_USER', $_SERVER);
    $previousPhpAuthUser = $_SERVER['PHP_AUTH_USER'] ?? null;

    unset($_SERVER['REMOTE_USER'], $_SERVER['PHP_AUTH_USER']);

    try {
        $result = match ($method) {
            'url-key' => $service->loginByKey('fixture-key'),
            'basic-auth' => (function() use ($app, $service, $trustedSource) {
                if ($trustedSource) {
                    $_SERVER['REMOTE_USER'] = 'fixture-upstream';
                } else {
                    $app->request->authUser = 'fixture-upstream';
                    $_SERVER['PHP_AUTH_USER'] = 'fixture-upstream';
                }

                return $service->shouldLogin();
            })(),
            'ip' => (function() use ($app, $service, $trustedSource) {
                $app->request->remoteIp = $trustedSource ? '192.0.2.10' : '198.51.100.20';
                $app->request->userIp = '192.0.2.10';

                return $service->shouldLogin();
            })(),
        };
    } finally {
        if ($hadRemoteUser) {
            $_SERVER['REMOTE_USER'] = $previousRemoteUser;
        } else {
            unset($_SERVER['REMOTE_USER']);
        }

        if ($hadPhpAuthUser) {
            $_SERVER['PHP_AUTH_USER'] = $previousPhpAuthUser;
        } else {
            unset($_SERVER['PHP_AUTH_USER']);
        }
    }

    return [$result, $app->session];
}

function runUrlKeyCase(mixed $key, array $urlKeys, SecurityFixtureUser $user): array
{
    $app = fixtureApp($user);
    $service = fixtureService(new verbb\autologin\models\Settings([
        'enabled' => true,
        'urlKeys' => $urlKeys,
    ]));

    return [$service->loginByKey($key), $app->session];
}

function expectUrlKeyRateLimit(callable $attempt, SecurityFixtureApp $app, string $message): void
{
    try {
        $attempt();
        throw new RuntimeException($message);
    } catch (yii\web\TooManyRequestsHttpException) {
        $retryAfter = (int)$app->response->getHeaders()->get('Retry-After');
        fixtureAssert($retryAfter >= 1 && $retryAfter <= 300, 'Rate-limited responses must provide a bounded Retry-After header.');
    }
}

$settings = new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'fixture-key'],
    'basicAuth' => ['administrator' => 'fixture-upstream'],
    'ipWhitelist' => ['administrator' => ['192.0.2.10']],
]);

$configuredSettings = new verbb\autologin\models\Settings([
    'mfaAssuredMethods' => ['basicAuth'],
]);
fixtureAssert($configuredSettings->mfaAssuredMethods === ['basicAuth'], 'The MFA assurance policy must load from PHP configuration.');
fixtureAssert(!array_key_exists('mfaAssuredMethods', $configuredSettings->toArray()), 'The configuration-only MFA assurance policy must not be serialized into project config.');

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

$blankKeyUsers = [
    'space' => [' ', fixtureUser(admin: true)],
    'tab and newline' => ["\t\n", fixtureUser()],
];

foreach ($blankKeyUsers as $label => [$key, $user]) {
    [$result, $session] = runUrlKeyCase($key, ['administrator' => ''], $user);
    fixtureAssert($result === false, "A $label request key must not match a blank configured key.");
    fixtureAssert($session->loginAttempts === 0, "A $label request key must not create a session.");
}

$unicodeWhitespace = "\u{00A0}\u{2003}\u{3000}";
[$result, $session] = runUrlKeyCase($unicodeWhitespace, ['administrator' => $unicodeWhitespace], fixtureUser(admin: true));
fixtureAssert($result === false, 'A Unicode whitespace request key must not match a whitespace-only configured key.');
fixtureAssert($session->loginAttempts === 0, 'A Unicode whitespace key must not create a session.');

foreach (['a', 'ab', "\u{1F511}", "\u{1F511}\u{1F511}"] as $shortKey) {
    [$result, $session] = runUrlKeyCase($shortKey, ['administrator' => $shortKey], fixtureUser(admin: true));
    fixtureAssert($result === false, 'A URL key shorter than three characters must be denied.');
    fixtureAssert($session->loginAttempts === 0, 'A short URL key must not create a session.');
}

[$result, $session] = runUrlKeyCase(' abc ', ['administrator' => 'abc'], fixtureUser(admin: true));
fixtureAssert($result === true, 'A padded URL key of the minimum length must retain the existing trim behavior.');
fixtureAssert($session->loginAttempts === 1, 'A valid minimum-length URL key must create a session.');

[$result, $session] = runUrlKeyCase("\u{1F511}\u{1F511}\u{1F511}", ['administrator' => "\u{1F511}\u{1F511}\u{1F511}"], fixtureUser(admin: true));
fixtureAssert($result === true, 'A valid three-character Unicode URL key must be accepted.');
fixtureAssert($session->loginAttempts === 1, 'A valid three-character Unicode URL key must create a session.');

[$result, $session] = runUrlKeyCase(123, ['administrator' => '123'], fixtureUser(admin: true));
fixtureAssert($result === true, 'A numeric request key must retain the existing scalar coercion behavior.');
fixtureAssert($session->loginAttempts === 1, 'A valid numeric request key must create a session.');

[$result, $session] = runUrlKeyCase(['123'], ['administrator' => '123'], fixtureUser(admin: true));
fixtureAssert($result === false, 'A structured request key must be denied.');
fixtureAssert($session->loginAttempts === 0, 'A structured request key must not create a session.');

[$result, $session] = runUrlKeyCase('fixture-key', [
    'blank-user' => '',
    'administrator' => 'fixture-key',
], fixtureUser(admin: true));
fixtureAssert($result === true, 'A blank mapping must not prevent a later valid URL key from matching.');
fixtureAssert($session->loginAttempts === 1, 'A later valid URL key must create only one session.');

[$result, $session] = runUrlKeyCase(' ', [
    'editor' => 'fixture-key',
    'administrator' => '',
], fixtureUser(admin: true));
fixtureAssert($result === false, 'A later blank mapping must not match a whitespace request key.');
fixtureAssert($session->loginAttempts === 0, 'A later blank mapping must not create a session.');

$rateLimitedApp = fixtureApp(fixtureUser(admin: true));
$rateLimitedApp->request->remoteIp = '203.0.113.10';
$rateLimitedService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));

for ($attempt = 1; $attempt <= 5; $attempt++) {
    $rateLimitedApp->request->userIp = "198.51.100.$attempt";
    fixtureAssert($rateLimitedService->loginByKey("wrong-key-$attempt") === false, "Unmatched URL key attempt $attempt must be denied.");
}

fixtureAssert($rateLimitedApp->mutex->heldNames === [], 'The URL-key limiter must release its mutex after failed attempts.');
$limiterState = json_encode([$rateLimitedApp->cache->entries, $rateLimitedApp->mutex->acquiredNames]);
fixtureAssert(!str_contains($limiterState, '203.0.113.10'), 'The URL-key limiter must not store the raw direct connection address.');
fixtureAssert(!str_contains($limiterState, 'wrong-key'), 'The URL-key limiter must not store attempted credentials.');

expectUrlKeyRateLimit(
    fn() => $rateLimitedService->loginByKey('abc'),
    $rateLimitedApp,
    'A sixth URL-key attempt from the same direct address must be rate limited.',
);
fixtureAssert($rateLimitedApp->session->loginAttempts === 0, 'Rate limiting must run before a matching key can create a session.');

$rateLimitedApp->request->remoteIp = '203.0.113.11';
fixtureAssert($rateLimitedService->loginByKey('abc') === true, 'A separate direct connection address must retain its own URL-key allowance.');
fixtureAssert($rateLimitedApp->session->loginAttempts === 1, 'A valid key from an independent source must create the intended session.');

$successfulApp = fixtureApp(fixtureUser(admin: true));
$successfulApp->request->remoteIp = '192.0.2.20';
$successfulService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));
fixtureAssert($successfulService->loginByKey('wrong-one') === false, 'An unmatched key below the limit must be denied.');
fixtureAssert($successfulService->loginByKey('wrong-two') === false, 'A second unmatched key below the limit must be denied.');
fixtureAssert($successfulService->loginByKey('abc') === true, 'A valid existing key below the limit must remain usable.');

$malformedApp = fixtureApp(fixtureUser(admin: true));
$malformedApp->request->remoteIp = '192.0.2.30';
$malformedService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));
fixtureAssert($malformedService->loginByKey('ab') === false, 'Existing short-key rejection must remain unchanged.');
fixtureAssert($malformedService->loginByKey(['abc']) === false, 'Existing structured-key rejection must remain unchanged.');
fixtureAssert($malformedApp->cache->entries === [], 'Malformed keys must not create rate-limit state.');

$consoleApp = fixtureApp(fixtureUser(admin: true));
$consoleApp->request->console = true;
$consoleApp->mutex->failAcquires = true;
$consoleService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));
fixtureAssert($consoleService->loginByKey('abc') === true, 'Trusted console calls must retain their existing URL-key behavior.');
fixtureAssert($consoleApp->mutex->acquiredNames === [], 'Trusted console calls must not consume the public HTTP attempt budget.');

$expiredApp = fixtureApp(fixtureUser(admin: true));
$expiredApp->request->remoteIp = '192.0.2.40';
$expiredService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));
fixtureAssert($expiredService->loginByKey('wrong-one') === false, 'An unmatched key must create a rate-limit entry.');
$expiredCacheKey = array_key_first($expiredApp->cache->entries);
$expiredApp->cache->entries[$expiredCacheKey] = ['count' => 5, 'resetAt' => time() - 1];
fixtureAssert($expiredService->loginByKey('wrong-two') === false, 'An expired rate-limit window must allow a new failed attempt.');
fixtureAssert($expiredApp->cache->entries[$expiredCacheKey]['count'] === 1, 'An expired rate-limit window must restart its count.');

$lockFailureApp = fixtureApp(fixtureUser(admin: true));
$lockFailureApp->request->remoteIp = '192.0.2.50';
$lockFailureApp->mutex->failAcquires = true;
$lockFailureService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));
expectUrlKeyRateLimit(
    fn() => $lockFailureService->loginByKey('wrong-key'),
    $lockFailureApp,
    'URL-key authentication must fail closed when limiter locking is unavailable.',
);

$cacheFailureApp = fixtureApp(fixtureUser(admin: true));
$cacheFailureApp->request->remoteIp = '192.0.2.60';
$cacheFailureApp->cache->failWrites = true;
$cacheFailureService = fixtureService(new verbb\autologin\models\Settings([
    'enabled' => true,
    'urlKeys' => ['administrator' => 'abc'],
]));
expectUrlKeyRateLimit(
    fn() => $cacheFailureService->loginByKey('wrong-key'),
    $cacheFailureApp,
    'URL-key authentication must fail closed when limiter state cannot be persisted.',
);
fixtureAssert($cacheFailureApp->mutex->heldNames === [], 'The URL-key limiter must release its mutex after cache failure.');

foreach (['basic-auth', 'ip'] as $method) {
    [$result, $session] = runLoginMethod($method, $settings, fixtureUser());
    fixtureAssert($result === true, "An active mapped user must still be able to log in by $method.");
    fixtureAssert($session->loginAttempts === 1 && $session->identityId === 42, "$method must create the intended session.");
}

[$result, $session] = runLoginMethod('basic-auth', $settings, fixtureUser(), false);
fixtureAssert($result === false, 'A browser-supplied Basic Auth username must not be accepted as authenticated upstream identity.');
fixtureAssert($session->loginAttempts === 0, 'A browser-supplied Basic Auth username must not create a session.');

$mismatchedBasicSettings = clone $settings;
$mismatchedBasicSettings->basicAuth = ['administrator' => 'different-upstream-user'];
[$result, $session] = runLoginMethod('basic-auth', $mismatchedBasicSettings, fixtureUser());
fixtureAssert($result === false, 'A mismatched authenticated upstream identity must be denied.');
fixtureAssert($session->loginAttempts === 0, 'A mismatched authenticated upstream identity must not create a session.');

[$result, $session] = runLoginMethod('ip', $settings, fixtureUser(), false);
fixtureAssert($result === false, 'A forwarded client IP must not override the direct connection address.');
fixtureAssert($session->loginAttempts === 0, 'A forwarded client IP must not create a session.');

$assuredMethodNames = [
    'url-key' => 'urlKeys',
    'basic-auth' => 'basicAuth',
    'ip' => 'ipWhitelist',
];

foreach ($assuredMethodNames as $method => $settingName) {
    [$result, $session] = runLoginMethod($method, $settings, fixtureUser(hasActiveMfa: true));
    fixtureAssert($result === false, "$method must deny an account with active Craft two-step verification by default.");
    fixtureAssert($session->loginAttempts === 0, "$method must not create a session before Craft two-step verification.");

    $assuredSettings = clone $settings;
    $assuredSettings->mfaAssuredMethods = [$settingName];
    [$result, $session] = runLoginMethod($method, $assuredSettings, fixtureUser(hasActiveMfa: true));
    fixtureAssert($result === true, "$method must allow an active two-step account when the method is explicitly assured.");
    fixtureAssert($session->loginAttempts === 1 && $session->identityId === 42, "$method assurance must create only the intended session.");
}

$basicOnlySettings = clone $settings;
$basicOnlySettings->mfaAssuredMethods = ['basicAuth'];
[$result, $session] = runLoginMethod('url-key', $basicOnlySettings, fixtureUser(hasActiveMfa: true));
fixtureAssert($result === false, 'Assuring Basic Auth must not allow an MFA account to log in with a URL key.');
fixtureAssert($session->loginAttempts === 0, 'An unassured URL key must not create an MFA account session.');

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

$prepareSubmittedSettings = new ReflectionMethod($controller, 'prepareSubmittedSettings');
$preparedSettings = $prepareSubmittedSettings->invoke($controller, [
    'mfaAssuredMethods' => ['urlKeys'],
]);
fixtureAssert(!array_key_exists('mfaAssuredMethods', $preparedSettings), 'The configuration-only MFA assurance policy must not be accepted from the control panel.');

$app->request->post = false;

try {
    $controller->actionSaveSettings();
    throw new RuntimeException('Settings mutation must require POST.');
} catch (yii\web\MethodNotAllowedHttpException) {
}

echo "Autologin security fixture passed.\n";
