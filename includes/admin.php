<?php
/**
 * Admin Dashboard Page - VERSIONE COMPLETA
 * File: includes/admin-page.php
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Ottieni statistiche
global $wpdb;
$table_name = $wpdb->prefix . 'rem_rules';

$stats = array();
$stats['total_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$stats['active_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_active = 1");
$stats['page_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE scope = 'page'");
$stats['site_rules'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE scope = 'site'");
$stats['pages_affected'] = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_name WHERE scope = 'page' AND post_id > 0");

// Ottieni regole recenti
$recent_rules = $wpdb->get_results("
    SELECT r.*, p.post_title 
    FROM $table_name r 
    LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID 
    ORDER BY r.updated_at DESC 
    LIMIT 10
");

// Analisi utilizzo breakpoint
$rules_data = $wpdb->get_col("SELECT rules FROM $table_name WHERE is_active = 1");
$breakpoint_usage = array('mobile' => 0, 'tablet' => 0, 'desktop' => 0);

foreach ($rules_data as $rule_json) {
    $rule = json_decode($rule_json, true);
    if (is_array($rule)) {
        foreach (array_keys($rule) as $breakpoint) {
            if (isset($breakpoint_usage[$breakpoint])) {
                $breakpoint_usage[$breakpoint]++;
            }
        }
    }
}

$settings = get_option('rem_settings', array());
?>

<div class="wrap rem-admin-wrap">
    <div class="rem-admin-header">
        <h1>🎨 Responsive Element Manager</h1>
        <p>Gestisci il comportamento responsive del tuo sito con controlli avanzati</p>
        <div class="rem-version-info">
            <span>Versione <?php echo REM_VERSION; ?></span>
            <span>•</span>
            <span>PHP <?php echo PHP_VERSION; ?></span>
            <span>•</span>
            <span>WordPress <?php echo get_bloginfo('version'); ?></span>
        </div>
    </div>

    <div class="rem-admin-content">
        
        <!-- Statistiche Rapide -->
        <div class="rem-stats-grid">
            <div class="rem-stat-card">
                <div class="rem-stat-number"><?php echo number_format($stats['total_rules']); ?></div>
                <div class="rem-stat-label">Regole Totali</div>
                <?php if ($stats['total_rules'] > 0): ?>
                    <div class="rem-stat-change positive">
                        +<?php echo number_format(($stats['active_rules'] / $stats['total_rules']) * 100, 1); ?>% attive
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="rem-stat-card">
                <div class="rem-stat-number"><?php echo number_format($stats['pages_affected']); ?></div>
                <div class="rem-stat-label">Pagine Interessate</div>
                <div class="rem-stat-description">Pagine con regole personalizzate</div>
            </div>
            
            <div class="rem-stat-card">
                <div class="rem-stat-number"><?php echo number_format($stats['site_rules']); ?></div>
                <div class="rem-stat-label">Regole Globali</div>
                <div class="rem-stat-description">Applicate a tutto il sito</div>
            </div>
            
            <div class="rem-stat-card">
                <div class="rem-stat-number"><?php echo number_format($breakpoint_usage['mobile']); ?></div>
                <div class="rem-stat-label">Regole Mobile</div>
                <div class="rem-stat-icon">📱</div>
            </div>
        </div>

        <!-- Azioni Rapide -->
        <div class="rem-card">
            <div class="rem-card-header">
                <h2 class="rem-card-title">🚀 Azioni Rapide</h2>
            </div>
            <div class="rem-quick-actions">
                <div class="rem-action-grid">
                    <a href="<?php echo home_url(); ?>?rem_editor=1" class="rem-action-card" target="_blank">
                        <div class="rem-action-icon">🎨</div>
                        <div class="rem-action-title">Apri Editor Frontend</div>
                        <div class="rem-action-description">Modifica elementi in tempo reale</div>
                    </a>
                    
                    <button class="rem-action-card" onclick="remAdminDashboard.testConnection()">
                        <div class="rem-action-icon">🔧</div>
                        <div class="rem-action-title">Test Connessione</div>
                        <div class="rem-action-description">Verifica funzionamento AJAX</div>
                    </button>
                    
                    <button class="rem-action-card" onclick="remAdminDashboard.clearCache()">
                        <div class="rem-action-icon">🧹</div>
                        <div class="rem-action-title">Pulisci Cache CSS</div>
                        <div class="rem-action-description">Rigenera stili responsive</div>
                    </button>
                    
                    <a href="<?php echo admin_url('admin.php?page=responsive-elements-settings'); ?>" class="rem-action-card">
                        <div class="rem-action-icon">⚙️</div>
                        <div class="rem-action-title">Impostazioni</div>
                        <div class="rem-action-description">Configura comportamento plugin</div>
                    </a>
                </div>
            </div>
        </div>

        <div class="rem-dashboard-grid">
            
            <!-- Regole Recenti -->
            <div class="rem-card">
                <div class="rem-card-header">
                    <h2 class="rem-card-title">📋 Regole Recenti</h2>
                    <div class="rem-card-actions">
                        <a href="<?php echo admin_url('admin.php?page=responsive-elements-rules'); ?>" class="button">
                            Vedi Tutte
                        </a>
                    </div>
                </div>
                <div class="rem-recent-rules">
                    <?php if (!empty($recent_rules)): ?>
                        <div class="rem-rules-list">
                            <?php foreach ($recent_rules as $rule): ?>
                                <div class="rem-rule-item">
                                    <div class="rem-rule-selector">
                                        <code><?php echo esc_html($rule->element_selector); ?></code>
                                    </div>
                                    <div class="rem-rule-meta">
                                        <span class="rem-rule-scope rem-scope-<?php echo $rule->scope; ?>">
                                            <?php echo $rule->scope === 'site' ? '🌐 Globale' : '📄 Pagina'; ?>
                                        </span>
                                        <?php if ($rule->post_title): ?>
                                            <span class="rem-rule-page"><?php echo esc_html($rule->post_title); ?></span>
                                        <?php endif; ?>
                                        <span class="rem-rule-date">
                                            <?php echo human_time_diff(strtotime($rule->updated_at)) . ' fa'; ?>
                                        </span>
                                    </div>
                                    <div class="rem-rule-breakpoints">
                                        <?php 
                                        $rule_data = json_decode($rule->rules, true);
                                        if (is_array($rule_data)) {
                                            foreach (['mobile', 'tablet', 'desktop'] as $bp) {
                                                if (isset($rule_data[$bp])) {
                                                    $icon = $bp === 'mobile' ? '📱' : ($bp === 'tablet' ? '📟' : '🖥️');
                                                    echo "<span class='rem-bp-indicator rem-bp-$bp' title='$bp'>$icon</span>";
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="rem-empty-state">
                            <div class="rem-empty-icon">📱</div>
                            <div class="rem-empty-title">Nessuna regola ancora</div>
                            <div class="rem-empty-description">
                                Inizia a personalizzare i tuoi elementi responsive!
                            </div>
                            <a href="<?php echo home_url(); ?>?rem_editor=1" class="button button-primary" target="_blank">
                                Apri Editor Frontend
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Utilizzo Breakpoint -->
            <div class="rem-card">
                <div class="rem-card-header">
                    <h2 class="rem-card-title">📊 Utilizzo Breakpoint</h2>
                </div>
                <div class="rem-breakpoint-stats">
                    <div class="rem-chart-container">
                        <canvas id="breakpoint-usage-chart" width="300" height="200"></canvas>
                    </div>
                    <div class="rem-breakpoint-legend">
                        <div class="rem-legend-item">
                            <span class="rem-legend-color" style="background: #ff6384;"></span>
                            <span class="rem-legend-label">📱 Mobile: <?php echo $breakpoint_usage['mobile']; ?></span>
                        </div>
                        <div class="rem-legend-item">
                            <span class="rem-legend-color" style="background: #36a2eb;"></span>
                            <span class="rem-legend-label">📟 Tablet: <?php echo $breakpoint_usage['tablet']; ?></span>
                        </div>
                        <div class="rem-legend-item">
                            <span class="rem-legend-color" style="background: #ffce56;"></span>
                            <span class="rem-legend-label">🖥️ Desktop: <?php echo $breakpoint_usage['desktop']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Sistema -->
            <div class="rem-card">
                <div class="rem-card-header">
                    <h2 class="rem-card-title">⚡ Status Sistema</h2>
                </div>
                <div class="rem-system-status">
                    <div class="rem-status-item">
                        <div class="rem-status-label">Database</div>
                        <div class="rem-status-value rem-status-ok">✅ Connesso</div>
                    </div>
                    <div class="rem-status-item">
                        <div class="rem-status-label">Tabelle Plugin</div>
                        <?php 
                        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                        ?>
                        <div class="rem-status-value <?php echo $table_exists ? 'rem-status-ok' : 'rem-status-error'; ?>">
                            <?php echo $table_exists ? '✅ Presenti' : '❌ Mancanti'; ?>
                        </div>
                    </div>
                    <div class="rem-status-item">
                        <div class="rem-status-label">Cache CSS</div>
                        <?php 
                        $cache_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_rem_css_cache_%'");
                        ?>
                        <div class="rem-status-value rem-status-info">
                            📄 <?php echo $cache_count; ?> file cached
                        </div>
                    </div>
                    <div class="rem-status-item">
                        <div class="rem-status-label">Memoria PHP</div>
                        <div class="rem-status-value rem-status-info">
                            💾 <?php echo size_format(memory_get_usage()); ?>
                        </div>
                    </div>
                    <div class="rem-status-item">
                        <div class="rem-status-label">Frontend Editor</div>
                        <div class="rem-status-value <?php echo $settings['enable_frontend_editor'] ? 'rem-status-ok' : 'rem-status-warning'; ?>">
                            <?php echo $settings['enable_frontend_editor'] ? '✅ Abilitato' : '⚠️ Disabilitato'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Strumenti Manutenzione -->
            <div class="rem-card">
                <div class="rem-card-header">
                    <h2 class="rem-card-title">🔧 Strumenti Manutenzione</h2>
                </div>
                <div class="rem-maintenance-tools">
                    <div class="rem-tool-section">
                        <h4>Backup e Ripristino</h4>
                        <div class="rem-tool-actions">
                            <button class="button" onclick="remAdminDashboard.exportRules()">
                                📥 Esporta Regole
                            </button>
                            <button class="button" onclick="remAdminDashboard.showImportDialog()">
                                📤 Importa Regole
                            </button>
                        </div>
                    </div>
                    
                    <div class="rem-tool-section">
                        <h4>Ottimizzazione</h4>
                        <div class="rem-tool-actions">
                            <button class="button" onclick="remAdminDashboard.optimizeDatabase()">
                                🗃️ Ottimizza Database
                            </button>
                            <button class="button" onclick="remAdminDashboard.regenerateCSS()">
                                🎨 Rigenera CSS
                            </button>
                        </div>
                    </div>
                    
                    <div class="rem-tool-section">
                        <h4>Debug</h4>
                        <div class="rem-tool-actions">
                            <button class="button" onclick="remAdminDashboard.downloadDebugInfo()">
                                🐛 Scarica Info Debug
                            </button>
                            <button class="button button-link-delete" onclick="remAdminDashboard.resetPlugin()">
                                🔄 Reset Plugin
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suggerimenti -->
        <div class="rem-card rem-tips-card">
            <div class="rem-card-header">
                <h2 class="rem-card-title">💡 Suggerimenti Pro</h2>
            </div>
            <div class="rem-tips-grid">
                <div class="rem-tip">
                    <div class="rem-tip-icon">🎯</div>
                    <div class="rem-tip-content">
                        <h4>Selezione Precisa</h4>
                        <p>Usa ID CSS specifici per selezionare gli elementi con maggiore precisione</p>
                    </div>
                </div>
                <div class="rem-tip">
                    <div class="rem-tip-icon">📱</div>
                    <div class="rem-tip-content">
                        <h4>Mobile First</h4>
                        <p>Inizia sempre dal mobile, poi espandi verso tablet e desktop</p>
                    </div>
                </div>
                <div class="rem-tip">
                    <div class="rem-tip-icon">🔄</div>
                    <div class="rem-tip-content">
                        <h4>Proporzioni Auto</h4>
                        <p>Usa i pulsanti 🔄 per calcolare automaticamente dimensioni proporzionali</p>
                    </div>
                </div>
                <div class="rem-tip">
                    <div class="rem-tip-icon">⚡</div>
                    <div class="rem-tip-content">
                        <h4>Performance</h4>
                        <p>Pulisci regolarmente la cache CSS per mantenere le prestazioni ottimali</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div id="rem-import-modal" class="rem-modal-overlay" style="display: none;">
    <div class="rem-modal">
        <div class="rem-modal-header">
            <h3>📤 Importa Regole</h3>
            <button class="rem-modal-close" onclick="remAdminDashboard.closeImportDialog()">&times;</button>
        </div>
        <div class="rem-modal-body">
            <div class="rem-form-group">
                <label for="rem-import-file">Seleziona File JSON:</label>
                <input type="file" id="rem-import-file" accept=".json">
            </div>
            <div class="rem-form-group">
                <label>
                    <input type="checkbox" id="rem-import-overwrite" checked>
                    Sovrascrivi regole esistenti
                </label>
            </div>
        </div>
        <div class="rem-modal-footer">
            <button class="button button-primary" onclick="remAdminDashboard.processImport()">
                📤 Importa
            </button>
            <button class="button" onclick="remAdminDashboard.closeImportDialog()">
                Annulla
            </button>
        </div>
    </div>
</div>

<script>
// Dashboard JavaScript
const remAdminDashboard = {
    
    testConnection: function() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '🔄 Testing...';
        button.disabled = true;
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'rem_test_connection',
                nonce: '<?php echo wp_create_nonce('rem_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('✅ Connessione riuscita!', 'success');
                console.log('Connection test result:', data.data);
            } else {
                this.showNotification('❌ Test fallito: ' + data.data, 'error');
            }
        })
        .catch(error => {
            this.showNotification('❌ Errore di connessione: ' + error.message, 'error');
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
    },
    
    clearCache: function() {
        if (!confirm('Pulire la cache CSS? Questo potrebbe rallentare temporaneamente il caricamento.')) {
            return;
        }
        
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '🧹 Pulendo...';
        button.disabled = true;
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'rem_clear_cache',
                nonce: '<?php echo wp_create_nonce('rem_admin_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('✅ Cache CSS pulita!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification('❌ Errore: ' + data.data, 'error');
            }
        })
        .catch(error => {
            this.showNotification('❌ Errore: ' + error.message, 'error');
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
    },
    
    exportRules: function() {
        const format = 'json';
        const scope = 'all';
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'rem_export_rules',
                format: format,
                scope: scope,
                nonce: '<?php echo wp_create_nonce('rem_admin_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const blob = new Blob([data.data.content], {type: data.data.mime_type});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = data.data.filename;
                document.body.appendChild(a);
                a.click();
                URL.revokeObjectURL(url);
                document.body.removeChild(a);
                this.showNotification('✅ Esportazione completata!', 'success');
            } else {
                this.showNotification('❌ Errore: ' + data.data, 'error');
            }
        })
        .catch(error => {
            this.showNotification('❌ Errore: ' + error.message, 'error');
        });
    },
    
    showImportDialog: function() {
        document.getElementById('rem-import-modal').style.display = 'flex';
    },
    
    closeImportDialog: function() {
        document.getElementById('rem-import-modal').style.display = 'none';
    },
    
    processImport: function() {
        const fileInput = document.getElementById('rem-import-file');
        const file = fileInput.files[0];
        
        if (!file) {
            this.showNotification('⚠️ Seleziona un file JSON', 'warning');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const data = JSON.parse(e.target.result);
                this.importRules(data);
            } catch (error) {
                this.showNotification('❌ File JSON non valido', 'error');
            }
        };
        reader.readAsText(file);
    },
    
    importRules: function(data) {
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'rem_import_rules',
                import_data: JSON.stringify(data),
                nonce: '<?php echo wp_create_nonce('rem_admin_nonce'); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('✅ ' + data.data.message, 'success');
                this.closeImportDialog();
                setTimeout(() => location.reload(), 1500);
            } else {
                this.showNotification('❌ Errore: ' + data.data, 'error');
            }
        })
        .catch(error => {
            this.showNotification('❌ Errore: ' + error.message, 'error');
        });
    },
    
    optimizeDatabase: function() {
        // Implementa ottimizzazione database
        this.showNotification('🔄 Funzionalità in sviluppo', 'info');
    },
    
    regenerateCSS: function() {
        // Implementa rigenerazione CSS
        this.showNotification('🔄 Funzionalità in sviluppo', 'info');
    },
    
    downloadDebugInfo: function() {
        // Implementa download info debug
        this.showNotification('🔄 Funzionalità in sviluppo', 'info');
    },
    
    resetPlugin: function() {
        if (!confirm('⚠️ ATTENZIONE: Questa azione eliminerà TUTTE le regole responsive! Procedere?')) {
            return;
        }
        
        if (!confirm('🚨 CONFERMA FINALE: Sei sicuro di voler resettare completamente il plugin?')) {
            return;
        }
        
        this.showNotification('🔄 Reset in sviluppo - contatta supporto', 'warning');
    },
    
    showNotification: function(message, type) {
        const notification = document.createElement('div');
        notification.className = `notice notice-${type} is-dismissible rem-notification`;
        notification.innerHTML = `<p>${message}</p>`;
        
        const container = document.querySelector('.rem-admin-header');
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
};

// Inizializza grafico breakpoint
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('breakpoint-usage-chart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['📱 Mobile', '📟 Tablet', '🖥️ Desktop'],
                datasets: [{
                    data: [<?php echo $breakpoint_usage['mobile']; ?>, <?php echo $breakpoint_usage['tablet']; ?>, <?php echo $breakpoint_usage['desktop']; ?>],
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>

<style>
/* Stili specifici per la dashboard */
.rem-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.rem-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.rem-action-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    cursor: pointer;
}

.rem-action-card:hover {
    border-color: #0073aa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,115,170,0.15);
    color: inherit;
    text-decoration: none;
}

.rem-action-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.rem-action-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.rem-action-description {
    font-size: 13px;
    color: #666;
}

.rem-recent-rules {
    max-height: 400px;
    overflow-y: auto;
}

.rem-rule-item {
    padding: 12px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.rem-rule-item:last-child {
    border-bottom: none;
}

.rem-rule-selector code {
    background: #f1f3f4;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #d73502;
}

.rem-rule-meta {
    display: flex;
    gap: 10px;
    align-items: center;
    font-size: 12px;
    color: #666;
    flex: 1;
}

.rem-rule-scope {
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 10px;
}

.rem-scope-page {
    background: #e3f2fd;
    color: #1976d2;
}

.rem-scope-site {
    background: #f3e5f5;
    color: #7b1fa2;
}

.rem-rule-breakpoints {
    display: flex;
    gap: 4px;
}

.rem-bp-indicator {
    opacity: 0.7;
    font-size: 14px;
}

.rem-chart-container {
    height: 200px;
    margin-bottom: 15px;
}

.rem-breakpoint-legend {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rem-legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

.rem-legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
}

.rem-system-status {
    display: grid;
    gap: 10px;
}

.rem-status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.rem-status-label {
    font-weight: 500;
    color: #333;
}

.rem-status-value {
    font-size: 13px;
    font-weight: 600;
}

.rem-status-ok { color: #28a745; }
.rem-status-warning { color: #ffc107; }
.rem-status-error { color: #dc3545; }
.rem-status-info { color: #17a2b8; }

.rem-maintenance-tools {
    display: grid;
    gap: 20px;
}

.rem-tool-section h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 14px;
}

.rem-tool-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.rem-tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.rem-tip {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.rem-tip-icon {
    font-size: 24px;
    flex-shrink: 0;
}

.rem-tip-content h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 14px;
}

.rem-tip-content p {
    margin: 0;
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.rem-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.rem-empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.rem-empty-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.rem-empty-description {
    margin-bottom: 20px;
    font-size: 14px;
}

.rem-version-info {
    font-size: 12px;
    opacity: 0.8;
    margin-top: 5px;
}

.rem-notification {
    margin: 10px 0;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal styles */
.rem-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rem-modal {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.rem-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.rem-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.rem-modal-body {
    padding: 20px;
}

.rem-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .rem-dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .rem-action-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .rem-tips-grid {
        grid-template-columns: 1fr;
    }
    
    .rem-rule-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>