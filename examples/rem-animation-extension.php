<?php
/**
 * ESEMPIO: Modulo di estensione per Responsive Element Manager
 * 
 * Questo file dimostra come creare un modulo personalizzato che estende
 * le funzionalità del plugin principale.
 * 
 * NOME MODULO: Animation Effects
 * DESCRIZIONE: Aggiunge controlli per animazioni CSS e transizioni
 * VERSIONE: 1.0.0
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe del modulo Animation Effects
 */
class REM_Animation_Extension {
    
    const VERSION = '1.0.0';
    const MODULE_ID = 'animations';
    
    private static $instance = null;
    
    /**
     * Singleton pattern
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Costruttore privato
     */
    private function __construct() {
        // Verifica che il plugin principale sia attivo
        if (!class_exists('ResponsiveElementManager')) {
            add_action('admin_notices', array($this, 'missing_plugin_notice'));
            return;
        }
        
        $this->init();
    }
    
    /**
     * Inizializzazione del modulo
     */
    public function init() {
        // Hook nel sistema del plugin principale
        add_action('rem_loaded', array($this, 'register_module'));
        add_filter('rem_supported_properties', array($this, 'add_animation_properties'));
        add_filter('rem_validate_rules', array($this, 'validate_animation_rules'), 10, 2);
        add_filter('rem_css_rules', array($this, 'generate_animation_css'), 10, 2);
        add_action('rem_admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('rem_frontend_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        
        // Hook specifici del modulo
        add_action('wp_ajax_rem_get_animation_presets', array($this, 'ajax_get_presets'));
        add_action('wp_ajax_rem_save_animation_preset', array($this, 'ajax_save_preset'));
        
        // Registra il modulo nel sistema
        add_filter('rem_registered_modules', array($this, 'register_in_system'));
    }
    
    /**
     * Registra il modulo nel sistema principale
     */
    public function register_module() {
        do_action('rem_register_module', array(
            'id' => self::MODULE_ID,
            'name' => 'Animation Effects',
            'description' => 'Aggiunge controlli per animazioni CSS e transizioni',
            'version' => self::VERSION,
            'author' => 'Il tuo nome',
            'settings_callback' => array($this, 'render_settings'),
            'active' => true
        ));
    }
    
    /**
     * Aggiunge proprietà di animazione alle proprietà supportate
     */
    public function add_animation_properties($properties) {
        $animation_properties = array(
            'transition_duration' => array(
                'type' => 'dimension',
                'units' => array('s', 'ms'),
                'min' => 0,
                'max' => 10,
                'default_unit' => 's',
                'label' => 'Durata Transizione',
                'group' => 'animation'
            ),
            'transition_property' => array(
                'type' => 'select',
                'values' => array(
                    'all' => 'Tutte le proprietà',
                    'opacity' => 'Opacità',
                    'transform' => 'Trasformazione',
                    'color' => 'Colore',
                    'background-color' => 'Colore di sfondo',
                    'width' => 'Larghezza',
                    'height' => 'Altezza'
                ),
                'label' => 'Proprietà Transizione',
                'group' => 'animation'
            ),
            'transition_timing' => array(
                'type' => 'select',
                'values' => array(
                    'ease' => 'Ease',
                    'ease-in' => 'Ease In',
                    'ease-out' => 'Ease Out',
                    'ease-in-out' => 'Ease In Out',
                    'linear' => 'Linear',
                    'cubic-bezier(0.25, 0.1, 0.25, 1)' => 'Personalizzato'
                ),
                'label' => 'Timing Transizione',
                'group' => 'animation'
            ),
            'hover_transform' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Nessuna',
                    'scale(1.05)' => 'Scala leggera',
                    'scale(1.1)' => 'Scala media',
                    'translateY(-5px)' => 'Muovi su',
                    'rotate(5deg)' => 'Ruota leggera',
                    'skewX(5deg)' => 'Inclina'
                ),
                'label' => 'Trasformazione Hover',
                'group' => 'animation'
            ),
            'animation_preset' => array(
                'type' => 'select',
                'values' => $this->get_animation_presets(),
                'label' => 'Preset Animazione',
                'group' => 'animation'
            )
        );
        
        return array_merge($properties, $animation_properties);
    }
    
    /**
     * Valida le regole di animazione
     */
    public function validate_animation_rules($validated_rules, $original_rules) {
        foreach ($validated_rules as $breakpoint => &$rule_set) {
            // Valida durata transizione
            if (isset($original_rules[$breakpoint]['transition_duration'])) {
                $duration = $original_rules[$breakpoint]['transition_duration'];
                if (isset($duration['value']) && isset($duration['unit'])) {
                    $value = floatval($duration['value']);
                    $unit = in_array($duration['unit'], array('s', 'ms')) ? $duration['unit'] : 's';
                    
                    // Converti tutto in secondi per validazione
                    $seconds = $unit === 'ms' ? $value / 1000 : $value;
                    
                    if ($seconds >= 0 && $seconds <= 10) {
                        $rule_set['transition_duration'] = array(
                            'value' => $value,
                            'unit' => $unit
                        );
                    }
                }
            }
            
            // Valida proprietà transizione
            if (isset($original_rules[$breakpoint]['transition_property'])) {
                $property = sanitize_text_field($original_rules[$breakpoint]['transition_property']);
                $allowed_properties = array('all', 'opacity', 'transform', 'color', 'background-color', 'width', 'height');
                
                if (in_array($property, $allowed_properties)) {
                    $rule_set['transition_property'] = $property;
                }
            }
            
            // Valida timing function
            if (isset($original_rules[$breakpoint]['transition_timing'])) {
                $timing = sanitize_text_field($original_rules[$breakpoint]['transition_timing']);
                $allowed_timings = array('ease', 'ease-in', 'ease-out', 'ease-in-out', 'linear');
                
                // Permetti anche cubic-bezier personalizzati
                if (in_array($timing, $allowed_timings) || preg_match('/^cubic-bezier\([0-9.,\s]+\)$/', $timing)) {
                    $rule_set['transition_timing'] = $timing;
                }
            }
            
            // Valida trasformazioni hover
            if (isset($original_rules[$breakpoint]['hover_transform'])) {
                $transform = sanitize_text_field($original_rules[$breakpoint]['hover_transform']);
                $safe_transforms = array(
                    'scale(1.05)', 'scale(1.1)', 'scale(0.95)',
                    'translateY(-5px)', 'translateY(-10px)', 'translateX(5px)',
                    'rotate(5deg)', 'rotate(-5deg)', 'skewX(5deg)'
                );
                
                if (in_array($transform, $safe_transforms)) {
                    $rule_set['hover_transform'] = $transform;
                }
            }
            
            // Valida preset animazione
            if (isset($original_rules[$breakpoint]['animation_preset'])) {
                $preset = sanitize_text_field($original_rules[$breakpoint]['animation_preset']);
                $presets = $this->get_animation_presets();
                
                if (isset($presets[$preset])) {
                    $rule_set['animation_preset'] = $preset;
                }
            }
        }
        
        return $validated_rules;
    }
    
    /**
     * Genera CSS per le animazioni
     */
    public function generate_animation_css($css_string, $rule_data) {
        $css_rules = explode('; ', rtrim($css_string, '; '));
        
        // Transizione
        $transition_parts = array();
        
        if (isset($rule_data['transition_property'])) {
            $transition_parts[] = $rule_data['transition_property'];
        }
        
        if (isset($rule_data['transition_duration'])) {
            $transition_parts[] = $rule_data['transition_duration']['value'] . $rule_data['transition_duration']['unit'];
        }
        
        if (isset($rule_data['transition_timing'])) {
            $transition_parts[] = $rule_data['transition_timing'];
        }
        
        if (!empty($transition_parts)) {
            $css_rules[] = 'transition: ' . implode(' ', $transition_parts) . ' !important';
        }
        
        // Preset animazione
        if (isset($rule_data['animation_preset']) && !empty($rule_data['animation_preset'])) {
            $preset_css = $this->get_preset_css($rule_data['animation_preset']);
            if ($preset_css) {
                $css_rules = array_merge($css_rules, $preset_css);
            }
        }
        
        // Genera CSS per hover se specificato
        if (isset($rule_data['hover_transform']) && !empty($rule_data['hover_transform'])) {
            // Questo verrà gestito separatamente nel CSS finale
            $css_rules[] = '--rem-hover-transform: ' . $rule_data['hover_transform'];
        }
        
        return implode('; ', array_filter($css_rules));
    }
    
    /**
     * Ottiene i preset di animazione disponibili
     */
    private function get_animation_presets() {
        return array(
            '' => 'Nessuno',
            'fade-in' => 'Fade In',
            'slide-in-left' => 'Slide da Sinistra',
            'slide-in-right' => 'Slide da Destra',
            'slide-in-up' => 'Slide dal Basso',
            'slide-in-down' => 'Slide dall\'Alto',
            'zoom-in' => 'Zoom In',
            'bounce-in' => 'Bounce In',
            'pulse' => 'Pulse',
            'shake' => 'Shake'
        );
    }
    
    /**
     * Ottiene il CSS per un preset specifico
     */
    private function get_preset_css($preset) {
        $presets = array(
            'fade-in' => array(
                'animation: remFadeIn 0.6s ease-out forwards !important'
            ),
            'slide-in-left' => array(
                'animation: remSlideInLeft 0.6s ease-out forwards !important'
            ),
            'slide-in-right' => array(
                'animation: remSlideInRight 0.6s ease-out forwards !important'
            ),
            'slide-in-up' => array(
                'animation: remSlideInUp 0.6s ease-out forwards !important'
            ),
            'slide-in-down' => array(
                'animation: remSlideInDown 0.6s ease-out forwards !important'
            ),
            'zoom-in' => array(
                'animation: remZoomIn 0.6s ease-out forwards !important'
            ),
            'bounce-in' => array(
                'animation: remBounceIn 0.8s ease-out forwards !important'
            ),
            'pulse' => array(
                'animation: remPulse 2s infinite !important'
            ),
            'shake' => array(
                'animation: remShake 0.6s ease-in-out !important'
            )
        );
        
        return isset($presets[$preset]) ? $presets[$preset] : null;
    }
    
    /**
     * Carica script admin
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script(
            'rem-animation-admin',
            plugin_dir_url(__FILE__) . 'assets/js/animation-admin.js',
            array('rem-admin'),
            self::VERSION,
            true
        );
        
        wp_localize_script('rem-animation-admin', 'remAnimation', array(
            'presets' => $this->get_animation_presets(),
            'nonce' => wp_create_nonce('rem_animation_nonce')
        ));
    }
    
    /**
     * Carica script frontend
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'rem-animation-keyframes',
            plugin_dir_url(__FILE__) . 'assets/css/animation-keyframes.css',
            array(),
            self::VERSION
        );
        
        wp_enqueue_script(
            'rem-animation-frontend',
            plugin_dir_url(__FILE__) . 'assets/js/animation-frontend.js',
            array('rem-frontend'),
            self::VERSION,
            true
        );
    }
    
    /**
     * AJAX: Ottiene preset animazioni
     */
    public function ajax_get_presets() {
        check_ajax_referer('rem_animation_nonce', 'nonce');
        
        wp_send_json_success($this->get_animation_presets());
    }
    
    /**
     * AJAX: Salva preset personalizzato
     */
    public function ajax_save_preset() {
        check_ajax_referer('rem_animation_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $preset_name = sanitize_text_field($_POST['preset_name'] ?? '');
        $preset_css = sanitize_textarea_field($_POST['preset_css'] ?? '');
        
        if (empty($preset_name) || empty($preset_css)) {
            wp_send_json_error('Dati preset incompleti');
        }
        
        // Salva preset personalizzato
        $custom_presets = get_option('rem_animation_custom_presets', array());
        $custom_presets[$preset_name] = $preset_css;
        update_option('rem_animation_custom_presets', $custom_presets);
        
        wp_send_json_success(array(
            'message' => 'Preset salvato con successo',
            'preset_name' => $preset_name
        ));
    }
    
    /**
     * Registra nel sistema dei moduli
     */
    public function register_in_system($modules) {
        $modules[self::MODULE_ID] = array(
            'name' => 'Animation Effects',
            'version' => self::VERSION,
            'description' => 'Controlli avanzati per animazioni e transizioni CSS',
            'author' => 'Il tuo nome',
            'active' => true,
            'file' => __FILE__
        );
        
        return $modules;
    }
    
    /**
     * Renderizza le impostazioni del modulo
     */
    public function render_settings() {
        $settings = get_option('rem_animation_settings', array());
        ?>
        <div class="rem-module-settings">
            <h3>Impostazioni Animation Effects</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Abilita Animazioni</th>
                    <td>
                        <label>
                            <input type="checkbox" name="rem_animation_settings[enabled]" 
                                   value="1" <?php checked(isset($settings['enabled']) ? $settings['enabled'] : 1); ?>>
                            Abilita il modulo animazioni
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Prefisso Animazioni</th>
                    <td>
                        <input type="text" name="rem_animation_settings[prefix]" 
                               value="<?php echo esc_attr($settings['prefix'] ?? 'rem'); ?>" 
                               class="regular-text">
                        <p class="description">Prefisso per le classi CSS delle animazioni</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Durata Predefinita</th>
                    <td>
                        <input type="number" name="rem_animation_settings[default_duration]" 
                               value="<?php echo esc_attr($settings['default_duration'] ?? '0.6'); ?>" 
                               step="0.1" min="0" max="10" class="small-text">
                        <span>secondi</span>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Avviso per plugin mancante
     */
    public function missing_plugin_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>Animation Effects Extension:</strong> 
                Richiede il plugin "Responsive Element Manager" per funzionare.
            </p>
        </div>
        <?php
    }
    
    /**
     * Genera CSS per keyframes
     */
    public static function generate_keyframes_css() {
        return '
        @keyframes remFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes remSlideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes remSlideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes remSlideInUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes remSlideInDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes remZoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes remBounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes remPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes remShake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        ';
    }
}

// Inizializza il modulo
add_action('plugins_loaded', function() {
    REM_Animation_Extension::get_instance();
});

// Hook per aggiungere CSS keyframes
add_action('wp_head', function() {
    if (rem_is_feature_enabled('enable_animations')) {
        echo '<style id="rem-animation-keyframes">';
        echo REM_Animation_Extension::generate_keyframes_css();
        echo '</style>';
    }
});

/**
 * JavaScript per integrare il modulo nell'interfaccia admin
 * 
 * Questo codice va nel file: assets/js/animation-admin.js
 */
/*
(function($) {
    'use strict';
    
    // Estende REM con funzionalità di animazione
    if (window.REM) {
        REM.Animation = {
            
            init: function() {
                this.addAnimationControls();
                this.bindEvents();
            },
            
            addAnimationControls: function() {
                // Aggiunge un nuovo tab per le animazioni
                const animationTab = `
                    <button class="rem-tab-btn" data-breakpoint="animation">
                        ✨ Animazioni
                    </button>
                `;
                
                $('.rem-breakpoint-tabs').append(animationTab);
                
                // Aggiunge il pannello delle animazioni
                const animationPanel = `
                    <div class="rem-tab-panel" data-breakpoint="animation">
                        ${this.createAnimationControls()}
                    </div>
                `;
                
                $('.rem-breakpoint-content').append(animationPanel);
            },
            
            createAnimationControls: function() {
                return `
                    <div class="rem-animation-controls">
                        <h4>Transizioni</h4>
                        
                        <div class="rem-form-row">
                            <div class="rem-form-group">
                                <label>Durata:</label>
                                <div class="rem-input-group">
                                    <input type="number" class="rem-transition-duration" step="0.1" min="0" max="10">
                                    <select class="rem-transition-duration-unit">
                                        <option value="s">s</option>
                                        <option value="ms">ms</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="rem-form-group">
                                <label>Proprietà:</label>
                                <select class="rem-transition-property">
                                    <option value="">-- Seleziona --</option>
                                    <option value="all">Tutte</option>
                                    <option value="opacity">Opacità</option>
                                    <option value="transform">Trasformazione</option>
                                </select>
                            </div>
                        </div>
                        
                        <h4>Effetti Hover</h4>
                        
                        <div class="rem-form-group">
                            <label>Trasformazione:</label>
                            <select class="rem-hover-transform">
                                <option value="">Nessuna</option>
                                <option value="scale(1.05)">Scala +5%</option>
                                <option value="translateY(-5px)">Sposta su</option>
                            </select>
                        </div>
                        
                        <h4>Preset Animazioni</h4>
                        
                        <div class="rem-form-group">
                            <label>Animazione:</label>
                            <select class="rem-animation-preset">
                                <option value="">Nessuna</option>
                            </select>
                        </div>
                    </div>
                `;
            },
            
            bindEvents: function() {
                // Carica preset quando il modal si apre
                $(document).on('rem-modal-opened', this.loadPresets.bind(this));
            },
            
            loadPresets: function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'rem_get_animation_presets',
                        nonce: remAnimation.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            const select = $('.rem-animation-preset');
                            select.empty();
                            
                            $.each(response.data, function(value, label) {
                                select.append(`<option value="${value}">${label}</option>`);
                            });
                        }
                    }
                });
            }
        };
        
        // Inizializza quando REM è pronto
        $(document).ready(function() {
            if (window.REM && REM.Animation) {
                REM.Animation.init();
            }
        });
    }
    
})(jQuery);
*/