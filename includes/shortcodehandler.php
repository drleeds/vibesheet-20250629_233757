<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function blog_list_shortcode_callback( $atts ) {
	$defaults = array(
		'posts_per_page' => 10,
		'category'       => '',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'pagination'     => 'false',
	);
	$atts = shortcode_atts( $defaults, $atts, 'blog_list' );

	$posts_per_page = max( 1, intval( $atts['posts_per_page'] ) );
	$category       = sanitize_text_field( trim( $atts['category'] ) );

	$allowed_orderbys = array( 'date', 'title', 'rand' );
	$orderby_raw      = strtolower( sanitize_key( $atts['orderby'] ) );
	$orderby          = in_array( $orderby_raw, $allowed_orderbys, true ) ? $orderby_raw : $defaults['orderby'];

	$order      = strtoupper( $atts['order'] ) === 'ASC' ? 'ASC' : 'DESC';
	$pagination = filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN );

	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	if ( $category !== '' ) {
		$cat_key = is_numeric( $category ) ? 'cat_' . intval( $category ) : 'category_' . $category;
	} else {
		$cat_key = 'cat_all';
	}

	$cache_key = 'blog_list_' . md5(
		'pp:' . $posts_per_page .
		'|cat:' . $cat_key .
		'|orderby:' . $orderby .
		'|order:' . $order .
		'|pagination:' . ( $pagination ? '1' : '0' ) .
		'|page:' . $paged
	);

	$settings        = get_option( 'blog_list_settings', array() );
	$cache_duration  = isset( $settings['cache_duration'] ) ? intval( $settings['cache_duration'] ) : HOUR_IN_SECONDS;
	if ( $cache_duration <= 0 ) {
		$cache_duration = HOUR_IN_SECONDS;
	}

	$output = get_transient( $cache_key );

	if ( false === $output ) {
		$query_args = array(
			'post_type'      => 'post',
			'posts_per_page' => $posts_per_page,
			'orderby'        => $orderby,
			'order'          => $order,
			'paged'          => $pagination ? $paged : 1,
		);

		if ( $category !== '' ) {
			if ( is_numeric( $category ) ) {
				$query_args['cat'] = intval( $category );
			} else {
				$query_args['category_name'] = $category;
			}
		}

		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {
			ob_start();
			echo '<ul class="blog-list-shortcode">';
			while ( $query->have_posts() ) {
				$query->the_post();
				echo '<li>';
				echo '<a href="' . esc_url( get_permalink() ) . '">';
				echo esc_html( get_the_title() );
				echo '</a>';
				echo '</li>';
			}
			echo '</ul>';

			if ( $pagination && $query->max_num_pages > 1 ) {
				$big = 999999999;
				$pagination_links = paginate_links( array(
					'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'    => '?paged=%#%',
					'current'   => $paged,
					'total'     => $query->max_num_pages,
					'type'      => 'list',
				) );
				if ( $pagination_links ) {
					echo '<nav class="blog-list-pagination" aria-label="' . esc_attr__( 'Blog list pagination', 'blog-list' ) . '">';
					echo $pagination_links;
					echo '</nav>';
				}
			}

			$output = ob_get_clean();
		} else {
			$output = '<p class="blog-list-no-posts">' . esc_html__( 'No posts found.', 'blog-list' ) . '</p>';
		}

		wp_reset_postdata();
		set_transient( $cache_key, $output, $cache_duration );
	}

	return $output;
}
?>