<?php
/**
 * Plugin Name: Random Post on Refresh
 * Description: Show a random post on every page load.
 * Plugin URI: http://wpscholar.com/wordpress-plugins/random-post-on-refresh/
 * Version: 1.2.3
 * Author: Micah Wood
 * Author URI: https://wpscholar.com
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: random-post-on-refresh
 * Domain Path: /languages
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright 2018-2025 by Micah Wood - All rights reserved.
 *
 * @package RandomPostOnRefresh
 */

if ( ! class_exists( 'RandomPostOnRefresh' ) ) {

	/**
	 * Class RandomPostOnRefresh
	 */
	class RandomPostOnRefresh {

		const SHORTCODE = 'random_post_on_refresh';

		const DEFAULT_ATTRIBUTES = array(
			'author'         => '',
			'class'          => '',
			'ids'            => '',
			'image_required' => 'true',
			'not'            => '',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_type'      => 'post',
			'posts_per_page' => 100,
			'search'         => '',
			'show'           => 'title, image, excerpt',
			'size'           => 'large',
			'taxonomy'       => '',
			'terms'          => '',
		);

		/**
		 * Initialize the plugin.
		 */
		public static function initialize() {
			add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
			add_filter( 'widget_text', 'do_shortcode' );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
			add_shortcode( self::SHORTCODE, array( __CLASS__, 'shortcode' ) );
		}

		/**
		 * Load the textdomain.
		 */
		public static function load_textdomain() {
			load_plugin_textdomain( 'random-post-on-refresh', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Enqueue style.
		 */
		public static function wp_enqueue_scripts() {
			$plugin_version = get_file_data( __FILE__, array( 'Version' ), 'plugin' );
			wp_register_style( self::SHORTCODE, plugins_url( '/assets/random-post-on-refresh.css', __FILE__ ), array(), $plugin_version );
		}

		/**
		 * Shortcode handler
		 *
		 * @param array $atts Shortcode attributes
		 *
		 * @return bool|string
		 */
		public static function shortcode( $atts ) {

			wp_enqueue_style( self::SHORTCODE );

			$atts = shortcode_atts(
				self::DEFAULT_ATTRIBUTES,
				array_change_key_case( array_filter( (array) $atts ), CASE_LOWER ),
				self::SHORTCODE
			);

			$image_size = $atts['size'];

			$groups = array_filter(
				array_map(
					function ( $group ) {
						return self::list_to_array( $group );
					},
					self::list_to_array( $atts['show'], '|' )
				)
			);

			$can_show = array( 'title', 'image', 'excerpt', 'content' );
			$show     = array_merge( ...$groups );

			$show_title   = in_array( 'title', $show, true );
			$show_image   = in_array( 'image', $show, true );
			$show_excerpt = in_array( 'excerpt', $show, true );
			$show_content = in_array( 'content', $show, true );

			$image_required = wp_validate_boolean( $atts['image_required'] );

			// Check for featured image support
			if ( $show_image && ! current_theme_supports( 'post-thumbnails' ) ) {
				return self::error(
					__( 'Sorry, your theme does not support featured images. Update the "show" attribute to exclude the "image" option.', 'random-post-on-refresh' ),
					'[' . self::SHORTCODE . ' show="title, excerpt"]'
				);
			}

			// Taxonomy validation
			if ( ! empty( $atts['taxonomy'] ) && ! taxonomy_exists( $atts['taxonomy'] ) ) {
				return self::error(
					sprintf(
					// Translators: %1$s is replaced with taxonomy shortcode argument and %2$s is replaced with a comma-separated list of available taxonomies.
						__( 'Sorry, taxonomy "%1$s" is invalid. Valid options are: %2$s. Please check your shortcode implementation.', 'random-post-on-refresh' ),
						$atts['taxonomy'],
						implode( ', ', get_taxonomies() )
					),
					'[' . self::SHORTCODE . ' taxonomy="' . $atts['taxonomy'] . '"]'
				);
			}

			// Taxonomy/term attribute validation
			if ( ! empty( $atts['terms'] ) && empty( $atts['taxonomy'] ) ) {
				return self::error(
					__( 'Sorry, you cannot use the terms attribute without the taxonomy attribute. Please check your shortcode implementation.', 'random-post-on-refresh' ),
					'[' . self::SHORTCODE . ' terms="' . $atts['terms'] . '"]'
				);
			}

			if ( empty( $atts['terms'] ) && ! empty( $atts['taxonomy'] ) ) {
				return self::error(
					__( 'Sorry, you cannot use the taxonomy attribute without the terms attribute. Please check your shortcode implementation.', 'random-post-on-refresh' ),
					'[' . self::SHORTCODE . ' taxonomy="' . $atts['taxonomy'] . '"]'
				);
			}

			// Post type validation
			$post_types = array_filter( array_map( 'trim', explode( ',', $atts['post_type'] ) ) );

			foreach ( $post_types as $post_type ) {
				if ( ! post_type_exists( $post_type ) ) {
					return self::error(
						sprintf(
						// Translators: %1$s is replaced with post_type shortcode argument and %2$s is replaced with a comma-separated list of available post types.
							__( 'Sorry, post type "%1$s" is invalid. Valid options are: %2$s. Please check your shortcode implementation.', 'random-post-on-refresh' ),
							$post_type,
							implode( ', ', get_post_types( array( 'public' => true ) ) )
						),
						'[' . self::SHORTCODE . ' post_type="' . $atts['post_type'] . '"]'
					);
				}
			}

			// Build query args using the new method
			$query_args = self::build_query_args( $atts );

			$query = new WP_Query( $query_args );

			if ( ! $query->have_posts() ) {
				return self::error(
					__( 'Sorry, no matching posts were found. Your query may be too restrictive. Please check your shortcode implementation.', 'random-post-on-refresh' ) .
					( $image_required ? ' ' . __( 'Currently, only posts with featured images will be shown. Perhaps try setting the "image_required" property to "false"?', 'random-post-on-refresh' ) : '' )
				);
			}

			$posts = $query->posts;

			/**
			 * The randomly selected post.
			 *
			 * @var WP_Post $post
			 */
			$post = $posts[ array_rand( $posts ) ];

			if ( $show_image && $image_required && ! has_post_thumbnail( $post ) ) {
				return self::error(
					__( 'Sorry, the selected post does not have a featured image.', 'random-post-on-refresh' )
				);
			}

			$display = array();
			foreach ( $groups as $items ) {
				if ( count( $groups ) > 1 ) {
					$display[] = '<span class="random-post-on-refresh__group">';
				}
				foreach ( $items as $item ) {
					if ( in_array( $item, $can_show, true ) ) {
						switch ( $item ) {
							case 'title':
								$display['title'] = $show_title ? sprintf( '<span class="random-post-on-refresh__title">%s</span>', esc_html( get_the_title( $post ) ) ) : '';
								break;
							case 'image':
								$display['image'] = $show_image && has_post_thumbnail( $post ) ? sprintf( '<span class="random-post-on-refresh__image">%s</span>', get_the_post_thumbnail( $post, $image_size ) ) : '';
								break;
							case 'excerpt':
								$display['excerpt'] = $show_excerpt ? sprintf( '<span class="random-post-on-refresh__excerpt">%s</span>', self::get_the_excerpt( $post ) ) : '';
								break;
							case 'content':
								$display['content'] = $show_content ? sprintf( '<span class="random-post-on-refresh__content">%s</span>', apply_filters( 'the_content', wp_kses_post( $post->post_content ) ) ) : '';
								break;
						}
					}
				}
				if ( count( $groups ) > 1 ) {
					$display[] = '</span>';
				}
			}

			return sprintf(
				'<div class="random-post-on-refresh %s"><a href="%s">%s</a></div>',
				esc_attr(
					implode(
						' ',
						array_filter(
							array(
								count( $groups ) > 1 ? '--has-groups' : '',
								$atts['class'],
							)
						)
					)
				),
				esc_url( get_the_permalink( $post ) ),
				implode( '', array_filter( $display ) )
			);
		}

		/**
		 * Build WP_Query arguments from shortcode attributes.
		 *
		 * @param array $atts Shortcode attributes
		 * @return array Query arguments for WP_Query
		 */
		public static function build_query_args( $atts ) {
			$post_types = array_filter( array_map( 'trim', explode( ',', $atts['post_type'] ) ) );

			$orderby = $atts['orderby'];

			// Just in case someone uses "random" instead of "rand".
			if ( 'random' === $orderby ) {
				$orderby = 'rand';
			}

			$query_args = array(
				'post_type'      => $post_types,
				'posts_per_page' => absint( $atts['posts_per_page'] ),
				'orderby'        => $orderby,
				'order'          => strtoupper( $atts['order'] ) === 'ASC' ? 'ASC' : 'DESC',
			);

			if ( ! empty( $atts['author'] ) ) {
				$query_args['author__in'] = self::parse_id_list( $atts['author'] );
			}

			if ( ! empty( $atts['ids'] ) ) {
				$query_args['post__in'] = self::parse_id_list( $atts['ids'] );
			}

			if ( ! empty( $atts['not'] ) ) {
				$query_args['post__not_in'] = self::parse_id_list( $atts['not'] );
			}

			if ( ! empty( $atts['search'] ) ) {
				$query_args['s'] = $atts['search'];
			}

			if ( ! empty( $atts['taxonomy'] ) && ! empty( $atts['terms'] ) ) {
				$terms = self::parse_id_list( $atts['terms'] );
				if ( 'category' === $atts['taxonomy'] ) {
					$query_args['category__in'] = $terms;
				} elseif ( 'post_tag' === $atts['taxonomy'] ) {
					$query_args['tag__in'] = $terms;
				} else {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => $atts['taxonomy'],
							'field'    => 'term_id',
							'terms'    => $terms,
						),
					);
				}
			}

			// Only fetch posts with images?
			if ( ! empty( $atts['show'] ) && strpos( $atts['show'], 'image' ) !== false && wp_validate_boolean( $atts['image_required'] ) ) {
				$query_args['meta_query'] = array( array( 'key' => '_thumbnail_id' ) );
			}

			// Never load the current post.
			$query_args['post__not_in'][] = get_the_ID();

			$query_args = apply_filters( 'random_post_on_refresh_query_args', $query_args, $atts );

			return $query_args;
		}

		/**
		 * Parse an ID list into an array.
		 *
		 * @param string $id_list A comma separated list of IDs
		 *
		 * @return int[]
		 */
		public static function parse_id_list( $id_list ) {
			$ids = array();
			if ( ! empty( $id_list ) ) {
				$ids = array_values( array_filter( array_map( 'absint', explode( ',', preg_replace( '#[^0-9,]#', '', $id_list ) ) ) ) );
			}

			return $ids;
		}

		/**
		 * Convert a list (string) to an array
		 *
		 * @param string $separated_list A delimiter separated list of items
		 * @param string $delimiter The delimiter used to separate items.
		 *
		 * @return array
		 */
		public static function list_to_array( $separated_list, $delimiter = ',' ) {
			return array_filter( array_map( 'trim', explode( $delimiter, $separated_list ) ) );
		}

		/**
		 * Get the excerpt for a specific post (outside of the loop).
		 *
		 * @param WP_Post $post The WordPress post.
		 *
		 * @return string
		 */
		public static function get_the_excerpt( WP_Post $post ) {
			return (string) apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $post->post_excerpt, $post ) );
		}

		/**
		 * Setup error message.
		 *
		 * @param string $message The error message to display.
		 *
		 * @param string $example (optional) An example shortcode usage.
		 *
		 * @return string
		 */
		public static function error( $message, $example = '' ) {

			if ( current_user_can( 'edit_posts', get_the_ID() ) ) {

				return sprintf(
					'<div class="random-post-on-refresh-error"><p>%1$s</p>%2$s<p>%3$s</p><p>%4$s</p></div>',
					esc_html( $message ),
					empty( $example ) ? '' : '<p>' . esc_html( $example ) . '</p>',
					sprintf(
						'<a href="%1$s" target="_blank" rel="noreferrer noopener">%2$s</a>',
						'https://wordpress.org/plugins/random-post-on-refresh/#faq-header',
						__( 'Consult the documentation', 'random-post-on-refresh' )
					),
					esc_html__( 'Note: This helpful notification is only visible to logged in users who can edit this shortcode.', 'random-post-on-refresh' )
				);

			}

			return '';
		}
	}

	add_action( 'plugins_loaded', array( 'RandomPostOnRefresh', 'initialize' ) );

}