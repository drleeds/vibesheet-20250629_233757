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
            )
        );
        $this->registerSections();
        $this->registerFields();
    }

    public function addSettingsPage() {
        add_options_page(
            __( 'Blog List Settings', 'bloglist' ),
            __( 'Blog List', 'bloglist' ),
            'manage_options',
            $this->page_slug,
            array( $this, 'renderSettingsPage' )
        );
    }

    private function registerSections() {
        add_settings_section(
            'bloglist_main_section',
            __( 'General Settings', 'bloglist' ),
            array( $this, 'renderSectionInfo' ),
            $this->page_slug
        );
        add_settings_section(
            'bloglist_cache_section',
            __( 'Caching Settings', 'bloglist' ),
            array( $this, 'renderCacheSectionInfo' ),
            $this->page_slug
        );
    }

    private function registerFields() {
        add_settings_field(
            'posts_per_page',
            __( 'Posts Per Page', 'bloglist' ),
            array( $this, 'renderPostsPerPageField' ),
            $this->page_slug,
            'bloglist_main_section'
        );
        add_settings_field(
            'default_category',
            __( 'Default Category', 'bloglist' ),
            array( $this, 'renderDefaultCategoryField' ),
            $this->page_slug,
            'bloglist_main_section'
        );
        add_settings_field(
            'enable_pagination',
            __( 'Enable Pagination', 'bloglist' ),
            array( $this, 'renderEnablePaginationField' ),
            $this->page_slug,
            'bloglist_main_section'
        );
        add_settings_field(
            'cache_duration',
            __( 'Cache Duration (seconds)', 'bloglist' ),
            array( $this, 'renderCacheDurationField' ),
            $this->page_slug,
            'bloglist_cache_section'
        );
    }

    public function sanitizeSettings( $input ) {
        $defaults = array(
            'posts_per_page'    => 10,
            'default_category'  => '',
            'enable_pagination' => 1,
            'cache_duration'    => 3600,
        );
        $existing  = get_option( $this->option_name, array() );
        $sanitized = wp_parse_args( $existing, $defaults );

        if ( isset( $input['posts_per_page'] ) ) {
            $sanitized['posts_per_page'] = absint( $input['posts_per_page'] );
        }
        if ( isset( $input['default_category'] ) ) {
            $sanitized['default_category'] = sanitize_text_field( $input['default_category'] );
        }
        $sanitized['enable_pagination'] = ! empty( $input['enable_pagination'] ) ? 1 : 0;
        if ( isset( $input['cache_duration'] ) ) {
            $sanitized['cache_duration'] = absint( $input['cache_duration'] );
        }

        return $sanitized;
    }

    public function renderSettingsPage() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Blog List Settings', 'bloglist' ); ?></h1>
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
        echo '<p>' . esc_html__( 'Configure how many posts to display and filtering options.', 'bloglist' ) . '</p>';
    }

    public function renderCacheSectionInfo() {
        echo '<p>' . esc_html__( 'Configure caching for faster load times.', 'bloglist' ) . '</p>';
    }

    public function renderPostsPerPageField() {
        $options = get_option( $this->option_name, array() );
        $value   = isset( $options['posts_per_page'] ) ? absint( $options['posts_per_page'] ) : 10;
        printf(
            '<input type="number" name="%1$s[posts_per_page]" value="%2$d" min="1" />',
            esc_attr( $this->option_name ),
            $value
        );
    }

    public function renderDefaultCategoryField() {
        $options   = get_option( $this->option_name, array() );
        $selected  = isset( $options['default_category'] ) ? sanitize_text_field( $options['default_category'] ) : '';
        $categories = get_categories( array( 'hide_empty' => false ) );
        printf( '<select name="%1$s[default_category]">', esc_attr( $this->option_name ) );
        printf( '<option value="">%s</option>', esc_html__( 'All Categories', 'bloglist' ) );
        foreach ( $categories as $cat ) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $cat->slug ),
                selected( $selected, $cat->slug, false ),
                esc_html( $cat->name )
            );
        }
        echo '</select>';
    }

    public function renderEnablePaginationField() {
        $options = get_option( $this->option_name, array() );
        $checked = ! empty( $options['enable_pagination'] ) ? 'checked' : '';
        printf(
            '<input type="checkbox" name="%1$s[enable_pagination]" value="1" %2$s />',
            esc_attr( $this->option_name ),
            $checked
        );
    }

    public function renderCacheDurationField() {
        $options = get_option( $this->option_name, array() );
        $value   = isset( $options['cache_duration'] ) ? absint( $options['cache_duration'] ) : 3600;
        printf(
            '<input type="number" name="%1$s[cache_duration]" value="%2$d" min="0" />',
            esc_attr( $this->option_name ),
            $value
        );
    }
}

new BlogListAdmin();