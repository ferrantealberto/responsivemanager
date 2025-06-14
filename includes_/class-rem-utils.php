<?php
/**
 * Funzioni di utilità per Responsive Element Manager
 * 
 * Questo file contiene funzioni helper utilizzate in tutto il plugin
 * per operazioni comuni, validazione, formattazione e utilità varie.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe per funzioni di utilità
 */
class REM_Utils {
    
    /**
     * Formatta dimensioni CSS
     */
    public static function format_css_dimension($value, $unit = 'px') {
        if (empty($value) || !is_numeric($value)) {
            return '';
        }
        
        return floatval($value) . $unit;
    }
    
    /**
     * Converte unità CSS
     */
    public static function convert_css_unit($value, $from_unit, $to_unit, $base_font_size = 16) {
        if ($from_unit === $to_unit) {
            return $value;
        }
        
        // Conversioni comuni
        $conversions = array(
            'px_to_em' => $value / $base_font_size,
            'em_to_px' => $value * $base_font_size,
            'px_to_rem' => $value / $base_font_size,
            'rem_to_px' => $value * $base_font_size,
            'px_to_pt' => $value * 0.75,
            'pt_to_px' => $value / 0.75
        );
        
        $conversion_key = $from_unit . '_to_' . $to_unit;
        
        return isset($conversions[$conversion_key]) ? 
               round($conversions[$conversion_key], 3) : 
               $value;
    }
    
    /**
     * Valida e pulisce un selettore CSS
     */
    public static function sanitize_css_selector($selector) {
        // Rimuovi caratteri non validi
        $selector = preg_replace('/[<>{};"\'\\\\]/', '', $selector);
        
        // Rimuovi spazi extra
        $selector = preg_replace('/\s+/', ' ', trim($selector));
        
        // Limita lunghezza
        if (strlen($selector) > REM_Config::MAX_SELECTOR_LENGTH) {
            $selector = substr($selector, 0, REM_Config::MAX_SELECTOR_LENGTH);
        }
        
        return $selector;
    }
    
    /**
     * Verifica se una stringa è JSON valido
     */
    public static function is_valid_json($string) {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Converte array in JSON sicuro
     */
    public static function safe_json_encode($data, $pretty = false) {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        
        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }
        
        return json_encode($data, $flags);
    }
    
    /**
     * Decodifica JSON con gestione errori
     */
    public static function safe_json_decode($json, $assoc = true) {
        if (!is_string($json)) {
            return false;
        }
        
        $decoded = json_decode($json, $assoc);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        return $decoded;
    }
    
    /**
     * Genera hash MD5 sicuro
     */
    public static function generate_hash($data) {
        if (is_array($data) || is_object($data)) {
            $data = serialize($data);
        }
        
        return md5($data . wp_salt());
    }
    
    /**
     * Formatta bytes in formato leggibile
     */
    public static function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Verifica se l'utente ha i permessi necessari
     */
    public static function current_user_can_manage_rem() {
        return current_user_can('manage_responsive_elements') || 
               current_user_can('edit_posts');
    }
    
    /**
     * Ottiene l'IP del client in modo sicuro
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
    
    /**
     * Ottiene user agent pulito
     */
    public static function get_user_agent() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return sanitize_text_field(substr($user_agent, 0, 255));
    }
    
    /**
     * Verifica se la richiesta è AJAX
     */
    public static function is_ajax_request() {
        return defined('DOING_AJAX') && DOING_AJAX;
    }
    
    /**
     * Verifica se siamo in ambiente di sviluppo
     */
    public static function is_development() {
        $dev_indicators = array(
            defined('WP_DEBUG') && WP_DEBUG,
            defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            strpos(home_url(), 'localhost') !== false,
            strpos(home_url(), '127.0.0.1') !== false,
            strpos(home_url(), '.local') !== false,
            strpos(home_url(), '.dev') !== false
        );
        
        return in_array(true, $dev_indicators, true);
    }
    
    /**
     * Ottiene informazioni sul device dall'user agent
     */
    public static function get_device_info() {
        $user_agent = self::get_user_agent();
        
        $device_info = array(
            'type' => 'unknown',
            'os' => 'unknown',
            'browser' => 'unknown',
            'is_mobile' => false,
            'is_tablet' => false,
            'is_desktop' => false
        );
        
        // Rilevamento mobile
        if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
            $device_info['is_mobile'] = true;
            $device_info['type'] = 'mobile';
        }
        
        // Rilevamento tablet
        if (preg_match('/iPad|Android.*Tablet/', $user_agent)) {
            $device_info['is_tablet'] = true;
            $device_info['is_mobile'] = false;
            $device_info['type'] = 'tablet';
        }
        
        // Se non è mobile/tablet, è desktop
        if (!$device_info['is_mobile'] && !$device_info['is_tablet']) {
            $device_info['is_desktop'] = true;
            $device_info['type'] = 'desktop';
        }
        
        // Sistema operativo
        if (preg_match('/Windows/', $user_agent)) {
            $device_info['os'] = 'windows';
        } elseif (preg_match('/Mac/', $user_agent)) {
            $device_info['os'] = 'mac';
        } elseif (preg_match('/Linux/', $user_agent)) {
            $device_info['os'] = 'linux';
        } elseif (preg_match('/Android/', $user_agent)) {
            $device_info['os'] = 'android';
        } elseif (preg_match('/iOS|iPhone|iPad/', $user_agent)) {
            $device_info['os'] = 'ios';
        }
        
        // Browser
        if (preg_match('/Chrome/', $user_agent) && !preg_match('/Edge/', $user_agent)) {
            $device_info['browser'] = 'chrome';
        } elseif (preg_match('/Firefox/', $user_agent)) {
            $device_info['browser'] = 'firefox';
        } elseif (preg_match('/Safari/', $user_agent) && !preg_match('/Chrome/', $user_agent)) {
            $device_info['browser'] = 'safari';
        } elseif (preg_match('/Edge/', $user_agent)) {
            $device_info['browser'] = 'edge';
        }
        
        return $device_info;
    }
    
    /**
     * Minifica CSS
     */
    public static function minify_css($css) {
        // Rimuovi commenti
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Rimuovi spazi e newline extra
        $css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Rimuovi spazi attorno a caratteri speciali
        $css = str_replace(array(' {', '{ ', ' }', '} ', '; ', ' ;', ': ', ' :', ', ', ' ,'), 
                          array('{', '{', '}', '}', ';', ';', ':', ':', ',', ','), $css);
        
        return trim($css);
    }
    
    /**
     * Comprime una stringa
     */
    public static function compress_string($string) {
        if (function_exists('gzcompress')) {
            return base64_encode(gzcompress($string, 9));
        }
        
        return $string;
    }
    
    /**
     * Decomprime una stringa
     */
    public static function decompress_string($string) {
        if (function_exists('gzuncompress')) {
            $decoded = base64_decode($string);
            if ($decoded !== false) {
                $decompressed = gzuncompress($decoded);
                if ($decompressed !== false) {
                    return $decompressed;
                }
            }
        }
        
        return $string;
    }
    
    /**
     * Genera un token sicuro
     */
    public static function generate_token($length = 32) {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length / 2));
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length / 2));
        } else {
            // Fallback meno sicuro
            return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 3)), 0, $length);
        }
    }
    
    /**
     * Verifica rate limiting
     */
    public static function check_rate_limit($action, $limit = 60, $period = 3600) {
        if (!rem_is_feature_enabled('enable_rate_limiting')) {
            return true;
        }
        
        $user_id = get_current_user_id();
        $ip = self::get_client_ip();
        $key = "rem_rate_limit_{$action}_{$user_id}_{$ip}";
        
        $current_count = get_transient($key);
        
        if ($current_count === false) {
            set_transient($key, 1, $period);
            return true;
        }
        
        if ($current_count >= $limit) {
            return false;
        }
        
        set_transient($key, $current_count + 1, $period);
        return true;
    }
    
    /**
     * Pulisce le transient scadute
     */
    public static function cleanup_expired_transients() {
        global $wpdb;
        
        $time = time();
        
        $expired = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_%' 
             AND option_value < {$time}"
        );
        
        foreach ($expired as $transient) {
            $key = str_replace('_transient_timeout_', '', $transient);
            delete_transient($key);
        }
        
        return count($expired);
    }
    
    /**
     * Ottiene statistiche di memoria
     */
    public static function get_memory_usage() {
        return array(
            'current' => memory_get_usage(true),
            'current_formatted' => self::format_bytes(memory_get_usage(true)),
            'peak' => memory_get_peak_usage(true),
            'peak_formatted' => self::format_bytes(memory_get_peak_usage(true)),
            'limit' => ini_get('memory_limit')
        );
    }
    
    /**
     * Cronometra l'esecuzione di una funzione
     */
    public static function benchmark($callback, $iterations = 1) {
        $start_time = microtime(true);
        $start_memory = memory_get_usage();
        
        for ($i = 0; $i < $iterations; $i++) {
            if (is_callable($callback)) {
                call_user_func($callback);
            }
        }
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        
        return array(
            'time' => round(($end_time - $start_time) * 1000, 2), // millisecondi
            'memory' => $end_memory - $start_memory,
            'memory_formatted' => self::format_bytes($end_memory - $start_memory),
            'iterations' => $iterations,
            'avg_time' => round((($end_time - $start_time) / $iterations) * 1000, 4)
        );
    }
    
    /**
     * Verifica se una URL è valida e sicura
     */
    public static function is_safe_url($url) {
        if (empty($url)) {
            return false;
        }
        
        // Verifica formato URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsed_url = parse_url($url);
        
        // Verifica protocollo
        if (!isset($parsed_url['scheme']) || 
            !in_array($parsed_url['scheme'], array('http', 'https'))) {
            return false;
        }
        
        // Verifica che non punti a localhost o IP privati
        if (isset($parsed_url['host'])) {
            $host = $parsed_url['host'];
            
            if (in_array($host, array('localhost', '127.0.0.1', '::1'))) {
                return false;
            }
            
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                // Se non è un IP pubblico valido, verifica che sia un dominio valido
                if (!filter_var($host, FILTER_VALIDATE_DOMAIN)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Calcola la similarità tra due stringhe
     */
    public static function string_similarity($str1, $str2) {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        if ($len1 === 0 || $len2 === 0) {
            return 0;
        }
        
        similar_text($str1, $str2, $percent);
        return round($percent, 2);
    }
    
    /**
     * Tronca testo preservando le parole
     */
    public static function truncate_text($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        $last_space = strrpos($truncated, ' ');
        
        if ($last_space !== false && $last_space > $length * 0.7) {
            $truncated = substr($truncated, 0, $last_space);
        }
        
        return $truncated . $suffix;
    }
    
    /**
     * Converte slug in nome leggibile
     */
    public static function slug_to_title($slug) {
        $title = str_replace(array('-', '_'), ' ', $slug);
        return ucwords($title);
    }
    
    /**
     * Genera un colore casuale in formato hex
     */
    public static function generate_random_color() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verifica se un colore è scuro
     */
    public static function is_dark_color($hex_color) {
        // Rimuovi il # se presente
        $hex_color = ltrim($hex_color, '#');
        
        // Converti in RGB
        $r = hexdec(substr($hex_color, 0, 2));
        $g = hexdec(substr($hex_color, 2, 2));
        $b = hexdec(substr($hex_color, 4, 2));
        
        // Calcola luminanza
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        return $luminance < 0.5;
    }
    
    /**
     * Ottiene il contrasto ideale per un colore
     */
    public static function get_contrast_color($hex_color) {
        return self::is_dark_color($hex_color) ? '#FFFFFF' : '#000000';
    }
    
    /**
     * Valida un indirizzo email
     */
    public static function is_valid_email($email) {
        return is_email($email) !== false;
    }
    
    /**
     * Ottiene l'estensione di un file
     */
    public static function get_file_extension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    
    /**
     * Verifica se un file ha un'estensione permessa
     */
    public static function is_allowed_file_type($filename, $allowed_types = array()) {
        if (empty($allowed_types)) {
            $allowed_types = array('json', 'css', 'txt', 'xml');
        }
        
        $extension = self::get_file_extension($filename);
        return in_array($extension, $allowed_types);
    }
    
    /**
     * Debug helper - stampa variabile in modo leggibile
     */
    public static function debug_var($var, $label = '', $return = false) {
        if (!rem_is_feature_enabled('debug_mode')) {
            return;
        }
        
        $output = '';
        
        if (!empty($label)) {
            $output .= "<strong>$label:</strong><br>";
        }
        
        $output .= '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ddd; overflow: auto; max-height: 400px;">';
        $output .= htmlspecialchars(print_r($var, true));
        $output .= '</pre>';
        
        if ($return) {
            return $output;
        }
        
        echo $output;
    }
}

/**
 * Funzioni helper globali per facilità d'uso
 */

/**
 * Shortcut per logging
 */
function rem_log($message, $level = 'info', $context = array()) {
    if (class_exists('REM_Utils')) {
        return rem_log($message, $level, $context);
    }
}

/**
 * Shortcut per debug
 */
function rem_debug($var, $label = '') {
    if (class_exists('REM_Utils')) {
        REM_Utils::debug_var($var, $label);
    }
}

/**
 * Shortcut per verifica permessi
 */
function rem_current_user_can() {
    return class_exists('REM_Utils') ? REM_Utils::current_user_can_manage_rem() : false;
}

/**
 * Shortcut per formattazione CSS
 */
function rem_format_css($value, $unit = 'px') {
    return class_exists('REM_Utils') ? REM_Utils::format_css_dimension($value, $unit) : $value . $unit;
}

/**
 * Shortcut per minificazione CSS
 */
function rem_minify_css($css) {
    return class_exists('REM_Utils') ? REM_Utils::minify_css($css) : $css;
}

/**
 * Shortcut per generazione hash
 */
function rem_hash($data) {
    return class_exists('REM_Utils') ? REM_Utils::generate_hash($data) : md5(serialize($data));
}

/**
 * Shortcut per formattazione bytes
 */
function rem_format_bytes($bytes, $precision = 2) {
    return class_exists('REM_Utils') ? REM_Utils::format_bytes($bytes, $precision) : $bytes . ' B';
}

/**
 * Shortcut per verifica ambiente development
 */
function rem_is_dev() {
    return class_exists('REM_Utils') ? REM_Utils::is_development() : (defined('WP_DEBUG') && WP_DEBUG);
}