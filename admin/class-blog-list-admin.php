class Blog_List_Admin { // Renamed class

    private $option_group = 'blog_list_settings_group';
    private $option_name = 'blog_list_settings';
    private $page_slug = 'blog-list-settings';

    public function __construct() {
        add_action( 'admin_init', array( $this, 'registerSettings' ) );
        add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
    }

    public function registerSettings() {
        register_setting(
            $this->option_group,
            $this->option_name,
            array(
                'sanitize_callback' => array( $this, 'sanitizeSettings' ),
                // It's good practice to define a default value for the option
                'default' => $this->getDefaultSettings(),
            )
        );
        $this->registerSections();
        $this->registerFields();
    }

    public function addSettingsPage() {
        add_options_page(
            __( 'Blog List Settings', 'blog-list' ), // Changed text domain to match plugin
            __( 'Blog List', 'blog-list' ),         // Changed text domain to match plugin
            'manage_options',
            $this->page_slug,
            array( $this, 'renderSettingsPage' )
        );
    }

    private function registerSections() {
        add_settings_section(
            'bloglist_main_section',
            __( 'General Settings', 'blog-list' ),   // Changed text domain
            array( $this, 'renderSectionInfo' ),
            $this->page_slug
        );
        add_settings_section(
            'bloglist_cache_section',
            __( 'Caching Settings', 'blog-list' ), // Changed text domain
            array( $this, 'renderCacheSectionInfo' ),
            $this->page_slug
        );
    }

    private function registerFields() {
        add_settings_field(
            'posts_per_page',
            __( 'Posts Per Page', 'blog-list' ),    // Changed text domain
            array( $this, 'renderPostsPerPageField' ),
            $this->page_slug,
            'bloglist_main_section'
        );
        add_settings_field(
            'default_category',
            __( 'Default Category', 'blog-list' ), // Changed text domain
            array( $this, 'renderDefaultCategoryField' ),
            $this->page_slug,
            'bloglist_main_section'
        );
        add_settings_field(
            'enable_pagination',
            __( 'Enable Pagination', 'blog-list' ), // Changed text domain
            array( $this, 'renderEnablePaginationField' ),
            $this->page_slug,
            'bloglist_main_section'
        );
        add_settings_field(
            'cache_duration',
            __( 'Cache Duration (seconds)', 'blog-list' ), // Changed text domain
            array( $this, 'renderCacheDurationField' ),
            $this->page_slug,
            'bloglist_cache_section'
        );

        // New fields from adminsettingspage.php
        add_settings_field(
            'date_format',
            __( 'Date Format', 'blog-list' ),
            array( $this, 'renderTextField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'date_format', 'default' => 'F j, Y', 'description' => __( 'PHP date format for post dates.', 'blog-list' ) )
        );
        add_settings_field(
            'default_start_date',
            __( 'Default Start Date', 'blog-list' ),
            array( $this, 'renderDateField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'default_start_date', 'default' => '', 'description' => __( 'Start date filter default (YYYY-MM-DD).', 'blog-list' ) )
        );
        add_settings_field(
            'default_end_date',
            __( 'Default End Date', 'blog-list' ),
            array( $this, 'renderDateField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'default_end_date', 'default' => '', 'description' => __( 'End date filter default (YYYY-MM-DD).', 'blog-list' ) )
        );
        add_settings_field(
            'label_copy_button',
            __( 'Copy Button Label', 'blog-list' ),
            array( $this, 'renderTextField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'label_copy_button', 'default' => __( 'Copy to Clipboard', 'blog-list' ) )
        );
        add_settings_field(
            'label_export_csv',
            __( 'CSV Export Label', 'blog-list' ),
            array( $this, 'renderTextField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'label_export_csv', 'default' => __( 'Export CSV', 'blog-list' ) )
        );
        add_settings_field(
            'label_export_md',
            __( 'Markdown Export Label', 'blog-list' ),
            array( $this, 'renderTextField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'label_export_md', 'default' => __( 'Export Markdown', 'blog-list' ) )
        );
        add_settings_field(
            'enable_csv_export',
            __( 'Enable CSV Export', 'blog-list' ),
            array( $this, 'renderCheckboxField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'enable_csv_export', 'default' => 1 )
        );
        add_settings_field(
            'enable_md_export',
            __( 'Enable Markdown Export', 'blog-list' ),
            array( $this, 'renderCheckboxField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'enable_md_export', 'default' => 1 )
        );
        add_settings_field(
            'enable_calendar_sync',
            __( 'Enable Calendar Sync', 'blog-list' ),
            array( $this, 'renderCheckboxField' ),
            $this->page_slug,
            'bloglist_main_section',
            array( 'label_for' => 'enable_calendar_sync', 'default' => 0 )
        );
    }

    public function getDefaultSettings() {
        return array(
            'posts_per_page'       => 10,
            'default_category'     => '', // Changed to empty string for "All Categories"
            'enable_pagination'    => 1,
            'cache_duration'       => 3600,
            'date_format'          => 'F j, Y',
            'default_start_date'   => '',
            'default_end_date'     => '',
            'label_copy_button'    => __( 'Copy to Clipboard', 'blog-list' ),
            'label_export_csv'     => __( 'Export CSV', 'blog-list' ),
            'label_export_md'      => __( 'Export Markdown', 'blog-list' ),
            'enable_csv_export'    => 1,
            'enable_md_export'     => 1,
            'enable_calendar_sync' => 0,
        );
    }

    public function sanitizeSettings( $input ) {
        $defaults = $this->getDefaultSettings();
        $sanitized = array();

        $sanitized['posts_per_page'] = isset( $input['posts_per_page'] ) ? absint( $input['posts_per_page'] ) : $defaults['posts_per_page'];
        $sanitized['default_category'] = isset( $input['default_category'] ) ? sanitize_text_field( $input['default_category'] ) : $defaults['default_category'];
        $sanitized['enable_pagination'] = !empty( $input['enable_pagination'] ) ? 1 : 0;
        $sanitized['cache_duration'] = isset( $input['cache_duration'] ) ? absint( $input['cache_duration'] ) : $defaults['cache_duration'];

        // Sanitize new fields
        $sanitized['date_format'] = isset( $input['date_format'] ) ? sanitize_text_field( $input['date_format'] ) : $defaults['date_format'];

        $start_date = isset( $input['default_start_date'] ) ? sanitize_text_field( $input['default_start_date'] ) : $defaults['default_start_date'];
        if ( $start_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
            $start_date = $defaults['default_start_date'];
        }
        $sanitized['default_start_date'] = $start_date;

        $end_date = isset( $input['default_end_date'] ) ? sanitize_text_field( $input['default_end_date'] ) : $defaults['default_end_date'];
        if ( $end_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
            $end_date = $defaults['default_end_date'];
        }
        $sanitized['default_end_date'] = $end_date;

        $sanitized['label_copy_button'] = isset( $input['label_copy_button'] ) ? sanitize_text_field( $input['label_copy_button'] ) : $defaults['label_copy_button'];
        $sanitized['label_export_csv'] = isset( $input['label_export_csv'] ) ? sanitize_text_field( $input['label_export_csv'] ) : $defaults['label_export_csv'];
        $sanitized['label_export_md'] = isset( $input['label_export_md'] ) ? sanitize_text_field( $input['label_export_md'] ) : $defaults['label_export_md'];

        $sanitized['enable_csv_export'] = !empty( $input['enable_csv_export'] ) ? 1 : 0;
        $sanitized['enable_md_export'] = !empty( $input['enable_md_export'] ) ? 1 : 0;
        $sanitized['enable_calendar_sync'] = !empty( $input['enable_calendar_sync'] ) ? 1 : 0;

        return $sanitized;
    }

    public function renderSettingsPage() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Blog List Settings', 'blog-list' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_group );
                do_settings_sections( $this->page_slug );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function renderSectionInfo() {
        echo '<p>' . esc_html__( 'Configure how many posts to display and filtering options.', 'blog-list' ) . '</p>';
    }

    public function renderCacheSectionInfo() {
        echo '<p>' . esc_html__( 'Configure caching for faster load times.', 'blog-list' ) . '</p>';
    }

    public function renderPostsPerPageField() {
        $options = get_option( $this->option_name, $this->getDefaultSettings() );
        $value   = isset( $options['posts_per_page'] ) ? absint( $options['posts_per_page'] ) : $this->getDefaultSettings()['posts_per_page'];
        printf(
            '<input type="number" id="posts_per_page" name="%1$s[posts_per_page]" value="%2$d" min="1" />',
            esc_attr( $this->option_name ),
            $value
        );
    }

    public function renderDefaultCategoryField() {
        $options   = get_option( $this->option_name, $this->getDefaultSettings() );
        $selected  = isset( $options['default_category'] ) ? sanitize_text_field( $options['default_category'] ) : $this->getDefaultSettings()['default_category'];
        $categories = get_categories( array( 'hide_empty' => false ) );
        printf( '<select id="default_category" name="%1$s[default_category]">', esc_attr( $this->option_name ) );
        printf( '<option value="">%s</option>', esc_html__( 'All Categories', 'blog-list' ) );
        foreach ( $categories as $cat ) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $cat->slug ), // Store slug as value
                selected( $selected, $cat->slug, false ),
                esc_html( $cat->name )
            );
        }
        echo '</select>';
         echo '<p class="description">' . esc_html__( 'Category to filter by default.', 'blog-list' ) . '</p>';
    }

    public function renderEnablePaginationField() {
        $options = get_option( $this->option_name, $this->getDefaultSettings() );
        $checked = !empty( $options['enable_pagination'] ) ? 'checked' : '';
        printf(
            '<input type="checkbox" id="enable_pagination" name="%1$s[enable_pagination]" value="1" %2$s />',
            esc_attr( $this->option_name ),
            $checked
        );
    }

    public function renderCacheDurationField() {
        $options = get_option( $this->option_name, $this->getDefaultSettings() );
        $value   = isset( $options['cache_duration'] ) ? absint( $options['cache_duration'] ) : $this->getDefaultSettings()['cache_duration'];
        printf(
            '<input type="number" id="cache_duration" name="%1$s[cache_duration]" value="%2$d" min="0" />',
            esc_attr( $this->option_name ),
            $value
        );
    }

    // Generic renderer for text fields
    public function renderTextField( $args ) {
        $options = get_option( $this->option_name, $this->getDefaultSettings() );
        $id = $args['label_for'];
        $value = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : (isset($args['default']) ? $args['default'] : '');
        printf(
            '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text" />',
            esc_attr( $id ),
            esc_attr( $this->option_name ),
            esc_attr( $value )
        );
        if ( !empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    // Generic renderer for date fields
    public function renderDateField( $args ) {
        $options = get_option( $this->option_name, $this->getDefaultSettings() );
        $id = $args['label_for'];
        $value = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : (isset($args['default']) ? $args['default'] : '');
        printf(
            '<input type="date" id="%1$s" name="%2$s[%1$s]" value="%3$s" />',
            esc_attr( $id ),
            esc_attr( $this->option_name ),
            esc_attr( $value )
        );
        if ( !empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    // Generic renderer for checkbox fields
    public function renderCheckboxField( $args ) {
        $options = get_option( $this->option_name, $this->getDefaultSettings() );
        $id = $args['label_for'];
        $checked = isset( $options[$id] ) ? checked( 1, $options[$id], false ) : checked( 1, (isset($args['default']) ? $args['default'] : 0), false );
        printf(
            '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s />',
            esc_attr( $id ),
            esc_attr( $this->option_name ),
            $checked
        );
        if ( !empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }
}

new Blog_List_Admin();