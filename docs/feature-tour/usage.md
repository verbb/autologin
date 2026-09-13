# Usage

## Login by URL
You can provide your users with a url that automatically logs them in. To set this up, you need a mapping from an existing Craft username to a secret login key in the `urlKeys` [configuration](docs:get-started/configuration) setting.

```php
<?php

return [
    'urlKeys' => [
        'steeve' => 'BepmD8GQBZpaFpXQ',
    ],
];
```

After setting that up, you can login by going to `https://my-site.test/autologin?key=BepmD8GQBZpaFpXQ`. This will login the user `steeve` automatically.

If you want to redirect to the control panel dashboard, add `cp=true` to the url: `https://my-site.test/autologin?key=BepmD8GQBZpaFpXQ&cp=true`

## Testing and Revoking a Link

Use a separate private browser window to test the link while logged out. Autologin does not switch an already signed-in visitor to a different account. After opening the link, check which account is active and that its permissions suit the intended task. Adding `cp=true` chooses the control panel as the destination; it does not grant additional permissions.

The key is a reusable credential, not the user's Craft password or a one-time invitation. Use a unique, difficult-to-guess value and share it only with someone who should be able to sign in as that account. To revoke the link, remove its mapping from `urlKeys` or replace the key. Test the old link in a logged-out browser after changing it; changing a key does not end an existing logged-in session.
