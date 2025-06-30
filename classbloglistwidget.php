<?php
if ( ! class_exists( 'Blog_List_Widget' ) ) {
    class Blog_List_Widget extends WP_Widget {

        public function __construct() {
            parent::__construct(
                'blog_list_widget',
                __( 'Blog List', 'blog-list' ),
                array(
                    'classname'   => 'blog_list_widget',
                    'description' => __( 'Displays a customizable list of blog posts.', 'blog-list' ),
                )
            );
        }

        public function widget( $args, $instance ) {
            $title        = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );
            $number_posts = ! empty( $instance['number_posts'] ) ? absint( $instance['number_posts'] ) : 5;
            $order        = isset( $instance['order'] ) && in_array( $instance['order'], array( 'ASC', 'DESC' ), true ) ? $instance['order'] : 'DESC';
            $orderby      = isset( $instance['orderby'] ) && in_array( $instance['orderby'], array( 'date', 'title', 'rand', 'comment_count' ), true ) ? $instance['orderby'] : 'date';
            $categories   = isset( $instance['categories'] ) && is_array( $instance['categories'] ) ? array_map( 'absint', $instance['categories'] ) : array();

            echo $args['before_widget'];

            if ( $title ) {
                echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
            }

            $query_args = array(
                'post_type'      => 'post',
                'posts_per_page' => $number_posts,
                'order'          => $order,
                'orderby'        => $orderby,
                'no_found_rows'  => true,
            );

            if ( ! empty( $categories ) ) {
                $query_args['category__in'] = $categories;
            }

            $cache_key = 'blog_list_' . md5( serialize( $query_args ) );
            $output    = get_transient( $cache_key );

            if ( false === $output ) {
                $query = new WP_Query( $query_args );
                if ( $query->have_posts() ) {
                    $output = '<ul class="blog-list-widget-list">';
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $output .= '<li>';
                        $output .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
                        $output .= '</li>';
                    }
                    $output .= '</ul>';
                    wp_reset_postdata();
                } else {
                    $output = '<p>' . esc_html__( 'No posts found.', 'blog-list' ) . '</p>';
                }
                set_transient( $cache_key, $output, HOUR_IN_SECONDS * 12 );
            }

            echo $output;
            echo $args['after_widget'];
        }

        public function form( $instance ) {
            $title        = isset( $instance['title'] ) ? $instance['title'] : '';
            $number_posts = isset( $instance['number_posts'] ) ? absint( $instance['number_posts'] ) : 5;
            $order        = isset( $instance['order'] ) ? $instance['order'] : 'DESC';
            $orderby      = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
            $categories   = isset( $instance['categories'] ) && is_array( $instance['categories'] ) ? $instance['categories'] : array();
            $all_cats     = get_categories( array( 'hide_empty' => false ) );
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blog-list' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'number_posts' ) ); ?>"><?php esc_html_e( 'Number of posts:', 'blog-list' ); ?></label>
                <input id="<?php echo esc_attr( $this->get_field_id( 'number_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_posts' ) ); ?>" type="number" min="1" value="<?php echo esc_attr( $number_posts ); ?>" class="tiny-text">
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Order:', 'blog-list' ); ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
                    <option value="ASC"<?php selected( $order, 'ASC' ); ?>><?php esc_html_e( 'Ascending', 'blog-list' ); ?></option>
                    <option value="DESC"<?php selected( $order, 'DESC' ); ?>><?php esc_html_e( 'Descending', 'blog-list' ); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order by:', 'blog-list' ); ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
                    <option value="date"<?php selected( $orderby, 'date' ); ?>><?php esc_html_e( 'Date', 'blog-list' ); ?></option>
                    <option value="title"<?php selected( $orderby, 'title' ); ?>><?php esc_html_e( 'Title', 'blog-list' ); ?></option>
                    <option value="rand"<?php selected( $orderby, 'rand' ); ?>><?php esc_html_e( 'Random', 'blog-list' ); ?></option>
                    <option value="comment_count"<?php selected( $orderby, 'comment_count' ); ?>><?php esc_html_e( 'Comment Count', 'blog-list' ); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>"><?php esc_html_e( 'Filter by Categories:', 'blog-list' ); ?></label>
                <select id="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>" multiple="multiple" size="5" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'categories' ) ); ?>[]">
                    <?php foreach ( $all_cats as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"<?php echo in_array( $cat->term_id, $categories, true ) ? ' selected' : ''; ?>>
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <?php
        }

        public function update( $new_instance, $old_instance ) {
            $instance                    = array();
            $instance['title']           = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
            $instance['number_posts']    = isset( $new_instance['number_posts'] ) ? absint( $new_instance['number_posts'] ) : 5;
            $instance['order']           = isset( $new_instance['order'] ) && in_array( $new_instance['order'], array( 'ASC', 'DESC' ), true ) ? $new_instance['order'] : 'DESC';
            $instance['orderby']         = isset( $new_instance['orderby'] ) && in_array( $new_instance['orderby'], array( 'date', 'title', 'rand', 'comment_count' ), true ) ? $new_instance['orderby'] : 'date';
            if ( isset( $new_instance['categories'] ) && is_array( $new_instance['categories'] ) ) {
                $instance['categories'] = array_map( 'absint', $new_instance['categories'] );
            } else {
                $instance['categories'] = array();
            }
            // Clear this widget instance cache
            $query_args = array(
                'post_type'      => 'post',
                'posts_per_page' => $instance['number_posts'],
                'order'          => $instance['order'],
                'orderby'        => $instance['orderby'],
                'no_found_rows'  => true,
            );
            if ( ! empty( $instance['categories'] ) ) {
                $query_args['category__in'] = $instance['categories'];
            }
            $cache_key = 'blog_list_' . md5( serialize( $query_args ) );
            delete_transient( $cache_key );

            return $instance;
        }

    }
}