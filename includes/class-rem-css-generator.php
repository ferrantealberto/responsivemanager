<?php
/**
 * Classe per generare il CSS - VERSIONE CORRETTA E COMPLETA
 * Supporta posizionamento x,y, allineamento, proporzioni automatiche
 */
class REM_CSS_Generator {
    
    /**
     * Genera CSS completo per tutte le regole
     */
    public static function generate_css($post_id = null) {
        if ($post_id === null) {
            $post_id = get_the_ID();
        }
        
        // Verifica cache CSS
        $cache_key = 'rem_css_cache_' . $post_id;
        $cached_css = get_transient($cache_key);
        
        if ($cached_css !== false && !defined('WP_DEBUG') || !WP_DEBUG) {
            return $cached_css;
        }
        
        $rules = REM_Rule_Manager::get_rules($post_id);
        
        if (empty($rules)) {
            return '';
        }
        
        $css_output = '';
        $breakpoints = self::get_breakpoints();
        
        foreach ($rules as $rule) {
            $selector = $rule->element_selector;
            $rule_css = $rule->rules;
            
            if (!is_array($rule_css)) {
                continue;
            }
            
            foreach ($breakpoints as $breakpoint => $media_config) {
                if (isset($rule_css[$breakpoint]) && !empty($rule_css[$breakpoint])) {
                    $css_rules = self::convert_rules_to_css($rule_css[$breakpoint]);
                    
                    if (!empty($css_rules)) {
                        $media_query = $media_config['media_query'] ?? '';
                        
                        if ($breakpoint === 'desktop' || empty($media_query)) {
                            $css_output .= "$selector { $css_rules }\n";
                        } else {
                            $css_output .= "@media $media_query { $selector { $css_rules } }\n";
                        }
                    }
                }
            }
        }
        
        // Aggiungi CSS per classi di allineamento
        $css_output .= self::get_alignment_css();
        
        // Cache il risultato per 1 ora
        set_transient($cache_key, $css_output, HOUR_IN_SECONDS);
        
        return apply_filters('rem_generated_css', $css_output, $rules);
    }
    
    /**
     * Ottiene configurazione breakpoints per CSS
     */
    private static function get_breakpoints() {
        $breakpoints = array(
            'mobile' => array(
                'media_query' => '(max-width: 767px)',
                'width' => 375,
                'height' => 667
            ),
            'tablet' => array(
                'media_query' => '(min-width: 768px) and (max-width: 1023px)',
                'width' => 768,
                'height' => 1024
            ),
            'desktop' => array(
                'media_query' => '', // No media query for desktop
                'width' => 1920,
                'height' => 1080
            ),
        );
        
        return apply_filters('rem_css_breakpoints', $breakpoints);
    }
    
    /**
     * Converte regole in CSS - VERSIONE COMPLETA E CORRETTA
     */
    private static function convert_rules_to_css($rules) {
        $css_rules = array();
        
        // POSIZIONAMENTO - NUOVO
        if (isset($rules['position']) && !empty($rules['position'])) {
            $css_rules[] = 'position: ' . $rules['position'] . ' !important';
            
            // Coordinate X (left)
            if (isset($rules['position_x'])) {
                $value = $rules['position_x']['value'];
                $unit = $rules['position_x']['unit'];
                if ($unit === 'auto') {
                    $css_rules[] = 'left: auto !important';
                } else {
                    $css_rules[] = 'left: ' . $value . $unit . ' !important';
                }
            }
            
            // Coordinate Y (top)
            if (isset($rules['position_y'])) {
                $value = $rules['position_y']['value'];
                $unit = $rules['position_y']['unit'];
                if ($unit === 'auto') {
                    $css_rules[] = 'top: auto !important';
                } else {
                    $css_rules[] = 'top: ' . $value . $unit . ' !important';
                }
            }
        }
        
        // FONT PROPERTIES
        if (isset($rules['font_size'])) {
            $css_rules[] = 'font-size: ' . $rules['font_size']['value'] . $rules['font_size']['unit'] . ' !important';
        }
        
        if (isset($rules['font_family']) && !empty($rules['font_family'])) {
            $css_rules[] = 'font-family: ' . $rules['font_family'] . ' !important';
        }
        
        if (isset($rules['font_weight']) && !empty($rules['font_weight'])) {
            $css_rules[] = 'font-weight: ' . $rules['font_weight'] . ' !important';
        }
        
        if (isset($rules['line_height'])) {
            $line_height_value = $rules['line_height']['value'];
            $line_height_unit = $rules['line_height']['unit'];
            $css_rules[] = 'line-height: ' . $line_height_value . $line_height_unit . ' !important';
        }
        
        // TEXT ALIGNMENT
        if (isset($rules['text_align']) && !empty($rules['text_align'])) {
            $css_rules[] = 'text-align: ' . $rules['text_align'] . ' !important';
        }
        
        // ELEMENT ALIGNMENT - NUOVO
        if (isset($rules['element_align']) && !empty($rules['element_align'])) {
            switch ($rules['element_align']) {
                case 'left':
                    $css_rules[] = 'margin-left: 0 !important';
                    $css_rules[] = 'margin-right: auto !important';
                    break;
                case 'center':
                    $css_rules[] = 'margin-left: auto !important';
                    $css_rules[] = 'margin-right: auto !important';
                    break;
                case 'right':
                    $css_rules[] = 'margin-left: auto !important';
                    $css_rules[] = 'margin-right: 0 !important';
                    break;
                case 'justify':
                    $css_rules[] = 'width: 100% !important';
                    break;
            }
        }
        
        // COLORS
        if (isset($rules['text_color']) && !empty($rules['text_color'])) {
            $css_rules[] = 'color: ' . $rules['text_color'] . ' !important';
        }
        
        if (isset($rules['background_color']) && !empty($rules['background_color'])) {
            $css_rules[] = 'background-color: ' . $rules['background_color'] . ' !important';
        }
        
        if (isset($rules['border_color']) && !empty($rules['border_color'])) {
            $css_rules[] = 'border-color: ' . $rules['border_color'] . ' !important';
        }
        
        // DISPLAY AND LAYOUT
        if (isset($rules['display']) && !empty($rules['display'])) {
            $css_rules[] = 'display: ' . $rules['display'] . ' !important';
            
            // Flex properties (only if display is flex)
            if ($rules['display'] === 'flex') {
                if (isset($rules['flex_direction']) && !empty($rules['flex_direction'])) {
                    $css_rules[] = 'flex-direction: ' . $rules['flex_direction'] . ' !important';
                }
                if (isset($rules['justify_content']) && !empty($rules['justify_content'])) {
                    $css_rules[] = 'justify-content: ' . $rules['justify_content'] . ' !important';
                }
                if (isset($rules['align_items']) && !empty($rules['align_items'])) {
                    $css_rules[] = 'align-items: ' . $rules['align_items'] . ' !important';
                }
            }
        }
        
        // DIMENSIONS
        if (isset($rules['width'])) {
            $value = $rules['width']['value'];
            $unit = $rules['width']['unit'];
            if ($unit === 'auto') {
                $css_rules[] = 'width: auto !important';
            } else {
                $css_rules[] = 'width: ' . $value . $unit . ' !important';
            }
        }
        
        if (isset($rules['height'])) {
            $value = $rules['height']['value'];
            $unit = $rules['height']['unit'];
            if ($unit === 'auto') {
                $css_rules[] = 'height: auto !important';
            } else {
                $css_rules[] = 'height: ' . $value . $unit . ' !important';
            }
        }
        
        // SPACING - Margin e Padding
        $spacing_properties = array('margin', 'padding');
        $sides = array('top', 'right', 'bottom', 'left');
        
        foreach ($spacing_properties as $property) {
            foreach ($sides as $side) {
                $key = $property . '_' . $side;
                if (isset($rules[$key])) {
                    $value = $rules[$key]['value'];
                    $unit = $rules[$key]['unit'];
                    
                    if ($unit === 'auto') {
                        $css_rules[] = "$property-$side: auto !important";
                    } else {
                        $css_rules[] = "$property-$side: $value$unit !important";
                    }
                }
            }
        }
        
        // BORDER
        if (isset($rules['border_width'])) {
            $value = $rules['border_width']['value'];
            $unit = $rules['border_width']['unit'];
            $css_rules[] = 'border-width: ' . $value . $unit . ' !important';
        }
        
        if (isset($rules['border_style']) && !empty($rules['border_style'])) {
            $css_rules[] = 'border-style: ' . $rules['border_style'] . ' !important';
        }
        
        if (isset($rules['border_radius'])) {
            $value = $rules['border_radius']['value'];
            $unit = $rules['border_radius']['unit'];
            $css_rules[] = 'border-radius: ' . $value . $unit . ' !important';
        }
        
        // EFFECTS
        if (isset($rules['opacity']) && $rules['opacity'] !== 1) {
            $css_rules[] = 'opacity: ' . $rules['opacity'] . ' !important';
        }
        
        if (isset($rules['box_shadow']) && !empty($rules['box_shadow'])) {
            $css_rules[] = 'box-shadow: ' . $rules['box_shadow'] . ' !important';
        }
        
        // TRANSFORM - per effetti futuri
        if (isset($rules['transform']) && !empty($rules['transform'])) {
            $css_rules[] = 'transform: ' . $rules['transform'] . ' !important';
        }
        
        // TRANSITION - per animazioni
        if (isset($rules['transition']) && !empty($rules['transition'])) {
            $css_rules[] = 'transition: ' . $rules['transition'] . ' !important';
        }
        
        return apply_filters('rem_css_rules', implode('; ', $css_rules), $rules);
    }
    
    /**
     * CSS per classi di allineamento - NUOVO
     */
    private static function get_alignment_css() {
        return "
        /* Responsive Element Manager - Classi di Allineamento */
        .rem-align-left {
            margin-left: 0 !important;
            margin-right: auto !important;
        }
        
        .rem-align-center {
            margin-left: auto !important;
            margin-right: auto !important;
        }
        
        .rem-align-right {
            margin-left: auto !important;
            margin-right: 0 !important;
        }
        
        .rem-align-justify {
            width: 100% !important;
        }
        
        /* Classi helper per posizionamento */
        .rem-positioned {
            position: relative !important;
        }
        
        .rem-positioned-absolute {
            position: absolute !important;
        }
        
        .rem-positioned-fixed {
            position: fixed !important;
        }
        
        .rem-positioned-sticky {
            position: sticky !important;
        }
        
        /* Classi per elementi responsive */
        .rem-responsive-element {
            transition: all 0.3s ease !important;
        }
        
        .rem-hidden-mobile {
            display: none !important;
        }
        
        .rem-hidden-tablet {
            display: none !important;
        }
        
        .rem-hidden-desktop {
            display: none !important;
        }
        
        /* Media queries per classi responsive */
        @media (max-width: 767px) {
            .rem-hidden-mobile {
                display: none !important;
            }
            .rem-show-mobile {
                display: block !important;
            }
        }
        
        @media (min-width: 768px) and (max-width: 1023px) {
            .rem-hidden-tablet {
                display: none !important;
            }
            .rem-show-tablet {
                display: block !important;
            }
        }
        
        @media (min-width: 1024px) {
            .rem-hidden-desktop {
                display: none !important;
            }
            .rem-show-desktop {
                display: block !important;
            }
        }
        ";
    }
    
    /**
     * NUOVO: Genera CSS per una singola regola
     */
    public static function generate_rule_css($rule_data, $selector, $breakpoint = null) {
        if (!is_array($rule_data)) {
            return '';
        }
        
        $css_output = '';
        $breakpoints = self::get_breakpoints();
        
        if ($breakpoint) {
            // Genera CSS per un breakpoint specifico
            if (isset($rule_data[$breakpoint])) {
                $css_rules = self::convert_rules_to_css($rule_data[$breakpoint]);
                if (!empty($css_rules)) {
                    $media_query = $breakpoints[$breakpoint]['media_query'] ?? '';
                    if (empty($media_query) || $breakpoint === 'desktop') {
                        $css_output = "$selector { $css_rules }";
                    } else {
                        $css_output = "@media $media_query { $selector { $css_rules } }";
                    }
                }
            }
        } else {
            // Genera CSS per tutti i breakpoint
            foreach ($breakpoints as $bp => $config) {
                if (isset($rule_data[$bp])) {
                    $css_rules = self::convert_rules_to_css($rule_data[$bp]);
                    if (!empty($css_rules)) {
                        $media_query = $config['media_query'] ?? '';
                        if (empty($media_query) || $bp === 'desktop') {
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
    
    /**
     * NUOVO: Calcola proporzioni automatiche
     */
    public static function calculate_proportional_value($source_value, $source_breakpoint, $target_breakpoint, $property_type = 'general') {
        $breakpoints = self::get_breakpoints();
        
        if (!isset($breakpoints[$source_breakpoint]) || !isset($breakpoints[$target_breakpoint])) {
            return null;
        }
        
        $source_config = $breakpoints[$source_breakpoint];
        $target_config = $breakpoints[$target_breakpoint];
        
        // Determina il ratio in base al tipo di proprietà
        switch ($property_type) {
            case 'width':
            case 'horizontal':
                $ratio = $target_config['width'] / $source_config['width'];
                break;
            case 'height':
            case 'vertical':
                $ratio = $target_config['height'] / $source_config['height'];
                break;
            case 'font':
                // Per i font usa una media pesata
                $ratio = sqrt(($target_config['width'] * $target_config['height']) / 
                             ($source_config['width'] * $source_config['height']));
                break;
            default:
                // Default: usa ratio larghezza
                $ratio = $target_config['width'] / $source_config['width'];
                break;
        }
        
        return round($source_value * $ratio, 2);
    }
    
    /**
     * NUOVO: Ottiene proprietà supportate con metadati
     */
    public static function get_supported_properties() {
        return array(
            'position' => array(
                'type' => 'select',
                'group' => 'layout',
                'proportional' => false
            ),
            'position_x' => array(
                'type' => 'dimension',
                'group' => 'layout',
                'proportional' => true,
                'proportion_type' => 'horizontal'
            ),
            'position_y' => array(
                'type' => 'dimension',
                'group' => 'layout',
                'proportional' => true,
                'proportion_type' => 'vertical'
            ),
            'font_size' => array(
                'type' => 'dimension',
                'group' => 'typography',
                'proportional' => true,
                'proportion_type' => 'font'
            ),
            'font_family' => array(
                'type' => 'select',
                'group' => 'typography',
                'proportional' => false
            ),
            'font_weight' => array(
                'type' => 'select',
                'group' => 'typography',
                'proportional' => false
            ),
            'text_align' => array(
                'type' => 'select',
                'group' => 'typography',
                'proportional' => false
            ),
            'element_align' => array(
                'type' => 'select',
                'group' => 'layout',
                'proportional' => false
            ),
            'text_color' => array(
                'type' => 'color',
                'group' => 'appearance',
                'proportional' => false
            ),
            'background_color' => array(
                'type' => 'color',
                'group' => 'appearance',
                'proportional' => false
            ),
            'border_color' => array(
                'type' => 'color',
                'group' => 'appearance',
                'proportional' => false
            ),
            'display' => array(
                'type' => 'select',
                'group' => 'layout',
                'proportional' => false
            ),
            'width' => array(
                'type' => 'dimension',
                'group' => 'layout',
                'proportional' => true,
                'proportion_type' => 'horizontal'
            ),
            'height' => array(
                'type' => 'dimension',
                'group' => 'layout',
                'proportional' => true,
                'proportion_type' => 'vertical'
            ),
            'margin_top' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'vertical'
            ),
            'margin_right' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'horizontal'
            ),
            'margin_bottom' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'vertical'
            ),
            'margin_left' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'horizontal'
            ),
            'padding_top' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'vertical'
            ),
            'padding_right' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'horizontal'
            ),
            'padding_bottom' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'vertical'
            ),
            'padding_left' => array(
                'type' => 'dimension',
                'group' => 'spacing',
                'proportional' => true,
                'proportion_type' => 'horizontal'
            ),
            'opacity' => array(
                'type' => 'range',
                'group' => 'appearance',
                'proportional' => false
            ),
            'box_shadow' => array(
                'type' => 'select',
                'group' => 'appearance',
                'proportional' => false
            ),
            'border_radius' => array(
                'type' => 'dimension',
                'group' => 'appearance',
                'proportional' => true,
                'proportion_type' => 'general'
            )
        );
    }
    
    /**
     * NUOVO: Minifica CSS per produzione
     */
    public static function minify_css($css) {
        if (empty($css)) {
            return '';
        }
        
        // Rimuovi commenti
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Rimuovi spazi extra
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        
        // Rimuovi spazi attorno a determinati caratteri
        $css = str_replace(array('; ', ' ;', ' {', '{ ', '} ', ' }', ': ', ' :', ', ', ' ,'), 
                          array(';', ';', '{', '{', '}', '}', ':', ':', ',', ','), $css);
        
        return trim($css);
    }
    
    /**
     * NUOVO: Valida CSS generato
     */
    public static function validate_css($css) {
        if (empty($css)) {
            return true;
        }
        
        // Check base per sintassi CSS
        $errors = array();
        
        // Verifica parentesi bilanciate
        $open_braces = substr_count($css, '{');
        $close_braces = substr_count($css, '}');
        
        if ($open_braces !== $close_braces) {
            $errors[] = 'Parentesi graffe non bilanciate';
        }
        
        // Verifica media query
        preg_match_all('/@media[^{]+{/', $css, $media_matches);
        foreach ($media_matches[0] as $media) {
            if (!preg_match('/^\s*@media\s+[^{]+{\s*$/', $media)) {
                $errors[] = 'Sintassi media query non valida: ' . trim($media);
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * NUOVO: Cache del CSS
     */
    public static function clear_css_cache($post_id = null) {
        if ($post_id) {
            delete_transient('rem_css_cache_' . $post_id);
        } else {
            // Pulisci tutta la cache CSS
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_rem_css_cache_%'");
        }
        
        do_action('rem_css_cache_cleared', $post_id);
    }
}

/**
 * NUOVO: Classe per gestire utilità CSS avanzate
 */
class REM_CSS_Utils {
    
    /**
     * Converte unità CSS
     */
    public static function convert_units($value, $from_unit, $to_unit, $context = 'width') {
        if ($from_unit === $to_unit) {
            return $value;
        }
        
        // Base di conversione (16px = 1rem = 1em in molti casi)
        $base_px = 16;
        
        // Converte tutto in px prima
        $value_in_px = $value;
        
        switch ($from_unit) {
            case 'em':
            case 'rem':
                $value_in_px = $value * $base_px;
                break;
            case '%':
                // Dipende dal contesto, assumiamo larghezza schermo per semplicità
                $viewport_width = $context === 'width' ? 1920 : 1080;
                $value_in_px = ($value / 100) * $viewport_width;
                break;
            case 'vw':
                $value_in_px = ($value / 100) * 1920; // Assumiamo 1920px di larghezza
                break;
            case 'vh':
                $value_in_px = ($value / 100) * 1080; // Assumiamo 1080px di altezza
                break;
        }
        
        // Converte da px all'unità target
        switch ($to_unit) {
            case 'px':
                return round($value_in_px, 2);
            case 'em':
            case 'rem':
                return round($value_in_px / $base_px, 3);
            case '%':
                $viewport_size = $context === 'width' ? 1920 : 1080;
                return round(($value_in_px / $viewport_size) * 100, 2);
            case 'vw':
                return round(($value_in_px / 1920) * 100, 2);
            case 'vh':
                return round(($value_in_px / 1080) * 100, 2);
            default:
                return $value;
        }
    }
    
    /**
     * Ottiene valore computato da CSS
     */
    public static function get_computed_value($css_value) {
        // Estrae valore e unità da una stringa CSS
        if (preg_match('/^([\d.]+)(\w+|%)$/', trim($css_value), $matches)) {
            return array(
                'value' => floatval($matches[1]),
                'unit' => $matches[2]
            );
        }
        
        return null;
    }
    
    /**
     * Combina più valori CSS
     */
    public static function combine_css_values($values, $property) {
        switch ($property) {
            case 'margin':
            case 'padding':
                // Combina valori di spacing
                if (count($values) === 4) {
                    return implode(' ', $values);
                }
                break;
            case 'border':
                // Combina width, style, color
                return implode(' ', array_filter($values));
            case 'transform':
                // Combina multiple trasformazioni
                return implode(' ', $values);
        }
        
        return implode(' ', $values);
    }
}

/**
 * NUOVO: Hook e filtri per estensioni
 */
add_filter('rem_css_before_output', function($css) {
    // Permette ai moduli di modificare il CSS prima dell'output
    return $css;
});

add_action('rem_css_generated', function($css, $post_id) {
    // Hook dopo la generazione del CSS
}, 10, 2);

add_filter('rem_css_properties', function($properties) {
    // Permette di aggiungere nuove proprietà CSS
    return $properties;
});

// Pulisci cache CSS quando una regola viene salvata o eliminata
add_action('rem_rule_saved', function($rule_data, $rule_id) {
    REM_CSS_Generator::clear_css_cache($rule_data['post_id']);
}, 10, 2);

add_action('rem_rule_deleted', function($rule_id, $rule) {
    REM_CSS_Generator::clear_css_cache($rule->post_id);
}, 10, 2);