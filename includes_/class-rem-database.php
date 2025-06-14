<?php
/**
 * Classe per gestire il database del plugin Responsive Element Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class REM_Database {
    
    const DB_VERSION = '1.0';
    const TABLE_RULES = 'rem_rules';
    const TABLE_BACKUPS = 'rem_backups';
    
    /**
     * Crea le tabelle del plugin
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabella delle regole
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $sql_rules = "CREATE TABLE $table_rules (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            element_selector varchar(500) NOT NULL,
            element_id varchar(255) DEFAULT '',
            element_class varchar(500) DEFAULT '',
            element_tag varchar(50) DEFAULT '',
            scope enum('page', 'site') NOT NULL DEFAULT 'page',
            post_id mediumint(9) DEFAULT 0,
            rules longtext NOT NULL,
            priority int(11) DEFAULT 10,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX idx_scope_post (scope, post_id),
            INDEX idx_selector (element_selector(255)),
            INDEX idx_active (is_active),
            INDEX idx_priority (priority),
            INDEX idx_created (created_at),
            FOREIGN KEY (created_by) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL
        ) $charset_collate;";
        
        // Tabella dei backup
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        $sql_backups = "CREATE TABLE $table_backups (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            backup_name varchar(255) NOT NULL,
            backup_data longtext NOT NULL,
            rules_count int(11) DEFAULT 0,
            file_size bigint(20) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            created_by bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX idx_created (created_at),
            FOREIGN KEY (created_by) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_rules);
        dbDelta($sql_backups);
        
        // Salva la versione del database
        update_option('rem_db_version', self::DB_VERSION);
        
        // Hook per estensioni
        do_action('rem_database_created');
    }
    
    /**
     * Verifica se le tabelle esistono
     */
    public static function tables_exist() {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        $rules_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_rules'") === $table_rules;
        $backups_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_backups'") === $table_backups;
        
        return $rules_exists && $backups_exists;
    }
    
    /**
     * Aggiorna il database se necessario
     */
    public static function maybe_upgrade() {
        $current_version = get_option('rem_db_version', '0');
        
        if (version_compare($current_version, self::DB_VERSION, '<')) {
            self::upgrade_database($current_version);
        }
    }
    
    /**
     * Aggiorna il database
     */
    private static function upgrade_database($from_version) {
        global $wpdb;
        
        // Esempio di upgrade da versione 0 a 1.0
        if (version_compare($from_version, '1.0', '<')) {
            // Aggiungi colonne se necessario
            $table_rules = $wpdb->prefix . self::TABLE_RULES;
            
            // Controlla se la colonna priority esiste
            $column_exists = $wpdb->get_results(
                "SHOW COLUMNS FROM $table_rules LIKE 'priority'"
            );
            
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $table_rules ADD COLUMN priority int(11) DEFAULT 10 AFTER rules");
                $wpdb->query("ALTER TABLE $table_rules ADD INDEX idx_priority (priority)");
            }
            
            // Controlla se la colonna is_active esiste
            $column_exists = $wpdb->get_results(
                "SHOW COLUMNS FROM $table_rules LIKE 'is_active'"
            );
            
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $table_rules ADD COLUMN is_active tinyint(1) DEFAULT 1 AFTER priority");
                $wpdb->query("ALTER TABLE $table_rules ADD INDEX idx_active (is_active)");
            }
        }
        
        // Aggiorna la versione
        update_option('rem_db_version', self::DB_VERSION);
        
        // Hook per estensioni
        do_action('rem_database_upgraded', $from_version, self::DB_VERSION);
    }
    
    /**
     * Elimina le tabelle del plugin
     */
    public static function drop_tables() {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        $wpdb->query("DROP TABLE IF EXISTS $table_rules");
        $wpdb->query("DROP TABLE IF EXISTS $table_backups");
        
        delete_option('rem_db_version');
        
        // Hook per estensioni
        do_action('rem_database_dropped');
    }
    
    /**
     * Ottimizza le tabelle del database
     */
    public static function optimize_tables() {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        $wpdb->query("OPTIMIZE TABLE $table_rules");
        $wpdb->query("OPTIMIZE TABLE $table_backups");
        
        return array(
            'success' => true,
            'message' => 'Tabelle ottimizzate con successo'
        );
    }
    
    /**
     * Ottieni statistiche del database
     */
    public static function get_database_stats() {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        // Statistiche regole
        $total_rules = $wpdb->get_var("SELECT COUNT(*) FROM $table_rules");
        $active_rules = $wpdb->get_var("SELECT COUNT(*) FROM $table_rules WHERE is_active = 1");
        $page_rules = $wpdb->get_var("SELECT COUNT(*) FROM $table_rules WHERE scope = 'page'");
        $site_rules = $wpdb->get_var("SELECT COUNT(*) FROM $table_rules WHERE scope = 'site'");
        
        // Statistiche backup
        $total_backups = $wpdb->get_var("SELECT COUNT(*) FROM $table_backups");
        $backup_size = $wpdb->get_var("SELECT SUM(file_size) FROM $table_backups");
        
        // Dimensione tabelle
        $rules_size = $wpdb->get_var(
            "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
             FROM information_schema.tables 
             WHERE table_schema='{$wpdb->dbname}' AND table_name='$table_rules'"
        );
        
        $backups_size = $wpdb->get_var(
            "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
             FROM information_schema.tables 
             WHERE table_schema='{$wpdb->dbname}' AND table_name='$table_backups'"
        );
        
        return array(
            'rules' => array(
                'total' => (int) $total_rules,
                'active' => (int) $active_rules,
                'inactive' => (int) $total_rules - (int) $active_rules,
                'page_specific' => (int) $page_rules,
                'site_wide' => (int) $site_rules,
                'table_size_mb' => (float) $rules_size
            ),
            'backups' => array(
                'total' => (int) $total_backups,
                'total_size_bytes' => (int) $backup_size,
                'total_size_mb' => round((int) $backup_size / 1024 / 1024, 2),
                'table_size_mb' => (float) $backups_size
            ),
            'total_size_mb' => (float) $rules_size + (float) $backups_size
        );
    }
    
    /**
     * Pulisci vecchie regole non utilizzate
     */
    public static function cleanup_old_rules($days = 30) {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        
        // Elimina regole inattive più vecchie di X giorni
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_rules 
             WHERE is_active = 0 
             AND updated_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
        
        // Elimina regole per pagine che non esistono più
        $orphaned = $wpdb->query(
            "DELETE r FROM $table_rules r 
             LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID 
             WHERE r.scope = 'page' 
             AND r.post_id > 0 
             AND p.ID IS NULL"
        );
        
        return array(
            'success' => true,
            'deleted_inactive' => $deleted,
            'deleted_orphaned' => $orphaned,
            'total_deleted' => $deleted + $orphaned
        );
    }
    
    /**
     * Crea un backup delle regole
     */
    public static function create_backup($name = null) {
        global $wpdb;
        
        if (!$name) {
            $name = 'Backup automatico ' . date('Y-m-d H:i:s');
        }
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        // Ottieni tutte le regole
        $rules = $wpdb->get_results("SELECT * FROM $table_rules ORDER BY id");
        
        $backup_data = array(
            'version' => self::DB_VERSION,
            'created_at' => current_time('mysql'),
            'rules' => $rules
        );
        
        $json_data = json_encode($backup_data);
        $file_size = strlen($json_data);
        $rules_count = count($rules);
        
        $result = $wpdb->insert(
            $table_backups,
            array(
                'backup_name' => $name,
                'backup_data' => $json_data,
                'rules_count' => $rules_count,
                'file_size' => $file_size,
                'created_by' => get_current_user_id()
            ),
            array('%s', '%s', '%d', '%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('backup_failed', 'Errore nella creazione del backup');
        }
        
        return array(
            'success' => true,
            'backup_id' => $wpdb->insert_id,
            'rules_count' => $rules_count,
            'file_size' => $file_size
        );
    }
    
    /**
     * Ripristina un backup
     */
    public static function restore_backup($backup_id) {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        // Ottieni il backup
        $backup = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_backups WHERE id = %d",
            $backup_id
        ));
        
        if (!$backup) {
            return new WP_Error('backup_not_found', 'Backup non trovato');
        }
        
        $backup_data = json_decode($backup->backup_data, true);
        
        if (!$backup_data || !isset($backup_data['rules'])) {
            return new WP_Error('invalid_backup', 'Dati del backup non validi');
        }
        
        // Inizia transazione
        $wpdb->query('START TRANSACTION');
        
        try {
            // Elimina tutte le regole attuali
            $wpdb->query("DELETE FROM $table_rules");
            
            // Ripristina le regole dal backup
            foreach ($backup_data['rules'] as $rule) {
                $wpdb->insert(
                    $table_rules,
                    array(
                        'element_selector' => $rule->element_selector,
                        'element_id' => $rule->element_id,
                        'element_class' => $rule->element_class,
                        'element_tag' => $rule->element_tag ?? '',
                        'scope' => $rule->scope,
                        'post_id' => $rule->post_id,
                        'rules' => $rule->rules,
                        'priority' => $rule->priority ?? 10,
                        'is_active' => $rule->is_active ?? 1,
                        'created_by' => get_current_user_id()
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d')
                );
            }
            
            $wpdb->query('COMMIT');
            
            return array(
                'success' => true,
                'restored_rules' => count($backup_data['rules'])
            );
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('restore_failed', 'Errore nel ripristino: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina backup vecchi
     */
    public static function cleanup_old_backups($keep_count = 10) {
        global $wpdb;
        
        $table_backups = $wpdb->prefix . self::TABLE_BACKUPS;
        
        // Mantieni solo gli ultimi N backup
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_backups 
             WHERE id NOT IN (
                 SELECT id FROM (
                     SELECT id FROM $table_backups 
                     ORDER BY created_at DESC 
                     LIMIT %d
                 ) temp
             )",
            $keep_count
        ));
        
        return array(
            'success' => true,
            'deleted_backups' => $deleted
        );
    }
    
    /**
     * Esporta regole in formato specifico
     */
    public static function export_rules($format = 'json', $scope = 'all', $post_id = 0) {
        global $wpdb;
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        
        $where_clause = "WHERE is_active = 1";
        $params = array();
        
        if ($scope === 'page' && $post_id > 0) {
            $where_clause .= " AND scope = 'page' AND post_id = %d";
            $params[] = $post_id;
        } elseif ($scope === 'site') {
            $where_clause .= " AND scope = 'site'";
        }
        
        $query = "SELECT * FROM $table_rules $where_clause ORDER BY scope, post_id, priority";
        
        if (!empty($params)) {
            $rules = $wpdb->get_results($wpdb->prepare($query, ...$params));
        } else {
            $rules = $wpdb->get_results($query);
        }
        
        switch ($format) {
            case 'json':
                return self::export_as_json($rules);
            case 'css':
                return self::export_as_css($rules);
            case 'xml':
                return self::export_as_xml($rules);
            default:
                return new WP_Error('invalid_format', 'Formato di esportazione non supportato');
        }
    }
    
    /**
     * Esporta come JSON
     */
    private static function export_as_json($rules) {
        $export_data = array(
            'version' => self::DB_VERSION,
            'exported_at' => current_time('mysql'),
            'site_url' => get_site_url(),
            'rules_count' => count($rules),
            'rules' => $rules
        );
        
        return array(
            'content' => json_encode($export_data, JSON_PRETTY_PRINT),
            'filename' => 'rem-rules-' . date('Y-m-d-H-i-s') . '.json',
            'mime_type' => 'application/json'
        );
    }
    
    /**
     * Esporta come CSS
     */
    private static function export_as_css($rules) {
        $css_content = "/* Responsive Element Manager - Regole CSS Esportate */\n";
        $css_content .= "/* Esportato il: " . current_time('Y-m-d H:i:s') . " */\n\n";
        
        foreach ($rules as $rule) {
            $rule_data = json_decode($rule->rules, true);
            $selector = $rule->element_selector;
            
            if (empty($rule_data)) continue;
            
            $css_content .= "/* Regola ID: {$rule->id} - Scope: {$rule->scope} */\n";
            
            foreach ($rule_data as $breakpoint => $properties) {
                if (empty($properties)) continue;
                
                $css_rules = array();
                
                if (isset($properties['font_size'])) {
                    $css_rules[] = "font-size: {$properties['font_size']['value']}{$properties['font_size']['unit']}";
                }
                
                if (isset($properties['font_family'])) {
                    $css_rules[] = "font-family: {$properties['font_family']}";
                }
                
                if (isset($properties['text_align'])) {
                    $css_rules[] = "text-align: {$properties['text_align']}";
                }
                
                if (isset($properties['width'])) {
                    $css_rules[] = "width: {$properties['width']['value']}{$properties['width']['unit']}";
                }
                
                if (isset($properties['height'])) {
                    $css_rules[] = "height: {$properties['height']['value']}{$properties['height']['unit']}";
                }
                
                if (!empty($css_rules)) {
                    if ($breakpoint === 'desktop') {
                        $css_content .= "$selector {\n";
                        foreach ($css_rules as $css_rule) {
                            $css_content .= "    $css_rule;\n";
                        }
                        $css_content .= "}\n\n";
                    } else {
                        $media_query = $breakpoint === 'mobile' ? 
                            '(max-width: 767px)' : 
                            '(min-width: 768px) and (max-width: 1023px)';
                        
                        $css_content .= "@media $media_query {\n";
                        $css_content .= "    $selector {\n";
                        foreach ($css_rules as $css_rule) {
                            $css_content .= "        $css_rule;\n";
                        }
                        $css_content .= "    }\n";
                        $css_content .= "}\n\n";
                    }
                }
            }
        }
        
        return array(
            'content' => $css_content,
            'filename' => 'rem-rules-' . date('Y-m-d-H-i-s') . '.css',
            'mime_type' => 'text/css'
        );
    }
    
    /**
     * Esporta come XML
     */
    private static function export_as_xml($rules) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rem_export></rem_export>');
        
        $xml->addAttribute('version', self::DB_VERSION);
        $xml->addAttribute('exported_at', current_time('mysql'));
        $xml->addAttribute('site_url', get_site_url());
        $xml->addAttribute('rules_count', count($rules));
        
        $rules_node = $xml->addChild('rules');
        
        foreach ($rules as $rule) {
            $rule_node = $rules_node->addChild('rule');
            $rule_node->addAttribute('id', $rule->id);
            
            $rule_node->addChild('element_selector', htmlspecialchars($rule->element_selector));
            $rule_node->addChild('element_id', htmlspecialchars($rule->element_id));
            $rule_node->addChild('element_class', htmlspecialchars($rule->element_class));
            $rule_node->addChild('scope', $rule->scope);
            $rule_node->addChild('post_id', $rule->post_id);
            $rule_node->addChild('priority', $rule->priority);
            $rule_node->addChild('is_active', $rule->is_active);
            $rule_node->addChild('rules', htmlspecialchars($rule->rules));
            $rule_node->addChild('created_at', $rule->created_at);
            $rule_node->addChild('updated_at', $rule->updated_at);
        }
        
        return array(
            'content' => $xml->asXML(),
            'filename' => 'rem-rules-' . date('Y-m-d-H-i-s') . '.xml',
            'mime_type' => 'application/xml'
        );
    }
    
    /**
     * Importa regole da JSON
     */
    public static function import_rules($json_data, $overwrite = false) {
        global $wpdb;
        
        $data = json_decode($json_data, true);
        
        if (!$data || !isset($data['rules'])) {
            return new WP_Error('invalid_data', 'Dati di importazione non validi');
        }
        
        $table_rules = $wpdb->prefix . self::TABLE_RULES;
        $imported = 0;
        $skipped = 0;
        $errors = array();
        
        foreach ($data['rules'] as $rule) {
            // Verifica se la regola esiste già
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_rules 
                 WHERE element_selector = %s AND scope = %s AND post_id = %d",
                $rule->element_selector ?? $rule['element_selector'],
                $rule->scope ?? $rule['scope'],
                $rule->post_id ?? $rule['post_id']
            ));
            
            if ($existing && !$overwrite) {
                $skipped++;
                continue;
            }
            
            $rule_data = array(
                'element_selector' => $rule->element_selector ?? $rule['element_selector'],
                'element_id' => $rule->element_id ?? $rule['element_id'] ?? '',
                'element_class' => $rule->element_class ?? $rule['element_class'] ?? '',
                'element_tag' => $rule->element_tag ?? $rule['element_tag'] ?? '',
                'scope' => $rule->scope ?? $rule['scope'],
                'post_id' => $rule->post_id ?? $rule['post_id'] ?? 0,
                'rules' => is_string($rule->rules ?? $rule['rules']) ? 
                          ($rule->rules ?? $rule['rules']) : 
                          json_encode($rule->rules ?? $rule['rules']),
                'priority' => $rule->priority ?? $rule['priority'] ?? 10,
                'is_active' => $rule->is_active ?? $rule['is_active'] ?? 1,
                'created_by' => get_current_user_id()
            );
            
            if ($existing && $overwrite) {
                $result = $wpdb->update(
                    $table_rules,
                    $rule_data,
                    array('id' => $existing),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d'),
                    array('%d')
                );
            } else {
                $result = $wpdb->insert(
                    $table_rules,
                    $rule_data,
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d')
                );
            }
            
            if ($result !== false) {
                $imported++;
            } else {
                $errors[] = "Errore nell'importazione della regola: " . $rule_data['element_selector'];
            }
        }
        
        return array(
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        );
    }
}

// Hook per l'inizializzazione del database
add_action('plugins_loaded', array('REM_Database', 'maybe_upgrade'));