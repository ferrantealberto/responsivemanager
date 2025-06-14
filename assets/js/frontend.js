<?php
/**
 * Plugin Name: Responsive Element Manager
 * Plugin URI: https://yoursite.com/plugins/responsive-element-manager
 * Description: Gestisce il comportamento responsive degli elementi del sito in tempo reale
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definisce le costanti del plugin
define('REM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('REM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('REM_VERSION', '1.0.0');

/**
 * Classe principale del plugin
 */
class ResponsiveElementManager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Carica i moduli
        $this->load_modules();
        
        // Hooks di attivazione/disattivazione
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Aggiunge le azioni WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_rem_save_rule', array($this, 'ajax_save_rule'));
        add_action('wp_ajax_rem_delete_rule', array($this, 'ajax_delete_rule'));
        add_action('wp_ajax_rem_get_rules', array($this, 'ajax_get_rules'));
        add_action('wp_head', array($this, 'output_custom_css'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Hook per estensioni future
        do_action('rem_loaded', $this);
    }
    
    /**
     * Carica i moduli del plugin
     */
    private function load_modules() {
        require_once REM_PLUGIN_PATH . 'includes/class-rem-database.php';
        require_once REM_PLUGIN_PATH . 'includes/class-rem-css-generator.php';
        require_once REM_PLUGIN_PATH . 'includes/class-rem-element-selector.php';
        require_once REM_PLUGIN_PATH . 'includes/class-rem-rule-manager.php';
        
        // Hook per caricare moduli aggiuntivi
        do_action('rem_load_modules');
    }
    
    /**
     * Attivazione del plugin
     */
    public function activate() {
        REM_Database::create_tables();
        
        // Hook per estensioni
        do_action('rem_activate');
    }
    
    /**
     * Disattivazione del plugin
     */
    public function deactivate() {
        // Hook per estensioni
        do_action('rem_deactivate');
    }
    
    /**
     * Carica gli script frontend
     */
    public function enqueue_frontend_scripts() {
        if (current_user_can('edit_posts')) {
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
            
            wp_localize_script('rem-frontend', 'rem_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rem_nonce'),
                'current_post_id' => get_the_ID()
            ));
        }
    }
    
    /**
     * Carica gli script admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'responsive-elements') !== false) {
            wp_enqueue_script(
                'rem-admin',
                REM_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                REM_VERSION,
                true
            );
            
            wp_enqueue_style(
                'rem-admin',
                REM_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                REM_VERSION
            );
        }
    }
    
    /**
     * Aggiunge il menu admin
     */
    public function add_admin_menu() {
        add_menu_page(
            'Responsive Elements',
            'Responsive Elements',
            'manage_options',
            'responsive-elements',
            array($this, 'admin_page'),
            'dashicons-smartphone',
            30
        );
    }
    
    /**
     * Pagina admin
     */
    public function admin_page() {
        include REM_PLUGIN_PATH . 'includes/admin-page.php';
    }
    
    /**
     * AJAX: Salva regola
     */
    public function ajax_save_rule() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Non autorizzato');
        }
        
        $rule_data = $_POST['rule_data'];
        $result = REM_Rule_Manager::save_rule($rule_data);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX: Elimina regola
     */
    public function ajax_delete_rule() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_die('Non autorizzato');
        }
        
        $rule_id = intval($_POST['rule_id']);
        $result = REM_Rule_Manager::delete_rule($rule_id);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX: Ottieni regole
     */
    public function ajax_get_rules() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $rules = REM_Rule_Manager::get_rules($post_id);
        
        wp_send_json_success($rules);
    }
    
    /**
     * Output CSS personalizzato
     */
    public function output_custom_css() {
        $css = REM_CSS_Generator::generate_css();
        if (!empty($css)) {
            echo "<style id='rem-custom-css'>\n" . $css . "\n</style>\n";
        }
    }
}

/**
 * Classe per gestire il database
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
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_scope_post (scope, post_id),
            INDEX idx_selector (element_selector(255))
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Hook per estensioni
        do_action('rem_database_created');
    }
}

/**
 * Classe per gestire le regole
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
            'rules' => json_encode($validated_data['rules'])
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
                array('%s', '%s', '%s', '%s', '%d', '%s'),
                array('%d')
            );
        } else {
            // Crea nuova regola
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%s', '%s', '%s', '%s', '%d', '%s')
            );
        }
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nel salvare la regola');
        }
        
        // Hook per estensioni
        do_action('rem_rule_saved', $validated_data);
        
        return array('success' => true, 'message' => 'Regola salvata con successo');
    }
    
    public static function delete_rule($rule_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $rule_id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nell\'eliminare la regola');
        }
        
        // Hook per estensioni
        do_action('rem_rule_deleted', $rule_id);
        
        return array('success' => true, 'message' => 'Regola eliminata con successo');
    }
    
    public static function get_rules($post_id = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        if ($post_id > 0) {
            // Ottieni regole specifiche per la pagina e globali
            $rules = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE (scope = 'page' AND post_id = %d) OR scope = 'site' ORDER BY created_at DESC",
                $post_id
            ));
        } else {
            // Ottieni solo regole globali
            $rules = $wpdb->get_results(
                "SELECT * FROM $table_name WHERE scope = 'site' ORDER BY created_at DESC"
            );
        }
        
        // Decodifica le regole JSON
        foreach ($rules as &$rule) {
            $rule->rules = json_decode($rule->rules, true);
        }
        
        return $rules;
    }
    
    private static function validate_rule_data($data) {
        $validated = array();
        
        // Validazione selector
        if (empty($data['selector'])) {
            return new WP_Error('validation_error', 'Selettore elemento richiesto');
        }
        $validated['selector'] = sanitize_text_field($data['selector']);
        
        // Validazione scope
        if (!in_array($data['scope'], array('page', 'site'))) {
            return new WP_Error('validation_error', 'Scope non valido');
        }
        $validated['scope'] = $data['scope'];
        
        // Validazione post_id
        $validated['post_id'] = isset($data['post_id']) ? intval($data['post_id']) : 0;
        
        // Validazione element_id e element_class
        $validated['element_id'] = isset($data['element_id']) ? sanitize_text_field($data['element_id']) : '';
        $validated['element_class'] = isset($data['element_class']) ? sanitize_text_field($data['element_class']) : '';
        
        // Validazione regole
        if (empty($data['rules']) || !is_array($data['rules'])) {
            return new WP_Error('validation_error', 'Regole non valide');
        }
        
        $validated['rules'] = self::validate_rules($data['rules']);
        if (is_wp_error($validated['rules'])) {
            return $validated['rules'];
        }
        
        return apply_filters('rem_validate_rule_data', $validated, $data);
    }
    
    private static function validate_rules($rules) {
        $validated_rules = array();
        
        foreach ($rules as $breakpoint => $rule_set) {
            if (!is_array($rule_set)) continue;
            
            $validated_rules[$breakpoint] = array();
            
            // Validazione font size
            if (isset($rule_set['font_size'])) {
                $font_size = $rule_set['font_size'];
                if (isset($font_size['value']) && isset($font_size['unit'])) {
                    $validated_rules[$breakpoint]['font_size'] = array(
                        'value' => floatval($font_size['value']),
                        'unit' => in_array($font_size['unit'], array('px', '%', 'em', 'rem')) ? $font_size['unit'] : 'px'
                    );
                }
            }
            
            // Validazione font family
            if (isset($rule_set['font_family'])) {
                $validated_rules[$breakpoint]['font_family'] = sanitize_text_field($rule_set['font_family']);
            }
            
            // Validazione allineamento
            if (isset($rule_set['text_align'])) {
                if (in_array($rule_set['text_align'], array('left', 'center', 'right', 'justify'))) {
                    $validated_rules[$breakpoint]['text_align'] = $rule_set['text_align'];
                }
            }
            
            // Validazione dimensioni
            if (isset($rule_set['width'])) {
                $width = $rule_set['width'];
                if (isset($width['value']) && isset($width['unit'])) {
                    $validated_rules[$breakpoint]['width'] = array(
                        'value' => floatval($width['value']),
                        'unit' => in_array($width['unit'], array('px', '%', 'em', 'rem', 'vw')) ? $width['unit'] : 'px'
                    );
                }
            }
            
            if (isset($rule_set['height'])) {
                $height = $rule_set['height'];
                if (isset($height['value']) && isset($height['unit'])) {
                    $validated_rules[$breakpoint]['height'] = array(
                        'value' => floatval($height['value']),
                        'unit' => in_array($height['unit'], array('px', '%', 'em', 'rem', 'vh')) ? $height['unit'] : 'px'
                    );
                }
            }
        }
        
        return apply_filters('rem_validate_rules', $validated_rules, $rules);
    }
}

/**
 * Classe per generare il CSS
 */
class REM_CSS_Generator {
    
    public static function generate_css() {
        $rules = REM_Rule_Manager::get_rules(get_the_ID());
        
        if (empty($rules)) {
            return '';
        }
        
        $css_output = '';
        $breakpoints = self::get_breakpoints();
        
        foreach ($rules as $rule) {
            $selector = $rule->element_selector;
            $rule_css = $rule->rules;
            
            foreach ($breakpoints as $breakpoint => $media_query) {
                if (isset($rule_css[$breakpoint]) && !empty($rule_css[$breakpoint])) {
                    $css_rules = self::convert_rules_to_css($rule_css[$breakpoint]);
                    
                    if (!empty($css_rules)) {
                        if ($breakpoint === 'desktop') {
                            $css_output .= "$selector { $css_rules }\n";
                        } else {
                            $css_output .= "@media $media_query { $selector { $css_rules } }\n";
                        }
                    }
                }
            }
        }
        
        return apply_filters('rem_generated_css', $css_output, $rules);
    }
    
    private static function get_breakpoints() {
        $breakpoints = array(
            'mobile' => '(max-width: 767px)',
            'tablet' => '(min-width: 768px) and (max-width: 1023px)',
            'desktop' => '', // No media query for desktop (default)
        );
        
        return apply_filters('rem_breakpoints', $breakpoints);
    }
    
    private static function convert_rules_to_css($rules) {
        $css_rules = array();
        
        // Font size
        if (isset($rules['font_size'])) {
            $css_rules[] = 'font-size: ' . $rules['font_size']['value'] . $rules['font_size']['unit'];
        }
        
        // Font family
        if (isset($rules['font_family'])) {
            $css_rules[] = 'font-family: ' . $rules['font_family'];
        }
        
        // Text align
        if (isset($rules['text_align'])) {
            $css_rules[] = 'text-align: ' . $rules['text_align'];
        }
        
        // Width
        if (isset($rules['width'])) {
            $css_rules[] = 'width: ' . $rules['width']['value'] . $rules['width']['unit'];
        }
        
        // Height
        if (isset($rules['height'])) {
            $css_rules[] = 'height: ' . $rules['height']['value'] . $rules['height']['unit'];
        }
        
        return apply_filters('rem_css_rules', implode('; ', $css_rules), $rules);
    }
}

// Inizializza il plugin
ResponsiveElementManager::get_instance();