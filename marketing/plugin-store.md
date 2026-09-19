Autologin signs a nominated Craft user in when a trusted request matches your configuration. Use private URL keys, approved IP addresses, or HTTP Basic Authentication for controlled access and development workflows.

Map a difficult-to-guess URL key to a Craft account and share a link that signs that user in. Choose the site or control panel destination and revoke access by replacing or removing the key.

## Features

- **Private URL keys:** Sign a mapped user in from a unique URL and revoke the link through configuration.
- **IP allowlists:** Automatically recognise nominated users when requests arrive from approved addresses.
- **Basic Authentication:** Map an HTTP Basic Authentication username to a Craft account.
- **Site or control panel:** Send the signed-in user to the public site or directly to the Craft dashboard.
- **Redirect control:** Choose the destination that should follow a successful automatic login.
- **Project configuration:** Keep mappings and behaviour in a PHP config file alongside the rest of the project.
- **Flexible login rules:** URL keys work well for deliberate, shareable access. IP allowlists and Basic Authentication mappings cover predictable networks and protected environments without asking someone to enter their Craft password each time.
