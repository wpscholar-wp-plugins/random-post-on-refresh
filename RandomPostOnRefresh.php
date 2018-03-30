<?php

/*
 * Plugin Name: Random Post on Refresh
 * Description: Show a random post on every page load.
 * Plugin URI: http://wpscholar.com/wordpress-plugins/random-post-on-refresh/
 * Author: Micah Wood
 * Author URI: http://wpscholar.com
 * Version: 1.0
 * Text Domain: random-post-on-refresh
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright 2018 by Micah Wood - All rights reserved.
 */

if ( ! class_exists( 'RandomPostOnRefresh' ) ) {

	/**
	 * Class RandomPostOnRefresh
	 */
	class RandomPostOnRefresh {

		const SHORTCODE = 'random_post_on_refresh';

		/**
		 * Initialize the plugin.
		 */
		public static function initialize() {
			load_plugin_textdomain( 'random-post-on-refresh', false, __DIR__ . '/languages' );
			add_filter( 'widget_text', 'do_shortcode' );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
			add_shortcode( self::SHORTCODE, array( __CLASS__, 'shortcode' ) );
		}

		public static function wp_enqueue_scripts() {
			wp_register_style( self::SHORTCODE, plugins_url( '/assets/random-post-on-refresh.css', __FILE__ ) );
		}

		/**
		 * Shortcode handler
		 *
		 * @param array $atts
		 *
		 * @return bool|string
		 */
		public static function shortcode( $atts ) {

			wp_enqueue_style( self::SHORTCODE );

			$atts = shortcode_atts(
				array(
					'author'    => '',
					'ids'       => '',
					'not'       => '',
					'post_type' => 'post',
					'search'    => '',
					'taxonomy'  => '',
					'terms'     => '',
					'class'     => '',
					'size'      => 'large',
					'show'      => 'title, image, excerpt',
				),
				array_change_key_case( array_filter( (array) $atts ), CASE_LOWER ),
				self::SHORTCODE
			);

			$image_size = $atts['size'];

			$groups = array_filter( array_map( function ( $group ) {
				return self::list_to_array( $group );
			}, self::list_to_array( $atts['show'], '|' ) ) );

			$can_show = [ 'title', 'image', 'excerpt', 'content' ];
			$show = array_merge( ...$groups );

			$show_title = in_array( 'title', $show, true );
			$show_image = in_array( 'image', $show, true );
			$show_excerpt = in_array( 'excerpt', $show, true );
			$show_content = in_array( 'content', $show, true );

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
					sprintf( __( 'Sorry, taxonomy "%s" is invalid. Valid options are: %s. Please check your shortcode implementation.', 'random-post-on-refresh' ),
						$atts['taxonomy'],
						implode( ', ', get_taxonomies() )
					),
					'[' . self::SHORTCODE . ' taxonomy="' . $atts['taxonomy'] . '"]'
				);
			}

			// Taxonomy/term attribute validation
			if ( ! empty( $atts['terms'] ) && empty( $atts['taxonomy'] ) ) {
				return self::error(
					sprintf( __( 'Sorry, you cannot use the terms attribute without the taxonomy attribute. Please check your shortcode implementation.', 'random-post-on-refresh' ), $post_type ),
					'[' . self::SHORTCODE . ' terms="' . $atts['terms'] . '"]'
				);
			}

			if ( empty( $atts['terms'] ) && ! empty( $atts['taxonomy'] ) ) {
				return self::error(
					sprintf( __( 'Sorry, you cannot use the taxonomy attribute without the terms attribute. Please check your shortcode implementation.', 'random-post-on-refresh' ), $post_type ),
					'[' . self::SHORTCODE . ' taxonomy="' . $atts['taxonomy'] . '"]'
				);
			}

			// Post type validation
			$post_types = array_filter( array_map( 'trim', explode( ',', $atts['post_type'] ) ) );

			foreach ( $post_types as $post_type ) {
				if ( ! post_type_exists( $post_type ) ) {
					return self::error(
						sprintf(
							__( 'Sorry, post type "%s" is invalid. Valid options are: %s. Please check your shortcode implementation.', 'random-post-on-refresh' ),
							$post_type,
							implode( ', ', get_post_types( [ 'public' => true ] ) )
						),
						'[' . self::SHORTCODE . ' post_type="' . $atts['post_type'] . '"]'
					);
				}
			}

			$query_args = [
				'post_type' => $post_types,
			];

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
				$query_args['s'] = self::parse_id_list( $atts['search'] );
			}

			if ( ! empty( $atts['taxonomy'] ) && ! empty( $atts['terms'] ) ) {
				$terms = self::parse_id_list( $atts['terms'] );
				if ( 'category' === $atts['taxonomy'] ) {
					$query_args['category__in'] = $terms;
				} else if ( 'post_tag' === $atts['taxonomy'] ) {
					$query_args['tag__in'] = $terms;
				} else {
					$query_args['tax_query'] = [
						'taxonomy' => $atts['taxonomy'],
						'terms'    => self::parse_id_list( $atts['terms'] ),
					];
				}
			}

			if ( $show_image ) {
				$query_args['meta_query'] = [ [ 'key' => '_thumbnail_id' ] ];
			}

			$query = new WP_Query( $query_args );

			if ( ! $query->have_posts() ) {
				return self::error(
					__( 'Sorry, no matching posts were found. Your query may be too restrictive. Please check your shortcode implementation.', 'random-post-on-refresh' )
				);
			}

			$posts = $query->posts;

			/**
			 * @var WP_Post $post
			 */
			$post = $posts[ array_rand( $posts ) ];

			if ( $show_image && ! has_post_thumbnail( $post ) ) {
				return self::error(
					__( 'Sorry, the selected post does not have a featured image.', 'random-post-on-refresh' )
				);
			}

			$display = [];
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
								$display['image'] = $show_image ? sprintf( '<span class="random-post-on-refresh__image">%s</span>', get_the_post_thumbnail( $post, $image_size ) ) : '';
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
				esc_attr( implode( ' ', array_filter( [
					count( $groups ) > 1 ? '--has-groups' : '',
					$atts['class']
				] ) ) ),
				esc_url( get_the_permalink( $post ) ),
				implode( '', array_filter( $display ) )
			);
		}

		/**
		 * Parse an ID list into an array.
		 *
		 * @param string $list
		 *
		 * @return int[]
		 */
		public static function parse_id_list( $list ) {
			$ids = array();
			if ( ! empty( $list ) ) {
				$ids = array_filter( array_map( 'absint', explode( ',', preg_replace( '#[^0-9,]#', '', $list ) ) ) );
			}

			return $ids;
		}

		/**
		 * Convert a list (string) to an array
		 *
		 * @param string $list
		 * @param string $delimiter
		 *
		 * @return array
		 */
		public static function list_to_array( $list, $delimiter = ',' ) {
			return array_filter( array_map( 'trim', explode( $delimiter, $list ) ) );
		}

		/**
		 * Get the excerpt for a specific post (outside of the loop).
		 *
		 * @param WP_Post $post
		 *
		 * @return string
		 */
		public static function get_the_excerpt( WP_Post $post ) {
			$excerpt = empty( $post->post_excerpt ) ? $post->post_content : $post->post_excerpt;

			return (string) apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', wp_kses_post( $excerpt ) ) );
		}

		/**
		 * Setup error message.
		 *
		 * @param string $message
		 *
		 * @param string $example
		 *
		 * @return string
		 */
		public static function error( $message, $example = '' ) {

			if ( current_user_can( 'edit_posts', get_the_ID() ) ) {

				return sprintf(
					'<div class="random-post-on-refresh-error"><p>%s</p>%s<p>%s</p></div>',
					esc_html( $message ),
					empty( $example ) ? '' : '<p>' . esc_html( $example ) . '</p>',
					esc_html( 'Note: This helpful notification is only visible to logged in users who can edit this shortcode.' )
				);

			}

			return '';

		}

	}

	RandomPostOnRefresh::initialize();

}