# Usage

## Login by URL
You can provide your users with a url that automatically logs them in. To set this up, you need a pair of username => password in the `urlKeys` [configuration](docs:get-started/configuration) setting.

```php
<?php

return [
    'urlKeys' => [
        'steeve' => 'BepmD8GQBZpaFpXQ',
    ],
];
```

After setting that up, you can login by going to `http://my-site.test/autologin?key=BepmD8GQBZpaFpXQ`. This will login the user `steeve` automatically!

If you want to redirect to the control panel dashboard, add `cp=true` to the url: `http://my-site.test/autologin?key=BepmD8GQBZpaFpXQ&cp=true`
