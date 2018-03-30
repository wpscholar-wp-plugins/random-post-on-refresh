=== Random Post on Refresh ===
Contributors: woodent, Imzodigital
Donate link: https://www.paypal.me/wpscholar
Tags: random post, post rotation, different post
Requires at least: 4.5
Tested up to: 4.9.4
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Show a random post on every page load.

== Description ==

The **Random Post on Refresh** plugin allows you to randomly display a different post on every page load.

Using this plugin is simple:

1. Install the plugin
2. Activate the plugin
3. On the page or post where you want to have a random post display, add the `[random_post_on_refresh]` shortcode where you want your post to appear.
5. Save your changes.

= Features =

* Works with custom post types
* No settings page, just an easy way for you to add random posts to your site
* Clean, well written code that won't bog down your site

== Installation ==

= Prerequisites =
If you don't meet the below requirements, I highly recommend you upgrade your WordPress install or move to a web host
that supports a more recent version of PHP.

* Requires WordPress version 3.2 or greater
* Requires PHP version 5 or greater ( PHP version 5.2.4 is required to run WordPress version 3.2 )

= The Easy Way =

1. In your WordPress admin, go to 'Plugins' and then click on 'Add New'.
2. In the search box, type in 'Random Post on Refresh' and hit enter.  This plugin should be the first and likely the only result.
3. Click on the 'Install' link.
4. Once installed, click the 'Activate this plugin' link.

= The Hard Way =

1. Download the .zip file containing the plugin.
2. Upload the file into your `/wp-content/plugins/` directory and unzip
3. Find the plugin in the WordPress admin on the 'Plugins' page and click 'Activate'

== Frequently Asked Questions ==

The `[random_post_on_refresh]` shortcode supports a few attributes to give you more control over the results:

* **author** - Provide an author ID or a comma-separated list of author IDs if you want to limit the random post to one of those authors. Example: `[random_post_on_refresh author="1, 11, 14"]`

* **ids** - Provide a comma-separated list of post IDs to pull random posts from.  Example: `[random_post_on_refresh ids="19, 87, 113, 997"]`

* **not** - Provide a comma-separated list of post IDs to exclude. Example: `[random_post_on_refresh not="3, 456, 876"]`

* **post_type** - Provide a post type or a comma-separated list of post types to pull from. You must use the internal post type name. Default is `post`. Example: `[random_post_on_refresh post_type="page"]`

* **search** - Provide a custom search term to limit the random posts returned.  Example: `[random_post_on_refresh search="relativity"]`

* **taxonomy** - Provide a custom taxonomy to pull from. Requires the `terms` attribute to be set as well. Example: `[random_post_on_refresh taxonomy="post_tag" terms="2,4"]`

* **terms** - Provide a term ID or comma-separated list of term IDs to limit the random posts returned. Requires the `taxonomy` attribute to be set as well. Example: `[random_post_on_refresh taxonomy="post_tag" terms="2,4"]`

* **class** - Provide a custom class name for the wrapping HTML div. Example: `[random_post_on_refresh class="my-custom-class"]`

* **size** - Provide a registered image size to use for image display (optional, only takes effect if images are being shown). Example: `[random_post_on_refresh size="thumbnail"]`

* **show** - Provide a comma-separated list of post elements to display. You can also use a vertical pipe `|` character instead of a comma to separate items into columns. Options are: title, image, excerpt, content. Defaults to `title, image, excerpt`. Items will show in the order you provide. Note: If images are shown, only posts with featured images will be returned. Example: `[random_post_on_refresh show="title, image"]`

Keep in mind that any of these attributes can be combined as needed.  Example: `[random_post_on_refresh author="19" size="full" not="2310"]`.  Also, keep in mind that the shortcode can be used in text widgets.

== Changelog ==

= 1.0 =

* Tested in WordPress version 4.9.2

== Upgrade Notice ==

