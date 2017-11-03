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

## Configuring Autologin

```php
<?php
return [
    // Enable the Autologin plugin
    'enabled'           => true,

    // A list of Craft usernames/emails mapped to IPs
    'ipWhitelist'       => [
        'craftUsername' => [
            '162.247.141.58',
            '162.247.141.59',
        ],
    ],

    // A list of Craft usernames/emails mapped to basic auth usernames
    'basicAuth'         => [
        'craftUserName1' => 'basicAuthUsername',
        'craftUserName2' => 'basicAuthUsername2',    
    ],
    
    // A list of Craft usernames/emails mapped to url keys
    'urlKeys'           => [
        'craftUserName' => 'BepmD8GQBZpaFpXQ',
    ],

    // Set this to a username if you want to automatically login on localhost
    'localhostUsername' => '',

    // Redirect to this url after logging in automatically
    'redirectUrl'       => '',
];

```

## Login by URL

You can provide your users with a url that automatically logs them in. To set this up, you need a pair of username => password in the `urlKeys` setting.

After setting that up, you can login by going to _siteurl.tld/autologin?key=BepmD8GQBZpaFpXQ_.

If you want to redirect to the control panel dashboard, add cp=true to the url: _siteurl.tld/autologin?key=BepmD8GQBZpaFpXQ&cp=true_

## Credits

Icon: [Login icon by Gregor Cresnar](https://thenounproject.com/term/login/1039023)

Brought to you by [Superbig](https://superbig.co)
