# Google Preferred Source CTA

WordPress plugin that adds a "Follow on Google News" CTA shortcode and auto-append feature. Publishers can highlight preferred source URLs for reader engagement.

## Features

- **Follow CTA shortcode** -- `[google_preferred_source]` renders a styled CTA linking to your Google News publisher page
- **Auto-append** -- optionally appends the CTA to all post content automatically
- **Settings page** -- configure publisher URL and auto-append behavior from the WordPress admin
- **Input sanitization** -- all settings are sanitized before storage

## Installation

1. Upload the plugin to `wp-content/plugins/google-preferred-source-cta/`
2. Activate via the WordPress admin
3. Navigate to **Settings > Google Preferred Source** to configure

## Usage

### Shortcode

```
[google_preferred_source]
```

### Auto-Append

Enable in Settings > Google Preferred Source to automatically add the CTA to all posts.

## Testing

```bash
composer install
./vendor/bin/phpunit
```

## License

GPL-2.0-or-later
