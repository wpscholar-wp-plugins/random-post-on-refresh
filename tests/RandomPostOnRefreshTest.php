<?php
/**
 * Unit tests for RandomPostOnRefresh plugin
 *
 * @package RandomPostOnRefresh
 */

use PHPUnit\Framework\TestCase;

/**
 * Test cases for RandomPostOnRefresh utility methods and shortcode handler.
 */
class RandomPostOnRefreshTest extends TestCase {
	/**
	 * Test that parse_id_list returns an array of integers from a string.
	 */
	public function test_parse_id_list_returns_array_of_ints() {
		$result = RandomPostOnRefresh::parse_id_list( '1,2,3,abc,4' );
		$this->assertEquals( array( 1, 2, 3, 4 ), $result );
	}

	/**
	 * Test that parse_id_list returns an empty array for an empty string.
	 */
	public function test_parse_id_list_empty_string_returns_empty_array() {
		$result = RandomPostOnRefresh::parse_id_list( '' );
		$this->assertEquals( array(), $result );
	}

	/**
	 * Test that list_to_array splits and trims a comma-separated string.
	 */
	public function test_list_to_array_splits_and_trims() {
		$result = RandomPostOnRefresh::list_to_array( 'a, b, c' );
		$this->assertEquals( array( 'a', 'b', 'c' ), $result );
	}

	/**
	 * Test that list_to_array works with a custom delimiter.
	 */
	public function test_list_to_array_with_custom_delimiter() {
		$result = RandomPostOnRefresh::list_to_array( 'a|b|c', '|' );
		$this->assertEquals( array( 'a', 'b', 'c' ), $result );
	}

	/**
	 * Smoke test: shortcode handler should return a string.
	 */
	public function test_shortcode_returns_string() {
		$output = RandomPostOnRefresh::shortcode( array() );
		$this->assertIsString( $output );
	}
}
