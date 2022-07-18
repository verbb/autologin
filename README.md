# Autologin plugin for Craft CMS
Automatically login based on whitelisted IP, basic auth username or URL keys.

## Installation
You can install Autologin via the plugin store, or through Composer.

### Craft Plugin Store
To install **Autologin**, navigate to the _Plugin Store_ section of your Craft control panel, search for `Autologin`, and click the _Try_ button.

### Composer
You can also add the package to your project using Composer.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:
    
        composer require verbb/autologin

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Autologin.

## Configuring Autologin
The plugin is configured in the `config/` directory in a file you create called `autologin.php`. What follows is an example of what it might contain.

```php
<?php
return [
    // Enable the Autologin plugin
    'enabled' => true,

    // A list of Craft usernames/emails mapped to IPs
    'ipWhitelist' => [
        'craftUsername' => [
            '162.247.141.58',
            '162.247.141.59',
        ],
    ],

    // A list of Craft usernames/emails mapped to basic auth usernames
    'basicAuth' => [
        'craftUserName1' => 'basicAuthUsername',
        'craftUserName2' => 'basicAuthUsername2',    
    ],
    
    // A list of Craft usernames/emails mapped to url keys
    'urlKeys' => [
        'craftUserName' => 'BepmD8GQBZpaFpXQ',
    ],

    // Redirect to this url after logging in automatically
    'redirectUrl' => '',
];
```

## Login by URL
You can provide your users with a url that automatically logs them in. To set this up, you need a pair of username => password in the `urlKeys` setting.

After setting that up, you can login by going to `siteurl.tld/autologin?key=BepmD8GQBZpaFpXQ`.

If you want to redirect to the control panel dashboard, add cp=true to the url: `siteurl.tld/autologin?key=BepmD8GQBZpaFpXQ&cp=true`

## Credits
Originally created by the team at [Superbig](https://superbig.co/).

## Show your Support
Autologin is licensed under the MIT license, meaning it will always be free and open source – we love free stuff! If you'd like to show your support to the plugin regardless, [Sponsor](https://github.com/sponsors/verbb) development.

<h2></h2>

<a href="https://verbb.io" target="_blank">
    <img width="100" src="https://verbb.io/assets/img/verbb-pill.svg">
</a>
