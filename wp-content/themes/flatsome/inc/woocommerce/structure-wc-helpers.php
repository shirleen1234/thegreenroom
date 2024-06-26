<?php
// Get Product Lists
function ux_list_products( $args ) {

	if ( isset( $args ) ) {
		$options = $args;

		$number = 8;
		if ( isset( $options['products'] ) ) {
			$number = $options['products'];
		}

		$show = ''; // featured, onsale.
		if ( isset( $options['show'] ) ) {
			$show = $options['show'];
		}

		$page_number = '1';
		if ( isset( $options['page_number'] ) ) {
			$page_number = $options['page_number'];
		}

		$orderby = 'date';
		$order   = 'desc';
		if ( isset( $options['orderby'] ) ) {
			$orderby = $options['orderby'];
		}
		if ( isset( $options['order'] ) ) {
			$order = $options['order'];
		}
		if ( $orderby == 'menu_order' ) {
			$order = 'asc';
		}

		$tags = array();
		if ( isset( $options['tags'] ) ) {
			$_tags = array_filter( array_map( 'trim', explode( ',', $options['tags'] ) ) );
			$tags  = array_map( function ( $tag ) {
				if ( is_numeric( $tag ) ) {
					$term = get_term( $tag );
					if ( $term instanceof WP_Term ) {
						return $term->slug;
					}
				}

				return $tag;
			}, $_tags );
		}

		$offset = '';
		if ( isset( $options['offset'] ) ) {
			$offset = $options['offset'];

			$found_posts_filter_callback = function ( $found_posts, $query ) use ( $offset ) {
				return $found_posts - (int) $offset;
			};

			add_filter( 'found_posts', $found_posts_filter_callback, 1, 2 );
		}
	} else {
		return false;
	}

	$offset = (int) $page_number > 1
		? (int) $offset + ( (int) $page_number - 1 ) * (int) $number
		: $offset;

	$query_args = array(
		'posts_per_page'      => $number,
		'post_status'         => 'publish',
		'post_type'           => 'product',
		'paged'               => $page_number,
		'ignore_sticky_posts' => 1,
		'order'               => $order,
		'product_tag'         => $tags,
		'offset'              => $offset,
		'meta_query'          => WC()->query->get_meta_query(), // @codingStandardsIgnoreLine
		'tax_query'           => WC()->query->get_tax_query(), // @codingStandardsIgnoreLine
	);

	switch ( $show ) {
		case 'featured':
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
				'operator' => 'IN',
			);
			break;
		case 'onsale':
			$query_args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			break;
	}

	switch ( $orderby ) {
		case 'menu_order':
			$query_args['orderby'] = 'menu_order';
			break;
		case 'title':
			$query_args['orderby'] = 'name';
			break;
		case 'date':
			$query_args['orderby'] = 'date';
			break;
		case 'price':
			$query_args['meta_key'] = '_price'; // @codingStandardsIgnoreLine
			$query_args['orderby']  = 'meta_value_num';
			break;
		case 'rand':
			$query_args['orderby'] = 'rand'; // @codingStandardsIgnoreLine
			break;
		case 'sales':
			$query_args['meta_key'] = 'total_sales'; // @codingStandardsIgnoreLine
			$query_args['orderby']  = 'meta_value_num';
			break;
		default:
			$query_args['orderby'] = 'date';
	}

	$query_args = ux_maybe_add_category_args( $query_args, $options['cat'], 'IN' );

	if ( isset( $options['out_of_stock'] ) && $options['out_of_stock'] === 'exclude' ) {
		$product_visibility_term_ids = wc_get_product_visibility_term_ids();
		$query_args['tax_query'][]   = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'term_taxonomy_id',
			'terms'    => $product_visibility_term_ids['outofstock'],
			'operator' => 'NOT IN',
		);
	}

	$results = new WP_Query( $query_args );

	if ( isset( $found_posts_filter_callback ) ) {
		remove_filter( 'found_posts', $found_posts_filter_callback, 1 );
	}

	return $results;
} // List products

/**
 * Set categories query args if not empty.
 *
 * @param array  $query_args Query args.
 * @param string $category   Shortcode category attribute value.
 * @param string $operator   Query Operator.
 *
 * @return array $query_args
 */
function ux_maybe_add_category_args( $query_args, $category, $operator ) {
	if ( ! empty( $category ) ) {

		if ( empty( $query_args['tax_query'] ) ) {
			$query_args['tax_query'] = array(); // @codingStandardsIgnoreLine
		}

		$categories = array_map( 'sanitize_title', explode( ',', $category ) );
		$field      = 'slug';

		if ( is_numeric( $categories[0] ) ) {
			$field      = 'term_id';
			$categories = array_map( 'absint', $categories );
			// Check numeric slugs.
			foreach ( $categories as $cat ) {
				$the_cat = get_term_by( 'slug', $cat, 'product_cat' );
				if ( false !== $the_cat ) {
					$categories[] = $the_cat->term_id;
				}
			}
		}

		$query_args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'terms'    => $categories,
			'field'    => $field,
			'operator' => $operator,
		);
	}

	return $query_args;
}

global $pagenow;
if ( ! get_theme_mod( 'activated_before' ) && is_admin() && isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) {
	/**
	 * Set Default WooCommerce Image sizes upon theme activation.
	 */
	function flatsome_woocommerce_image_dimensions() {
		$single  = array(
			'width'  => '510',
			'height' => '600',
			'crop'   => 1,
		);
		$catalog = array(
			'width'  => '247',
			'height' => '300',
			'crop'   => 1,
		);

		update_option( 'woocommerce_single_image_width', $single['width'] );
		update_option( 'woocommerce_thumbnail_image_width', $catalog['width'] );
		update_option( 'woocommerce_thumbnail_cropping', 'custom' );
		update_option( 'woocommerce_thumbnail_cropping_custom_width', 5 );
		update_option( 'woocommerce_thumbnail_cropping_custom_height', 6 );

		// Mark customize store as completed (WC 8.8).
		update_option( 'woocommerce_admin_customize_store_completed', 'yes' );
	}

	add_action( 'init', 'flatsome_woocommerce_image_dimensions', 1 );

	/**
	 * Set a theme mod to retrieve first activation state from.
	 */
	function flatsome_first_activation_state() {
		if ( ! get_theme_mod( 'activated_before' ) ) {
			set_theme_mod( 'activated_before', true );
		}
	}

	add_action( 'shutdown', 'flatsome_first_activation_state' );
}
