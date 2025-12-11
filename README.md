# Language Redirect for Craft CMS

Redirects users to localized URLs based on browser language preferences. When a user visits your site's root path (`/`), the plugin detects their browser language and redirects them to the appropriate localized URL.

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

```bash
composer require liquidbcn/craftcms-language-redirect
```

Then go to Settings → Plugins and click "Install".

## Configuration

### Option 1: Control Panel (recommended)

Go to Settings → Plugins → Language Redirect and configure:

- **Default Language**: Fallback locale if browser language isn't in your URL list (e.g., `en-GB`)
- **Language URLs**: Map locale codes to your site URLs

### Option 2: Config file

Create `config/language-redirect.php`:

```php
<?php

return [
    'defaultLanguage' => 'en-GB',
    'urls' => [
        'ca'    => '/ca/',
        'ca-ES' => '/ca/',
        'es'    => '/es/',
        'es-ES' => '/es/',
        'en-GB' => '/en/',
        'en-US' => '/en/',
        'en'    => '/en/',
    ],
];
```

Config file settings take precedence over Control Panel settings.

## How it works

1. User visits your site root (`/`)
2. Plugin reads the `Accept-Language` header from the browser
3. Matches against your configured locale mappings
4. Performs a 301 redirect to the corresponding URL

The plugin only triggers on GET and HEAD requests to the root path.

## License

MIT

---

Developed by [Liquid Studio](https://liquidbcn.com)