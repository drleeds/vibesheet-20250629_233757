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
            basename( BLOG_LIST_PLUGIN_DIR ) . '/languages' // Corrected path for languages directory
        );
    }

    protected function registerComponents() {
        // Ensure correct class names and that these classes are loaded.
        // The main plugin file (blog-list.php) should handle requiring these files.
        if (class_exists('BlogList_Shortcode')) { // Corrected class name from file
            add_shortcode( 'blog_list', array( 'BlogList_Shortcode', 'callback' ) ); // callback is the public method
        }

        // Widget registration is often handled directly in the main plugin file or a dedicated handler
        // like widgethandler.php, which is already enqueued.
        // If Blog_List_Widget is correctly defined and loaded, widgets_init in blog-list.php or widgethandler.php handles it.

        if ( is_admin() ) {
            // Ensure Blog_List_Admin class is loaded.
            if (class_exists('Blog_List_Admin')) {
                 // Check if methods exist before adding action, or ensure they are public static
                if (method_exists('Blog_List_Admin', 'add_admin_menu')) {
                    add_action( 'admin_menu', array( 'Blog_List_Admin', 'add_admin_menu' ) );
                }
                if (method_exists('Blog_List_Admin', 'register_settings')) {
                    add_action( 'admin_init', array( 'Blog_List_Admin', 'register_settings' ) );
                }
            }
        }
    }
}

// Activation and deactivation hooks should be in the main plugin file (blog-list.php)
// to ensure they use the correct __FILE__ path.
// Removing them from here as they are duplicated and incorrect in this context.

Blog_List_Plugin::get_instance();