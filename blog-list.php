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

/**
 * Load plugin textdomain for translations.
 */
function blog_list_load_textdomain() {
    load_plugin_textdomain( 'blog-list', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'blog_list_load_textdomain' );

/**
 * Register the widget.
 */
function blog_list_register_widget() {
    register_widget( 'Blog_List_Widget' );
}

/**
 * Enqueue frontend scripts and styles.
 */
function blog_list_enqueue_assets() {
    wp_enqueue_style( 'blog-list-style', BLOG_LIST_PLUGIN_URL . 'assets/css/style.css', array(), BLOG_LIST_VERSION );
    // Updated to use block.js as the main script, assuming it contains frontend logic.
    // If block.js is purely for the editor, this line might need to be removed or changed.
    wp_enqueue_script( 'blog-list-script', BLOG_LIST_PLUGIN_URL . 'assets/js/block.js', array( 'jquery' ), BLOG_LIST_VERSION, true );
}

/**
 * Enqueue block editor assets for Gutenberg.
 */
// This function is removed as block editor assets are handled by class-blog-list-block.php
// function blog_list_enqueue_block_editor_assets() {
//     wp_enqueue_script(
//         'blog-list-block-editor-script',
//         BLOG_LIST_PLUGIN_URL . 'assets/js/block.js', // Adjusted path
//         array( 'wp-blocks', 'wp-element', 'wp-editor' ),
//         BLOG_LIST_VERSION,
//         true
//     );
// }

/**
 * Initialize plugin: load components, register shortcode, widget, and block.
 */
function blog_list_init() {
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/shortcodehandler.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/widgethandler.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-block.php'; // Adjusted class name
    require_once BLOG_LIST_PLUGIN_DIR . 'admin/adminsettingspage.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'admin/class-blog-list-admin.php'; // Added missing class

    // It's good practice to load all class files that might be needed.
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-plugin.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-shortcode.php';
    require_once BLOG_LIST_PLUGIN_DIR . 'includes/class-blog-list-widget.php';


    add_shortcode( 'blog_list', 'blog_list_shortcode_handler' );
    add_action( 'widgets_init', 'blog_list_register_widget' );

    // Block registration is handled by includes/class-blog-list-block.php via includes/class-blog-list-plugin.php
    // So, the direct register_block_type call here is removed.

    add_action( 'wp_enqueue_scripts', 'blog_list_enqueue_assets' );
    // Block editor assets are enqueued by class-blog-list-block.php if that class is handling registration.
    // However, the hook 'enqueue_block_editor_assets' is standard.
    // Let's keep blog_list_enqueue_block_editor_assets but ensure it doesn't conflict with class-blog-list-block.php.
    // For now, assuming class-blog-list-block.php handles its own editor script registration.
    // If blog_list_enqueue_block_editor_assets in this file is meant for other general editor assets, it can stay.
    // Based on its content (script for 'blog-list-block-editor-script'), it's likely for the block.
    // The class Blog_List_Block also registers 'blog-list-block-editor-script'.
    // To avoid duplication, this specific enqueue action for block editor assets can be removed from here
    // if the class handles it.
    // add_action( 'enqueue_block_editor_assets', 'blog_list_enqueue_block_editor_assets' );
    // Decision: Removing it here as class-blog-list-block.php should be self-contained for block assets.
}
add_action( 'init', 'blog_list_init' );