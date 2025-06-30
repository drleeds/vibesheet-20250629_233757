function blog_list_add_settings_page() {
    add_options_page(
        __( 'Blog List Settings', 'blog-list' ),
        __( 'Blog List', 'blog-list' ),
        'manage_options',
        'blog_list',
        'blog_list_settings_page_html'
    );
}

/**
 * Register settings, sections, and fields.
 */
function blog_list_admin_init() {
    register_setting(
        'blog_list_settings',
        'blog_list_options',
        'blog_list_sanitize_options'
    );

    add_settings_section(
        'blog_list_main_section',
        __( 'General Settings', 'blog-list' ),
        'blog_list_section_text',
        'blog_list'
    );

    $fields = array(
        array(
            'id'          => 'date_format',
            'title'       => __( 'Date Format', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'date_format',
                'type'        => 'text',
                'default'     => 'F j, Y',
                'description' => __( 'PHP date format for post dates.', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'default_category',
            'title'       => __( 'Default Category', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'default_category',
                'type'        => 'select',
                'options'     => blog_list_get_category_options(),
                'default'     => 0,
                'description' => __( 'Category to filter by default.', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'default_start_date',
            'title'       => __( 'Default Start Date', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'default_start_date',
                'type'        => 'date',
                'default'     => '',
                'description' => __( 'Start date filter default (YYYY-MM-DD).', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'default_end_date',
            'title'       => __( 'Default End Date', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'default_end_date',
                'type'        => 'date',
                'default'     => '',
                'description' => __( 'End date filter default (YYYY-MM-DD).', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'label_copy_button',
            'title'       => __( 'Copy Button Label', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'label_copy_button',
                'type'        => 'text',
                'default'     => __( 'Copy to Clipboard', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'label_export_csv',
            'title'       => __( 'CSV Export Label', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'label_export_csv',
                'type'        => 'text',
                'default'     => __( 'Export CSV', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'label_export_md',
            'title'       => __( 'Markdown Export Label', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'label_export_md',
                'type'        => 'text',
                'default'     => __( 'Export Markdown', 'blog-list' ),
            ),
        ),
        array(
            'id'          => 'enable_csv_export',
            'title'       => __( 'Enable CSV Export', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'enable_csv_export',
                'type'        => 'checkbox',
                'default'     => 1,
            ),
        ),
        array(
            'id'          => 'enable_md_export',
            'title'       => __( 'Enable Markdown Export', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'enable_md_export',
                'type'        => 'checkbox',
                'default'     => 1,
            ),
        ),
        array(
            'id'          => 'enable_calendar_sync',
            'title'       => __( 'Enable Calendar Sync', 'blog-list' ),
            'callback'    => 'blog_list_render_field',
            'page'        => 'blog_list',
            'section'     => 'blog_list_main_section',
            'args'        => array(
                'label_for'   => 'enable_calendar_sync',
                'type'        => 'checkbox',
                'default'     => 0,
            ),
        ),
    );

    foreach ( $fields as $field ) {
        add_settings_field(
            $field['id'],
            $field['title'],
            $field['callback'],
            $field['page'],
            $field['section'],
            $field['args']
        );
    }
}

/**
 * Section description callback.
 */
function blog_list_section_text( $args ) {
    echo '<p>' . esc_html__( 'Configure default behaviors and labels for the Blog List plugin.', 'blog-list' ) . '</p>';
}

/**
 * Field rendering callback.
 */
function blog_list_render_field( $args ) {
    $options     = get_option( 'blog_list_options', array() );
    $id          = $args['label_for'];
    $type        = $args['type'];
    $value       = isset( $options[ $id ] ) ? $options[ $id ] : ( isset( $args['default'] ) ? $args['default'] : '' );
    $description = isset( $args['description'] ) ? $args['description'] : '';

    switch ( $type ) {
        case 'text':
            printf(
                '<input type="text" id="%1$s" name="blog_list_options[%1$s]" value="%2$s" class="regular-text" />',
                esc_attr( $id ),
                esc_attr( $value )
            );
            break;
        case 'date':
            printf(
                '<input type="date" id="%1$s" name="blog_list_options[%1$s]" value="%2$s" />',
                esc_attr( $id ),
                esc_attr( $value )
            );
            break;
        case 'select':
            if ( ! empty( $args['options'] ) && is_array( $args['options'] ) ) {
                printf( '<select id="%1$s" name="blog_list_options[%1$s]">', esc_attr( $id ) );
                foreach ( $args['options'] as $opt_value => $opt_label ) {
                    printf(
                        '<option value="%1$s"%3$s>%2$s</option>',
                        esc_attr( $opt_value ),
                        esc_html( $opt_label ),
                        selected( $value, $opt_value, false )
                    );
                }
                echo '</select>';
            }
            break;
        case 'checkbox':
            printf(
                '<input type="checkbox" id="%1$s" name="blog_list_options[%1$s]" value="1"%2$s />',
                esc_attr( $id ),
                checked( 1, $value, false )
            );
            break;
    }

    if ( $description ) {
        printf( '<p class="description">%s</p>', esc_html( $description ) );
    }
}

/**
 * Sanitize and validate settings input.
 */
function blog_list_sanitize_options( $input ) {
    $defaults = array(
        'date_format'          => 'F j, Y',
        'default_category'     => 0,
        'default_start_date'   => '',
        'default_end_date'     => '',
        'label_copy_button'    => __( 'Copy to Clipboard', 'blog-list' ),
        'label_export_csv'     => __( 'Export CSV', 'blog-list' ),
        'label_export_md'      => __( 'Export Markdown', 'blog-list' ),
        'enable_csv_export'    => 1,
        'enable_md_export'     => 1,
        'enable_calendar_sync' => 0,
    );

    $output = array();

    // Text and numeric fields.
    $output['date_format']      = sanitize_text_field( $input['date_format'] ?? $defaults['date_format'] );
    $output['default_category'] = absint( $input['default_category'] ?? $defaults['default_category'] );

    // Date fields with pattern validation.
    $start_date = sanitize_text_field( $input['default_start_date'] ?? $defaults['default_start_date'] );
    if ( $start_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
        $start_date = '';
    }
    $output['default_start_date'] = $start_date;

    $end_date = sanitize_text_field( $input['default_end_date'] ?? $defaults['default_end_date'] );
    if ( $end_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
        $end_date = '';
    }
    $output['default_end_date'] = $end_date;

    // Label fields.
    $output['label_copy_button'] = sanitize_text_field( $input['label_copy_button'] ?? $defaults['label_copy_button'] );
    $output['label_export_csv']  = sanitize_text_field( $input['label_export_csv'] ?? $defaults['label_export_csv'] );
    $output['label_export_md']   = sanitize_text_field( $input['label_export_md'] ?? $defaults['label_export_md'] );

    // Checkbox fields.
    $output['enable_csv_export']    = isset( $input['enable_csv_export'] ) ? 1 : 0;
    $output['enable_md_export']     = isset( $input['enable_md_export'] ) ? 1 : 0;
    $output['enable_calendar_sync'] = isset( $input['enable_calendar_sync'] ) ? 1 : 0;

    return $output;
}

/**
 * Get category options for select field.
 */
function blog_list_get_category_options() {
    $categories = get_terms( array(
        'taxonomy'   => 'category',
        'hide_empty' => false,
    ) );
    $options = array( 0 => __( 'All Categories', 'blog-list' ) );
    if ( ! is_wp_error( $categories ) ) {
        foreach ( $categories as $cat ) {
            $options[ $cat->term_id ] = $cat->name;
        }
    }
    return $options;
}

/**
 * Render the settings page HTML.
 */
function blog_list_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Blog List Settings', 'blog-list' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'blog_list_settings' );
            do_settings_sections( 'blog_list' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_menu', 'blog_list_add_settings_page' );
add_action( 'admin_init', 'blog_list_admin_init' );