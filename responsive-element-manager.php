<?php
/**
 * Plugin Name: Responsive Element Manager
 * Plugin URI: https://yoursite.com/plugins/responsive-element-manager
 * Description: Gestisce il comportamento responsive degli elementi del sito in tempo reale con controlli avanzati
 * Version: 1.2.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: responsive-element-manager
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definisce le costanti del plugin
define('REM_VERSION', '1.2.0');
define('REM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('REM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('REM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('REM_DB_VERSION', '1.2');

/**
 * Classe principale del plugin Responsive Element Manager - VERSIONE CORRETTA
 */
class ResponsiveElementManager {
    
    private static $instance = null;
    private $modules = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Hook di attivazione/disattivazione
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Inizializzazione del plugin
     */
    public function init() {
        // Verifica versione PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }
        
        // Carica i moduli
        $this->load_modules();
        
        // Aggiunge le azioni WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_head', array($this, 'output_custom_css'));
        
        // AJAX Endpoints - CORRETTI E COMPLETI
        add_action('wp_ajax_rem_save_rule', array($this, 'ajax_save_rule'));
        add_action('wp_ajax_rem_delete_rule', array($this, 'ajax_delete_rule'));
        add_action('wp_ajax_rem_get_rules', array($this, 'ajax_get_rules'));
        add_action('wp_ajax_rem_get_css', array($this, 'ajax_get_css')); // NUOVO ENDPOINT
        add_action('wp_ajax_rem_export_rules', array($this, 'ajax_export_rules'));
        add_action('wp_ajax_rem_import_rules', array($this, 'ajax_import_rules'));
        add_action('wp_ajax_rem_get_element_info', array($this, 'ajax_get_element_info'));
        add_action('wp_ajax_rem_test_connection', array($this, 'ajax_test_connection')); // NUOVO
        
        // Hook per estensioni
        do_action('rem_loaded', $this);
        
        // Verifica aggiornamenti database
        $this->check_database_updates();
    }
    
    /**
     * Carica la traduzione
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'responsive-element-manager',
            false,
            dirname(REM_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Carica i moduli del plugin
     */
    private function load_modules() {
        // Directory moduli
        $modules_dir = REM_PLUGIN_PATH . 'modules/';
        
        if (!is_dir($modules_dir)) {
            wp_mkdir_p($modules_dir);
        }
        
        // Carica moduli core (se esistono file separati)
        $core_modules = array(
            'includes/class-rem-database.php',
            'includes/class-rem-css-generator.php',
            'includes/class-rem-rule-manager.php',
            'includes/class-rem-config.php'
        );
        
        foreach ($core_modules as $module) {
            $file_path = REM_PLUGIN_PATH . $module;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // Carica moduli aggiuntivi dalla directory modules/
        if (is_dir($modules_dir)) {
            $module_files = glob($modules_dir . '*.php');
            
            foreach ($module_files as $file) {
                if (is_readable($file)) {
                    require_once $file;
                    
                    $module_name = basename($file, '.php');
                    $this->modules[$module_name] = array(
                        'file' => $file,
                        'loaded' => true,
                        'loaded_at' => current_time('mysql')
                    );
                }
            }
        }
        
        // Hook per moduli personalizzati
        do_action('rem_modules_loaded', $this->modules);
    }
    
    /**
     * Attivazione del plugin
     */
    public function activate() {
        // Crea/aggiorna tabelle database
        REM_Database::create_tables();
        REM_Database::upgrade_tables();
        
        // Imposta opzioni predefinite
        $default_settings = array(
            'enable_frontend_editor' => true,
            'enable_admin_bar_menu' => true,
            'auto_save_changes' => false,
            'load_on_all_pages' => true,
            'minimum_user_capability' => 'edit_posts',
            'enable_css_minification' => false,
            'enable_backup_system' => true,
            'max_rules_per_page' => 1000,
            'enable_auto_proportions' => true, // NUOVO
            'enable_position_controls' => true, // NUOVO
            'enable_alignment_controls' => true // NUOVO
        );
        
        add_option('rem_settings', $default_settings);
        add_option('rem_version', REM_VERSION);
        add_option('rem_db_version', REM_DB_VERSION);
        add_option('rem_activated_at', current_time('mysql'));
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Hook per estensioni
        do_action('rem_activate');
    }
    
    /**
     * Disattivazione del plugin
     */
    public function deactivate() {
        // Pulisci i cron job
        wp_clear_scheduled_hook('rem_cleanup_database');
        wp_clear_scheduled_hook('rem_backup_rules');
        
        // Hook per estensioni
        do_action('rem_deactivate');
    }
    
    /**
     * Carica gli script frontend - VERSIONE CORRETTA
     */
    public function enqueue_frontend_scripts() {
        // Verifica permessi utente
        $settings = get_option('rem_settings', array());
        $min_capability = $settings['minimum_user_capability'] ?? 'edit_posts';
        
        if (!current_user_can($min_capability)) {
            return;
        }
        
        // Verifica se caricare su questa pagina
        if (!$this->should_load_on_current_page()) {
            return;
        }
        
        wp_enqueue_script(
            'rem-frontend',
            REM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            REM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'rem-frontend',
            REM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            REM_VERSION
        );
        
        // Localizza script con dati necessari - CORRETTO
        wp_localize_script('rem-frontend', 'rem_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rem_nonce'),
            'current_post_id' => get_the_ID(),
            'plugin_url' => REM_PLUGIN_URL,
            'settings' => $this->get_frontend_settings(),
            'breakpoints' => $this->get_breakpoints(),
            'user_preferences' => $this->get_user_preferences(),
            'device_proportions' => $this->get_device_proportions(), // NUOVO
            'supported_properties' => $this->get_supported_properties() // NUOVO
        ));
        
        // Hook per moduli che vogliono aggiungere script
        do_action('rem_frontend_enqueue_scripts');
    }
    
    /**
     * Carica gli script admin
     */
    public function enqueue_admin_scripts($hook) {
        // Carica solo nelle pagine del plugin
        if (strpos($hook, 'responsive-elements') === false) {
            return;
        }
        
        wp_enqueue_script(
            'rem-admin',
            REM_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            REM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'rem-admin',
            REM_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            REM_VERSION
        );
        
        // Localizza script admin
        wp_localize_script('rem-admin', 'remAdmin', array(
            'nonce' => wp_create_nonce('rem_admin_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'plugin_url' => REM_PLUGIN_URL,
            'modules' => $this->get_active_modules(),
            'breakpoints' => $this->get_breakpoints(),
            'settings' => get_option('rem_settings', array())
        ));
        
        // Hook per moduli
        do_action('rem_admin_enqueue_scripts', $hook);
    }
    
    /**
     * Aggiunge il menu admin
     */
    public function add_admin_menu() {
        $settings = get_option('rem_settings', array());
        $min_capability = $settings['minimum_user_capability'] ?? 'manage_options';
        
        // Menu principale
        add_menu_page(
            __('Responsive Elements', 'responsive-element-manager'),
            __('Responsive Elements', 'responsive-element-manager'),
            $min_capability,
            'responsive-elements',
            array($this, 'admin_page_dashboard'),
            'dashicons-smartphone',
            30
        );
        
        // Sottomenu
        add_submenu_page(
            'responsive-elements',
            __('Dashboard', 'responsive-element-manager'),
            __('Dashboard', 'responsive-element-manager'),
            $min_capability,
            'responsive-elements',
            array($this, 'admin_page_dashboard')
        );
        
        add_submenu_page(
            'responsive-elements',
            __('Regole', 'responsive-element-manager'),
            __('Regole', 'responsive-element-manager'),
            $min_capability,
            'responsive-elements-rules',
            array($this, 'admin_page_rules')
        );
        
        add_submenu_page(
            'responsive-elements',
            __('Impostazioni', 'responsive-element-manager'),
            __('Impostazioni', 'responsive-element-manager'),
            $min_capability,
            'responsive-elements-settings',
            array($this, 'admin_page_settings')
        );
        
        add_submenu_page(
            'responsive-elements',
            __('Moduli', 'responsive-element-manager'),
            __('Moduli', 'responsive-element-manager'),
            $min_capability,
            'responsive-elements-modules',
            array($this, 'admin_page_modules')
        );
        
        // Hook per aggiungere pagine personalizzate
        do_action('rem_admin_menu', $min_capability);
    }
    
    /**
     * Pagina dashboard admin
     */
    public function admin_page_dashboard() {
        include REM_PLUGIN_PATH . 'includes/admin-page.php';
    }
    
    /**
     * Pagina regole admin
     */
    public function admin_page_rules() {
        include REM_PLUGIN_PATH . 'includes/admin-rules-list.php';
    }
    
    /**
     * Pagina impostazioni admin
     */
    public function admin_page_settings() {
        include REM_PLUGIN_PATH . 'includes/admin-settings.php';
    }
    
    /**
     * Pagina moduli admin
     */
    public function admin_page_modules() {
        ?>
        <div class="wrap">
            <h1><?php _e('Moduli Responsive Element Manager', 'responsive-element-manager'); ?></h1>
            
            <div class="rem-modules-grid">
                <?php foreach ($this->modules as $module_id => $module_data): ?>
                    <div class="rem-module-card">
                        <h3><?php echo esc_html(ucwords(str_replace('-', ' ', $module_id))); ?></h3>
                        <p><strong><?php _e('File:', 'responsive-element-manager'); ?></strong> <?php echo esc_html(basename($module_data['file'])); ?></p>
                        <p><strong><?php _e('Caricato:', 'responsive-element-manager'); ?></strong> <?php echo esc_html($module_data['loaded_at']); ?></p>
                        <span class="rem-module-status rem-module-active"><?php _e('Attivo', 'responsive-element-manager'); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($this->modules)): ?>
                    <div class="rem-no-modules">
                        <p><?php _e('Nessun modulo aggiuntivo trovato.', 'responsive-element-manager'); ?></p>
                        <p><?php _e('Aggiungi moduli nella directory:', 'responsive-element-manager'); ?> <code>modules/</code></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .rem-modules-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .rem-module-card { background: white; padding: 20px; border: 1px solid #ccd0d4; border-radius: 8px; }
        .rem-module-status { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .rem-module-active { background: #d4edda; color: #155724; }
        .rem-no-modules { grid-column: 1 / -1; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; }
        </style>
        <?php
    }
    
    /**
     * Output CSS personalizzato
     */
    public function output_custom_css() {
        $css = REM_CSS_Generator::generate_css();
        if (!empty($css)) {
            echo "<style id='rem-custom-css'>\n" . $css . "\n</style>\n";
        }
        
        // Hook per moduli che vogliono aggiungere CSS
        do_action('rem_output_custom_css');
    }
    
    /**
     * AJAX: Salva regola - VERSIONE CORRETTA
     */
    public function ajax_save_rule() {
        // Verifica nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        $settings = get_option('rem_settings', array());
        $min_capability = $settings['minimum_user_capability'] ?? 'edit_posts';
        
        if (!current_user_can($min_capability)) {
            wp_send_json_error(__('Permessi insufficienti', 'responsive-element-manager'));
            return;
        }
        
        $rule_data = json_decode(stripslashes($_POST['rule_data'] ?? '{}'), true);
        
        if (empty($rule_data)) {
            wp_send_json_error(__('Dati regola non validi', 'responsive-element-manager'));
            return;
        }
        
        // Log per debug
        error_log('REM: Saving rule data: ' . print_r($rule_data, true));
        
        $result = REM_Rule_Manager::save_rule($rule_data);
        
        if (is_wp_error($result)) {
            error_log('REM: Error saving rule: ' . $result->get_error_message());
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        // Pulisci la cache CSS se esiste
        $this->clear_css_cache();
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Elimina regola
     */
    public function ajax_delete_rule() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        $settings = get_option('rem_settings', array());
        $min_capability = $settings['minimum_user_capability'] ?? 'edit_posts';
        
        if (!current_user_can($min_capability)) {
            wp_send_json_error(__('Permessi insufficienti', 'responsive-element-manager'));
            return;
        }
        
        $rule_id = intval($_POST['rule_id'] ?? 0);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('ID regola non valido', 'responsive-element-manager'));
            return;
        }
        
        $result = REM_Rule_Manager::delete_rule($rule_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        // Pulisci la cache CSS
        $this->clear_css_cache();
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Ottieni regole
     */
    public function ajax_get_rules() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $rules = REM_Rule_Manager::get_rules($post_id);
        
        wp_send_json_success($rules);
    }
    
    /**
     * NUOVO: AJAX: Ottieni CSS generato
     */
    public function ajax_get_css() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $css = REM_CSS_Generator::generate_css($post_id);
        
        wp_send_json_success(array(
            'css' => $css,
            'post_id' => $post_id,
            'generated_at' => current_time('mysql')
        ));
    }
    
    /**
     * NUOVO: AJAX: Test connessione
     */
    public function ajax_test_connection() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        // Test basic della connessione
        $test_data = array(
            'server_time' => current_time('mysql'),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => REM_VERSION,
            'memory_usage' => size_format(memory_get_usage()),
            'database_connection' => true
        );
        
        // Test connessione database
        global $wpdb;
        $table_name = $wpdb->prefix . 'rem_rules';
        try {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $test_data['rules_count'] = intval($count);
        } catch (Exception $e) {
            $test_data['database_connection'] = false;
            $test_data['database_error'] = $e->getMessage();
        }
        
        wp_send_json_success($test_data);
    }
    
    /**
     * AJAX: Esporta regole
     */
    public function ajax_export_rules() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permessi insufficienti', 'responsive-element-manager'));
            return;
        }
        
        $format = sanitize_text_field($_POST['format'] ?? 'json');
        $scope = sanitize_text_field($_POST['scope'] ?? 'all');
        
        $export_data = REM_Rule_Manager::export_rules($format, $scope);
        
        wp_send_json_success($export_data);
    }
    
    /**
     * AJAX: Importa regole
     */
    public function ajax_import_rules() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permessi insufficienti', 'responsive-element-manager'));
            return;
        }
        
        $import_data = json_decode(stripslashes($_POST['import_data'] ?? '{}'), true);
        
        if (empty($import_data)) {
            wp_send_json_error(__('Dati importazione non validi', 'responsive-element-manager'));
            return;
        }
        
        $result = REM_Rule_Manager::import_rules($import_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Ottieni informazioni elemento
     */
    public function ajax_get_element_info() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error(__('Nonce non valido', 'responsive-element-manager'));
            return;
        }
        
        $selector = sanitize_text_field($_POST['selector'] ?? '');
        
        if (empty($selector)) {
            wp_send_json_error(__('Selettore non fornito', 'responsive-element-manager'));
            return;
        }
        
        // Qui potresti implementare logica per analizzare l'elemento
        $element_info = array(
            'selector' => $selector,
            'exists' => true, // In un'implementazione reale verificheresti questo
            'children_count' => 0,
            'has_rules' => false
        );
        
        wp_send_json_success($element_info);
    }
    
    /**
     * Verifica se caricare il plugin sulla pagina corrente
     */
    private function should_load_on_current_page() {
        $settings = get_option('rem_settings', array());
        
        if (!isset($settings['load_on_all_pages']) || $settings['load_on_all_pages']) {
            return true;
        }
        
        // Logica personalizzata per caricare solo su pagine specifiche
        return apply_filters('rem_should_load_on_current_page', true);
    }
    
    /**
     * Ottieni impostazioni per il frontend
     */
    private function get_frontend_settings() {
        $settings = get_option('rem_settings', array());
        
        return array(
            'auto_save' => $settings['auto_save_changes'] ?? false,
            'show_tooltips' => $settings['show_tooltips'] ?? true,
            'enable_keyboard_shortcuts' => $settings['enable_keyboard_shortcuts'] ?? true,
            'preview_delay' => $settings['preview_delay'] ?? 500,
            'enable_auto_proportions' => $settings['enable_auto_proportions'] ?? true,
            'enable_position_controls' => $settings['enable_position_controls'] ?? true,
            'enable_alignment_controls' => $settings['enable_alignment_controls'] ?? true
        );
    }
    
    /**
     * Ottieni breakpoint configurati
     */
    private function get_breakpoints() {
        $default_breakpoints = array(
            'mobile' => array(
                'label' => __('Mobile', 'responsive-element-manager'),
                'icon' => '📱',
                'max_width' => 767,
                'media_query' => '(max-width: 767px)'
            ),
            'tablet' => array(
                'label' => __('Tablet', 'responsive-element-manager'),
                'icon' => '📟',
                'min_width' => 768,
                'max_width' => 1023,
                'media_query' => '(min-width: 768px) and (max-width: 1023px)'
            ),
            'desktop' => array(
                'label' => __('Desktop', 'responsive-element-manager'),
                'icon' => '🖥️',
                'min_width' => 1024,
                'media_query' => '(min-width: 1024px)'
            )
        );
        
        return apply_filters('rem_breakpoints', $default_breakpoints);
    }
    
    /**
     * NUOVO: Ottieni proporzioni dispositivi
     */
    private function get_device_proportions() {
        $default_proportions = array(
            'mobile' => array('width' => 375, 'height' => 667),
            'tablet' => array('width' => 768, 'height' => 1024),
            'desktop' => array('width' => 1920, 'height' => 1080)
        );
        
        return apply_filters('rem_device_proportions', $default_proportions);
    }
    
    /**
     * NUOVO: Ottieni proprietà supportate
     */
    private function get_supported_properties() {
        $default_properties = array(
            'position', 'position_x', 'position_y',
            'font_size', 'font_family', 'font_weight', 'text_align',
            'text_color', 'background_color', 'border_color',
            'display', 'flex_direction', 'justify_content', 'align_items',
            'width', 'height', 'element_align',
            'margin_top', 'margin_right', 'margin_bottom', 'margin_left',
            'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
            'opacity', 'box_shadow', 'border_radius'
        );
        
        return apply_filters('rem_supported_properties', $default_properties);
    }
    
    /**
     * Ottieni preferenze utente
     */
    private function get_user_preferences() {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return array();
        }
        
        return get_user_meta($user_id, 'rem_preferences', true) ?: array();
    }
    
    /**
     * Ottieni moduli attivi
     */
    private function get_active_modules() {
        return array_map(function($module) {
            return array(
                'loaded' => $module['loaded'],
                'file' => basename($module['file'])
            );
        }, $this->modules);
    }
    
    /**
     * Verifica aggiornamenti database
     */
    private function check_database_updates() {
        $current_db_version = get_option('rem_db_version', '1.0');
        
        if (version_compare($current_db_version, REM_DB_VERSION, '<')) {
            REM_Database::upgrade_tables();
            update_option('rem_db_version', REM_DB_VERSION);
        }
    }
    
    /**
     * NUOVO: Pulisci cache CSS
     */
    private function clear_css_cache() {
        // Elimina cache CSS se presente
        delete_transient('rem_css_cache');
        delete_transient('rem_css_cache_' . get_the_ID());
        
        // Hook per estensioni
        do_action('rem_css_cache_cleared');
    }
    
    /**
     * Avviso versione PHP
     */
    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php _e('Responsive Element Manager:', 'responsive-element-manager'); ?></strong>
                <?php printf(
                    __('Richiede PHP 7.4 o superiore. Versione corrente: %s', 'responsive-element-manager'),
                    PHP_VERSION
                ); ?>
            </p>
        </div>
        <?php
    }
}

/**
 * Classe per gestire il database - VERSIONE CORRETTA
 */
class REM_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            element_selector varchar(500) NOT NULL,
            element_id varchar(255) DEFAULT '',
            element_class varchar(500) DEFAULT '',
            scope enum('page', 'site') NOT NULL DEFAULT 'page',
            post_id mediumint(9) DEFAULT 0,
            rules longtext NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            priority int DEFAULT 10,
            conditions longtext DEFAULT NULL,
            module_data longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_scope_post (scope, post_id),
            INDEX idx_selector (element_selector(255)),
            INDEX idx_active (is_active),
            INDEX idx_priority (priority)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        do_action('rem_database_created');
    }
    
    public static function upgrade_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        // Aggiungi colonne mancanti
        $columns_to_add = array(
            'is_active' => "ALTER TABLE $table_name ADD COLUMN is_active TINYINT(1) DEFAULT 1",
            'priority' => "ALTER TABLE $table_name ADD COLUMN priority INT DEFAULT 10",
            'conditions' => "ALTER TABLE $table_name ADD COLUMN conditions LONGTEXT DEFAULT NULL",
            'module_data' => "ALTER TABLE $table_name ADD COLUMN module_data LONGTEXT DEFAULT NULL"
        );
        
        foreach ($columns_to_add as $column => $sql) {
            $column_exists = $wpdb->get_results(
                $wpdb->prepare(
                    "SHOW COLUMNS FROM $table_name LIKE %s",
                    $column
                )
            );
            
            if (empty($column_exists)) {
                $wpdb->query($sql);
            }
        }
        
        do_action('rem_database_upgraded');
    }
}

/**
 * Classe per gestire le regole - VERSIONE CORRETTA
 */
class REM_Rule_Manager {
    
    public static function save_rule($rule_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        // Validazione dei dati
        $validated_data = self::validate_rule_data($rule_data);
        if (is_wp_error($validated_data)) {
            return $validated_data;
        }
        
        $data = array(
            'element_selector' => $validated_data['selector'],
            'element_id' => $validated_data['element_id'],
            'element_class' => $validated_data['element_class'],
            'scope' => $validated_data['scope'],
            'post_id' => $validated_data['scope'] === 'page' ? $validated_data['post_id'] : 0,
            'rules' => json_encode($validated_data['rules']),
            'is_active' => $validated_data['is_active'] ?? 1,
            'priority' => $validated_data['priority'] ?? 10,
            'conditions' => !empty($validated_data['conditions']) ? json_encode($validated_data['conditions']) : null,
            'module_data' => !empty($validated_data['module_data']) ? json_encode($validated_data['module_data']) : null
        );
        
        // Verifica se esiste già una regola per questo elemento
        $existing_rule = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE element_selector = %s AND scope = %s AND post_id = %d",
            $data['element_selector'],
            $data['scope'],
            $data['post_id']
        ));
        
        if ($existing_rule) {
            // Aggiorna regola esistente
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing_rule->id),
                array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s'),
                array('%d')
            );
            $rule_id = $existing_rule->id;
            $action = 'updated';
        } else {
            // Crea nuova regola
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s')
            );
            $rule_id = $wpdb->insert_id;
            $action = 'created';
        }
        
        if ($result === false) {
            error_log('REM: Database error: ' . $wpdb->last_error);
            return new WP_Error('db_error', __('Errore nel salvare la regola: ', 'responsive-element-manager') . $wpdb->last_error);
        }
        
        // Hook per estensioni
        do_action('rem_rule_saved', $validated_data, $rule_id, $action);
        
        return array(
            'success' => true, 
            'message' => __('Regola salvata con successo', 'responsive-element-manager'),
            'rule_id' => $rule_id,
            'action' => $action
        );
    }
    
    public static function delete_rule($rule_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        // Verifica che la regola esista
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $rule_id
        ));
        
        if (!$rule) {
            return new WP_Error('not_found', __('Regola non trovata', 'responsive-element-manager'));
        }
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $rule_id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Errore nell\'eliminare la regola', 'responsive-element-manager'));
        }
        
        // Hook per estensioni
        do_action('rem_rule_deleted', $rule_id, $rule);
        
        return array(
            'success' => true, 
            'message' => __('Regola eliminata con successo', 'responsive-element-manager')
        );
    }
    
    public static function get_rules($post_id = 0, $active_only = true) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $where_conditions = array();
        $where_values = array();
        
        if ($post_id > 0) {
            $where_conditions[] = "(scope = 'page' AND post_id = %d) OR scope = 'site'";
            $where_values[] = $post_id;
        } else {
            $where_conditions[] = "scope = 'site'";
        }
        
        if ($active_only) {
            $where_conditions[] = "is_active = 1";
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $sql = "SELECT * FROM $table_name WHERE $where_clause ORDER BY priority ASC, created_at DESC";
        
        if (!empty($where_values)) {
            $rules = $wpdb->get_results($wpdb->prepare($sql, $where_values));
        } else {
            $rules = $wpdb->get_results($sql);
        }
        
        // Decodifica i dati JSON
        foreach ($rules as &$rule) {
            $rule->rules = json_decode($rule->rules, true);
            $rule->conditions = $rule->conditions ? json_decode($rule->conditions, true) : null;
            $rule->module_data = $rule->module_data ? json_decode($rule->module_data, true) : null;
        }
        
        return apply_filters('rem_get_rules', $rules, $post_id);
    }
    
    public static function export_rules($format = 'json', $scope = 'all') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $where_clause = '';
        if ($scope === 'site') {
            $where_clause = "WHERE scope = 'site'";
        } elseif ($scope === 'page') {
            $where_clause = "WHERE scope = 'page'";
        }
        
        $rules = $wpdb->get_results("SELECT * FROM $table_name $where_clause ORDER BY created_at DESC");
        
        $export_data = array(
            'version' => REM_VERSION,
            'exported_at' => current_time('mysql'),
            'site_url' => get_site_url(),
            'total_rules' => count($rules),
            'rules' => $rules
        );
        
        if ($format === 'json') {
            return array(
                'content' => json_encode($export_data, JSON_PRETTY_PRINT),
                'filename' => 'rem-export-' . date('Y-m-d-H-i-s') . '.json',
                'mime_type' => 'application/json'
            );
        }
        
        return $export_data;
    }
    
    public static function import_rules($import_data) {
        global $wpdb;
        
        if (!isset($import_data['rules']) || !is_array($import_data['rules'])) {
            return new WP_Error('invalid_data', __('Dati di importazione non validi', 'responsive-element-manager'));
        }
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($import_data['rules'] as $rule_data) {
            $rule_array = (array) $rule_data;
            
            // Rimuovi l'ID per evitare conflitti
            unset($rule_array['id']);
            
            $result = self::save_rule($rule_array);
            
            if (is_wp_error($result)) {
                $skipped++;
            } else {
                $imported++;
            }
        }
        
        return array(
            'imported' => $imported,
            'skipped' => $skipped,
            'message' => sprintf(
                __('Importate %d regole, %d ignorate', 'responsive-element-manager'),
                $imported,
                $skipped
            )
        );
    }
    
    private static function validate_rule_data($data) {
        $validated = array();
        
        // Validazione selector
        if (empty($data['selector'])) {
            return new WP_Error('validation_error', __('Selettore elemento richiesto', 'responsive-element-manager'));
        }
        $validated['selector'] = sanitize_text_field($data['selector']);
        
        // Validazione scope
        if (!in_array($data['scope'] ?? 'page', array('page', 'site'))) {
            return new WP_Error('validation_error', __('Scope non valido', 'responsive-element-manager'));
        }
        $validated['scope'] = $data['scope'];
        
        // Altri campi
        $validated['post_id'] = intval($data['post_id'] ?? 0);
        $validated['element_id'] = sanitize_text_field($data['element_id'] ?? '');
        $validated['element_class'] = sanitize_text_field($data['element_class'] ?? '');
        $validated['is_active'] = !empty($data['is_active']) ? 1 : 1; // Default attivo
        $validated['priority'] = intval($data['priority'] ?? 10);
        
        // Validazione regole
        if (empty($data['rules']) || !is_array($data['rules'])) {
            return new WP_Error('validation_error', __('Regole non valide', 'responsive-element-manager'));
        }
        
        $validated['rules'] = $data['rules']; // La validazione dettagliata sarà fatta dai moduli
        $validated['conditions'] = $data['conditions'] ?? null;
        $validated['module_data'] = $data['module_data'] ?? null;
        
        return apply_filters('rem_validate_rule_data', $validated, $data);
    }
}

// Includi la classe CSS Generator corretta nel prossimo file...

// Inizializza il plugin
ResponsiveElementManager::get_instance();

// Funzioni helper globali
function rem_get_rules($post_id = 0) {
    return REM_Rule_Manager::get_rules($post_id);
}

function rem_save_rule($rule_data) {
    return REM_Rule_Manager::save_rule($rule_data);
}

function rem_delete_rule($rule_id) {
    return REM_Rule_Manager::delete_rule($rule_id);
}

function rem_generate_css($post_id = null) {
    return REM_CSS_Generator::generate_css($post_id);
}