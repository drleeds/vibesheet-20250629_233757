<?php
/**
 * Plugin Name: Blog List
 * Description: Displays a list of blog posts with caching, widget, shortcode, and Gutenberg block support.
 * Version: 1.0.0
 * Text Domain: blog-list
 */

if ( ! defined( 'BLOG_LIST_PLUGIN_DIR' ) ) {
    define( 'BLOG_LIST_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BLOG_LIST_PLUGIN_URL' ) ) {
    define( 'BLOG_LIST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'BLOG_LIST_VERSION' ) ) {
    define( 'BLOG_LIST_VERSION', '1.0.0' );
}

function blog_list_clear_transients() {
    global $wpdb;
    $like = '_transient_blog_list_%';
    $sql = $wpdb->prepare(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
        $like
    );
    $names = $wpdb->get_col( $sql );
    if ( ! empty( $names ) ) {
        foreach ( $names as $name ) {
            $key = substr( $name, strlen( '_transient_' ) );
            delete_transient( $key );
        }
    }
}

/**
 * Activation hook: set default settings and clear old transients.
 */
function blog_list_activate() {
    $default_settings = array(
        'posts_per_page' => 10,
        'show_date'      => 1,
        'show_excerpt'   => 1,
        'cache_duration' => 3600,
    );
    add_option( 'blog_list_settings', $default_settings );
    blog_list_clear_transients();
}
register_activation_hook( __FILE__, 'blog_list_activate' );

/**
 * Deactivation hook: clear plugin transients.
 */
function blog_list_deactivate() {
    blog_list_clear_transients();
}
register_deactivation_hook( __FILE__, 'blog_list_deactivate' );

/**
 * Uninstall hook: clear transients and delete settings.
 */
function blog_list_uninstall() {
    blog_list_clear_transients();
    delete_option( 'blog_list_settings' );
}
register_uninstall_hook( __FILE__, 'blog_list_uninstall' );

// Removed global functions and their hooks for textdomain, widget registration, and asset enqueueing.
// This functionality is now handled by the Blog_List_Plugin class.

// Load the main plugin class file
require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-plugin.php';

/**
 * Returns the main instance of Blog_List_Plugin.
 * Ensures only one instance of Blog_List_Plugin is loaded or can be loaded.
 *
 * @return Blog_List_Plugin Main instance
 */
function blog_list_get_plugin_instance() {
    return Blog_List_Plugin::get_instance();
}

// Initialize the plugin
blog_list_get_plugin_instance();

// Activation/deactivation/uninstall hooks are global and remain here.
// Other actions like plugins_loaded, widgets_init, wp_enqueue_scripts are managed by Blog_List_Plugin.