<?php
/**
 * File di disinstallazione per Responsive Element Manager
 * 
 * Questo file viene eseguito quando il plugin viene disinstallato
 * tramite l'interfaccia di WordPress.
 */

// Previeni l'accesso diretto
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include il file principale per avere accesso alle classi
require_once plugin_dir_path(__FILE__) . 'responsive-element-manager.php';

/**
 * Classe per gestire la disinstallazione completa
 */
class REM_Uninstaller {
    
    /**
     * Esegue la disinstallazione completa
     */
    public static function uninstall() {
        // Verifica permessi utente
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        // Verifica che il plugin sia effettivamente in fase di disinstallazione
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }
        
        // Carica le impostazioni per verificare se eliminare i dati
        $settings = get_option('rem_settings', array());
        $delete_data = isset($settings['delete_data_on_uninstall']) ? 
                      $settings['delete_data_on_uninstall'] : false;
        
        // Se l'utente ha scelto di mantenere i dati, non fare nulla
        if (!$delete_data) {
            // Log dell'operazione
            error_log('REM: Plugin disinstallato ma dati conservati per scelta utente');
            return;
        }
        
        // Esegui backup finale prima dell'eliminazione
        self::create_final_backup();
        
        // Elimina tutte le tabelle del database
        self::drop_database_tables();
        
        // Elimina tutte le opzioni e metadati
        self::delete_options_and_metadata();
        
        // Pulisci file e directory se esistenti
        self::cleanup_files();
        
        // Rimuovi capacità personalizzate
        self::remove_custom_capabilities();
        
        // Rimuovi eventi cron
        self::unschedule_all_events();
        
        // Pulisci cache
        self::cleanup_cache_and_transients();
        
        // Log finale
        self::log_uninstallation();
        
        // Hook per estensioni
        do_action('rem_uninstall_complete');
    }
    
    /**
     * Crea un backup finale prima dell'eliminazione
     */
    private static function create_final_backup() {
        try {
            // Verifica se ci sono regole da salvare
            global $wpdb;
            $table_name = $wpdb->prefix . 'rem_rules';
            
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
                $rule_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                
                if ($rule_count > 0) {
                    // Crea backup in formato JSON nella directory di upload
                    $upload_dir = wp_upload_dir();
                    $backup_dir = $upload_dir['basedir'] . '/rem-backups/';
                    
                    if (!is_dir($backup_dir)) {
                        wp_mkdir_p($backup_dir);
                    }
                    
                    $backup_file = $backup_dir . 'final-backup-' . date('Y-m-d-H-i-s') . '.json';
                    
                    // Esporta tutti i dati
                    $export_data = array(
                        'version' => REM_VERSION,
                        'site_url' => get_site_url(),
                        'backup_type' => 'final_uninstall',
                        'created_at' => current_time('mysql'),
                        'settings' => get_option('rem_settings'),
                        'custom_breakpoints' => get_option('rem_custom_breakpoints'),
                        'rules' => $wpdb->get_results("SELECT * FROM $table_name"),
                        'statistics' => self::get_final_statistics()
                    );
                    
                    file_put_contents($backup_file, json_encode($export_data, JSON_PRETTY_PRINT));
                    
                    // Proteggi il file con .htaccess
                    $htaccess_content = "Order deny,allow\nDeny from all\n";
                    file_put_contents($backup_dir . '.htaccess', $htaccess_content);
                    
                    error_log("REM: Backup finale creato in $backup_file");
                }
            }
        } catch (Exception $e) {
            error_log('REM: Errore nella creazione del backup finale: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina tutte le tabelle del database
     */
    private static function drop_database_tables() {
        global $wpdb;
        
        $tables_to_drop = array(
            $wpdb->prefix . 'rem_rules',
            $wpdb->prefix . 'rem_backups'
        );
        
        foreach ($tables_to_drop as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        // Verifica che le tabelle siano state eliminate
        foreach ($tables_to_drop as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                error_log("REM: Errore nell'eliminazione della tabella $table");
            }
        }
    }
    
    /**
     * Elimina tutte le opzioni e metadati
     */
    private static function delete_options_and_metadata() {
        global $wpdb;
        
        // Lista delle opzioni da eliminare
        $options_to_delete = array(
            'rem_settings',
            'rem_breakpoints', 
            'rem_custom_breakpoints',
            'rem_version',
            'rem_db_version',
            'rem_activated_at',
            'rem_first_install',
            'rem_last_cleanup',
            'rem_last_backup',
            'rem_cache_hash',
            'rem_statistics',
            'rem_user_preferences'
        );
        
        // Elimina opzioni specifiche
        foreach ($options_to_delete as $option) {
            delete_option($option);
        }
        
        // Elimina tutte le opzioni che iniziano con 'rem_'
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE 'rem_%'"
        );
        
        // Elimina user meta correlati
        $wpdb->query(
            "DELETE FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE 'rem_%'"
        );
        
        // Elimina post meta se il plugin ha salvato dati nei post
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} 
             WHERE meta_key LIKE 'rem_%'"
        );
        
        // Elimina term meta se utilizzati
        $wpdb->query(
            "DELETE FROM {$wpdb->termmeta} 
             WHERE meta_key LIKE 'rem_%'"
        );
    }
    
    /**
     * Pulisce file e directory
     */
    private static function cleanup_files() {
        $upload_dir = wp_upload_dir();
        $rem_dir = $upload_dir['basedir'] . '/rem-backups/';
        
        // Elimina directory backup solo se l'utente lo richiede esplicitamente
        $settings = get_option('rem_settings', array());
        $delete_backups = isset($settings['delete_backups_on_uninstall']) ? 
                         $settings['delete_backups_on_uninstall'] : false;
        
        if ($delete_backups && is_dir($rem_dir)) {
            self::delete_directory_recursive($rem_dir);
        }
        
        // Pulisci eventuali file temporanei
        $temp_files = glob(sys_get_temp_dir() . '/rem_*');
        foreach ($temp_files as $temp_file) {
            if (is_file($temp_file)) {
                unlink($temp_file);
            }
        }
    }
    
    /**
     * Elimina directory ricorsivamente
     */
    private static function delete_directory_recursive($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($path)) {
                self::delete_directory_recursive($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Rimuove capacità personalizzate
     */
    private static function remove_custom_capabilities() {
        $all_roles = wp_roles()->get_names();
        
        $capabilities_to_remove = array(
            'manage_responsive_elements',
            'edit_responsive_rules',
            'delete_responsive_rules'
        );
        
        foreach ($all_roles as $role_name => $display_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities_to_remove as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }
    
    /**
     * Rimuove tutti gli eventi cron
     */
    private static function unschedule_all_events() {
        $cron_events = array(
            'rem_cleanup_database',
            'rem_auto_backup', 
            'rem_optimize_database',
            'rem_check_updates',
            'rem_generate_reports'
        );
        
        foreach ($cron_events as $event) {
            wp_clear_scheduled_hook($event);
        }
        
        // Rimuovi eventi cron personalizzati
        $cron_array = get_option('cron');
        if (is_array($cron_array)) {
            foreach ($cron_array as $timestamp => $cron) {
                if (is_array($cron)) {
                    foreach ($cron as $hook => $args) {
                        if (strpos($hook, 'rem_') === 0) {
                            wp_unschedule_event($timestamp, $hook);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Pulisce cache e transient
     */
    private static function cleanup_cache_and_transients() {
        global $wpdb;
        
        // Elimina tutti i transient del plugin
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_rem_%' 
             OR option_name LIKE '_transient_timeout_rem_%'"
        );
        
        // Elimina site transient
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_site_transient_rem_%' 
             OR option_name LIKE '_site_transient_timeout_rem_%'"
        );
        
        // Pulisci cache oggetti se disponibile
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Pulisci cache di plugin esterni se presenti
        do_action('rem_clear_external_cache');
    }
    
    /**
     * Ottiene statistiche finali per il backup
     */
    private static function get_final_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            return array('error' => 'Tabella non esistente');
        }
        
        $stats = array();
        
        try {
            $stats['total_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            $stats['active_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_active = 1");
            $stats['page_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE scope = 'page'");
            $stats['site_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE scope = 'site'");
            $stats['pages_affected'] = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_name WHERE scope = 'page' AND post_id > 0");
            
            // Breakpoint usage
            $rules_data = $wpdb->get_col("SELECT rules FROM $table_name");
            $breakpoint_usage = array();
            
            foreach ($rules_data as $rule_json) {
                $rule = json_decode($rule_json, true);
                if (is_array($rule)) {
                    foreach (array_keys($rule) as $breakpoint) {
                        $breakpoint_usage[$breakpoint] = ($breakpoint_usage[$breakpoint] ?? 0) + 1;
                    }
                }
            }
            
            $stats['breakpoint_usage'] = $breakpoint_usage;
            
            // Periodo di utilizzo
            $first_rule = $wpdb->get_var("SELECT created_at FROM $table_name ORDER BY created_at ASC LIMIT 1");
            $last_rule = $wpdb->get_var("SELECT updated_at FROM $table_name ORDER BY updated_at DESC LIMIT 1");
            
            $stats['usage_period'] = array(
                'first_rule' => $first_rule,
                'last_rule' => $last_rule,
                'total_days' => $first_rule ? ceil((strtotime($last_rule) - strtotime($first_rule)) / 86400) : 0
            );
            
        } catch (Exception $e) {
            $stats['error'] = $e->getMessage();
        }
        
        return $stats;
    }
    
    /**
     * Log dell'operazione di disinstallazione
     */
    private static function log_uninstallation() {
        $log_data = array(
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'user_login' => wp_get_current_user()->user_login,
            'site_url' => get_site_url(),
            'plugin_version' => REM_VERSION,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'multisite' => is_multisite(),
            'action' => 'complete_uninstall'
        );
        
        error_log('REM: Disinstallazione completata - ' . json_encode($log_data));
        
        // Invia statistiche anonime se l'utente ha dato il consenso
        self::send_uninstall_feedback();
    }
    
    /**
     * Invia feedback anonimo sulla disinstallazione
     */
    private static function send_uninstall_feedback() {
        $settings = get_option('rem_settings', array());
        $allow_feedback = isset($settings['allow_anonymous_feedback']) ? 
                         $settings['allow_anonymous_feedback'] : false;
        
        if (!$allow_feedback) {
            return;
        }
        
        try {
            $feedback_data = array(
                'action' => 'uninstall',
                'version' => REM_VERSION,
                'site_hash' => md5(get_site_url()), // Hash anonimo del sito
                'wp_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'plugin_active_days' => self::calculate_active_days(),
                'rules_created' => self::get_rules_count(),
                'timestamp' => time()
            );
            
            // Invia dati in modo asincrono (non bloccante)
            wp_remote_post('https://api.yourplugin.com/feedback', array(
                'timeout' => 5,
                'blocking' => false,
                'body' => json_encode($feedback_data),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'REM-Plugin/' . REM_VERSION
                )
            ));
            
        } catch (Exception $e) {
            // Ignora errori nel feedback per non bloccare la disinstallazione
            error_log('REM: Errore nell\'invio del feedback: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcola i giorni di utilizzo del plugin
     */
    private static function calculate_active_days() {
        $first_install = get_option('rem_first_install');
        if (!$first_install) {
            return 0;
        }
        
        return ceil((time() - strtotime($first_install)) / 86400);
    }
    
    /**
     * Ottiene il numero totale di regole create
     */
    private static function get_rules_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }
        
        return 0;
    }
    
    /**
     * Verifica se è un ambiente di sviluppo
     */
    private static function is_development_environment() {
        $dev_indicators = array(
            strpos(get_site_url(), 'localhost') !== false,
            strpos(get_site_url(), '127.0.0.1') !== false,
            strpos(get_site_url(), '.local') !== false,
            strpos(get_site_url(), '.dev') !== false,
            defined('WP_DEBUG') && WP_DEBUG,
            defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development'
        );
        
        return in_array(true, $dev_indicators);
    }
}

// Esegui la disinstallazione
REM_Uninstaller::uninstall();

// Log finale
error_log('REM: File uninstall.php eseguito completamente');