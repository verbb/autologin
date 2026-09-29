<?php

return [
    // Enable the Autologin plugin
    'enabled' => true,

    // A list of usernames mapped to direct connection IPs
    'ipWhitelist' => [],

    // A list of Craft usernames mapped to authenticated REMOTE_USER identities
    'basicAuth' => [],

    // A list of Craft usernames mapped to url keys
    'urlKeys' => [],

    // Autologin methods that provide assurance equivalent to Craft two-step verification
    'mfaAssuredMethods' => [],

    // Redirect after logging in automatically
    'redirectUrl' => '',
];
