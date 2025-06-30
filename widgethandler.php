<?php
function bloglistplugin_register_widgets() {
    $widget_class = 'BlogList_Widget';
    if ( ! class_exists( $widget_class ) ) {
        $file = __DIR__ . '/classbloglistwidget.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    if ( class_exists( $widget_class ) ) {
        register_widget( $widget_class );
    }
}
add_action( 'widgets_init', 'bloglistplugin_register_widgets' );