# Random Post on Refresh

![Deploy to WordPress.org](https://github.com/wpscholar-wp-plugins/random-post-on-refresh/workflows/Deploy%20to%20WordPress.org/badge.svg?branch=master&event=push)

## Description
The **Random Post on Refresh** plugin allows you to randomly display a different WordPress post on every page load. It is lightweight, works with custom post types, and is easy to use via a simple shortcode.

[View on WordPress.org](https://wordpress.org/plugins/random-post-on-refresh/)

---

## Features
- Display a random post on every page load
- Supports custom post types
- No settings page: just use the shortcode
- Clean, well-written code
- Translation ready
- Can be used in widgets
- Customizable output (title, image, excerpt, content)
- Filter and action hooks for developers

---

## Installation

### Prerequisites
- WordPress 4.5 or greater
- PHP 5.4 or greater

### The Easy Way
1. In your WordPress admin, go to **Plugins > Add New**.
2. Search for "Random Post on Refresh".
3. Click **Install** and then **Activate**.

### The Hard Way
1. Download the plugin .zip file.
2. Upload it to your `/wp-content/plugins/` directory and unzip.
3. Activate the plugin from the **Plugins** page in your WordPress admin.

---

## Usage

Add the `[random_post_on_refresh]` shortcode to any post, page, or widget where you want a random post to appear. The plugin will output a random post each time the page is loaded.

### Example
```[random_post_on_refresh]```

---

## Shortcode Attributes
The shortcode supports several attributes for customizing the output:

| Attribute      | Description | Example |
| -------------- | ----------- | ------- |
| `author`       | Comma-separated list of author IDs to limit posts | `[random_post_on_refresh author="1,11,14"]` |
| `ids`          | Comma-separated list of post IDs to pull from | `[random_post_on_refresh ids="19,87,113,997"]` |
| `not`          | Comma-separated list of post IDs to exclude | `[random_post_on_refresh not="3,456,876"]` |
| `post_type`    | Comma-separated list of post types (default: `post`) | `[random_post_on_refresh post_type="page"]` |
| `search`       | Search term to filter posts | `[random_post_on_refresh search="relativity"]` |
| `taxonomy`     | Custom taxonomy to pull from (requires `terms`) | `[random_post_on_refresh taxonomy="post_tag" terms="2,4"]` |
| `terms`        | Comma-separated list of term IDs (requires `taxonomy`) | `[random_post_on_refresh taxonomy="category" terms="5,7"]` |
| `class`        | Custom class for the wrapping div | `[random_post_on_refresh class="my-custom-class"]` |
| `size`         | Image size to use (if showing images) | `[random_post_on_refresh size="thumbnail"]` |
| `show`         | Comma-separated or pipe-separated list of elements to display. Options: `title`, `image`, `excerpt`, `content`. Use `|` to create columns. Default: `title, image, excerpt` | `[random_post_on_refresh show="title, image"]` or `[random_post_on_refresh show="title|image|excerpt"]` |
| `image_required` | Only show posts with images (`true`/`false`, default: `true`) | `[random_post_on_refresh show="title, image" image_required="false"]` |

You can combine attributes as needed:
```[random_post_on_refresh author="19" size="full" not="2310"]```

---

## Styling

The plugin includes a CSS file (`assets/random-post-on-refresh.css`) that styles the output. You can override these styles in your theme if desired. Notable classes:
- `.random-post-on-refresh` (wrapper)
- `.random-post-on-refresh__title`, `.random-post-on-refresh__image`, `.random-post-on-refresh__excerpt`, `.random-post-on-refresh__content`
- `.random-post-on-refresh.--has-groups` (for column layouts)
- `.random-post-on-refresh-error` (for error messages)

---

## Extensibility

Developers can extend or modify the plugin using the following filter:

- `random_post_on_refresh_query_args` — Filter the WP_Query arguments before fetching posts.
  ```php
  add_filter( 'random_post_on_refresh_query_args', function( $args, $atts ) {
      // Modify $args as needed
      return $args;
  }, 10, 2 );
  ```

The plugin also supports translations and loads its text domain from `/languages`.

---

## Translation

- Translation ready (`/languages/random-post-on-refresh.pot` included)
- To contribute a translation, submit a pull request or use the WordPress.org translation system.

---

## Frequently Asked Questions

See the [FAQ section on WordPress.org](https://wordpress.org/plugins/random-post-on-refresh/#faq-header) for more details and troubleshooting.

---

## Changelog

See [readme.txt](./readme.txt) or the [WordPress.org changelog](https://wordpress.org/plugins/random-post-on-refresh/#developers) for version history.

---

## Contributing

All pull requests are welcome! This plugin is relatively simple, so feature requests will be considered carefully. Translations and bug fixes are especially appreciated.

- Follow the [WPScholar coding standards](https://github.com/wpscholar/phpcs-standards-wpscholar)
- Run `composer lint` to check code style
- Run `composer fix` to auto-fix code style issues
- Run `composer i18n` to update translation files

---

## License

GPLv3. See [LICENSE](http://www.gnu.org/licenses/gpl-3.0.html).

---

## Links
- [Plugin Homepage](https://wpscholar.com/wordpress-plugins/random-post-on-refresh/)
- [WordPress.org Plugin Page](https://wordpress.org/plugins/random-post-on-refresh/)
- [GitHub Repository](https://github.com/wpscholar-wp-plugins/random-post-on-refresh)
