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
		$result = RandomPostOnRefresh::list_to_array( ' a ,b, c ' );
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

	/**
	 * Test build_query_args with default attributes.
	 */
	public function test_build_query_args_defaults() {
		$args = RandomPostOnRefresh::build_query_args( RandomPostOnRefresh::DEFAULT_ATTRIBUTES );
		// Author
		$this->assertArrayNotHasKey( 'author__in', $args );
		// IDs
		$this->assertArrayNotHasKey( 'post__in', $args );
		// Not
		$this->assertArrayHasKey( 'post__not_in', $args ); // This should always be set
		// Post type
		$this->assertEquals( array( 'post' ), $args['post_type'] );
		// Posts per page
		$this->assertEquals( 100, $args['posts_per_page'] );
		// Search
		$this->assertArrayNotHasKey( 's', $args );
		// Show
		$this->assertArrayHasKey( 'meta_query', $args );
		$this->assertArrayHasKey( 'key', $args['meta_query'][0] );
		$this->assertEquals( '_thumbnail_id', $args['meta_query'][0]['key'] );
		// Taxonomy / Terms
		$this->assertArrayNotHasKey( 'category__in', $args );
		$this->assertArrayNotHasKey( 'tag__in', $args );
		$this->assertArrayNotHasKey( 'tax_query', $args );
	}

	/**
	 * Test build_query_args with minimal attributes.
	 */
	public function test_build_query_args_minimal() {
		$atts = array_merge(
			RandomPostOnRefresh::DEFAULT_ATTRIBUTES,
			array(
				'post_type' => 'post,page',
			)
		);
		$args = RandomPostOnRefresh::build_query_args( $atts );
		$this->assertEquals( array( 'post', 'page' ), $args['post_type'] );
		$this->assertEquals( 100, $args['posts_per_page'] );
		$this->assertArrayHasKey( 'post__not_in', $args );
	}

	/**
	 * Test build_query_args with author, ids, not, and search.
	 */
	public function test_build_query_args_with_filters() {
		$atts = array_merge(
			RandomPostOnRefresh::DEFAULT_ATTRIBUTES,
			array(
				'post_type' => 'post',
				'author'    => '1,2',
				'ids'       => '10,20',
				'not'       => '5,6',
				'search'    => 'test',
			)
		);
		$args = RandomPostOnRefresh::build_query_args( $atts );
		$this->assertEquals( array( 1, 2 ), $args['author__in'] );
		$this->assertEquals( array( 10, 20 ), $args['post__in'] );
		$this->assertEquals( array( 5, 6, $args['post__not_in'][2] ), $args['post__not_in'] ); // The last value is get_the_ID(), which is 0 in CLI
		$this->assertEquals( 'test', $args['s'] );
	}

	/**
	 * Test build_query_args with taxonomy and terms.
	 */
	public function test_build_query_args_with_taxonomy() {
		$atts = array_merge(
			RandomPostOnRefresh::DEFAULT_ATTRIBUTES,
			array(
				'post_type' => 'post',
				'taxonomy'  => 'category',
				'terms'     => '3,4',
			)
		);
		$args = RandomPostOnRefresh::build_query_args( $atts );
		$this->assertEquals( array( 3, 4 ), $args['category__in'] );
	}

	/**
	 * Test build_query_args with show=image and image_required=true.
	 */
	public function test_build_query_args_with_image_required() {
		$atts = array_merge(
			RandomPostOnRefresh::DEFAULT_ATTRIBUTES,
			array(
				'post_type'      => 'post',
				'show'           => 'title, image',
				'image_required' => 'true',
			)
		);
		$args = RandomPostOnRefresh::build_query_args( $atts );
		$this->assertArrayHasKey( 'meta_query', $args );
		$this->assertEquals( array( array( 'key' => '_thumbnail_id' ) ), $args['meta_query'] );
	}
}
