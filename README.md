# Autologin plugin for Craft CMS 3.x

Automatically login based on whitelisted IP or a key

![Screenshot](resources/img/icon.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require superbig/craft3-autologin

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Autologin.

## Autologin Overview

-Insert text here-

## Configuring Autologin

```php
<?php
return [
    // Enable the Autologin plugin
    'enabled'           => true,

    // A list of usernames mapped to IPs
    'ipWhitelist'       => [],

    // A list of Craft usernames mapped to basic auth usernames
    'basicAuth'         => [],

    // Set this to a username if you want to automatically login on localhost
    'localhostUsername' => '',

    // Redirect after logging in automatically
    'redirectUrl'       => '',
];

```

## Credits

Icon: [Login icon by Gregor Cresnar](git@github.com:sjelfull/craft3-autologin.git)

## Roadmap

- Add keys that can be provided to a controller to autologin

Brought to you by [Superbig](https://superbig.co)
