<?php
/**
 * PHPUnit bootstrap file for Random Post on Refresh plugin (wp-env)
 *
 * @package RandomPostOnRefresh
 */

// Load the Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load WordPress test suite functions
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

// Start up the WP testing environment
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

// Load the plugin.
require_once dirname( __DIR__ ) . '/RandomPostOnRefresh.php';
