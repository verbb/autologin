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

Whether to enable the Autologin plugin.
:::


::: reference
### `ipWhitelist`

**Type:** `array` · **Default:** `[]`

A list of Craft usernames/emails mapped to IPs.
:::


::: reference
### `basicAuth`

**Type:** `array` · **Default:** `[]`

A list of Craft usernames/emails mapped to basic auth usernames.
:::


::: reference
### `urlKeys`

**Type:** `array` · **Default:** `[]`

A list of Craft usernames/emails mapped to url keys.
:::


::: reference
### `redirectUrl`

**Type:** `string` · **Default:** `''`

Redirect to this url after logging in automatically.
:::


### IP Whitelist
Return an array with the key as the username you wish to autologin, and the value an array of IPs.

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

### Basic Auth
Return an array with the key as the username you wish to autologin, and the value a Basic HTTP Auth username.

```php
<?php

return [
    'basicAuth' => [
        'craftUserName1' => 'basicAuthUsername',
        'craftUserName2' => 'basicAuthUsername2',    
    ],
];
```

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
You can also manage configuration settings through the Control Panel by visiting Settings → Autologin.
