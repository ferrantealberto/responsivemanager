<?php
/**
 * Classe per gestire le regole del plugin Responsive Element Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class REM_Rule_Manager {
    
    /**
     * Salva una regola nel database
     */
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
            'element_tag' => $validated_data['element_tag'],
            'scope' => $validated_data['scope'],
            'post_id' => $validated_data['scope'] === 'page' ? $validated_data['post_id'] : 0,
            'rules' => json_encode($validated_data['rules']),
            'priority' => $validated_data['priority'],
            'is_active' => 1,
            'created_by' => get_current_user_id()
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
            unset($data['created_by']); // Non aggiornare il creatore
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing_rule->id),
                array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d'),
                array('%d')
            );
            $rule_id = $existing_rule->id;
        } else {
            // Crea nuova regola
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d')
            );
            $rule_id = $wpdb->insert_id;
        }
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nel salvare la regola: ' . $wpdb->last_error);
        }
        
        // Pulisci cache CSS
        self::clear_css_cache();
        
        // Hook per estensioni
        do_action('rem_rule_saved', $validated_data, $rule_id, $existing_rule ? 'updated' : 'created');
        
        return array(
            'success' => true, 
            'message' => 'Regola salvata con successo',
            'rule_id' => $rule_id,
            'action' => $existing_rule ? 'updated' : 'created'
        );
    }
    
    /**
     * Elimina una regola
     */
    public static function delete_rule($rule_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        // Verifica che la regola esista
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $rule_id
        ));
        
        if (!$rule) {
            return new WP_Error('rule_not_found', 'Regola non trovata');
        }
        
        // Verifica permessi
        if (!current_user_can('edit_posts')) {
            return new WP_Error('insufficient_permissions', 'Permessi insufficienti');
        }
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $rule_id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nell\'eliminare la regola: ' . $wpdb->last_error);
        }
        
        // Pulisci cache CSS
        self::clear_css_cache();
        
        // Hook per estensioni
        do_action('rem_rule_deleted', $rule_id, $rule);
        
        return array(
            'success' => true, 
            'message' => 'Regola eliminata con successo'
        );
    }
    
    /**
     * Ottieni regole dal database
     */
    public static function get_rules($post_id = 0, $scope = 'all', $active_only = true) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $where_conditions = array();
        $params = array();
        
        if ($active_only) {
            $where_conditions[] = "is_active = 1";
        }
        
        if ($scope === 'page' && $post_id > 0) {
            $where_conditions[] = "scope = 'page' AND post_id = %d";
            $params[] = $post_id;
        } elseif ($scope === 'site') {
            $where_conditions[] = "scope = 'site'";
        } elseif ($post_id > 0) {
            // Ottieni regole specifiche per la pagina e globali
            $where_conditions[] = "(scope = 'page' AND post_id = %d) OR scope = 'site'";
            $params[] = $post_id;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = "SELECT * FROM $table_name $where_clause ORDER BY priority ASC, created_at DESC";
        
        if (!empty($params)) {
            $rules = $wpdb->get_results($wpdb->prepare($query, ...$params));
        } else {
            $rules = $wpdb->get_results($query);
        }
        
        // Decodifica le regole JSON e aggiungi metadati
        foreach ($rules as &$rule) {
            $rule->rules = json_decode($rule->rules, true);
            
            // Aggiungi informazioni sulla pagina se necessario
            if ($rule->scope === 'page' && $rule->post_id > 0) {
                $post = get_post($rule->post_id);
                $rule->post_title = $post ? $post->post_title : 'Pagina eliminata';
                $rule->post_url = $post ? get_permalink($rule->post_id) : '';
            }
            
            // Aggiungi informazioni sui breakpoint
            $rule->breakpoints = $rule->rules ? array_keys($rule->rules) : array();
            $rule->breakpoints_count = count($rule->breakpoints);
        }
        
        return apply_filters('rem_get_rules', $rules, $post_id, $scope);
    }
    
    /**
     * Ottieni una singola regola
     */
    public static function get_rule($rule_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $rule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $rule_id
        ));
        
        if (!$rule) {
            return false;
        }
        
        $rule->rules = json_decode($rule->rules, true);
        
        return $rule;
    }
    
    /**
     * Attiva/disattiva una regola
     */
    public static function toggle_rule($rule_id, $is_active = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        if ($is_active === null) {
            // Toggle automatico
            $current_status = $wpdb->get_var($wpdb->prepare(
                "SELECT is_active FROM $table_name WHERE id = %d",
                $rule_id
            ));
            $is_active = $current_status ? 0 : 1;
        }
        
        $result = $wpdb->update(
            $table_name,
            array('is_active' => $is_active ? 1 : 0),
            array('id' => $rule_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nell\'aggiornare lo stato della regola');
        }
        
        // Pulisci cache CSS
        self::clear_css_cache();
        
        // Hook per estensioni
        do_action('rem_rule_toggled', $rule_id, $is_active);
        
        return array(
            'success' => true,
            'is_active' => $is_active,
            'message' => $is_active ? 'Regola attivata' : 'Regola disattivata'
        );
    }
    
    /**
     * Aggiorna la priorità di una regola
     */
    public static function update_priority($rule_id, $priority) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $priority = max(1, min(100, intval($priority))); // Limita tra 1 e 100
        
        $result = $wpdb->update(
            $table_name,
            array('priority' => $priority),
            array('id' => $rule_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nell\'aggiornare la priorità');
        }
        
        // Pulisci cache CSS
        self::clear_css_cache();
        
        return array(
            'success' => true,
            'priority' => $priority,
            'message' => 'Priorità aggiornata'
        );
    }
    
    /**
     * Duplica una regola
     */
    public static function duplicate_rule($rule_id, $new_scope = null, $new_post_id = null) {
        global $wpdb;
        
        $original_rule = self::get_rule($rule_id);
        
        if (!$original_rule) {
            return new WP_Error('rule_not_found', 'Regola originale non trovata');
        }
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $new_data = array(
            'element_selector' => $original_rule->element_selector,
            'element_id' => $original_rule->element_id,
            'element_class' => $original_rule->element_class,
            'element_tag' => $original_rule->element_tag,
            'scope' => $new_scope ?: $original_rule->scope,
            'post_id' => $new_post_id !== null ? $new_post_id : $original_rule->post_id,
            'rules' => json_encode($original_rule->rules),
            'priority' => $original_rule->priority,
            'is_active' => 1,
            'created_by' => get_current_user_id()
        );
        
        $result = $wpdb->insert(
            $table_name,
            $new_data,
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', 'Errore nella duplicazione della regola');
        }
        
        $new_rule_id = $wpdb->insert_id;
        
        // Hook per estensioni
        do_action('rem_rule_duplicated', $rule_id, $new_rule_id);
        
        return array(
            'success' => true,
            'new_rule_id' => $new_rule_id,
            'message' => 'Regola duplicata con successo'
        );
    }
    
    /**
     * Cerca regole
     */
    public static function search_rules($query, $filters = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $where_conditions = array();
        $params = array();
        
        // Ricerca testuale
        if (!empty($query)) {
            $where_conditions[] = "(element_selector LIKE %s OR element_id LIKE %s OR element_class LIKE %s)";
            $like_query = '%' . $wpdb->esc_like($query) . '%';
            $params[] = $like_query;
            $params[] = $like_query;
            $params[] = $like_query;
        }
        
        // Filtri
        if (isset($filters['scope']) && !empty($filters['scope'])) {
            $where_conditions[] = "scope = %s";
            $params[] = $filters['scope'];
        }
        
        if (isset($filters['post_id']) && !empty($filters['post_id'])) {
            $where_conditions[] = "post_id = %d";
            $params[] = intval($filters['post_id']);
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where_conditions[] = "is_active = %d";
            $params[] = intval($filters['is_active']);
        }
        
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $where_conditions[] = "created_at >= %s";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $where_conditions[] = "created_at <= %s";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $sql = "SELECT * FROM $table_name $where_clause ORDER BY priority ASC, updated_at DESC";
        
        if (!empty($params)) {
            $rules = $wpdb->get_results($wpdb->prepare($sql, ...$params));
        } else {
            $rules = $wpdb->get_results($sql);
        }
        
        // Decodifica regole e aggiungi metadati
        foreach ($rules as &$rule) {
            $rule->rules = json_decode($rule->rules, true);
            
            if ($rule->scope === 'page' && $rule->post_id > 0) {
                $post = get_post($rule->post_id);
                $rule->post_title = $post ? $post->post_title : 'Pagina eliminata';
            }
        }
        
        return $rules;
    }
    
    /**
     * Validazione dei dati delle regole
     */
    private static function validate_rule_data($data) {
        $validated = array();
        
        // Validazione selector
        if (empty($data['selector'])) {
            return new WP_Error('validation_error', 'Selettore elemento richiesto');
        }
        
        if (!REM_Element_Selector::validate_selector($data['selector'])) {
            return new WP_Error('validation_error', 'Selettore non valido');
        }
        
        $validated['selector'] = sanitize_text_field($data['selector']);
        
        // Validazione scope
        if (!in_array($data['scope'], array('page', 'site'))) {
            return new WP_Error('validation_error', 'Scope non valido');
        }
        $validated['scope'] = $data['scope'];
        
        // Validazione post_id
        $validated['post_id'] = isset($data['post_id']) ? intval($data['post_id']) : 0;
        
        // Validazione elementi
        $validated['element_id'] = isset($data['element_id']) ? sanitize_text_field($data['element_id']) : '';
        $validated['element_class'] = isset($data['element_class']) ? sanitize_text_field($data['element_class']) : '';
        $validated['element_tag'] = isset($data['element_tag']) ? sanitize_text_field($data['element_tag']) : '';
        
        // Validazione priorità
        $validated['priority'] = isset($data['priority']) ? max(1, min(100, intval($data['priority']))) : 10;
        
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
    
    /**
     * Validazione delle regole CSS
     */
    private static function validate_rules($rules) {
        $validated_rules = array();
        $allowed_breakpoints = array('mobile', 'tablet', 'desktop');
        
        // Permetti breakpoint personalizzati
        $custom_breakpoints = get_option('rem_custom_breakpoints', array());
        $allowed_breakpoints = array_merge($allowed_breakpoints, array_keys($custom_breakpoints));
        
        foreach ($rules as $breakpoint => $rule_set) {
            if (!in_array($breakpoint, $allowed_breakpoints) || !is_array($rule_set)) {
                continue;
            }
            
            $validated_rules[$breakpoint] = array();
            
            // Validazione font size
            if (isset($rule_set['font_size']) && is_array($rule_set['font_size'])) {
                $font_size = $rule_set['font_size'];
                if (isset($font_size['value']) && isset($font_size['unit']) && 
                    is_numeric($font_size['value']) && $font_size['value'] > 0) {
                    $validated_rules[$breakpoint]['font_size'] = array(
                        'value' => floatval($font_size['value']),
                        'unit' => in_array($font_size['unit'], array('px', '%', 'em', 'rem', 'vw', 'vh')) ? 
                                 $font_size['unit'] : 'px'
                    );
                }
            }
            
            // Validazione font family
            if (isset($rule_set['font_family']) && !empty($rule_set['font_family'])) {
                $validated_rules[$breakpoint]['font_family'] = sanitize_text_field($rule_set['font_family']);
            }
            
            // Validazione allineamento
            if (isset($rule_set['text_align']) && 
                in_array($rule_set['text_align'], array('left', 'center', 'right', 'justify'))) {
                $validated_rules[$breakpoint]['text_align'] = $rule_set['text_align'];
            }
            
            // Validazione dimensioni
            foreach (array('width', 'height') as $dimension) {
                if (isset($rule_set[$dimension]) && is_array($rule_set[$dimension])) {
                    $size = $rule_set[$dimension];
                    if (isset($size['value']) && isset($size['unit']) && 
                        is_numeric($size['value']) && $size['value'] > 0) {
                        $allowed_units = $dimension === 'width' ? 
                                        array('px', '%', 'em', 'rem', 'vw') : 
                                        array('px', '%', 'em', 'rem', 'vh');
                        
                        $validated_rules[$breakpoint][$dimension] = array(
                            'value' => floatval($size['value']),
                            'unit' => in_array($size['unit'], $allowed_units) ? 
                                     $size['unit'] : 'px'
                        );
                    }
                }
            }
            
            // Rimuovi breakpoint vuoti
            if (empty($validated_rules[$breakpoint])) {
                unset($validated_rules[$breakpoint]);
            }
        }
        
        if (empty($validated_rules)) {
            return new WP_Error('validation_error', 'Nessuna regola valida fornita');
        }
        
        return apply_filters('rem_validate_rules', $validated_rules, $rules);
    }
    
    /**
     * Ottieni statistiche delle regole
     */
    public static function get_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'rem_rules';
        
        $stats = array();
        
        // Conteggi base
        $stats['total_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $stats['active_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_active = 1");
        $stats['inactive_rules'] = $stats['total_rules'] - $stats['active_rules'];
        $stats['page_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE scope = 'page'");
        $stats['site_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE scope = 'site'");
        
        // Pagine interessate
        $stats['pages_affected'] = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_name WHERE scope = 'page' AND post_id > 0");
        
        // Breakpoint più utilizzati
        $rules_data = $wpdb->get_col("SELECT rules FROM $table_name WHERE is_active = 1");
        $breakpoint_usage = array('mobile' => 0, 'tablet' => 0, 'desktop' => 0);
        
        foreach ($rules_data as $rule_json) {
            $rule = json_decode($rule_json, true);
            if (is_array($rule)) {
                foreach ($rule as $breakpoint => $properties) {
                    if (isset($breakpoint_usage[$breakpoint])) {
                        $breakpoint_usage[$breakpoint]++;
                    }
                }
            }
        }
        
        $stats['breakpoint_usage'] = $breakpoint_usage;
        
        // Regole create nel tempo (ultimo mese)
        $timeline_data = $wpdb->get_results(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $table_name 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
             GROUP BY DATE(created_at) 
             ORDER BY date"
        );
        
        $stats['timeline'] = $timeline_data;
        
        return apply_filters('rem_statistics', $stats);
    }
    
    /**
     * Operazioni bulk
     */
    public static function bulk_action($action, $rule_ids) {
        global $wpdb;
        
        if (empty($rule_ids) || !is_array($rule_ids)) {
            return new WP_Error('invalid_data', 'Nessuna regola selezionata');
        }
        
        $table_name = $wpdb->prefix . 'rem_rules';
        $affected = 0;
        
        switch ($action) {
            case 'activate':
                $placeholders = implode(',', array_fill(0, count($rule_ids), '%d'));
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET is_active = 1 WHERE id IN ($placeholders)",
                    ...$rule_ids
                ));
                break;
                
            case 'deactivate':
                $placeholders = implode(',', array_fill(0, count($rule_ids), '%d'));
                $affected = $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name SET is_active = 0 WHERE id IN ($placeholders)",
                    ...$rule_ids
                ));
                break;
                
            case 'delete':
                $placeholders = implode(',', array_fill(0, count($rule_ids), '%d'));
                $affected = $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table_name WHERE id IN ($placeholders)",
                    ...$rule_ids
                ));
                break;
                
            default:
                return new WP_Error('invalid_action', 'Azione non supportata');
        }
        
        if ($affected > 0) {
            self::clear_css_cache();
            do_action('rem_bulk_action', $action, $rule_ids, $affected);
        }
        
        return array(
            'success' => true,
            'affected' => $affected,
            'message' => sprintf('%d regole elaborate', $affected)
        );
    }
    
    /**
     * Pulisce la cache CSS
     */
    public static function clear_css_cache() {
        // Elimina transient cache se utilizzato
        delete_transient('rem_generated_css');
        delete_transient('rem_css_hash');
        
        // Hook per cache plugin esterni
        do_action('rem_clear_css_cache');
        
        return true;
    }
    
    /**
     * Ottieni hash delle regole per cache
     */
    public static function get_rules_hash($post_id = 0) {
        $rules = self::get_rules($post_id);
        $rules_data = array();
        
        foreach ($rules as $rule) {
            $rules_data[] = array(
                'selector' => $rule->element_selector,
                'scope' => $rule->scope,
                'post_id' => $rule->post_id,
                'rules' => $rule->rules,
                'priority' => $rule->priority,
                'updated_at' => $rule->updated_at
            );
        }
        
        return md5(serialize($rules_data));
    }
}

// Registra hook AJAX
add_action('wp_ajax_rem_save_rule', array('REM_Rule_Manager', 'ajax_save_rule'));
add_action('wp_ajax_rem_delete_rule', array('REM_Rule_Manager', 'ajax_delete_rule'));
add_action('wp_ajax_rem_get_rules', array('REM_Rule_Manager', 'ajax_get_rules'));
add_action('wp_ajax_rem_toggle_rule', array('REM_Rule_Manager', 'ajax_toggle_rule'));
add_action('wp_ajax_rem_search_rules', array('REM_Rule_Manager', 'ajax_search_rules'));
add_action('wp_ajax_rem_bulk_action', array('REM_Rule_Manager', 'ajax_bulk_action'));

// Implementazione metodi AJAX
class REM_Rule_Manager_Ajax {
    
    public static function ajax_save_rule() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $rule_data = $_POST['rule_data'] ?? array();
        $result = REM_Rule_Manager::save_rule($rule_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    public static function ajax_delete_rule() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $rule_id = intval($_POST['rule_id'] ?? 0);
        $result = REM_Rule_Manager::delete_rule($rule_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    public static function ajax_get_rules() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id'] ?? 0);
        $scope = sanitize_text_field($_POST['scope'] ?? 'all');
        
        $rules = REM_Rule_Manager::get_rules($post_id, $scope);
        wp_send_json_success($rules);
    }
    
    public static function ajax_toggle_rule() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $rule_id = intval($_POST['rule_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : null;
        
        $result = REM_Rule_Manager::toggle_rule($rule_id, $is_active);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    public static function ajax_search_rules() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $filters = $_POST['filters'] ?? array();
        
        // Sanitizza filtri
        $clean_filters = array();
        if (isset($filters['scope'])) {
            $clean_filters['scope'] = sanitize_text_field($filters['scope']);
        }
        if (isset($filters['post_id'])) {
            $clean_filters['post_id'] = intval($filters['post_id']);
        }
        if (isset($filters['is_active'])) {
            $clean_filters['is_active'] = intval($filters['is_active']);
        }
        
        $rules = REM_Rule_Manager::search_rules($query, $clean_filters);
        wp_send_json_success($rules);
    }
    
    public static function ajax_bulk_action() {
        check_ajax_referer('rem_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
        }
        
        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $rule_ids = array_map('intval', $_POST['rule_ids'] ?? array());
        
        $result = REM_Rule_Manager::bulk_action($action, $rule_ids);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
}

// Registra i metodi AJAX
add_action('wp_ajax_rem_save_rule', array('REM_Rule_Manager_Ajax', 'ajax_save_rule'));
add_action('wp_ajax_rem_delete_rule', array('REM_Rule_Manager_Ajax', 'ajax_delete_rule'));
add_action('wp_ajax_rem_get_rules', array('REM_Rule_Manager_Ajax', 'ajax_get_rules'));
add_action('wp_ajax_rem_toggle_rule', array('REM_Rule_Manager_Ajax', 'ajax_toggle_rule'));
add_action('wp_ajax_rem_search_rules', array('REM_Rule_Manager_Ajax', 'ajax_search_rules'));
add_action('wp_ajax_rem_bulk_action', array('REM_Rule_Manager_Ajax', 'ajax_bulk_action'));