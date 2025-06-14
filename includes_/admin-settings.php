<?php
/**
 * Pagina delle impostazioni del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Gestione salvataggio impostazioni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (wp_verify_nonce($_POST['_wpnonce'], 'rem_save_settings')) {
        
        // Breakpoint personalizzati
        $custom_breakpoints = array();
        if (isset($_POST['breakpoints']) && is_array($_POST['breakpoints'])) {
            foreach ($_POST['breakpoints'] as $bp) {
                if (!empty($bp['name']) && !empty($bp['query'])) {
                    $custom_breakpoints[sanitize_key($bp['name'])] = sanitize_text_field($bp['query']);
                }
            }
        }
        update_option('rem_custom_breakpoints', $custom_breakpoints);
        
        // Impostazioni generali
        $settings = array(
            'enable_minification' => isset($_POST['enable_minification']) ? 1 : 0,
            'load_on_frontend_only' => isset($_POST['load_on_frontend_only']) ? 1 : 0,
            'enable_cache' => isset($_POST['enable_cache']) ? 1 : 0,
            'button_position' => sanitize_text_field($_POST['button_position'] ?? 'top-right'),
            'user_roles' => array_map('sanitize_text_field', $_POST['user_roles'] ?? array('administrator')),
            'excluded_pages' => sanitize_textarea_field($_POST['excluded_pages'] ?? ''),
            'custom_css_prefix' => sanitize_text_field($_POST['custom_css_prefix'] ?? 'rem'),
            'enable_animations' => isset($_POST['enable_animations']) ? 1 : 0,
            'debug_mode' => isset($_POST['debug_mode']) ? 1 : 0
        );
        
        update_option('rem_settings', $settings);
        
        echo '<div class="notice notice-success is-dismissible"><p>Impostazioni salvate con successo!</p></div>';
    }
}

// Carica impostazioni attuali
$settings = get_option('rem_settings', array(
    'enable_minification' => 0,
    'load_on_frontend_only' => 1,
    'enable_cache' => 0,
    'button_position' => 'top-right',
    'user_roles' => array('administrator'),
    'excluded_pages' => '',
    'custom_css_prefix' => 'rem',
    'enable_animations' => 1,
    'debug_mode' => 0
));

$custom_breakpoints = get_option('rem_custom_breakpoints', array());
$default_breakpoints = array(
    'mobile' => '(max-width: 767px)',
    'tablet' => '(min-width: 768px) and (max-width: 1023px)',
    'desktop' => ''
);
?>

<div class="rem-settings-page">
    <form method="post">
        <?php wp_nonce_field('rem_save_settings'); ?>
        
        <div class="rem-settings-section">
            <h3>⚙️ Impostazioni Generali</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Posizione Pulsante</th>
                    <td>
                        <select name="button_position">
                            <option value="top-right" <?php selected($settings['button_position'], 'top-right'); ?>>Alto Destra</option>
                            <option value="top-left" <?php selected($settings['button_position'], 'top-left'); ?>>Alto Sinistra</option>
                            <option value="bottom-right" <?php selected($settings['button_position'], 'bottom-right'); ?>>Basso Destra</option>
                            <option value="bottom-left" <?php selected($settings['button_position'], 'bottom-left'); ?>>Basso Sinistra</option>
                        </select>
                        <p class="description">Posizione del pulsante di attivazione dell'editor</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Ruoli Utente Autorizzati</th>
                    <td>
                        <?php
                        $roles = get_editable_roles();
                        foreach ($roles as $role_key => $role_info):
                        ?>
                        <label>
                            <input type="checkbox" name="user_roles[]" value="<?php echo esc_attr($role_key); ?>" 
                                   <?php checked(in_array($role_key, $settings['user_roles'])); ?>>
                            <?php echo esc_html($role_info['name']); ?>
                        </label><br>
                        <?php endforeach; ?>
                        <p class="description">Ruoli che possono utilizzare l'editor responsive</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Pagine Escluse</th>
                    <td>
                        <textarea name="excluded_pages" rows="4" cols="50" class="large-text"><?php echo esc_textarea($settings['excluded_pages']); ?></textarea>
                        <p class="description">ID delle pagine dove disabilitare l'editor (uno per riga)</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="rem-settings-section">
            <h3>🚀 Performance</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Minificazione CSS</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_minification" value="1" <?php checked($settings['enable_minification']); ?>>
                            Abilita minificazione automatica del CSS generato
                        </label>
                        <p class="description">Riduce la dimensione del CSS per migliorare i tempi di caricamento</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Cache CSS</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_cache" value="1" <?php checked($settings['enable_cache']); ?>>
                            Abilita cache del CSS generato
                        </label>
                        <p class="description">Memorizza il CSS per ridurre i tempi di elaborazione</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Caricamento Scripts</th>
                    <td>
                        <label>
                            <input type="checkbox" name="load_on_frontend_only" value="1" <?php checked($settings['load_on_frontend_only']); ?>>
                            Carica script solo nel frontend
                        </label>
                        <p class="description">Evita di caricare script nell'admin per migliorare le performance</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="rem-settings-section">
            <h3>📱 Breakpoint Personalizzati</h3>
            <p>Configura i breakpoint responsive del tuo sito. I breakpoint predefiniti sono:</p>
            
            <div class="rem-default-breakpoints">
                <?php foreach ($default_breakpoints as $name => $query): ?>
                <div class="rem-breakpoint-item">
                    <strong><?php echo ucfirst($name); ?>:</strong> 
                    <code><?php echo $query ?: 'Nessuna media query (predefinito)'; ?></code>
                </div>
                <?php endforeach; ?>
            </div>
            
            <h4>Breakpoint Aggiuntivi</h4>
            <div id="custom-breakpoints">
                <?php if (!empty($custom_breakpoints)): ?>
                    <?php foreach ($custom_breakpoints as $name => $query): ?>
                    <div class="rem-breakpoint-row">
                        <input type="text" name="breakpoints[][name]" value="<?php echo esc_attr($name); ?>" placeholder="Nome breakpoint">
                        <input type="text" name="breakpoints[][query]" value="<?php echo esc_attr($query); ?>" placeholder="Media query">
                        <button type="button" class="button rem-remove-breakpoint">Rimuovi</button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <button type="button" id="add-breakpoint" class="button">Aggiungi Breakpoint</button>
            <p class="description">Esempio: Nome: "large", Query: "(min-width: 1400px)"</p>
        </div>
        
        <div class="rem-settings-section">
            <h3>🎨 Personalizzazione</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Prefisso CSS</th>
                    <td>
                        <input type="text" name="custom_css_prefix" value="<?php echo esc_attr($settings['custom_css_prefix']); ?>" class="regular-text">
                        <p class="description">Prefisso per le classi CSS generate dal plugin</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Animazioni</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_animations" value="1" <?php checked($settings['enable_animations']); ?>>
                            Abilita animazioni nell'interfaccia
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="rem-settings-section">
            <h3>🔧 Sviluppo</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Modalità Debug</th>
                    <td>
                        <label>
                            <input type="checkbox" name="debug_mode" value="1" <?php checked($settings['debug_mode']); ?>>
                            Abilita modalità debug
                        </label>
                        <p class="description">Mostra informazioni aggiuntive nella console del browser</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="rem-settings-section">
            <h3>📊 Statistiche Sistema</h3>
            <div class="rem-system-stats">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'rem_rules';
                
                $total_rules = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $total_pages = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_name WHERE scope = 'page' AND post_id > 0");
                $db_size = $wpdb->get_var("SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema='{$wpdb->dbname}' AND table_name='$table_name'");
                ?>
                
                <div class="rem-stat-grid">
                    <div class="rem-stat-box">
                        <div class="stat-number"><?php echo $total_rules; ?></div>
                        <div class="stat-label">Regole Totali</div>
                    </div>
                    <div class="rem-stat-box">
                        <div class="stat-number"><?php echo $total_pages; ?></div>
                        <div class="stat-label">Pagine Configurate</div>
                    </div>
                    <div class="rem-stat-box">
                        <div class="stat-number"><?php echo $db_size ?: 'N/A'; ?></div>
                        <div class="stat-label">Dimensione DB (MB)</div>
                    </div>
                    <div class="rem-stat-box">
                        <div class="stat-number"><?php echo REM_VERSION; ?></div>
                        <div class="stat-label">Versione Plugin</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="rem-settings-section">
            <h3>🔄 Reset e Manutenzione</h3>
            <div class="rem-maintenance-actions">
                <button type="button" class="button button-secondary" onclick="remClearCache()">
                    Pulisci Cache CSS
                </button>
                <button type="button" class="button button-secondary" onclick="remOptimizeDb()">
                    Ottimizza Database
                </button>
                <button type="button" class="button button-link-delete" onclick="remResetSettings()">
                    Ripristina Impostazioni Predefinite
                </button>
            </div>
            <p class="description">Utilizza questi strumenti per la manutenzione del plugin</p>
        </div>
        
        <p class="submit">
            <input type="submit" name="save_settings" class="button-primary" value="Salva Impostazioni">
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestione breakpoint personalizzati
    document.getElementById('add-breakpoint').addEventListener('click', function() {
        const container = document.getElementById('custom-breakpoints');
        const row = document.createElement('div');
        row.className = 'rem-breakpoint-row';
        row.innerHTML = `
            <input type="text" name="breakpoints[][name]" placeholder="Nome breakpoint">
            <input type="text" name="breakpoints[][query]" placeholder="Media query">
            <button type="button" class="button rem-remove-breakpoint">Rimuovi</button>
        `;
        container.appendChild(row);
    });
    
    // Rimozione breakpoint
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('rem-remove-breakpoint')) {
            e.target.parentElement.remove();
        }
    });
});

// Funzioni di manutenzione
function remClearCache() {
    if (confirm('Sei sicuro di voler pulire la cache CSS?')) {
        jQuery.post(ajaxurl, {
            action: 'rem_clear_cache',
            nonce: '<?php echo wp_create_nonce('rem_maintenance'); ?>'
        }, function(response) {
            alert(response.success ? 'Cache pulita con successo!' : 'Errore nella pulizia della cache');
        });
    }
}

function remOptimizeDb() {
    if (confirm('Sei sicuro di voler ottimizzare il database?')) {
        jQuery.post(ajaxurl, {
            action: 'rem_optimize_db',
            nonce: '<?php echo wp_create_nonce('rem_maintenance'); ?>'
        }, function(response) {
            alert(response.success ? 'Database ottimizzato con successo!' : 'Errore nell\'ottimizzazione');
        });
    }
}

function remResetSettings() {
    if (confirm('Sei sicuro di voler ripristinare tutte le impostazioni ai valori predefiniti? Questa azione non può essere annullata.')) {
        jQuery.post(ajaxurl, {
            action: 'rem_reset_settings',
            nonce: '<?php echo wp_create_nonce('rem_maintenance'); ?>'
        }, function(response) {
            if (response.success) {
                alert('Impostazioni ripristinate con successo!');
                location.reload();
            } else {
                alert('Errore nel ripristino delle impostazioni');
            }
        });
    }
}
</script>

<style>
.rem-settings-section {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.rem-settings-section h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.rem-breakpoint-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    align-items: center;
}

.rem-breakpoint-row input {
    flex: 1;
}

.rem-default-breakpoints {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.rem-breakpoint-item {
    margin-bottom: 8px;
}

.rem-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.rem-stat-box {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.rem-maintenance-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .rem-breakpoint-row {
        flex-direction: column;
    }
    
    .rem-maintenance-actions {
        flex-direction: column;
    }
    
    .rem-stat-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>