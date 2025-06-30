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
        add_action( 'widgets_init', array( $this, 'registerWidget' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueueFrontendAssets' ) );
    }

    // Static activate/deactivate methods removed as hooks are handled by global functions in blog-list.php

    public function init() {
        // Load required class files for components
        // Ensure BLOG_LIST_PLUGIN_DIR is available or pass it if necessary, though it should be a defined constant.
        require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-block.php';
        require_once BLOG_LIST_PLUGIN_DIR . 'admin/class-blog-list-admin.php'; // For admin settings
        require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-shortcode.php';
        // class-blog-list-widget.php is loaded by the global blog_list_register_widget in blog-list.php

        if ( class_exists( 'Blog_List_Block' ) ) {
            new Blog_List_Block(); // The block's constructor hooks its own registration to init
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

    public function registerWidget() {
        // Ensure the widget class is loaded
        require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-widget.php';
        if ( class_exists( 'Blog_List_Widget' ) ) {
            register_widget( 'Blog_List_Widget' );
        }
    }

    public function enqueueFrontendAssets() {
        wp_enqueue_style( 'blog-list-style', BLOG_LIST_PLUGIN_URL . 'assets/css/style.css', array(), BLOG_LIST_VERSION );
        wp_enqueue_script( 'blog-list-script', BLOG_LIST_PLUGIN_URL . 'assets/js/script.js', array( 'jquery' ), BLOG_LIST_VERSION, true );
    }

    protected function registerComponents() {
        // Ensure correct class names and that these classes are loaded.
        // The main plugin file (blog-list.php) should handle requiring these files.
        if (class_exists('BlogList_Shortcode')) {
            $bloglist_shortcode = new BlogList_Shortcode();
            add_shortcode( 'blog_list', array( $bloglist_shortcode, 'callback' ) );
        }

        // Widget registration will be handled by this class. See constructor/init.

        // Admin components are instantiated when their class file is loaded in init(),
        // and their constructors handle their own hook registrations.
        // So, no explicit hook registration for Blog_List_Admin needed here.
        // if ( is_admin() ) {
        //     // Ensure Blog_List_Admin class is loaded.
        //     if (class_exists('Blog_List_Admin')) {
        //          // Check if methods exist before adding action, or ensure they are public static
        //         if (method_exists('Blog_List_Admin', 'add_admin_menu')) {
        //             add_action( 'admin_menu', array( 'Blog_List_Admin', 'add_admin_menu' ) );
        //         }
        //         if (method_exists('Blog_List_Admin', 'register_settings')) {
        //             add_action( 'admin_init', array( 'Blog_List_Admin', 'register_settings' ) );
        //         }
        //     }
        // }
    }
}

// Activation and deactivation hooks should be in the main plugin file (blog-list.php)
// to ensure they use the correct __FILE__ path.
// Removing them from here as they are duplicated and incorrect in this context.

Blog_List_Plugin::get_instance();