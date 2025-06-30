<?php
class BlogList_Shortcode {

    /**
     * Allowed values for 'orderby' attribute.
     *
     * @var string[]
     */
    protected $allowed_orderby = array(
        'date',
        'title',
        'rand',
        'author',
        'ID',
        'menu_order',
        'meta_value',
        'comment_count',
        'post_date',
        'post_title',
        'modified',
    );

    // Constructor and self-registration removed. Instantiation and registration will be handled by the main plugin class.

    /**
     * Shortcode callback.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function callback( $atts ) {
        $defaults = array(
            'posts_per_page' => 5,
            'category'       => '',
            'order'          => 'DESC',
            'orderby'        => 'date',
            'pagination'     => true,
            'cache_time'     => 3600,
        );
        $atts = shortcode_atts( $defaults, $atts, 'blog_list' );

        // Sanitize and validate attributes.
        $atts['posts_per_page'] = absint( $atts['posts_per_page'] );
        $atts['order']          = in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $atts['order'] ) : $defaults['order'];
        $atts['orderby']        = sanitize_text_field( $atts['orderby'] );
        if ( ! in_array( $atts['orderby'], $this->allowed_orderby, true ) ) {
            $atts['orderby'] = $defaults['orderby'];
        }
        $atts['category']   = sanitize_text_field( $atts['category'] );
        $atts['pagination'] = filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN );
        $atts['cache_time'] = absint( $atts['cache_time'] );

        // Determine current page for pagination.
        $page = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
        $atts['paged'] = $page;

        // Fetch posts (with caching).
        $data = $this->getPosts( $atts );

        // Render the output.
        return $this->render( $data, $atts );
    }

    /**
     * Retrieve posts using WP_Query and cache minimal data.
     *
     * @param array $atts Shortcode attributes.
     * @return array {
     *     @type int[]  posts         Array of post IDs.
     *     @type int    total_pages   Total number of pages.
     *     @type int    current_page  Current page number.
     * }
     */
    protected function getPosts( $atts ) {
        $transient_key = 'blog_list_' . md5( wp_json_encode( $atts ) );
        $cached = get_transient( $transient_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $query_args = array(
            'post_type'      => 'post',
            'posts_per_page' => $atts['posts_per_page'],
            'order'          => $atts['order'],
            'orderby'        => $atts['orderby'],
            'paged'          => $atts['paged'],
            'fields'         => 'ids', // Retrieve only IDs to minimize cached payload.
        );

        if ( ! empty( $atts['category'] ) ) {
            if ( is_numeric( $atts['category'] ) ) {
                $query_args['cat'] = absint( $atts['category'] );
            } else {
                $query_args['category_name'] = sanitize_title( $atts['category'] );
            }
        }

        $query = new WP_Query( $query_args );

        $result = array(
            'posts'        => $query->posts,
            'total_pages'  => $query->max_num_pages,
            'current_page' => $atts['paged'],
        );

        set_transient( $transient_key, $result, $atts['cache_time'] );

        return $result;
    }

    /**
     * Render the list of posts.
     *
     * @param array $data Data returned from getPosts().
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    protected function render( $data, $atts ) {
        if ( empty( $data['posts'] ) ) {
            return '<p>' . esc_html__( 'No posts found.', 'blog-list' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="blog-list-shortcode">
            <ul class="blog-list-items">
                <?php foreach ( $data['posts'] as $post_id ) : ?>
                    <li class="blog-list-item">
                        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                            <?php echo esc_html( get_the_title( $post_id ) ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ( $atts['pagination'] && $data['total_pages'] > 1 ) : ?>
                <div class="blog-list-pagination">
                    <?php
                    echo paginate_links( array(
                        'current'   => max( 1, $data['current_page'] ),
                        'total'     => $data['total_pages'],
                        'prev_text' => __( '&laquo;', 'blog-list' ),
                        'next_text' => __( '&raquo;', 'blog-list' ),
                        'type'      => 'list',
                    ) );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// new BlogList_Shortcode(); // Self-instantiation removed.