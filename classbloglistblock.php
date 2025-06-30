<?php
class Blog_List_Block {

    public function __construct() {
        add_action( 'init', [ $this, 'register' ] );
        add_action( 'save_post', [ $this, 'clearCache' ] );
    }

    public function register() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        $dir_url  = plugin_dir_url( __FILE__ );
        $dir_path = plugin_dir_path( __FILE__ );

        // Register editor assets.
        wp_register_script(
            'blog-list-block-editor-script',
            $dir_url . 'blockregistration.js',
            [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-block-editor' ],
            filemtime( $dir_path . 'blockregistration.js' ),
            true
        );
        wp_register_style(
            'blog-list-block-editor-style',
            $dir_url . 'editor.css',
            [ 'wp-edit-blocks' ],
            filemtime( $dir_path . 'editor.css' )
        );

        // Register frontend assets.
        wp_register_style(
            'blog-list-block-style',
            $dir_url . 'style.css',
            [],
            filemtime( $dir_path . 'style.css' )
        );
        wp_register_script(
            'blog-list-block-frontend-script',
            $dir_url . 'blockregistration.js',
            [ 'jquery' ],
            filemtime( $dir_path . 'blockregistration.js' ),
            true
        );

        register_block_type( 'blog-list/block', [
            'attributes'      => [
                'postsToShow' => [ 'type' => 'number',  'default' => 5 ],
                'categories'  => [ 'type' => 'array',   'items' => [ 'type' => 'number' ], 'default' => [] ],
                'showExcerpt' => [ 'type' => 'boolean', 'default' => true ],
                'paginate'    => [ 'type' => 'boolean', 'default' => false ],
                'orderBy'     => [ 'type' => 'string',  'default' => 'date' ],
                'order'       => [ 'type' => 'string',  'default' => 'desc' ],
            ],
            'editor_script'   => 'blog-list-block-editor-script',
            'editor_style'    => 'blog-list-block-editor-style',
            'style'           => 'blog-list-block-style',
            'script'          => 'blog-list-block-frontend-script',
            'render_callback' => [ $this, 'renderCallback' ],
        ] );
    }

    public function clearCache( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        global $wpdb;
        // Find all transients and timeouts with our key prefix.
        $pattern           = 'blb_%';
        $like_transient    = $wpdb->esc_like( '_transient_' . $pattern );
        $like_timeout      = $wpdb->esc_like( '_transient_timeout_' . $pattern );
        $transients        = $wpdb->get_col( $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $like_transient,
            $like_timeout
        ) );
        foreach ( $transients as $transient ) {
            // strip both prefixes
            $key = preg_replace( '/^_transient_timeout_|^_transient_/', '', $transient );
            delete_transient( $key );
        }
    }

    public function renderCallback( $attributes ) {
        $atts = wp_parse_args( $attributes, [
            'postsToShow' => 5,
            'categories'  => [],
            'showExcerpt' => true,
            'paginate'    => false,
            'orderBy'     => 'date',
            'order'       => 'desc',
        ] );

        $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

        // Determine caching for users.
        $use_cache   = ! is_user_logged_in();
        if ( $use_cache ) {
            $current_user = wp_get_current_user();
            $role_string  = implode( ',', $current_user->roles );
            $cache_key    = 'blb_' . substr( md5( serialize( $atts ) . $paged . $role_string ), 0, 20 );
            if ( false !== ( $cached = get_transient( $cache_key ) ) ) {
                return $cached;
            }
        }

        $query_args = [
            'posts_per_page' => $atts['postsToShow'],
            'orderby'        => $atts['orderBy'],
            'order'          => $atts['order'],
            'paged'          => $paged,
        ];

        if ( ! empty( $atts['categories'] ) ) {
            $query_args['category__in'] = array_map( 'absint', $atts['categories'] );
        }

        $query = new WP_Query( $query_args );

        ob_start();
        echo '<div class="blog-list-block">';
        if ( $query->have_posts() ) {
            echo '<ul class="blog-list-items">';
            while ( $query->have_posts() ) {
                $query->the_post();
                echo '<li class="blog-list-item">';
                echo '<a href="' . esc_url( get_permalink() ) . '" class="blog-list-link">' . esc_html( get_the_title() ) . '</a>';
                if ( $atts['showExcerpt'] ) {
                    echo '<p class="blog-list-excerpt">' . esc_html( get_the_excerpt() ) . '</p>';
                }
                echo '</li>';
            }
            echo '</ul>';
            if ( $atts['paginate'] ) {
                $big = 999999999;
                $pagination = paginate_links( [
                    'base'     => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                    'format'   => '?paged=%#%',
                    'current'  => $paged,
                    'total'    => $query->max_num_pages,
                    'type'     => 'list',
                ] );
                if ( $pagination ) {
                    echo '<nav class="blog-list-pagination">' . wp_kses_post( $pagination ) . '</nav>';
                }
            }
        } else {
            echo '<p class="blog-list-no-posts">' . esc_html__( 'No posts found.', 'blog-list' ) . '</p>';
        }
        echo '</div>';
        wp_reset_postdata();

        $output = ob_get_clean();

        if ( $use_cache ) {
            set_transient( $cache_key, $output, HOUR_IN_SECONDS * 12 );
        }

        return $output;
    }
}

new Blog_List_Block();