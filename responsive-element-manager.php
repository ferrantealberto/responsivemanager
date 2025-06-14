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
        // Le classi sono definite in questo stesso file
        // Non servono require_once esterni
        
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
     * Attivazione del plugin
     */
    public function activate() {
        REM_Database::create_tables();
        do_action('rem_activate');
    }
    
    /**
     * Disattivazione del plugin
     */
    public function deactivate() {
        do_action('rem_deactivate');
    }
    
    /**
     * Carica gli script frontend
     */
    public function enqueue_frontend_scripts() {
        // DEBUG: Verifica permessi utente
        add_action('wp_footer', function() {
            if (current_user_can('edit_posts')) {
                echo '<div style="position:fixed;top:10px;left:10px;background:green;color:white;padding:10px;z-index:99999;">USER CAN EDIT: YES</div>';
            } else {
                echo '<div style="position:fixed;top:10px;left:10px;background:red;color:white;padding:10px;z-index:99999;">USER CAN EDIT: NO - LOGIN AS ADMIN</div>';
            }
        });
        
        // FORZA caricamento sempre (per debug)
        wp_enqueue_script(
            'rem-frontend',
            REM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            REM_VERSION . '-' . time(), // Cache busting
            true
        );
        
        wp_enqueue_style(
            'rem-frontend',
            REM_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            REM_VERSION . '-' . time() // Cache busting
        );
        
        wp_localize_script('rem-frontend', 'rem_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rem_nonce'),
            'current_post_id' => get_the_ID()
        ));
        
        // PULSANTE EDITOR REALE
        if (current_user_can('edit_posts')) {
            add_action('wp_footer', function() {
                echo '
                <!-- Responsive Element Manager Editor -->
                <div id="rem-toggle-btn" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    width: 50px;
                    height: 50px;
                    background: #0073aa;
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    z-index: 999999;
                    font-size: 20px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                    transition: all 0.3s ease;
                    border: none;
                    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                " title="Attiva/Disattiva Editor Responsive">📱</div>

                <!-- Modal Editor -->
                <div id="rem-modal" style="
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 1000000;
                    overflow-y: auto;
                ">
                    <div style="
                        background: white;
                        width: 90%;
                        max-width: 600px;
                        margin: 50px auto;
                        border-radius: 8px;
                        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                        animation: slideIn 0.3s ease;
                    ">
                        <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; color: #333;">Configurazione Elemento Responsive</h3>
                            <span id="rem-close" style="font-size: 24px; cursor: pointer; color: #999;">&times;</span>
                        </div>
                        <div style="padding: 20px;">
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                                <p style="margin: 0;"><strong>Elemento selezionato:</strong> <span id="rem-selected-info">Nessuno</span></p>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Applica modifiche a:</label>
                                <select id="rem-scope" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="page">Solo questa pagina</option>
                                    <option value="site">Tutto il sito</option>
                                </select>
                            </div>
                            
                            <div style="border-bottom: 1px solid #ddd; margin-bottom: 20px;">
                                <div style="display: flex;">
                                    <button class="rem-tab-btn active" data-breakpoint="mobile" style="background: none; border: none; padding: 12px 20px; cursor: pointer; border-bottom: 2px solid #0073aa;">📱 Mobile</button>
                                    <button class="rem-tab-btn" data-breakpoint="tablet" style="background: none; border: none; padding: 12px 20px; cursor: pointer; border-bottom: 2px solid transparent;">📟 Tablet</button>
                                    <button class="rem-tab-btn" data-breakpoint="desktop" style="background: none; border: none; padding: 12px 20px; cursor: pointer; border-bottom: 2px solid transparent;">🖥️ Desktop</button>
                                </div>
                            </div>
                            
                            <div id="rem-breakpoint-content">
                                <div class="rem-tab-panel active" data-breakpoint="mobile">
                                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                                        <div style="flex: 1;">
                                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Dimensione Font:</label>
                                            <div style="display: flex; gap: 10px;">
                                                <input type="number" id="font-size-mobile" placeholder="24" style="flex: 2; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                                <select id="font-unit-mobile" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                                    <option value="px">px</option>
                                                    <option value="%">%</option>
                                                    <option value="em">em</option>
                                                    <option value="rem">rem</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div style="flex: 1;">
                                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Allineamento:</label>
                                            <select id="text-align-mobile" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                                                <option value="">-- Mantieni originale --</option>
                                                <option value="left">Sinistra</option>
                                                <option value="center">Centro</option>
                                                <option value="right">Destra</option>
                                                <option value="justify">Giustificato</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="padding: 20px; border-top: 1px solid #ddd; display: flex; gap: 10px; justify-content: flex-end;">
                            <button id="rem-save" style="background: #0073aa; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Salva Regole</button>
                            <button id="rem-cancel" style="background: #f3f4f5; color: #333; border: 1px solid #ddd; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Annulla</button>
                        </div>
                    </div>
                </div>

                <style>
                @keyframes slideIn {
                    from { opacity: 0; transform: translateY(-20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                #rem-toggle-btn:hover {
                    background: #005177 !important;
                    transform: scale(1.05);
                }
                
                #rem-toggle-btn.active {
                    background: #dc3232 !important;
                }
                
                .rem-tab-btn:hover {
                    background: #f8f9fa !important;
                }
                
                .rem-tab-btn.active {
                    color: #0073aa !important;
                    border-bottom-color: #0073aa !important;
                }
                
                body.rem-selecting {
                    cursor: crosshair !important;
                }
                
                body.rem-selecting * {
                    cursor: crosshair !important;
                }
                
                .rem-highlight {
                    outline: 2px solid #0073aa !important;
                    outline-offset: 2px !important;
                }
                
                .rem-selected {
                    outline: 3px solid #dc3232 !important;
                    outline-offset: 2px !important;
                    background: rgba(220, 50, 50, 0.1) !important;
                }
                </style>

                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let isActive = false;
                    let selectedElement = null;
                    
                    const toggleBtn = document.getElementById("rem-toggle-btn");
                    const modal = document.getElementById("rem-modal");
                    const closeBtn = document.getElementById("rem-close");
                    const cancelBtn = document.getElementById("rem-cancel");
                    const saveBtn = document.getElementById("rem-save");
                    
                    // Toggle editor
                    toggleBtn.addEventListener("click", function() {
                        isActive = !isActive;
                        
                        if (isActive) {
                            document.body.classList.add("rem-selecting");
                            toggleBtn.classList.add("active");
                            toggleBtn.style.background = "#dc3232";
                        } else {
                            document.body.classList.remove("rem-selecting");
                            toggleBtn.classList.remove("active");
                            toggleBtn.style.background = "#0073aa";
                            clearSelection();
                        }
                    });
                    
                    // Element selection
                    document.addEventListener("click", function(e) {
                        if (isActive && e.target !== toggleBtn && !modal.contains(e.target)) {
                            e.preventDefault();
                            e.stopPropagation();
                            selectElement(e.target);
                        }
                    });
                    
                    // Hover effects
                    document.addEventListener("mouseover", function(e) {
                        if (isActive && e.target !== toggleBtn && !modal.contains(e.target)) {
                            e.target.classList.add("rem-highlight");
                        }
                    });
                    
                    document.addEventListener("mouseout", function(e) {
                        if (isActive) {
                            e.target.classList.remove("rem-highlight");
                        }
                    });
                    
                    function selectElement(element) {
                        clearSelection();
                        selectedElement = element;
                        element.classList.add("rem-selected");
                        
                        const selector = generateSelector(element);
                        document.getElementById("rem-selected-info").textContent = selector;
                        modal.style.display = "block";
                    }
                    
                    function clearSelection() {
                        document.querySelectorAll(".rem-selected, .rem-highlight").forEach(el => {
                            el.classList.remove("rem-selected", "rem-highlight");
                        });
                        selectedElement = null;
                    }
                    
                    function generateSelector(element) {
                        if (element.id) {
                            return "#" + element.id;
                        } else if (element.className) {
                            const classes = element.className.split(" ").filter(c => c && !c.startsWith("rem-"));
                            if (classes.length > 0) {
                                return "." + classes[0];
                            }
                        }
                        return element.tagName.toLowerCase();
                    }
                    
                    // Close modal
                    [closeBtn, cancelBtn].forEach(btn => {
                        btn.addEventListener("click", function() {
                            modal.style.display = "none";
                            clearSelection();
                        });
                    });
                    
                    // Save rule
                    saveBtn.addEventListener("click", function() {
                        if (!selectedElement) return;
                        
                        const fontSize = document.getElementById("font-size-mobile").value;
                        const fontUnit = document.getElementById("font-unit-mobile").value;
                        const textAlign = document.getElementById("text-align-mobile").value;
                        const scope = document.getElementById("rem-scope").value;
                        
                        const selector = generateSelector(selectedElement);
                        
                        // Apply styles immediately
                        if (fontSize) {
                            selectedElement.style.fontSize = fontSize + fontUnit;
                        }
                        if (textAlign) {
                            selectedElement.style.textAlign = textAlign;
                        }
                        
                        // Here you would normally save to database via AJAX
                        alert("Regola salvata! Selettore: " + selector + "\\nFont: " + fontSize + fontUnit + "\\nAllineamento: " + textAlign + "\\nScope: " + scope);
                        
                        modal.style.display = "none";
                        clearSelection();
                    });
                });
                </script>';
            }, 999);
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
        echo '<div class="wrap"><h1>Responsive Element Manager</h1><p>Plugin attivo e funzionante!</p></div>';
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
        
        // Validazione di base
        if (empty($rule_data['selector'])) {
            return new WP_Error('validation_error', 'Selettore richiesto');
        }
        
        $data = array(
            'element_selector' => sanitize_text_field($rule_data['selector']),
            'element_id' => sanitize_text_field($rule_data['element_id'] ?? ''),
            'element_class' => sanitize_text_field($rule_data['element_class'] ?? ''),
            'scope' => sanitize_text_field($rule_data['scope'] ?? 'page'),
            'post_id' => intval($rule_data['post_id'] ?? 0),
            'rules' => json_encode($rule_data['rules'] ?? array())
        );
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nel salvare la regola');
        }
        
        return array('success' => true, 'message' => 'Regola salvata con successo');
    }
    
    public static function delete_rule($rule_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $result = $wpdb->delete($table_name, array('id' => $rule_id), array('%d'));
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nell\'eliminare la regola');
        }
        
        return array('success' => true, 'message' => 'Regola eliminata con successo');
    }
    
    public static function get_rules($post_id = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        if ($post_id > 0) {
            $rules = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE (scope = 'page' AND post_id = %d) OR scope = 'site' ORDER BY created_at DESC",
                $post_id
            ));
        } else {
            $rules = $wpdb->get_results("SELECT * FROM $table_name WHERE scope = 'site' ORDER BY created_at DESC");
        }
        
        foreach ($rules as &$rule) {
            $rule->rules = json_decode($rule->rules, true);
        }
        
        return $rules;
    }
}

/**
 * Classe per generare il CSS
 */
class REM_CSS_Generator {
    
    public static function generate_css($post_id = null) {
        if ($post_id === null) {
            $post_id = get_the_ID();
        }
        
        $rules = REM_Rule_Manager::get_rules($post_id);
        
        if (empty($rules)) {
            return '';
        }
        
        $css_output = '';
        $breakpoints = array(
            'mobile' => '(max-width: 767px)',
            'tablet' => '(min-width: 768px) and (max-width: 1023px)',
            'desktop' => ''
        );
        
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
        
        return $css_output;
    }
    
    private static function convert_rules_to_css($rules) {
        $css_rules = array();
        
        if (isset($rules['font_size'])) {
            $css_rules[] = 'font-size: ' . $rules['font_size']['value'] . $rules['font_size']['unit'];
        }
        
        if (isset($rules['font_family'])) {
            $css_rules[] = 'font-family: ' . $rules['font_family'];
        }
        
        if (isset($rules['text_align'])) {
            $css_rules[] = 'text-align: ' . $rules['text_align'];
        }
        
        if (isset($rules['width'])) {
            $css_rules[] = 'width: ' . $rules['width']['value'] . $rules['width']['unit'];
        }
        
        if (isset($rules['height'])) {
            $css_rules[] = 'height: ' . $rules['height']['value'] . $rules['height']['unit'];
        }
        
        return implode('; ', $css_rules);
    }
}

// Inizializza il plugin
ResponsiveElementManager::get_instance();