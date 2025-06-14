<?php
/**
 * Classe per gestire la selezione degli elementi
 */

if (!defined('ABSPATH')) {
    exit;
}

class REM_Element_Selector {
    
    /**
     * Genera un selettore CSS unico per un elemento
     */
    public static function generate_selector($element_data) {
        $selector = '';
        
        // Priorità: ID > Classe > Tag con contesto
        if (!empty($element_data['id'])) {
            $selector = '#' . $element_data['id'];
        } elseif (!empty($element_data['class'])) {
            $classes = explode(' ', $element_data['class']);
            $classes = array_filter($classes, function($class) {
                return !empty($class) && strpos($class, 'rem-') !== 0;
            });
            
            if (!empty($classes)) {
                $selector = '.' . implode('.', $classes);
            }
        }
        
        // Se non abbiamo ID o classi, usiamo il tag con contesto
        if (empty($selector)) {
            $tag = !empty($element_data['tag']) ? $element_data['tag'] : 'div';
            $selector = $tag;
            
            // Aggiungi contesto se disponibile
            if (!empty($element_data['parent_selector'])) {
                $selector = $element_data['parent_selector'] . ' > ' . $selector;
            }
        }
        
        return apply_filters('rem_generated_selector', $selector, $element_data);
    }
    
    /**
     * Valida un selettore CSS
     */
    public static function validate_selector($selector) {
        // Rimuovi spazi iniziali e finali
        $selector = trim($selector);
        
        if (empty($selector)) {
            return false;
        }
        
        // Lista di caratteri non permessi per sicurezza
        $forbidden_chars = array('<', '>', '{', '}', ';', '"', "'");
        foreach ($forbidden_chars as $char) {
            if (strpos($selector, $char) !== false) {
                return false;
            }
        }
        
        // Verifica che il selettore non contenga JavaScript
        if (preg_match('/javascript:|expression\s*\(|eval\s*\(/i', $selector)) {
            return false;
        }
        
        return apply_filters('rem_validate_selector', true, $selector);
    }
    
    /**
     * Ottiene informazioni sull'elemento dal selettore
     */
    public static function get_element_info($selector) {
        $info = array(
            'type' => 'unknown',
            'specificity' => 0,
            'target_count' => 'unknown'
        );
        
        // Determina il tipo di selettore
        if (strpos($selector, '#') === 0) {
            $info['type'] = 'id';
            $info['specificity'] = 100;
            $info['target_count'] = 1;
        } elseif (strpos($selector, '.') !== false) {
            $info['type'] = 'class';
            $info['specificity'] = 10;
        } else {
            $info['type'] = 'element';
            $info['specificity'] = 1;
        }
        
        return apply_filters('rem_element_info', $info, $selector);
    }
    
    /**
     * Suggerisce miglioramenti per un selettore
     */
    public static function suggest_improvements($selector) {
        $suggestions = array();
        
        // Se il selettore è troppo generico
        if (in_array(trim($selector), array('div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'))) {
            $suggestions[] = array(
                'type' => 'warning',
                'message' => 'Il selettore è molto generico e potrebbe influenzare molti elementi. Considera l\'aggiunta di una classe o ID specifico.'
            );
        }
        
        // Se il selettore è molto lungo
        if (strlen($selector) > 100) {
            $suggestions[] = array(
                'type' => 'info',
                'message' => 'Il selettore è molto lungo. Considera l\'uso di un ID o classe più semplice.'
            );
        }
        
        // Se contiene molti livelli di nesting
        if (substr_count($selector, '>') > 3) {
            $suggestions[] = array(
                'type' => 'warning',
                'message' => 'Il selettore ha molti livelli di nesting. Potrebbe essere fragile ai cambiamenti della struttura HTML.'
            );
        }
        
        return apply_filters('rem_selector_suggestions', $suggestions, $selector);
    }
    
    /**
     * Ottimizza un selettore per la performance
     */
    public static function optimize_selector($selector) {
        // Rimuovi spazi extra
        $optimized = preg_replace('/\s+/', ' ', trim($selector));
        
        // Semplifica selettori complessi quando possibile
        // Ad esempio: div.class-name > span.another-class può diventare .another-class se è unico
        
        return apply_filters('rem_optimized_selector', $optimized, $selector);
    }
    
    /**
     * Verifica se un selettore entra in conflitto con elementi del sistema
     */
    public static function check_system_conflicts($selector) {
        $system_selectors = array(
            '#wpadminbar',
            '.admin-bar',
            '#adminmenu',
            '.wp-toolbar',
            '#rem-modal',
            '#rem-toggle-btn',
            '.rem-overlay'
        );
        
        foreach ($system_selectors as $system_selector) {
            if (strpos($selector, $system_selector) !== false) {
                return array(
                    'conflict' => true,
                    'message' => "Il selettore entra in conflitto con elementi del sistema WordPress/Plugin: $system_selector"
                );
            }
        }
        
        return array('conflict' => false);
    }
}

/**
 * Classe per gestire l'CSS dinamico
 */
class REM_CSS_Generator {
    
    private static $breakpoints = null;
    
    /**
     * Genera tutto il CSS per le regole attive
     */
    public static function generate_css($post_id = null) {
        if ($post_id === null) {
            $post_id = get_the_ID();
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
            
            // Verifica conflitti di sistema prima di generare CSS
            $conflict_check = REM_Element_Selector::check_system_conflicts($selector);
            if ($conflict_check['conflict']) {
                continue; // Salta regole che causano conflitti
            }
            
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
    
    /**
     * Ottiene i breakpoint configurati
     */
    public static function get_breakpoints() {
        if (self::$breakpoints === null) {
            self::$breakpoints = array(
                'mobile' => '(max-width: 767px)',
                'tablet' => '(min-width: 768px) and (max-width: 1023px)',
                'desktop' => '', // No media query for desktop (default)
            );
            
            // Permetti personalizzazione dei breakpoint
            self::$breakpoints = apply_filters('rem_breakpoints', self::$breakpoints);
        }
        
        return self::$breakpoints;
    }
    
    /**
     * Converte le regole in CSS
     */
    private static function convert_rules_to_css($rules) {
        $css_rules = array();
        
        // Font size
        if (isset($rules['font_size']) && !empty($rules['font_size']['value'])) {
            $css_rules[] = 'font-size: ' . $rules['font_size']['value'] . $rules['font_size']['unit'] . ' !important';
        }
        
        // Font family
        if (isset($rules['font_family']) && !empty($rules['font_family'])) {
            $css_rules[] = 'font-family: ' . $rules['font_family'] . ' !important';
        }
        
        // Text align
        if (isset($rules['text_align']) && !empty($rules['text_align'])) {
            $css_rules[] = 'text-align: ' . $rules['text_align'] . ' !important';
        }
        
        // Width
        if (isset($rules['width']) && !empty($rules['width']['value'])) {
            $css_rules[] = 'width: ' . $rules['width']['value'] . $rules['width']['unit'] . ' !important';
        }
        
        // Height
        if (isset($rules['height']) && !empty($rules['height']['value'])) {
            $css_rules[] = 'height: ' . $rules['height']['value'] . $rules['height']['unit'] . ' !important';
        }
        
        // Padding (estensione futura)
        if (isset($rules['padding'])) {
            if (is_array($rules['padding'])) {
                $padding_parts = array();
                foreach (array('top', 'right', 'bottom', 'left') as $side) {
                    if (isset($rules['padding'][$side]) && !empty($rules['padding'][$side]['value'])) {
                        $padding_parts[] = $rules['padding'][$side]['value'] . $rules['padding'][$side]['unit'];
                    } else {
                        $padding_parts[] = '0';
                    }
                }
                if (!empty($padding_parts)) {
                    $css_rules[] = 'padding: ' . implode(' ', $padding_parts) . ' !important';
                }
            }
        }
        
        // Margin (estensione futura)
        if (isset($rules['margin'])) {
            if (is_array($rules['margin'])) {
                $margin_parts = array();
                foreach (array('top', 'right', 'bottom', 'left') as $side) {
                    if (isset($rules['margin'][$side]) && !empty($rules['margin'][$side]['value'])) {
                        $margin_parts[] = $rules['margin'][$side]['value'] . $rules['margin'][$side]['unit'];
                    } else {
                        $margin_parts[] = '0';
                    }
                }
                if (!empty($margin_parts)) {
                    $css_rules[] = 'margin: ' . implode(' ', $margin_parts) . ' !important';
                }
            }
        }
        
        return apply_filters('rem_css_rules', implode('; ', $css_rules), $rules);
    }
    
    /**
     * Minifica il CSS per ottimizzare le performance
     */
    public static function minify_css($css) {
        // Rimuovi commenti
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Rimuovi spazi extra
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        
        // Rimuovi spazi attorno a caratteri speciali
        $css = str_replace(array(' {', '{ ', ' }', '} ', '; ', ' ;', ': ', ' :', ', ', ' ,'), array('{', '{', '}', '}', ';', ';', ':', ':', ',', ','), $css);
        
        return trim($css);
    }
    
    /**
     * Genera CSS per un singolo breakpoint
     */
    public static function generate_breakpoint_css($breakpoint, $rules) {
        $css_output = '';
        $breakpoints = self::get_breakpoints();
        
        if (!isset($breakpoints[$breakpoint])) {
            return '';
        }
        
        $media_query = $breakpoints[$breakpoint];
        
        foreach ($rules as $selector => $rule_data) {
            if (isset($rule_data[$breakpoint]) && !empty($rule_data[$breakpoint])) {
                $css_rules = self::convert_rules_to_css($rule_data[$breakpoint]);
                
                if (!empty($css_rules)) {
                    if ($breakpoint === 'desktop') {
                        $css_output .= "$selector { $css_rules }\n";
                    } else {
                        $css_output .= "@media $media_query { $selector { $css_rules } }\n";
                    }
                }
            }
        }
        
        return $css_output;
    }
    
    /**
     * Verifica se il CSS generato è valido
     */
    public static function validate_css($css) {
        // Controlli di base per CSS valido
        $errors = array();
        
        // Verifica parentesi bilanciate
        $open_braces = substr_count($css, '{');
        $close_braces = substr_count($css, '}');
        
        if ($open_braces !== $close_braces) {
            $errors[] = 'Parentesi graffe non bilanciate nel CSS';
        }
        
        // Verifica media query
        if (preg_match_all('/@media[^{]*{/', $css, $matches)) {
            foreach ($matches[0] as $media) {
                if (!preg_match('/@media\s+[^{]+{/', $media)) {
                    $errors[] = 'Sintassi media query non valida: ' . $media;
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
}