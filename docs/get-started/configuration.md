# Configuration
Create a `autologin.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

The below shows the defaults already used by Autologin, so you don't need to add these options unless you want to modify the values.

```php
<?php

return [
    '*' => [
        'enabled' => true,
        'ipWhitelist' => [],
        'basicAuth' => [],
        'urlKeys' => [],
        'redirectUrl' => '',
    ],
];
```

## Configuration options
- `enabled` - Whether to enable the Autologin plugin.
- `ipWhitelist` - A list of Craft usernames/emails mapped to IPs.
- `basicAuth` - A list of Craft usernames/emails mapped to basic auth usernames.
- `urlKeys` - A list of Craft usernames/emails mapped to url keys.
- `redirectUrl` - Redirect to this url after logging in automatically.

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
