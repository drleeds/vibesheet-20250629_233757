function blog_list_remove_transients() {
    global $wpdb;
    // Delete single-site transients
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_blog_list_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_blog_list_%'" );
}

function blog_list_remove_network_transients() {
    global $wpdb;
    // Delete network-wide transients
    if ( isset( $wpdb->sitemeta ) ) {
        $wpdb->query( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_blog_list_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_timeout_blog_list_%'" );
    }
}

function blog_list_remove_options() {
    global $wpdb;
    // Plugin settings
    delete_option( 'blog_list_settings' );
    // Widget settings
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'widget_blog_list%'" );
    // Gutenberg block metadata
    delete_option( 'blog_list_block_settings' );
}

function blog_list_remove_network_options() {
    global $wpdb;
    // Network-level plugin settings
    delete_site_option( 'blog_list_settings' );
    delete_site_option( 'blog_list_block_settings' );
    // Network-level widget settings
    if ( isset( $wpdb->sitemeta ) ) {
        $wpdb->query( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE 'widget_blog_list%'" );
    }
}

function blog_list_flush_rewrites() {
    if ( function_exists( 'flush_rewrite_rules' ) ) {
        flush_rewrite_rules();
    }
}

function blog_list_uninstall_blog() {
    blog_list_remove_options();
    blog_list_remove_transients();
    blog_list_flush_rewrites();
}

if ( is_multisite() ) {
    $site_ids = get_sites( array( 'number' => 0, 'fields' => 'ids' ) );
    foreach ( $site_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        blog_list_uninstall_blog();
        restore_current_blog();
    }
    // Network-wide cleanup
    blog_list_remove_network_options();
    blog_list_remove_network_transients();
    blog_list_flush_rewrites();
} else {
    blog_list_uninstall_blog();
}
?>