<?php
/**
 * MODULO AVANZATO: Advanced Styling Extension
 * 
 * Estende Responsive Element Manager con controlli avanzati per:
 * - Selezione elementi migliorata (parent/child)
 * - Font family e colori
 * - Visibilità e layout avanzato
 * - Allineamento per tutti gli elementi
 * 
 * NOME MODULO: Advanced Styling
 * VERSIONE: 1.0.0
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe del modulo Advanced Styling
 */
class REM_Advanced_Styling_Extension {
    
    const VERSION = '1.0.0';
    const MODULE_ID = 'advanced_styling';
    
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
        add_filter('rem_supported_properties', array($this, 'add_advanced_properties'));
        add_filter('rem_validate_rules', array($this, 'validate_advanced_rules'), 10, 2);
        add_filter('rem_css_rules', array($this, 'generate_advanced_css'), 10, 2);
        
        // Modifica l'output del frontend per aggiungere controlli avanzati
        add_action('wp_footer', array($this, 'inject_advanced_controls'), 998);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_advanced_scripts'));
        
        // Hook AJAX specifici del modulo
        add_action('wp_ajax_rem_get_element_children', array($this, 'ajax_get_element_children'));
        add_action('wp_ajax_rem_get_google_fonts', array($this, 'ajax_get_google_fonts'));
        add_action('wp_ajax_rem_save_advanced_rule', array($this, 'ajax_save_advanced_rule'));
        
        // Registra il modulo nel sistema
        add_filter('rem_registered_modules', array($this, 'register_in_system'));
    }
    
    /**
     * Aggiunge proprietà avanzate alle proprietà supportate
     */
    public function add_advanced_properties($properties) {
        $advanced_properties = array(
            // Font avanzati
            'font_family' => array(
                'type' => 'select',
                'values' => $this->get_font_families(),
                'label' => 'Famiglia Font',
                'group' => 'typography'
            ),
            'font_weight' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Predefinito',
                    '100' => 'Thin (100)',
                    '200' => 'Extra Light (200)',
                    '300' => 'Light (300)',
                    '400' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi Bold (600)',
                    '700' => 'Bold (700)',
                    '800' => 'Extra Bold (800)',
                    '900' => 'Black (900)'
                ),
                'label' => 'Peso Font',
                'group' => 'typography'
            ),
            'line_height' => array(
                'type' => 'dimension',
                'units' => array('', 'px', 'em', 'rem', '%'),
                'min' => 0.5,
                'max' => 5,
                'step' => 0.1,
                'default_unit' => '',
                'label' => 'Altezza Linea',
                'group' => 'typography'
            ),
            
            // Colori
            'text_color' => array(
                'type' => 'color',
                'label' => 'Colore Testo',
                'group' => 'colors'
            ),
            'background_color' => array(
                'type' => 'color',
                'label' => 'Colore Sfondo',
                'group' => 'colors'
            ),
            'border_color' => array(
                'type' => 'color',
                'label' => 'Colore Bordo',
                'group' => 'colors'
            ),
            
            // Layout avanzato
            'display' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Predefinito',
                    'block' => 'Block',
                    'inline' => 'Inline',
                    'inline-block' => 'Inline Block',
                    'flex' => 'Flex',
                    'grid' => 'Grid',
                    'none' => 'Nascosto'
                ),
                'label' => 'Tipo Display',
                'group' => 'layout'
            ),
            'flex_direction' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Predefinito',
                    'row' => 'Riga',
                    'column' => 'Colonna',
                    'row-reverse' => 'Riga Inversa',
                    'column-reverse' => 'Colonna Inversa'
                ),
                'label' => 'Direzione Flex',
                'group' => 'layout',
                'depends_on' => 'display=flex'
            ),
            'justify_content' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Predefinito',
                    'flex-start' => 'Inizio',
                    'center' => 'Centro',
                    'flex-end' => 'Fine',
                    'space-between' => 'Spazio Tra',
                    'space-around' => 'Spazio Attorno',
                    'space-evenly' => 'Spazio Uguale'
                ),
                'label' => 'Allineamento Orizzontale',
                'group' => 'layout',
                'depends_on' => 'display=flex'
            ),
            'align_items' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Predefinito',
                    'flex-start' => 'Inizio',
                    'center' => 'Centro',
                    'flex-end' => 'Fine',
                    'stretch' => 'Estendi',
                    'baseline' => 'Baseline'
                ),
                'label' => 'Allineamento Verticale',
                'group' => 'layout',
                'depends_on' => 'display=flex'
            ),
            
            // Spaziatura
            'margin_top' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem', 'auto'),
                'min' => -500,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Margine Superiore',
                'group' => 'spacing'
            ),
            'margin_right' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem', 'auto'),
                'min' => -500,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Margine Destro',
                'group' => 'spacing'
            ),
            'margin_bottom' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem', 'auto'),
                'min' => -500,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Margine Inferiore',
                'group' => 'spacing'
            ),
            'margin_left' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem', 'auto'),
                'min' => -500,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Margine Sinistro',
                'group' => 'spacing'
            ),
            'padding_top' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem'),
                'min' => 0,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Padding Superiore',
                'group' => 'spacing'
            ),
            'padding_right' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem'),
                'min' => 0,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Padding Destro',
                'group' => 'spacing'
            ),
            'padding_bottom' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem'),
                'min' => 0,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Padding Inferiore',
                'group' => 'spacing'
            ),
            'padding_left' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem'),
                'min' => 0,
                'max' => 500,
                'default_unit' => 'px',
                'label' => 'Padding Sinistro',
                'group' => 'spacing'
            ),
            
            // Bordi
            'border_width' => array(
                'type' => 'dimension',
                'units' => array('px', 'em', 'rem'),
                'min' => 0,
                'max' => 50,
                'default_unit' => 'px',
                'label' => 'Spessore Bordo',
                'group' => 'border'
            ),
            'border_style' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Predefinito',
                    'none' => 'Nessuno',
                    'solid' => 'Solido',
                    'dashed' => 'Tratteggiato',
                    'dotted' => 'Punteggiato',
                    'double' => 'Doppio'
                ),
                'label' => 'Stile Bordo',
                'group' => 'border'
            ),
            'border_radius' => array(
                'type' => 'dimension',
                'units' => array('px', '%', 'em', 'rem'),
                'min' => 0,
                'max' => 100,
                'default_unit' => 'px',
                'label' => 'Raggio Bordo',
                'group' => 'border'
            ),
            
            // Effetti
            'opacity' => array(
                'type' => 'range',
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'label' => 'Opacità',
                'group' => 'effects'
            ),
            'box_shadow' => array(
                'type' => 'select',
                'values' => array(
                    '' => 'Nessuna',
                    '0 2px 4px rgba(0,0,0,0.1)' => 'Leggera',
                    '0 4px 8px rgba(0,0,0,0.15)' => 'Media',
                    '0 8px 16px rgba(0,0,0,0.2)' => 'Forte',
                    '0 12px 24px rgba(0,0,0,0.25)' => 'Molto Forte'
                ),
                'label' => 'Ombra',
                'group' => 'effects'
            )
        );
        
        return array_merge($properties, $advanced_properties);
    }
    
    /**
     * Ottiene le font families disponibili
     */
    private function get_font_families() {
        $fonts = array(
            '' => 'Predefinito',
            'Arial, sans-serif' => 'Arial',
            'Georgia, serif' => 'Georgia',
            'Times New Roman, serif' => 'Times New Roman',
            'Courier New, monospace' => 'Courier New',
            'Verdana, sans-serif' => 'Verdana',
            'Helvetica, sans-serif' => 'Helvetica',
            'Trebuchet MS, sans-serif' => 'Trebuchet MS',
            'Palatino, serif' => 'Palatino',
            'Garamond, serif' => 'Garamond'
        );
        
        // Aggiungi Google Fonts popolari
        $google_fonts = array(
            'Open Sans' => 'Open Sans',
            'Roboto' => 'Roboto',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Poppins' => 'Poppins',
            'Source Sans Pro' => 'Source Sans Pro',
            'Raleway' => 'Raleway',
            'Ubuntu' => 'Ubuntu',
            'Nunito' => 'Nunito',
            'Playfair Display' => 'Playfair Display'
        );
        
        foreach ($google_fonts as $key => $name) {
            $fonts["'$key', sans-serif"] = "$name (Google)";
        }
        
        return apply_filters('rem_advanced_font_families', $fonts);
    }
    
    /**
     * Valida le regole avanzate
     */
    public function validate_advanced_rules($validated_rules, $original_rules) {
        foreach ($validated_rules as $breakpoint => &$rule_set) {
            
            // Valida font family
            if (isset($original_rules[$breakpoint]['font_family'])) {
                $font_family = sanitize_text_field($original_rules[$breakpoint]['font_family']);
                if (!empty($font_family)) {
                    $rule_set['font_family'] = $font_family;
                }
            }
            
            // Valida font weight
            if (isset($original_rules[$breakpoint]['font_weight'])) {
                $font_weight = sanitize_text_field($original_rules[$breakpoint]['font_weight']);
                $allowed_weights = array('100', '200', '300', '400', '500', '600', '700', '800', '900');
                if (in_array($font_weight, $allowed_weights) || $font_weight === '') {
                    $rule_set['font_weight'] = $font_weight;
                }
            }
            
            // Valida line height
            if (isset($original_rules[$breakpoint]['line_height'])) {
                $line_height = $original_rules[$breakpoint]['line_height'];
                if (isset($line_height['value'])) {
                    $value = floatval($line_height['value']);
                    $unit = isset($line_height['unit']) ? $line_height['unit'] : '';
                    if ($value >= 0.5 && $value <= 5) {
                        $rule_set['line_height'] = array(
                            'value' => $value,
                            'unit' => in_array($unit, array('', 'px', 'em', 'rem', '%')) ? $unit : ''
                        );
                    }
                }
            }
            
            // Valida colori
            $color_properties = array('text_color', 'background_color', 'border_color');
            foreach ($color_properties as $color_prop) {
                if (isset($original_rules[$breakpoint][$color_prop])) {
                    $color = sanitize_text_field($original_rules[$breakpoint][$color_prop]);
                    if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) || 
                        preg_match('/^rgba?\([\d\s,\.]+\)$/', $color) ||
                        in_array($color, array('transparent', 'inherit', 'initial', 'unset'))) {
                        $rule_set[$color_prop] = $color;
                    }
                }
            }
            
            // Valida display
            if (isset($original_rules[$breakpoint]['display'])) {
                $display = sanitize_text_field($original_rules[$breakpoint]['display']);
                $allowed_displays = array('', 'block', 'inline', 'inline-block', 'flex', 'grid', 'none');
                if (in_array($display, $allowed_displays)) {
                    $rule_set['display'] = $display;
                }
            }
            
            // Valida proprietà flex
            $flex_properties = array(
                'flex_direction' => array('', 'row', 'column', 'row-reverse', 'column-reverse'),
                'justify_content' => array('', 'flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly'),
                'align_items' => array('', 'flex-start', 'center', 'flex-end', 'stretch', 'baseline')
            );
            
            foreach ($flex_properties as $prop => $allowed_values) {
                if (isset($original_rules[$breakpoint][$prop])) {
                    $value = sanitize_text_field($original_rules[$breakpoint][$prop]);
                    if (in_array($value, $allowed_values)) {
                        $rule_set[$prop] = $value;
                    }
                }
            }
            
            // Valida dimensioni (margin, padding, border)
            $dimension_properties = array(
                'margin_top', 'margin_right', 'margin_bottom', 'margin_left',
                'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
                'border_width', 'border_radius'
            );
            
            foreach ($dimension_properties as $prop) {
                if (isset($original_rules[$breakpoint][$prop])) {
                    $dimension = $original_rules[$breakpoint][$prop];
                    if (isset($dimension['value']) && isset($dimension['unit'])) {
                        $value = floatval($dimension['value']);
                        $unit = sanitize_text_field($dimension['unit']);
                        
                        // Validazione range specifica per proprietà
                        $is_valid = false;
                        if (strpos($prop, 'margin') === 0) {
                            $is_valid = ($value >= -500 && $value <= 500) || $unit === 'auto';
                        } elseif (strpos($prop, 'padding') === 0) {
                            $is_valid = $value >= 0 && $value <= 500;
                        } elseif ($prop === 'border_width') {
                            $is_valid = $value >= 0 && $value <= 50;
                        } elseif ($prop === 'border_radius') {
                            $is_valid = $value >= 0 && $value <= 100;
                        }
                        
                        if ($is_valid) {
                            $rule_set[$prop] = array(
                                'value' => $value,
                                'unit' => $unit
                            );
                        }
                    }
                }
            }
            
            // Valida border style
            if (isset($original_rules[$breakpoint]['border_style'])) {
                $border_style = sanitize_text_field($original_rules[$breakpoint]['border_style']);
                $allowed_styles = array('', 'none', 'solid', 'dashed', 'dotted', 'double');
                if (in_array($border_style, $allowed_styles)) {
                    $rule_set['border_style'] = $border_style;
                }
            }
            
            // Valida opacità
            if (isset($original_rules[$breakpoint]['opacity'])) {
                $opacity = floatval($original_rules[$breakpoint]['opacity']);
                if ($opacity >= 0 && $opacity <= 1) {
                    $rule_set['opacity'] = $opacity;
                }
            }
            
            // Valida box shadow
            if (isset($original_rules[$breakpoint]['box_shadow'])) {
                $box_shadow = sanitize_text_field($original_rules[$breakpoint]['box_shadow']);
                // Validazione base per ombre CSS
                if (empty($box_shadow) || preg_match('/^[\d\s\w\(\),\.-]+$/', $box_shadow)) {
                    $rule_set['box_shadow'] = $box_shadow;
                }
            }
        }
        
        return $validated_rules;
    }
    
    /**
     * Genera CSS per le proprietà avanzate
     */
    public function generate_advanced_css($css_string, $rule_data) {
        $css_rules = explode('; ', rtrim($css_string, '; '));
        
        // Font properties
        if (isset($rule_data['font_family']) && !empty($rule_data['font_family'])) {
            $css_rules[] = 'font-family: ' . $rule_data['font_family'] . ' !important';
        }
        
        if (isset($rule_data['font_weight']) && !empty($rule_data['font_weight'])) {
            $css_rules[] = 'font-weight: ' . $rule_data['font_weight'] . ' !important';
        }
        
        if (isset($rule_data['line_height'])) {
            $line_height_value = $rule_data['line_height']['value'];
            $line_height_unit = $rule_data['line_height']['unit'];
            $css_rules[] = 'line-height: ' . $line_height_value . $line_height_unit . ' !important';
        }
        
        // Colors
        if (isset($rule_data['text_color']) && !empty($rule_data['text_color'])) {
            $css_rules[] = 'color: ' . $rule_data['text_color'] . ' !important';
        }
        
        if (isset($rule_data['background_color']) && !empty($rule_data['background_color'])) {
            $css_rules[] = 'background-color: ' . $rule_data['background_color'] . ' !important';
        }
        
        // Display and layout
        if (isset($rule_data['display']) && !empty($rule_data['display'])) {
            $css_rules[] = 'display: ' . $rule_data['display'] . ' !important';
            
            // Flex properties (only if display is flex)
            if ($rule_data['display'] === 'flex') {
                if (isset($rule_data['flex_direction']) && !empty($rule_data['flex_direction'])) {
                    $css_rules[] = 'flex-direction: ' . $rule_data['flex_direction'] . ' !important';
                }
                if (isset($rule_data['justify_content']) && !empty($rule_data['justify_content'])) {
                    $css_rules[] = 'justify-content: ' . $rule_data['justify_content'] . ' !important';
                }
                if (isset($rule_data['align_items']) && !empty($rule_data['align_items'])) {
                    $css_rules[] = 'align-items: ' . $rule_data['align_items'] . ' !important';
                }
            }
        }
        
        // Spacing - Margin
        $margin_properties = array('top', 'right', 'bottom', 'left');
        foreach ($margin_properties as $side) {
            $prop_key = 'margin_' . $side;
            if (isset($rule_data[$prop_key])) {
                $value = $rule_data[$prop_key]['value'];
                $unit = $rule_data[$prop_key]['unit'];
                if ($unit === 'auto') {
                    $css_rules[] = 'margin-' . $side . ': auto !important';
                } else {
                    $css_rules[] = 'margin-' . $side . ': ' . $value . $unit . ' !important';
                }
            }
        }
        
        // Spacing - Padding
        $padding_properties = array('top', 'right', 'bottom', 'left');
        foreach ($padding_properties as $side) {
            $prop_key = 'padding_' . $side;
            if (isset($rule_data[$prop_key])) {
                $value = $rule_data[$prop_key]['value'];
                $unit = $rule_data[$prop_key]['unit'];
                $css_rules[] = 'padding-' . $side . ': ' . $value . $unit . ' !important';
            }
        }
        
        // Border
        if (isset($rule_data['border_width'])) {
            $value = $rule_data['border_width']['value'];
            $unit = $rule_data['border_width']['unit'];
            $css_rules[] = 'border-width: ' . $value . $unit . ' !important';
        }
        
        if (isset($rule_data['border_style']) && !empty($rule_data['border_style'])) {
            $css_rules[] = 'border-style: ' . $rule_data['border_style'] . ' !important';
        }
        
        if (isset($rule_data['border_color']) && !empty($rule_data['border_color'])) {
            $css_rules[] = 'border-color: ' . $rule_data['border_color'] . ' !important';
        }
        
        if (isset($rule_data['border_radius'])) {
            $value = $rule_data['border_radius']['value'];
            $unit = $rule_data['border_radius']['unit'];
            $css_rules[] = 'border-radius: ' . $value . $unit . ' !important';
        }
        
        // Effects
        if (isset($rule_data['opacity'])) {
            $css_rules[] = 'opacity: ' . $rule_data['opacity'] . ' !important';
        }
        
        if (isset($rule_data['box_shadow']) && !empty($rule_data['box_shadow'])) {
            $css_rules[] = 'box-shadow: ' . $rule_data['box_shadow'] . ' !important';
        }
        
        return implode('; ', array_filter($css_rules));
    }
    
    /**
     * Carica script avanzati
     */
    public function enqueue_advanced_scripts() {
        if (current_user_can('edit_posts')) {
            wp_add_inline_style('rem-frontend', $this->get_advanced_css());
            wp_add_inline_script('rem-frontend', $this->get_advanced_js());
        }
    }
    
    /**
     * CSS aggiuntivo per controlli avanzati
     */
    private function get_advanced_css() {
        return '
        /* Advanced styling controls */
        .rem-advanced-selector {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .rem-selector-options {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .rem-selector-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .rem-selector-btn.active {
            background: #0073aa;
            color: white;
            border-color: #0073aa;
        }
        
        .rem-children-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }
        
        .rem-child-option {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            font-size: 12px;
            font-family: monospace;
        }
        
        .rem-child-option:hover {
            background: #f8f9fa;
        }
        
        .rem-child-option:last-child {
            border-bottom: none;
        }
        
        .rem-controls-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .rem-control-group {
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 15px;
            background: #fafafa;
        }
        
        .rem-control-group h4 {
            margin: 0 0 12px 0;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .rem-color-input {
            width: 60px;
            height: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .rem-range-input {
            width: 100%;
            margin: 5px 0;
        }
        
        .rem-dimension-control {
            display: flex;
            gap: 5px;
            margin-bottom: 8px;
        }
        
        .rem-dimension-control input {
            flex: 2;
        }
        
        .rem-dimension-control select {
            flex: 1;
        }
        
        .rem-spacing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        
        @media (max-width: 768px) {
            .rem-controls-grid {
                grid-template-columns: 1fr;
            }
            
            .rem-selector-options {
                flex-direction: column;
            }
        }
        ';
    }
    
    /**
     * JavaScript aggiuntivo per controlli avanzati
     */
    private function get_advanced_js() {
        return '
        // Extend REM with advanced functionality
        if (typeof window.REMAdvanced === "undefined") {
            window.REMAdvanced = {
                selectedElementData: null,
                childElements: [],
                
                init: function() {
                    this.enhanceElementSelector();
                    this.addAdvancedControls();
                    this.bindAdvancedEvents();
                },
                
                enhanceElementSelector: function() {
                    // Migliora la selezione degli elementi
                    const originalSelectElement = window.selectElement;
                    
                    window.selectElement = function(element) {
                        REMAdvanced.selectedElementData = {
                            element: element,
                            selector: REMAdvanced.generateAdvancedSelector(element),
                            children: REMAdvanced.getElementChildren(element)
                        };
                        
                        REMAdvanced.updateSelectorInterface();
                        
                        if (originalSelectElement) {
                            originalSelectElement(element);
                        }
                    };
                },
                
                generateAdvancedSelector: function(element) {
                    // Genera selettore più preciso
                    let selector = "";
                    
                    if (element.id) {
                        selector = "#" + element.id;
                    } else if (element.className) {
                        const classes = element.className.split(" ").filter(c => c && !c.startsWith("rem-"));
                        if (classes.length > 0) {
                            selector = "." + classes.join(".");
                        }
                    } else {
                        // Usa il percorso nel DOM
                        selector = this.getElementPath(element);
                    }
                    
                    return selector;
                },
                
                getElementPath: function(element) {
                    const path = [];
                    while (element && element.nodeType === Node.ELEMENT_NODE) {
                        let selector = element.nodeName.toLowerCase();
                        if (element.id) {
                            selector += "#" + element.id;
                            path.unshift(selector);
                            break;
                        } else {
                            let sib = element, nth = 1;
                            while (sib = sib.previousElementSibling) {
                                if (sib.nodeName.toLowerCase() === selector) nth++;
                            }
                            if (nth !== 1) selector += ":nth-of-type(" + nth + ")";
                        }
                        path.unshift(selector);
                        element = element.parentNode;
                    }
                    return path.join(" > ");
                },
                
                getElementChildren: function(element) {
                    const children = [];
                    for (let i = 0; i < element.children.length; i++) {
                        const child = element.children[i];
                        children.push({
                            element: child,
                            selector: this.generateAdvancedSelector(child),
                            tagName: child.tagName.toLowerCase(),
                            text: child.textContent ? child.textContent.substring(0, 50) + "..." : ""
                        });
                    }
                    return children;
                },
                
                updateSelectorInterface: function() {
                    // Aggiorna interfaccia selettore avanzato
                    const selectorInfo = document.getElementById("rem-selected-info");
                    if (selectorInfo && this.selectedElementData) {
                        selectorInfo.innerHTML = `
                            <div class="rem-advanced-selector">
                                <div class="rem-selector-options">
                                    <button class="rem-selector-btn active" data-type="self">Elemento Selezionato</button>
                                    <button class="rem-selector-btn" data-type="parent">Elemento Padre</button>
                                    <button class="rem-selector-btn" data-type="children">Elementi Figli</button>
                                </div>
                                <div id="rem-current-selector">
                                    <strong>Selettore:</strong> <code>${this.selectedElementData.selector}</code>
                                </div>
                                <div id="rem-children-container" style="display: none;">
                                    <div class="rem-children-list">
                                        ${this.selectedElementData.children.map(child => `
                                            <div class="rem-child-option" data-selector="${child.selector}">
                                                <strong>${child.tagName}</strong> - ${child.text}
                                                <br><small>${child.selector}</small>
                                            </div>
                                        `).join("")}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                },
                
                addAdvancedControls: function() {
                    // Aggiunge controlli avanzati al modal
                    const modalBody = document.querySelector(".rem-modal-body");
                    if (modalBody) {
                        const advancedControls = document.createElement("div");
                        advancedControls.id = "rem-advanced-controls";
                        advancedControls.innerHTML = this.getAdvancedControlsHTML();
                        modalBody.appendChild(advancedControls);
                    }
                },
                
                getAdvancedControlsHTML: function() {
                    return `
                        <div class="rem-controls-grid">
                            <!-- Typography -->
                            <div class="rem-control-group">
                                <h4>🔤 Tipografia</h4>
                                <div class="rem-form-group">
                                    <label>Famiglia Font:</label>
                                    <select id="rem-font-family">
                                        <option value="">Predefinito</option>
                                        <option value="Arial, sans-serif">Arial</option>
                                        <option value="Georgia, serif">Georgia</option>
                                        <option value="\'Roboto\', sans-serif">Roboto (Google)</option>
                                        <option value="\'Open Sans\', sans-serif">Open Sans (Google)</option>
                                    </select>
                                </div>
                                <div class="rem-form-group">
                                    <label>Peso Font:</label>
                                    <select id="rem-font-weight">
                                        <option value="">Predefinito</option>
                                        <option value="300">Light (300)</option>
                                        <option value="400">Normal (400)</option>
                                        <option value="600">Semi Bold (600)</option>
                                        <option value="700">Bold (700)</option>
                                    </select>
                                </div>
                                <div class="rem-form-group">
                                    <label>Altezza Linea:</label>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-line-height" step="0.1" min="0.5" max="5">
                                        <select id="rem-line-height-unit">
                                            <option value="">numero</option>
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Colors -->
                            <div class="rem-control-group">
                                <h4>🎨 Colori</h4>
                                <div class="rem-form-group">
                                    <label>Colore Testo:</label>
                                    <input type="color" id="rem-text-color" class="rem-color-input">
                                </div>
                                <div class="rem-form-group">
                                    <label>Colore Sfondo:</label>
                                    <input type="color" id="rem-bg-color" class="rem-color-input">
                                </div>
                                <div class="rem-form-group">
                                    <label>Colore Bordo:</label>
                                    <input type="color" id="rem-border-color" class="rem-color-input">
                                </div>
                            </div>
                            
                            <!-- Layout -->
                            <div class="rem-control-group">
                                <h4>📐 Layout</h4>
                                <div class="rem-form-group">
                                    <label>Visualizzazione:</label>
                                    <select id="rem-display">
                                        <option value="">Predefinito</option>
                                        <option value="block">Block</option>
                                        <option value="inline">Inline</option>
                                        <option value="inline-block">Inline Block</option>
                                        <option value="flex">Flex</option>
                                        <option value="none">Nascosto</option>
                                    </select>
                                </div>
                                <div id="rem-flex-controls" style="display: none;">
                                    <div class="rem-form-group">
                                        <label>Allineamento Orizzontale:</label>
                                        <select id="rem-justify-content">
                                            <option value="">Predefinito</option>
                                            <option value="flex-start">Inizio</option>
                                            <option value="center">Centro</option>
                                            <option value="flex-end">Fine</option>
                                            <option value="space-between">Spazio Tra</option>
                                        </select>
                                    </div>
                                    <div class="rem-form-group">
                                        <label>Allineamento Verticale:</label>
                                        <select id="rem-align-items">
                                            <option value="">Predefinito</option>
                                            <option value="flex-start">Inizio</option>
                                            <option value="center">Centro</option>
                                            <option value="flex-end">Fine</option>
                                            <option value="stretch">Estendi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Spacing -->
                            <div class="rem-control-group">
                                <h4>📏 Spaziatura</h4>
                                <label>Margini:</label>
                                <div class="rem-spacing-grid">
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-margin-top" placeholder="Top">
                                        <select id="rem-margin-top-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                            <option value="auto">auto</option>
                                        </select>
                                    </div>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-margin-right" placeholder="Right">
                                        <select id="rem-margin-right-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                            <option value="auto">auto</option>
                                        </select>
                                    </div>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-margin-bottom" placeholder="Bottom">
                                        <select id="rem-margin-bottom-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                            <option value="auto">auto</option>
                                        </select>
                                    </div>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-margin-left" placeholder="Left">
                                        <select id="rem-margin-left-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                            <option value="auto">auto</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <label style="margin-top: 10px; display: block;">Padding:</label>
                                <div class="rem-spacing-grid">
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-padding-top" placeholder="Top" min="0">
                                        <select id="rem-padding-top-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                        </select>
                                    </div>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-padding-right" placeholder="Right" min="0">
                                        <select id="rem-padding-right-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                        </select>
                                    </div>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-padding-bottom" placeholder="Bottom" min="0">
                                        <select id="rem-padding-bottom-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                        </select>
                                    </div>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-padding-left" placeholder="Left" min="0">
                                        <select id="rem-padding-left-unit">
                                            <option value="px">px</option>
                                            <option value="em">em</option>
                                            <option value="%">%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Effects -->
                            <div class="rem-control-group">
                                <h4>✨ Effetti</h4>
                                <div class="rem-form-group">
                                    <label>Opacità:</label>
                                    <input type="range" id="rem-opacity" class="rem-range-input" min="0" max="1" step="0.1" value="1">
                                    <span id="rem-opacity-value">1</span>
                                </div>
                                <div class="rem-form-group">
                                    <label>Ombra:</label>
                                    <select id="rem-box-shadow">
                                        <option value="">Nessuna</option>
                                        <option value="0 2px 4px rgba(0,0,0,0.1)">Leggera</option>
                                        <option value="0 4px 8px rgba(0,0,0,0.15)">Media</option>
                                        <option value="0 8px 16px rgba(0,0,0,0.2)">Forte</option>
                                    </select>
                                </div>
                                <div class="rem-form-group">
                                    <label>Raggio Bordo:</label>
                                    <div class="rem-dimension-control">
                                        <input type="number" id="rem-border-radius" min="0" max="100">
                                        <select id="rem-border-radius-unit">
                                            <option value="px">px</option>
                                            <option value="%">%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                },
                
                bindAdvancedEvents: function() {
                    // Eventi per selezione avanzata
                    document.addEventListener("click", function(e) {
                        if (e.target.classList.contains("rem-selector-btn")) {
                            document.querySelectorAll(".rem-selector-btn").forEach(btn => btn.classList.remove("active"));
                            e.target.classList.add("active");
                            
                            const type = e.target.dataset.type;
                            REMAdvanced.handleSelectorTypeChange(type);
                        }
                        
                        if (e.target.classList.contains("rem-child-option")) {
                            const selector = e.target.dataset.selector;
                            document.getElementById("rem-current-selector").innerHTML = 
                                `<strong>Selettore:</strong> <code>${selector}</code>`;
                        }
                    });
                    
                    // Eventi per controlli avanzati
                    document.addEventListener("change", function(e) {
                        if (e.target.id === "rem-display") {
                            const flexControls = document.getElementById("rem-flex-controls");
                            if (e.target.value === "flex") {
                                flexControls.style.display = "block";
                            } else {
                                flexControls.style.display = "none";
                            }
                        }
                        
                        if (e.target.id === "rem-opacity") {
                            document.getElementById("rem-opacity-value").textContent = e.target.value;
                        }
                    });
                },
                
                handleSelectorTypeChange: function(type) {
                    const childrenContainer = document.getElementById("rem-children-container");
                    
                    switch(type) {
                        case "self":
                            childrenContainer.style.display = "none";
                            break;
                        case "parent":
                            const parentElement = this.selectedElementData.element.parentElement;
                            if (parentElement) {
                                const parentSelector = this.generateAdvancedSelector(parentElement);
                                document.getElementById("rem-current-selector").innerHTML = 
                                    `<strong>Selettore Padre:</strong> <code>${parentSelector}</code>`;
                            }
                            childrenContainer.style.display = "none";
                            break;
                        case "children":
                            childrenContainer.style.display = "block";
                            break;
                    }
                }
            };
            
            // Inizializza quando il DOM è pronto
            document.addEventListener("DOMContentLoaded", function() {
                REMAdvanced.init();
            });
        }
        ';
    }
    
    /**
     * Inietta controlli avanzati nel footer
     */
    public function inject_advanced_controls() {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        echo '
        <script>
        // Attiva REMAdvanced dopo che tutto è caricato
        if (typeof REMAdvanced !== "undefined") {
            REMAdvanced.init();
        }
        </script>';
    }
    
    /**
     * AJAX: Ottiene elementi figli
     */
    public function ajax_get_element_children() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $selector = sanitize_text_field($_POST['selector'] ?? '');
        
        // Qui potresti implementare logica per ottenere elementi figli
        // Per ora restituisci un esempio
        wp_send_json_success(array(
            'children' => array()
        ));
    }
    
    /**
     * AJAX: Ottiene Google Fonts
     */
    public function ajax_get_google_fonts() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        // Lista basic di Google Fonts
        $fonts = array(
            'Open Sans',
            'Roboto',
            'Lato',
            'Montserrat',
            'Poppins',
            'Source Sans Pro',
            'Raleway',
            'Ubuntu',
            'Nunito',
            'Playfair Display'
        );
        
        wp_send_json_success($fonts);
    }
    
    /**
     * AJAX: Salva regola avanzata
     */
    public function ajax_save_advanced_rule() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $rule_data = $_POST['rule_data'] ?? array();
        
        // Usa il sistema esistente del plugin principale
        $result = REM_Rule_Manager::save_rule($rule_data);
        
        wp_send_json($result);
    }
    
    /**
     * Registra nel sistema dei moduli
     */
    public function register_in_system($modules) {
        $modules[self::MODULE_ID] = array(
            'name' => 'Advanced Styling',
            'version' => self::VERSION,
            'description' => 'Controlli avanzati per styling responsive: font, colori, layout, spaziatura',
            'author' => 'Responsive Element Manager',
            'active' => true,
            'file' => __FILE__
        );
        
        return $modules;
    }
    
    /**
     * Registra il modulo nel sistema principale
     */
    public function register_module() {
        do_action('rem_register_module', array(
            'id' => self::MODULE_ID,
            'name' => 'Advanced Styling',
            'description' => 'Controlli avanzati per styling responsive',
            'version' => self::VERSION,
            'author' => 'Responsive Element Manager Team',
            'settings_callback' => array($this, 'render_settings'),
            'active' => true
        ));
    }
    
    /**
     * Renderizza le impostazioni del modulo
     */
    public function render_settings() {
        $settings = get_option('rem_advanced_styling_settings', array());
        ?>
        <div class="rem-module-settings">
            <h3>Impostazioni Advanced Styling</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Abilita Controlli Avanzati</th>
                    <td>
                        <label>
                            <input type="checkbox" name="rem_advanced_styling_settings[enabled]" 
                                   value="1" <?php checked(isset($settings['enabled']) ? $settings['enabled'] : 1); ?>>
                            Abilita tutti i controlli avanzati
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Carica Google Fonts</th>
                    <td>
                        <label>
                            <input type="checkbox" name="rem_advanced_styling_settings[load_google_fonts]" 
                                   value="1" <?php checked(isset($settings['load_google_fonts']) ? $settings['load_google_fonts'] : 1); ?>>
                            Carica automaticamente Google Fonts utilizzati
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Modalità Avanzata</th>
                    <td>
                        <label>
                            <input type="checkbox" name="rem_advanced_styling_settings[expert_mode]" 
                                   value="1" <?php checked(isset($settings['expert_mode']) ? $settings['expert_mode'] : 0); ?>>
                            Mostra controlli per utenti esperti (CSS custom, etc.)
                        </label>
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
                <strong>Advanced Styling Extension:</strong> 
                Richiede il plugin "Responsive Element Manager" per funzionare.
            </p>
        </div>
        <?php
    }
}

// Inizializza il modulo
add_action('plugins_loaded', function() {
    REM_Advanced_Styling_Extension::get_instance();
});

// Hook per caricare Google Fonts se necessario
add_action('wp_head', function() {
    $settings = get_option('rem_advanced_styling_settings', array());
    if (isset($settings['load_google_fonts']) && $settings['load_google_fonts']) {
        // Qui caricheresti dinamicamente i Google Fonts utilizzati
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    }
});