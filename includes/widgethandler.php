<?php
function bloglistplugin_register_widgets() {
    $widget_class = 'Blog_List_Widget'; // Ensure class name matches definition
    if ( ! class_exists( $widget_class ) ) {
        // The class should already be loaded by the main plugin file's autoloader or direct require.
        // However, if it's specifically loaded here, update the path.
        $file = __DIR__ . '/class-blog-list-widget.php'; // Updated file name
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    // Ensure the class name here matches the class name in class-blog-list-widget.php
    if ( class_exists( 'Blog_List_Widget' ) ) { // WordPress expects the exact class name.
        register_widget( 'Blog_List_Widget' );
    }
}
add_action( 'widgets_init', 'bloglistplugin_register_widgets' );