<?php
// Define plugin constants if not already defined
if (!defined('BLOG_LIST_PLUGIN_DIR')) {
    define('BLOG_LIST_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('BLOG_LIST_PLUGIN_URL')) {
    define('BLOG_LIST_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('BLOG_LIST_VERSION')) {
    define('BLOG_LIST_VERSION', '1.0.0');
}

function blog_list_activate() {
    add_option('blog_list_options', array('posts_per_page' => 5, 'cache_duration' => 3600));
}

function blog_list_deactivate() {
    delete_option('blog_list_options');
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_blog_list_%'");
}

function blog_list_setup_hooks() {
    // Activation/deactivation are handled in main plugin file to avoid duplicates
    add_action('init', 'blog_list_load_components');
    add_action('widgets_init', 'blog_list_register_widget');
    add_action('admin_menu', 'blog_list_admin_menu');
    add_action('admin_init', 'blog_list_register_settings');
    add_action('wp_enqueue_scripts', 'blog_list_enqueue_assets');
    add_action('init', 'blog_list_register_block');
}

function blog_list_load_components() {
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-widget.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-shortcode.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-admin.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-block.php';
    // class-ajax.php removed: file does not exist
}

function blog_list_register_widget() {
    register_widget('Blog_List_Widget');
}

function blog_list_admin_menu() {
    add_options_page(
        'Blog List Settings',
        'Blog List',
        'manage_options',
        'blog_list',
        array('Blog_List_Admin', 'settings_page')
    );
}

function blog_list_register_settings() {
    register_setting('blog_list_settings', 'blog_list_options', array('Blog_List_Admin', 'sanitize'));
}

function blog_list_register_block() {
    if (function_exists('register_block_type')) {
        register_block_type('blog-list/block', array(
            'editor_script'   => 'blog-list-block-editor',
            'editor_style'    => 'blog-list-block-editor',
            'script'          => 'blog-list-frontend',
            'style'           => 'blog-list-frontend',
            'render_callback' => array('Blog_List_Block', 'render'),
        ));
    }
}

function blog_list_enqueue_assets() {
    wp_enqueue_style(
        'blog-list-frontend',
        BLOG_LIST_PLUGIN_URL . 'assets/css/blog-list.css',
        array(),
        BLOG_LIST_VERSION
    );
    wp_register_script(
        'blog-list-frontend',
        BLOG_LIST_PLUGIN_URL . 'assets/js/blog-list.js',
        array('jquery'),
        BLOG_LIST_VERSION,
        true
    );
    wp_localize_script(
        'blog-list-frontend',
        'BlogList',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('blog_list_nonce'),
        )
    );
    wp_enqueue_script('blog-list-frontend');

    if (function_exists('wp_set_script_translations')) {
        wp_set_script_translations(
            'blog-list-frontend',
            'blog-list',
            BLOG_LIST_PLUGIN_DIR . 'languages'
        );
    }
    if (function_exists('register_block_type')) {
        wp_register_script(
            'blog-list-block-editor',
            BLOG_LIST_PLUGIN_URL . 'assets/js/blog-list-block.js',
            array('wp-blocks', 'wp-element', 'wp-editor'),
            BLOG_LIST_VERSION,
            true
        );
        wp_register_style(
            'blog-list-block-editor',
            BLOG_LIST_PLUGIN_URL . 'assets/css/blog-list-block.css',
            array('wp-edit-blocks'),
            BLOG_LIST_VERSION
        );
    }
}

blog_list_setup_hooks();