<?php
/**
 * PHPUnit bootstrap file for Random Post on Refresh plugin (wp-env)
 *
 * @package RandomPostOnRefresh
 */

// Load the Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load the plugin.
require_once dirname( __DIR__ ) . '/RandomPostOnRefresh.php';

// Load WordPress test suite functions
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

/**
 * Load the plugin.
 */
function _load_random_post_on_refresh_plugin() {
	require dirname( __DIR__ ) . '/RandomPostOnRefresh.php';
}
tests_add_filter( 'muplugins_loaded', '_load_random_post_on_refresh_plugin' );

// Start up the WP testing environment
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
