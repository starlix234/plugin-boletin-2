<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Newestler_Admin {

    private static $instance = null;
    private $option_name = 'newestler_options';

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Metabox in post editor
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function register_menu() {
        add_options_page(
            __( 'Newestler Settings', 'newestler' ),
            'Newestler',
            'manage_options',
            'newestler-settings',
            array( $this, 'settings_page' )
        );
    }

    public function register_settings() {
        register_setting( $this->option_name, $this->option_name, array( $this, 'sanitize_options' ) );

        add_settings_section(
            'newestler_main',
            __( 'Ajustes generales', 'newestler' ),
            '__return_false',
            'newestler-settings'
        );

        add_settings_field(
            'site_title',
            __( 'Título del boletín', 'newestler' ),
            array( $this, 'field_text' ),
            'newestler-settings',
            'newestler_main',
            array(
                'label_for' => 'site_title',
                'name' => 'site_title',
                'placeholder' => 'Boletín semanal'
            )
        );

        add_settings_field(
            'site_description',
            __( 'Descripción (meta)', 'newestler' ),
            array( $this, 'field_textarea' ),
            'newestler-settings',
            'newestler_main',
            array(
                'label_for' => 'site_description',
                'name' => 'site_description',
                'placeholder' => 'Resumen breve del boletín'
            )
        );

        add_settings_field(
            'default_category',
            __( 'Categoría por defecto', 'newestler' ),
            array( $this, 'field_select_categories' ),
            'newestler-settings',
            'newestler_main',
            array(
                'label_for' => 'default_category',
                'name' => 'default_category',
            )
        );

        add_settings_field(
            'default_limit',
            __( 'Límite por defecto', 'newestler' ),
            array( $this, 'field_number' ),
            'newestler-settings',
            'newestler_main',
            array(
                'label_for' => 'default_limit',
                'name' => 'default_limit',
                'placeholder' => '0 para todos'
            )
        );
    }

    public function sanitize_options( $input ) {
        $out = array();

        $out['site_title'] = isset( $input['site_title'] ) ? sanitize_text_field( $input['site_title'] ) : '';
        $out['site_description'] = isset( $input['site_description'] ) ? sanitize_textarea_field( $input['site_description'] ) : '';
        $out['default_category'] = isset( $input['default_category'] ) ? intval( $input['default_category'] ) : 0;
        $out['default_limit'] = isset( $input['default_limit'] ) ? intval( $input['default_limit'] ) : 0;

        return $out;
    }

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = get_option( $this->option_name, array() );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Newestler — Ajustes', 'newestler' ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( $this->option_name );
                do_settings_sections( 'newestler-settings' );
                submit_button();
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Atajos útiles', 'newestler' ); ?></h2>
            <p><?php esc_html_e( 'Shortcode principal:', 'newestler' ); ?> <code>[newestler_boletin]</code></p>
            <p><?php esc_html_e( 'Puedes pasar parametros: categoria (ID o slug), start_date, end_date, limit', 'newestler' ); ?></p>
        </div>
        <?php
    }

    public function field_text( $args ) {
        $options = get_option( $this->option_name, array() );
        $name = $args['name'];
        $val = isset( $options[ $name ] ) ? $options[ $name ] : '';
        printf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="regular-text" placeholder="%4$s" />',
            esc_attr( $name ),
            esc_attr( $this->option_name ),
            esc_attr( $val ),
            isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : ''
        );
    }

    public function field_textarea( $args ) {
        $options = get_option( $this->option_name, array() );
        $name = $args['name'];
        $val = isset( $options[ $name ] ) ? $options[ $name ] : '';
        printf( '<textarea id="%1$s" name="%2$s[%1$s]" rows="4" cols="60" placeholder="%4$s" class="large-text">%3$s</textarea>',
            esc_attr( $name ),
            esc_attr( $this->option_name ),
            esc_textarea( $val ),
            isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : ''
        );
    }

    public function field_number( $args ) {
        $options = get_option( $this->option_name, array() );
        $name = $args['name'];
        $val = isset( $options[ $name ] ) ? $options[ $name ] : '';
        printf( '<input type="number" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="small-text" placeholder="%4$s" />',
            esc_attr( $name ),
            esc_attr( $this->option_name ),
            esc_attr( $val ),
            isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : ''
        );
    }

    public function field_select_categories( $args ) {
        $options = get_option( $this->option_name, array() );
        $selected = isset( $options['default_category'] ) ? intval( $options['default_category'] ) : 0;
        $categories = get_categories( array( 'hide_empty' => false ) );
        echo '<select id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( $this->option_name ) . '[' . esc_attr( $args['name'] ) . ']">';
        echo '<option value="0">— ' . esc_html__( 'Ninguna (usar automático)', 'newestler' ) . ' —</option>';
        if ( ! empty( $categories ) ) {
            foreach ( $categories as $cat ) {
                printf( '<option value="%1$d" %2$s>%3$s</option>',
                    intval( $cat->term_id ),
                    selected( $selected, intval( $cat->term_id ), false ),
                    esc_html( $cat->name )
                );
            }
        }
        echo '</select>';
    }

    /* -----------------------
     *  Meta box (editor)
     * ----------------------- */
    public function add_metabox() {
        $screens = array( 'post', 'page' ); // puedes añadir post types
        foreach ( $screens as $screen ) {
            add_meta_box(
                'newestler_shortcode_box',
                __( 'Newestler — Insertar shortcode', 'newestler' ),
                array( $this, 'render_metabox' ),
                $screen,
                'side',
                'high'
            );
        }
    }

    public function render_metabox( $post ) {
        $options = get_option( $this->option_name, array() );
        $categories = get_categories( array( 'hide_empty' => false ) );
        $default_cat = isset( $options['default_category'] ) ? intval( $options['default_category'] ) : 0;
        $default_limit = isset( $options['default_limit'] ) ? intval( $options['default_limit'] ) : 0;

        // Valores por defecto para la UI
        $start_default = date( 'Y-m-d', strtotime( '-7 days' ) );
        $end_default = date( 'Y-m-d' );

        ?>
        <div class="newestler-metabox">
            <p>
                <label><strong><?php esc_html_e( 'Categoría', 'newestler' ); ?></strong></label>
                <select id="nw_metabox_cat" style="width:100%;">
                    <option value="0"><?php esc_html_e( '— Selecciona categoría —', 'newestler' ); ?></option>
                    <?php foreach ( $categories as $c ) : ?>
                        <option value="<?php echo esc_attr( $c->term_id ); ?>" <?php selected( $default_cat, $c->term_id ); ?>><?php echo esc_html( $c->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label><strong><?php esc_html_e( 'Fecha inicio', 'newestler' ); ?></strong></label>
                <input type="date" id="nw_metabox_start" value="<?php echo esc_attr( $start_default ); ?>" style="width:100%;" />
            </p>

            <p>
                <label><strong><?php esc_html_e( 'Fecha fin', 'newestler' ); ?></strong></label>
                <input type="date" id="nw_metabox_end" value="<?php echo esc_attr( $end_default ); ?>" style="width:100%;" />
            </p>

            <p>
                <label><strong><?php esc_html_e( 'Límite', 'newestler' ); ?></strong> <small><?php esc_html_e( '(0 = todos)', 'newestler' ); ?></small></label>
                <input type="number" id="nw_metabox_limit" value="<?php echo esc_attr( $default_limit ); ?>" min="0" step="1" style="width:100%;" />
            </p>

            <p>
                <button type="button" class="button button-primary" id="nw_insert_shortcode"><?php esc_html_e( 'Insertar shortcode', 'newestler' ); ?></button>
                <span id="nw_insert_status" style="margin-left:8px;"></span>
            </p>

            <p style="margin-top:10px;font-size:12px;color:#666;">
                <?php esc_html_e( 'Esto generará e insertará un shortcode del tipo:', 'newestler' ); ?>
                <br><code id="nw_shortcode_example">[newestler_boletin]</code>
            </p>
        </div>
        <?php
    }

    public function enqueue_admin_assets( $hook ) {
        // Cargar solo donde haga falta: post.php, post-new.php, settings page
        if ( in_array( $hook, array( 'post.php', 'post-new.php', 'settings_page_newestler-settings' ), true ) ) {
            // Encolar JS
            wp_enqueue_script( 'newestler-admin-js', NEWESTLER_URL . 'assets/js/admin-newestler.js', array( 'jquery' ), NEWESTLER_VERSION, true );
            // Encolar CSS ligero
            wp_enqueue_style( 'newestler-admin-css', NEWESTLER_URL . 'assets/css/admin.css', array(), NEWESTLER_VERSION );
            // Pasar datos a JS
            $options = get_option( $this->option_name, array() );
            wp_localize_script( 'newestler-admin-js', 'NewestlerAdmin', array(
                'default_category' => isset( $options['default_category'] ) ? intval( $options['default_category'] ) : 0,
                'nonce' => wp_create_nonce( 'newestler_admin_nonce' ),
            ) );
        }
    }
}

// Inicializar
Newestler_Admin::instance();
