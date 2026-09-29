# Configuration

You can customise Autologin’s settings using a PHP configuration file. This is optional: each setting has a default, so you only need to include the values you want to change.

To override a setting, create `autologin.php` in your Craft project’s `/config` directory and return an array of setting names and values. For example, the following will disable automatic login:

```php
<?php

return [
    'enabled' => false,
];
```

All other settings keep their defaults. Add any further settings you want to change to the same array. The options below explain the available settings and their defaults.

## Configuration Options

::: reference
### `enabled`

**Type:** `bool` · **Default:** `true`

Whether to enable automatic login through URL keys, Basic Auth mappings and IP mappings. Disabling automatic login does not remove configured mappings, so the same credentials become available again when it is re-enabled.
:::


::: reference
### `ipWhitelist`

**Type:** `array` · **Default:** `[]`

A list of Craft usernames or emails mapped to direct connection IP addresses. Autologin ignores forwarding headers such as `X-Forwarded-For`, so this setting cannot trust an address supplied by the browser.

If Craft runs behind a reverse proxy, the direct connection address is normally the proxy. Do not map that shared proxy address, because every visitor passing through it would match. This setting is suitable only when the direct peer identifies the visitor you intend to sign in.
:::


::: reference
### `basicAuth`

**Type:** `array` · **Default:** `[]`

A list of Craft usernames or emails mapped to authenticated upstream usernames. Autologin reads the username from the server-controlled `REMOTE_USER` value. It does not read the username or password from a browser’s `Authorization` header and does not validate a Basic Auth password itself.

Configure your web server or identity proxy to authenticate the request before it reaches Craft, populate `REMOTE_USER` only after successful authentication, strip conflicting client identity headers, and prevent visitors from bypassing that server to reach the Craft origin directly.
:::


::: reference
### `urlKeys`

**Type:** `array` · **Default:** `[]`

A list of Craft usernames/emails mapped to url keys.
:::


::: reference
### `mfaAssuredMethods`

**Type:** `array` · **Default:** `[]`

A list of Autologin methods that your site treats as providing assurance equivalent to Craft’s two-step verification. Allowed values are `basicAuth`, `ipWhitelist` and `urlKeys`.

When a Craft account has an active two-step method, Autologin refuses to create its session unless the matching login method appears in this list. Leave the default empty when users must complete Craft’s own two-step challenge. Add a method only when your external authentication and deployment controls satisfy your site’s MFA policy; this setting records the site owner’s assertion and does not cause Craft to perform a challenge.

Configure this security policy in `config/autologin.php`. It is deliberately not exposed on the control-panel settings page.
:::


::: reference
### `redirectUrl`

**Type:** `string` · **Default:** `''`

Redirect to this url after logging in automatically.
:::


### IP Whitelist
Return an array with the Craft username or email as the key and an array of direct connection IP addresses as the value.

```php
<?php

return [
    'ipWhitelist' => [
        'craftUsername' => [
            '162.247.141.58',
            '162.247.141.59',
        ],
    ],
];
```

### Authenticated Upstream Users
Return an array with the Craft username or email as the key and the authenticated `REMOTE_USER` value as the mapped username.

```php
<?php

return [
    'basicAuth' => [
        'craftUserName1' => 'basicAuthUsername',
        'craftUserName2' => 'basicAuthUsername2',
    ],
];
```

### Two-Step Verification Assurance
Accounts with an active Craft two-step method are denied by default. If an authenticated upstream system provides assurance equivalent to your Craft policy, list only that method in `mfaAssuredMethods`:

```php
<?php

return [
    'mfaAssuredMethods' => [
        'basicAuth',
    ],
];
```

This example allows authenticated upstream-user mappings to sign in accounts with active Craft two-step methods. IP and URL-key mappings remain denied for those accounts.

### URL Keys
Return an array with the key as the username you wish to autologin, and the value for the unique key.

```php
<?php

return [
    'urlKeys' => [
        'craftUserName' => 'BepmD8GQBZpaFpXQ',
    ],
];
```

## Control Panel
You can also manage ordinary configuration settings through the Control Panel by visiting **Settings → Autologin**. Configure `mfaAssuredMethods` in `config/autologin.php` so the site’s MFA assurance decision remains explicit in code.
