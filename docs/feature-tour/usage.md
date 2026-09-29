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

The key is a reusable credential, not the user's Craft password or a one-time invitation. Use a unique, difficult-to-guess value and share it only with someone who should be able to sign in as that account. To revoke the link, remove its mapping from `urlKeys` or replace the key. Replacing or removing a key invalidates existing links, but does not end sessions that were already created. Disabling automatic login blocks the link without deleting its mapping, so the same key works again if automatic login is re-enabled. Test the old link in a logged-out browser after changing it.

## Two-Step Verification
Autologin leaves accounts with an active Craft two-step method logged out by default. Those users can sign in through Craft’s normal login flow and complete their configured second step.

You can explicitly designate an Autologin method as providing assurance equivalent to your site’s two-step policy through the `mfaAssuredMethods` [configuration](docs:get-started/configuration#mfaassuredmethods) setting. This is intended for authenticated upstream identity systems. A URL key is a reusable bearer credential and does not provide a second factor by itself, so do not mark `urlKeys` as assured unless separate deployment controls genuinely satisfy your policy.
