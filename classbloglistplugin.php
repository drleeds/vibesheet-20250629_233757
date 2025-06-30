<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Blog_List_Plugin {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'loadTextdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
    }

    public static function activate() {
        if ( ! get_option( 'blog_list_settings' ) ) {
            $defaults = array(
                'posts_per_page'   => 10,
                'show_excerpt'     => true,
                'excerpt_length'   => 20,
                'cache_duration'   => 3600,
                'default_category' => 0,
            );
            add_option( 'blog_list_settings', $defaults );
        }
        flush_rewrite_rules();
    }

    public static function deactivate() {
        global $wpdb;
        $transient_prefix = '_transient_blog_list_';
        $timeout_prefix   = '_transient_timeout_blog_list_';
        $like_transient   = esc_sql( $wpdb->esc_like( $transient_prefix ) . '%' );
        $like_timeout     = esc_sql( $wpdb->esc_like( $timeout_prefix ) . '%' );

        $query = "
            SELECT option_name
            FROM {$wpdb->options}
            WHERE option_name LIKE '{$like_transient}'
               OR option_name LIKE '{$like_timeout}'
        ";
        $rows = $wpdb->get_col( $query );
        if ( $rows ) {
            foreach ( $rows as $option ) {
                $transient = preg_replace( '/^_transient(?:_timeout)?_/', '', $option );
                delete_transient( $transient );
            }
        }
        flush_rewrite_rules();
    }

    public function init() {
        if ( class_exists( 'Blog_List_Block' ) ) {
            $block = new Blog_List_Block();
            $block->register();
        }
        $this->registerComponents();
    }

    public function loadTextdomain() {
        load_plugin_textdomain(
            'blog-list',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    protected function registerComponents() {
        add_shortcode( 'blog_list', array( 'Blog_List_Shortcode', 'render' ) );
        add_action( 'widgets_init', function() {
            register_widget( 'Blog_List_Widget' );
        } );
        if ( is_admin() ) {
            add_action( 'admin_menu', array( 'Blog_List_Admin', 'add_admin_menu' ) );
            add_action( 'admin_init', array( 'Blog_List_Admin', 'register_settings' ) );
        }
    }
}

register_activation_hook( __FILE__, array( 'Blog_List_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Blog_List_Plugin', 'deactivate' ) );

Blog_List_Plugin::get_instance();