# Language Redirect for Craft CMS
This plugin allows you redirect your users to the desired url based on user browser preferences

## Requirements

This plugin requires Craft CMS 4.0.0 or later

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require liquid/craftcms-language-redirect

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for this plugin.


## Configuring the plugin

You can configure the plugin creating a file under your config folder named language-redirect.php with the desired settings.

defaultLanguage: default locale if none is detected

urls: array containing the mapping between the locale and the relative url of your language base path

*Example*
```
<?php

return [
    'defaultLanguage' => 'en-GB',
    'urls' =>  [
        'ca'    => '/ca/',
        'ca-ES' => '/ca/',
        'es'    => '/es/',
        'es-ES' => '/es/',
        'en-GB' => '/en/',
        'en-US' => '/en/',
        'en' =>    '/en/'
    ]
];

```


Brought to you by
<a href="https://liquidbcn.com" target="_blank">Liquid Studio</a>
