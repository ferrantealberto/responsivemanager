<?php
/**
 * File di configurazione e costanti per Responsive Element Manager
 * 
 * Questo file contiene tutte le configurazioni globali, costanti
 * e impostazioni predefinite del plugin.
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe per gestire la configurazione del plugin
 */
class REM_Config {
    
    /**
     * Versioni minime richieste
     */
    const MIN_WP_VERSION = '5.0';
    const MIN_PHP_VERSION = '7.4';
    const RECOMMENDED_PHP_VERSION = '8.0';
    
    /**
     * Impostazioni del database
     */
    const DB_VERSION = '1.0';
    const MAX_SELECTOR_LENGTH = 500;
    const MAX_CLASS_LENGTH = 500;
    const MAX_RULES_PER_ELEMENT = 10;
    
    /**
     * Impostazioni di performance
     */
    const CACHE_EXPIRATION = 3600; // 1 ora
    const MAX_CSS_SIZE = 1048576; // 1MB
    const MAX_BACKUP_SIZE = 10485760; // 10MB
    const CLEANUP_INTERVAL = 86400; // 24 ore
    
    /**
     * Breakpoint predefiniti
     */
    const DEFAULT_BREAKPOINTS = array(
        'mobile' => '(max-width: 767px)',
        'tablet' => '(min-width: 768px) and (max-width: 1023px)',
        'desktop' => '' // Nessuna media query (default)
    );
    
    /**
     * Unità CSS supportate
     */
    const SUPPORTED_UNITS = array(
        'length' => array('px', 'em', 'rem', '%', 'vw', 'vh', 'vmin', 'vmax'),
        'font_size' => array('px', 'em', 'rem', '%', 'pt'),
        'width' => array('px', 'em', 'rem', '%', 'vw'),
        'height' => array('px', 'em', 'rem', '%', 'vh')
    );
    
    /**
     * Proprietà CSS supportate
     */
    const SUPPORTED_PROPERTIES = array(
        'font_size' => array(
            'type' => 'dimension',
            'units' => array('px', 'em', 'rem', '%', 'pt'),
            'min' => 0,
            'max' => 999
        ),
        'font_family' => array(
            'type' => 'select',
            'values' => array(
                'Arial, sans-serif',
                'Georgia, serif',
                "'Times New Roman', serif",
                "'Courier New', monospace",
                'Verdana, sans-serif',
                'Helvetica, sans-serif',
                "'Trebuchet MS', sans-serif",
                "'Arial Black', sans-serif"
            )
        ),
        'text_align' => array(
            'type' => 'select',
            'values' => array('left', 'center', 'right', 'justify')
        ),
        'width' => array(
            'type' => 'dimension',
            'units' => array('px', 'em', 'rem', '%', 'vw'),
            'min' => 0,
            'max' => 9999
        ),
        'height' => array(
            'type' => 'dimension',
            'units' => array('px', 'em', 'rem', '%', 'vh'),
            'min' => 0,
            'max' => 9999
        )
    );
    
    /**
     * Limiti di sicurezza
     */
    const SECURITY_LIMITS = array(
        'max_rules_per_user' => 1000,
        'max_rules_per_page' => 50,
        'max_css_complexity' => 100, // Numero massimo di regole CSS per selettore
        'rate_limit_requests' => 60, // Richieste al minuto
        'max_selector_depth' => 10, // Livelli massimi di nesting CSS
        'blacklisted_selectors' => array(
            'html', 'body', '*', // Selettori troppo generici
            'script', 'style', 'link', // Elementi pericolosi
            '.wp-admin', '#wpadminbar' // Elementi di WordPress
        )
    );
    
    /**
     * Impostazioni di logging
     */
    const LOG_LEVELS = array(
        'none' => 0,
        'error' => 1,
        'warning' => 2,
        'info' => 3,
        'debug' => 4
    );
    
    /**
     * Configurazioni API esterne
     */
    const EXTERNAL_APIS = array(
        'google_fonts' => array(
            'url' => 'https://fonts.googleapis.com/css2',
            'cache_time' => 86400 // 24 ore
        ),
        'update_server' => array(
            'url' => 'https://api.yourplugin.com/updates',
            'timeout' => 10
        ),
        'feedback_server' => array(
            'url' => 'https://api.yourplugin.com/feedback',
            'timeout' => 5
        )
    );
    
    /**
     * Formati di esportazione supportati
     */
    const EXPORT_FORMATS = array(
        'json' => array(
            'extension' => 'json',
            'mime_type' => 'application/json',
            'description' => 'JSON per reimportazione'
        ),
        'css' => array(
            'extension' => 'css',
            'mime_type' => 'text/css',
            'description' => 'CSS pronto per l\'uso'
        ),
        'xml' => array(
            'extension' => 'xml',
            'mime_type' => 'application/xml',
            'description' => 'XML strutturato'
        )
    );
    
    /**
     * Configurazioni di sicurezza CSS
     */
    const CSS_SECURITY = array(
        'forbidden_properties' => array(
            'behavior', 'expression', 'javascript:', 'vbscript:',
            '-moz-binding', 'filter', '-ms-filter'
        ),
        'forbidden_values' => array(
            'javascript:', 'vbscript:', 'expression(', 'url(javascript:',
            'data:text/html', '<script', '</script>'
        ),
        'max_url_length' => 500,
        'allowed_protocols' => array('http:', 'https:', 'data:image/')
    );
    
    /**
     * Configurazioni per l'interfaccia utente
     */
    const UI_CONFIG = array(
        'modal_max_width' => 800,
        'modal_max_height' => '80vh',
        'animation_duration' => 300,
        'tooltip_delay' => 500,
        'auto_save_delay' => 2000,
        'search_min_chars' => 2,
        'pagination_per_page' => 20
    );
    
    /**
     * Configurazioni per dispositivi
     */
    const DEVICE_PRESETS = array(
        'mobile' => array(
            'name' => 'Mobile',
            'icon' => '📱',
            'width_range' => array(320, 767),
            'common_widths' => array(320, 375, 414, 480)
        ),
        'tablet' => array(
            'name' => 'Tablet',
            'icon' => '📟',
            'width_range' => array(768, 1023),
            'common_widths' => array(768, 820, 1024)
        ),
        'desktop' => array(
            'name' => 'Desktop',
            'icon' => '🖥️',
            'width_range' => array(1024, 9999),
            'common_widths' => array(1200, 1366, 1440, 1920)
        )
    );
    
    /**
     * Messaggi di errore standardizzati
     */
    const ERROR_MESSAGES = array(
        'invalid_selector' => 'Selettore CSS non valido o non sicuro',
        'invalid_property' => 'Proprietà CSS non supportata',
        'invalid_value' => 'Valore CSS non valido',
        'permission_denied' => 'Non hai i permessi per questa operazione',
        'rate_limit_exceeded' => 'Troppe richieste, riprova più tardi',
        'database_error' => 'Errore di connessione al database',
        'cache_error' => 'Errore nel sistema di cache',
        'file_too_large' => 'File troppo grande per essere processato',
        'invalid_format' => 'Formato file non supportato'
    );
    
    /**
     * Ottiene la configurazione completa
     */
    public static function get_config() {
        return array(
            'version' => REM_VERSION,
            'min_requirements' => array(
                'wp_version' => self::MIN_WP_VERSION,
                'php_version' => self::MIN_PHP_VERSION
            ),
            'breakpoints' => self::DEFAULT_BREAKPOINTS,
            'supported_properties' => self::SUPPORTED_PROPERTIES,
            'security_limits' => self::SECURITY_LIMITS,
            'ui_config' => self::UI_CONFIG,
            'device_presets' => self::DEVICE_PRESETS
        );
    }
    
    /**
     * Verifica se un selettore è sicuro
     */
    public static function is_safe_selector($selector) {
        // Controlli di base
        if (empty($selector) || strlen($selector) > self::MAX_SELECTOR_LENGTH) {
            return false;
        }
        
        // Verifica selettori in blacklist
        foreach (self::SECURITY_LIMITS['blacklisted_selectors'] as $blocked) {
            if (strpos($selector, $blocked) !== false) {
                return false;
            }
        }
        
        // Verifica profondità di nesting
        $depth = substr_count($selector, '>') + substr_count($selector, ' ');
        if ($depth > self::SECURITY_LIMITS['max_selector_depth']) {
            return false;
        }
        
        // Verifica caratteri pericolosi
        $dangerous_chars = array('<', '>', '{', '}', ';', '"', "'", '\\');
        foreach ($dangerous_chars as $char) {
            if (strpos($selector, $char) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Verifica se una proprietà CSS è supportata
     */
    public static function is_supported_property($property) {
        return isset(self::SUPPORTED_PROPERTIES[$property]);
    }
    
    /**
     * Verifica se un valore CSS è sicuro
     */
    public static function is_safe_css_value($value) {
        if (empty($value)) {
            return true; // Valori vuoti sono OK
        }
        
        // Converti in stringa se necessario
        $value = (string) $value;
        
        // Verifica lunghezza
        if (strlen($value) > 200) {
            return false;
        }
        
        // Verifica valori proibiti
        foreach (self::CSS_SECURITY['forbidden_values'] as $forbidden) {
            if (stripos($value, $forbidden) !== false) {
                return false;
            }
        }
        
        // Verifica URL se presente
        if (preg_match('/url\s*\(/i', $value)) {
            return self::is_safe_css_url($value);
        }
        
        return true;
    }
    
    /**
     * Verifica se un URL CSS è sicuro
     */
    private static function is_safe_css_url($url_value) {
        // Estrai URL dalla funzione url()
        if (preg_match('/url\s*\(\s*["\']?([^"\']+)["\']?\s*\)/i', $url_value, $matches)) {
            $url = $matches[1];
            
            // Verifica lunghezza
            if (strlen($url) > self::CSS_SECURITY['max_url_length']) {
                return false;
            }
            
            // Verifica protocolli consentiti
            $protocol_found = false;
            foreach (self::CSS_SECURITY['allowed_protocols'] as $protocol) {
                if (strpos($url, $protocol) === 0) {
                    $protocol_found = true;
                    break;
                }
            }
            
            // Se non inizia con un protocollo consentito, assumi che sia relativo (OK)
            if (strpos($url, ':') !== false && !$protocol_found) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Ottiene le impostazioni predefinite
     */
    public static function get_default_settings() {
        return array(
            // Generali
            'enable_minification' => false,
            'load_on_frontend_only' => true,
            'enable_cache' => false,
            'button_position' => 'top-right',
            'user_roles' => array('administrator', 'editor'),
            'excluded_pages' => '',
            'custom_css_prefix' => 'rem',
            'enable_animations' => true,
            'debug_mode' => false,
            
            // Backup
            'auto_backup' => true,
            'backup_frequency' => 'weekly',
            'max_backups' => 10,
            'delete_data_on_uninstall' => false,
            'delete_backups_on_uninstall' => false,
            
            // Sicurezza
            'enable_rate_limiting' => true,
            'log_level' => 'error',
            'allow_external_fonts' => true,
            'sanitize_selectors' => true,
            
            // UI/UX
            'show_welcome_screen' => true,
            'enable_tooltips' => true,
            'auto_save_enabled' => true,
            'show_advanced_options' => false,
            
            // Privacy
            'allow_anonymous_feedback' => false,
            'track_usage_stats' => false,
            
            // Performance
            'css_cache_enabled' => true,
            'preload_critical_css' => false,
            'defer_non_critical_css' => false,
            'optimize_css_delivery' => false
        );
    }
    
    /**
     * Verifica la compatibilità del sistema
     */
    public static function check_system_compatibility() {
        $issues = array();
        
        // Verifica versione WordPress
        if (version_compare(get_bloginfo('version'), self::MIN_WP_VERSION, '<')) {
            $issues[] = sprintf(
                'WordPress %s o superiore richiesto (installato: %s)',
                self::MIN_WP_VERSION,
                get_bloginfo('version')
            );
        }
        
        // Verifica versione PHP
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            $issues[] = sprintf(
                'PHP %s o superiore richiesto (installato: %s)',
                self::MIN_PHP_VERSION,
                PHP_VERSION
            );
        }
        
        // Verifica estensioni PHP
        $required_extensions = array('json', 'mbstring', 'pcre');
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $issues[] = "Estensione PHP '$extension' richiesta ma non disponibile";
            }
        }
        
        // Verifica permessi di scrittura
        $upload_dir = wp_upload_dir();
        if (!is_writable($upload_dir['basedir'])) {
            $issues[] = 'Directory di upload non scrivibile: ' . $upload_dir['basedir'];
        }
        
        // Verifica memoria PHP
        $memory_limit = ini_get('memory_limit');
        if ($memory_limit && intval($memory_limit) < 64) {
            $issues[] = 'Memoria PHP insufficiente (consigliati almeno 64MB)';
        }
        
        return empty($issues) ? true : $issues;
    }
    
    /**
     * Ottiene informazioni di sistema per debug
     */
    public static function get_system_info() {
        global $wpdb;
        
        return array(
            'plugin_version' => REM_VERSION,
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $wpdb->db_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'multisite' => is_multisite(),
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'active_theme' => get_template(),
            'active_plugins' => get_option('active_plugins'),
            'timezone' => wp_timezone_string(),
            'language' => get_locale()
        );
    }
}

/**
 * Funzioni helper globali
 */

/**
 * Ottiene una configurazione specifica
 */
function rem_get_config($key = null, $default = null) {
    $config = REM_Config::get_config();
    
    if ($key === null) {
        return $config;
    }
    
    return isset($config[$key]) ? $config[$key] : $default;
}

/**
 * Verifica se una funzionalità è abilitata
 */
function rem_is_feature_enabled($feature) {
    $settings = get_option('rem_settings', REM_Config::get_default_settings());
    return isset($settings[$feature]) ? $settings[$feature] : false;
}

/**
 * Ottiene un messaggio di errore standardizzato
 */
function rem_get_error_message($error_code, $context = '') {
    $messages = REM_Config::ERROR_MESSAGES;
    $message = isset($messages[$error_code]) ? $messages[$error_code] : 'Errore sconosciuto';
    
    if (!empty($context)) {
        $message .= ' (' . $context . ')';
    }
    
    return $message;
}

/**
 * Log delle operazioni del plugin
 */
function rem_log($message, $level = 'info', $context = array()) {
    if (!rem_is_feature_enabled('debug_mode')) {
        return;
    }
    
    $log_levels = REM_Config::LOG_LEVELS;
    $current_level = rem_is_feature_enabled('log_level') ?: 'error';
    
    if ($log_levels[$level] <= $log_levels[$current_level]) {
        $log_message = sprintf(
            '[REM] %s: %s',
            strtoupper($level),
            $message
        );
        
        if (!empty($context)) {
            $log_message .= ' | Context: ' . json_encode($context);
        }
        
        error_log($log_message);
    }
}

// Inizializzazione configurazioni al caricamento
add_action('plugins_loaded', function() {
    // Verifica compatibilità sistema
    $compatibility = REM_Config::check_system_compatibility();
    
    if ($compatibility !== true && is_admin()) {
        add_action('admin_notices', function() use ($compatibility) {
            echo '<div class="notice notice-error">';
            echo '<h3>Responsive Element Manager - Problemi di Compatibilità</h3>';
            echo '<ul>';
            foreach ($compatibility as $issue) {
                echo '<li>' . esc_html($issue) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        });
    }
});