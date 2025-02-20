<?php

/**
 * Is the given post is the main single post?
 * Works for any post type, including attachments and pages
 *
 * @param mixed $post Post ID, title, slug, or array of such.
 * @param mixed $post_types Optional. Post Type or array of Post Types
 * @return boolean
 * @since 1.3
 */
function momtaz_is_the_single( $post = false, $post_types = '' ) {

	$retval = false;

	if ( is_singular( $post_types ) ) {

		if ( ! empty( $post ) ) {

			$post = (array) $post;
			$post_obj = get_queried_object();

			if ( in_array( $post_obj->ID, $post, true ) ){
				$retval = true;
			} elseif ( in_array( $post_obj->post_title, $post, true ) ) {
				$retval = true;
			} elseif ( in_array( $post_obj->post_name, $post, true ) ) {
				$retval = true;
			}

		} else {

			$retval = true;

		}

	}

	return $retval;

}

/**
 * Display the post class attribute.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 * @since 1.1
 */
function momtaz_post_class( $class = '', $post_id = 0 ) {

	// Get post classes array.
	$classes = momtaz_get_post_class( $class, $post_id );

	// Display the `class` attribute.
	momtaz_html_atts( array( 'class' => $classes ) );

}

/**
 * Creates a set of classes for each site entry upon display. Each entry is given the class of
 * 'hentry'. Posts are given category, tag, and author classes. Alternate post classes of odd,
 * even, and alt are added.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param int $post_id An optional post ID.
 * @return array
 * @since 1.1
 */
function momtaz_get_post_class( $class = '', $post_id = 0 ) {

	$classes = array();

	// Get post object
	$post = get_post( $post_id );

	if ( empty( $post ) ) {
		return $classes;
	}

	// hAtom compliance.
	$classes[] = 'hentry';

	// Get post context.
	$context = momtaz_get_post_context( $post_id );

	// Merge the classes array with post context.
	$classes = array_merge( $classes, (array) $context );

	// Post taxonomies
	$post_taxonomies = get_post_taxonomies( $post );
	if ( ! empty( $post_taxonomies ) ){
		foreach ( $post_taxonomies as $taxonomy ) {
			$terms = get_the_terms( $post->ID, $taxonomy );
			if ( ! empty( $terms ) ) {
				foreach( $terms as $term ) {
					$classes[] = 'term-'. sanitize_html_class( $term->slug, $term->term_id );
				}
			}
		}
	}

	// Sticky posts.
	if ( is_home() && ! is_paged() && is_sticky( $post->ID ) ) {
		$classes[] = 'sticky';
	}

	// Is this post protected by a password?
	if ( post_password_required( $post ) ) {
		$classes[] = 'post-password-required';
	}

	// Post alt class.
	if ( ! momtaz_is_the_single( $post ) ) {
		static $post_alt = 0;
		$classes[] = 'set-' . ++$post_alt;
		$classes[] = ( $post_alt % 2 ) ? 'odd' : 'even';
	}

	// Has a custom excerpt?
	if ( has_excerpt( $post ) ) {
		$classes[] = 'has-excerpt';
	}

	// Custom classes.
	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_merge( $classes, $class );
	}

	// Apply the WordPress filters.
	$classes = apply_filters( 'post_class', $classes, $class, $post->ID );

	// Apply the Momtaz FW filters.
	$classes = apply_filters( 'momtaz_get_post_class', $classes, $post );

	// Removes any duplicate and empty classes.
	$classes = array_unique( array_filter( $classes ) );

	return $classes;

}

/**
 * Displays a "Continue Reading" link for excerpts.
 *
 * @return void
 * @since 1.3
 */
function momtaz_continue_reading_link( $post_id = 0, array $args = array() ) {
	echo momtaz_get_continue_reading_link( $post_id, $args );
}

/**
 * Returns a "Continue Reading" link for excerpts.
 *
 * @return string
 * @since 1.3
 */
function momtaz_get_continue_reading_link( $post_id = 0, array $args = array() ) {

	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	$args = array_merge( array(
		'text'  => __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'momtaz' ),
		'atts'  => array(
			'href'  => get_permalink( $post_id ),
			'class' => 'more-link',
		),
	), $args );

	$link = '<a' . momtaz_get_html_atts( $args['atts'] ) . '>' . $args['text'] . '</a>';
	return apply_filters( 'momtaz_continue_reading_link', $link, $post_id, $args );

}