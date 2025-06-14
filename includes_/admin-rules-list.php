<?php
/**
 * Template per la lista delle regole nell'admin
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ottieni tutte le regole
$all_rules = REM_Rule_Manager::get_rules(0, 'all', false); // Include anche quelle inattive
$statistics = REM_Rule_Manager::get_statistics();

// Paginazione
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$total_rules = count($all_rules);
$total_pages = ceil($total_rules / $per_page);
$offset = ($current_page - 1) * $per_page;
$rules_for_page = array_slice($all_rules, $offset, $per_page);

// Filtri applicati
$current_filters = array(
    'scope' => isset($_GET['filter_scope']) ? sanitize_text_field($_GET['filter_scope']) : '',
    'status' => isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '',
    'search' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : ''
);
?>

<div class="rem-rules-page">
    <!-- Statistiche rapide -->
    <div class="rem-stats-cards">
        <div class="rem-stat-card">
            <div class="rem-stat-number"><?php echo $statistics['total_rules']; ?></div>
            <div class="rem-stat-label">Regole Totali</div>
            <?php if ($statistics['total_rules'] > 0): ?>
                <div class="rem-stat-change positive">
                    <?php echo round(($statistics['active_rules'] / $statistics['total_rules']) * 100); ?>% Attive
                </div>
            <?php endif; ?>
        </div>
        
        <div class="rem-stat-card">
            <div class="rem-stat-number"><?php echo $statistics['site_rules']; ?></div>
            <div class="rem-stat-label">Regole Globali</div>
        </div>
        
        <div class="rem-stat-card">
            <div class="rem-stat-number"><?php echo $statistics['page_rules']; ?></div>
            <div class="rem-stat-label">Regole Specifiche</div>
        </div>
        
        <div class="rem-stat-card">
            <div class="rem-stat-number"><?php echo $statistics['pages_affected']; ?></div>
            <div class="rem-stat-label">Pagine Interessate</div>
        </div>
    </div>
    
    <!-- Azioni rapide -->
    <div class="rem-quick-actions">
        <h3>🚀 Azioni Rapide</h3>
        <div class="rem-action-buttons">
            <a href="<?php echo admin_url('admin.php?page=responsive-elements&action=export'); ?>" class="button button-secondary">
                <span class="dashicons dashicons-download"></span> Esporta Regole
            </a>
            <button type="button" class="button button-secondary" onclick="remShowImportDialog()">
                <span class="dashicons dashicons-upload"></span> Importa Regole
            </button>
            <button type="button" class="button button-secondary" onclick="remCreateBackup()">
                <span class="dashicons dashicons-backup"></span> Crea Backup
            </button>
            <button type="button" class="button button-secondary" onclick="remOptimizeDatabase()">
                <span class="dashicons dashicons-performance"></span> Ottimizza DB
            </button>
        </div>
    </div>
    
    <!-- Filtri e ricerca -->
    <div class="rem-filters">
        <form method="get" class="rem-filter-form">
            <input type="hidden" name="page" value="responsive-elements">
            <input type="hidden" name="action" value="list">
            
            <div class="rem-search-box">
                <span class="rem-search-icon dashicons dashicons-search"></span>
                <input type="text" name="s" value="<?php echo esc_attr($current_filters['search']); ?>" 
                       placeholder="Cerca selettori, ID, classi..." class="rem-search-input">
            </div>
            
            <div class="rem-filter-group">
                <label class="rem-filter-label">Scope:</label>
                <select name="filter_scope" class="rem-filter">
                    <option value="">Tutti</option>
                    <option value="site" <?php selected($current_filters['scope'], 'site'); ?>>Globali</option>
                    <option value="page" <?php selected($current_filters['scope'], 'page'); ?>>Specifiche</option>
                </select>
            </div>
            
            <div class="rem-filter-group">
                <label class="rem-filter-label">Stato:</label>
                <select name="filter_status" class="rem-filter">
                    <option value="">Tutti</option>
                    <option value="1" <?php selected($current_filters['status'], '1'); ?>>Attive</option>
                    <option value="0" <?php selected($current_filters['status'], '0'); ?>>Inattive</option>
                </select>
            </div>
            
            <button type="submit" class="button button-secondary">Filtra</button>
            
            <?php if (array_filter($current_filters)): ?>
                <a href="<?php echo admin_url('admin.php?page=responsive-elements'); ?>" class="button">
                    Rimuovi Filtri
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Azioni bulk -->
    <?php if (!empty($rules_for_page)): ?>
    <div class="rem-bulk-actions">
        <select id="rem-bulk-action-selector" class="rem-bulk-selector">
            <option value="">Azioni in massa</option>
            <option value="activate">Attiva selezionate</option>
            <option value="deactivate">Disattiva selezionate</option>
            <option value="delete">Elimina selezionate</option>
            <option value="export">Esporta selezionate</option>
        </select>
        <button id="rem-bulk-apply" class="rem-bulk-apply" disabled>Applica</button>
        <span class="rem-bulk-info">
            <span id="rem-selected-count">0</span> regole selezionate
        </span>
    </div>
    <?php endif; ?>
    
    <!-- Tabella delle regole -->
    <div class="rem-table-wrapper">
        <?php if (empty($rules_for_page)): ?>
            <div class="rem-empty-state">
                <div class="rem-empty-icon">📱</div>
                <h3>Nessuna regola trovata</h3>
                <p>Non ci sono regole responsive configurate o nessuna regola corrisponde ai tuoi filtri.</p>
                <a href="<?php echo home_url(); ?>" class="button button-primary" target="_blank">
                    Inizia Configurando una Pagina
                </a>
            </div>
        <?php else: ?>
            <table class="rem-rules-table wp-list-table widefat fixed striped" id="rem-rules-table">
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" id="rem-select-all">
                        </th>
                        <th class="rem-col-selector">Elemento</th>
                        <th class="rem-col-page">Pagina</th>
                        <th class="rem-col-scope">Scope</th>
                        <th class="rem-col-breakpoints">Breakpoints</th>
                        <th class="rem-col-status">Stato</th>
                        <th class="rem-col-priority">Priorità</th>
                        <th class="rem-col-date">Ultimo Aggiornamento</th>
                        <th class="rem-col-actions">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules_for_page as $rule): ?>
                    <tr class="rem-rule-row <?php echo $rule->is_active ? 'active' : 'inactive'; ?>" 
                        data-rule-id="<?php echo $rule->id; ?>">
                        <td class="check-column">
                            <input type="checkbox" class="rem-rule-checkbox" value="<?php echo $rule->id; ?>">
                        </td>
                        
                        <td class="rem-col-selector">
                            <div class="rem-selector-info">
                                <code class="rem-selector-code"><?php echo esc_html($rule->element_selector); ?></code>
                                <button type="button" class="rem-copy-selector" data-text="<?php echo esc_attr($rule->element_selector); ?>" 
                                        title="Copia selettore">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                            </div>
                            
                            <?php if ($rule->element_id): ?>
                                <div class="rem-element-detail">
                                    <strong>ID:</strong> <code><?php echo esc_html($rule->element_id); ?></code>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($rule->element_class): ?>
                                <div class="rem-element-detail">
                                    <strong>Class:</strong> <code><?php echo esc_html($rule->element_class); ?></code>
                                </div>
                            <?php endif; ?>
                        </td>
                        
                        <td class="rem-col-page">
                            <?php if ($rule->scope === 'page' && $rule->post_id): ?>
                                <?php if (isset($rule->post_title)): ?>
                                    <a href="<?php echo get_permalink($rule->post_id); ?>" target="_blank" 
                                       title="Visualizza pagina">
                                        <?php echo esc_html($rule->post_title); ?>
                                        <span class="dashicons dashicons-external"></span>
                                    </a>
                                    <div class="rem-page-id">ID: <?php echo $rule->post_id; ?></div>
                                <?php else: ?>
                                    <em class="rem-deleted-page">Pagina eliminata (ID: <?php echo $rule->post_id; ?>)</em>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="rem-global-indicator">— Globale —</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="rem-col-scope">
                            <span class="rem-scope-badge rem-scope-<?php echo $rule->scope; ?>">
                                <?php echo $rule->scope === 'site' ? 'Globale' : 'Pagina'; ?>
                            </span>
                        </td>
                        
                        <td class="rem-col-breakpoints">
                            <div class="rem-breakpoint-indicators">
                                <?php 
                                $breakpoint_icons = array(
                                    'mobile' => '📱',
                                    'tablet' => '📟', 
                                    'desktop' => '🖥️'
                                );
                                
                                foreach ($rule->breakpoints as $breakpoint): 
                                    $icon = $breakpoint_icons[$breakpoint] ?? '📐';
                                    echo "<span class='rem-breakpoint-icon' title='$breakpoint'>$icon</span> ";
                                endforeach; 
                                ?>
                            </div>
                            <div class="rem-breakpoint-count">
                                <?php echo count($rule->breakpoints); ?> configurati
                            </div>
                        </td>
                        
                        <td class="rem-col-status">
                            <div class="rem-status-toggle">
                                <label class="rem-toggle">
                                    <input type="checkbox" class="rem-status-checkbox" 
                                           data-rule-id="<?php echo $rule->id; ?>"
                                           <?php checked($rule->is_active); ?>>
                                    <span class="rem-toggle-slider"></span>
                                </label>
                                <span class="rem-status-text">
                                    <?php echo $rule->is_active ? 'Attiva' : 'Inattiva'; ?>
                                </span>
                            </div>
                        </td>
                        
                        <td class="rem-col-priority">
                            <div class="rem-priority-control">
                                <input type="number" class="rem-priority-input" 
                                       value="<?php echo $rule->priority; ?>" 
                                       min="1" max="100" 
                                       data-rule-id="<?php echo $rule->id; ?>"
                                       title="Priorità (1-100)">
                            </div>
                        </td>
                        
                        <td class="rem-col-date">
                            <div class="rem-date-info">
                                <div class="rem-date-main">
                                    <?php echo date('d/m/Y', strtotime($rule->updated_at)); ?>
                                </div>
                                <div class="rem-date-time">
                                    <?php echo date('H:i', strtotime($rule->updated_at)); ?>
                                </div>
                            </div>
                        </td>
                        
                        <td class="rem-col-actions">
                            <div class="rem-actions">
                                <?php if ($rule->scope === 'page' && $rule->post_id): ?>
                                    <button class="button button-small rem-preview-rule" 
                                            data-rule-id="<?php echo $rule->id; ?>" 
                                            data-post-id="<?php echo $rule->post_id; ?>"
                                            title="Anteprima">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                <?php endif; ?>
                                
                                <button class="button button-small rem-edit-rule" 
                                        data-rule-id="<?php echo $rule->id; ?>"
                                        title="Modifica">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                
                                <button class="button button-small rem-duplicate-rule" 
                                        data-rule-id="<?php echo $rule->id; ?>"
                                        title="Duplica">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                                
                                <button class="button button-small button-link-delete rem-delete-rule" 
                                        data-rule-id="<?php echo $rule->id; ?>"
                                        data-rule-name="<?php echo esc_attr($rule->element_selector); ?>"
                                        title="Elimina">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Paginazione -->
            <?php if ($total_pages > 1): ?>
            <div class="rem-pagination">
                <div class="tablenav-pages">
                    <span class="displaying-num">
                        <?php printf('%d elementi', $total_rules); ?>
                    </span>
                    
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'current' => $current_page,
                        'total' => $total_pages
                    ));
                    ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal per importazione -->
<div id="rem-import-dialog" class="rem-modal-overlay" style="display:none;">
    <div class="rem-modal">
        <div class="rem-modal-header">
            <h3 class="rem-modal-title">Importa Regole</h3>
            <button class="rem-modal-close">&times;</button>
        </div>
        <div class="rem-modal-body">
            <form id="rem-import-form" enctype="multipart/form-data">
                <div class="rem-form-group">
                    <label for="rem-import-file">Seleziona file JSON:</label>
                    <input type="file" id="rem-import-file" name="import_file" accept=".json" required>
                    <p class="rem-form-description">
                        Supporta file JSON esportati da questo plugin o da altri siti WordPress con REM installato.
                    </p>
                </div>
                
                <div class="rem-form-group">
                    <label>
                        <input type="checkbox" id="rem-import-overwrite" name="overwrite" value="1">
                        Sovrascrivi regole esistenti
                    </label>
                    <p class="rem-form-description">
                        Se attivato, le regole esistenti con lo stesso selettore verranno sostituite.
                    </p>
                </div>
            </form>
        </div>
        <div class="rem-modal-footer">
            <button id="rem-import-execute" class="button button-primary">Importa</button>
            <button class="button rem-modal-close">Annulla</button>
        </div>
    </div>
</div>

<!-- Modal per modifica regola -->
<div id="rem-edit-dialog" class="rem-modal-overlay" style="display:none;">
    <div class="rem-modal rem-edit-modal">
        <div class="rem-modal-header">
            <h3 class="rem-modal-title">Modifica Regola</h3>
            <button class="rem-modal-close">&times;</button>
        </div>
        <div class="rem-modal-body">
            <div id="rem-edit-content">
                <!-- Contenuto caricato dinamicamente -->
            </div>
        </div>
        <div class="rem-modal-footer">
            <button id="rem-edit-save" class="button button-primary">Salva Modifiche</button>
            <button class="button rem-modal-close">Annulla</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    // Gestione selezione multipla
    $('#rem-select-all').on('change', function() {
        $('.rem-rule-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });
    
    $('.rem-rule-checkbox').on('change', function() {
        updateSelectedCount();
        
        // Aggiorna stato "seleziona tutto"
        const total = $('.rem-rule-checkbox').length;
        const checked = $('.rem-rule-checkbox:checked').length;
        $('#rem-select-all').prop('indeterminate', checked > 0 && checked < total);
        $('#rem-select-all').prop('checked', checked === total);
    });
    
    function updateSelectedCount() {
        const count = $('.rem-rule-checkbox:checked').length;
        $('#rem-selected-count').text(count);
        $('#rem-bulk-apply').prop('disabled', count === 0);
    }
    
    // Toggle stato regola
    $('.rem-status-checkbox').on('change', function() {
        const ruleId = $(this).data('rule-id');
        const isActive = $(this).is(':checked') ? 1 : 0;
        const $row = $(this).closest('.rem-rule-row');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rem_toggle_rule',
                rule_id: ruleId,
                is_active: isActive,
                nonce: '<?php echo wp_create_nonce('rem_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $row.toggleClass('active', isActive);
                    $row.toggleClass('inactive', !isActive);
                    $('.rem-status-text', $row).text(isActive ? 'Attiva' : 'Inattiva');
                    
                    // Mostra notifica
                    showNotice(response.data.message, 'success');
                } else {
                    // Ripristina stato precedente
                    $(this).prop('checked', !isActive);
                    showNotice('Errore nel cambiare stato: ' + response.data, 'error');
                }
            }.bind(this),
            error: function() {
                $(this).prop('checked', !isActive);
                showNotice('Errore di connessione', 'error');
            }.bind(this)
        });
    });
    
    // Aggiornamento priorità
    $('.rem-priority-input').on('change', function() {
        const ruleId = $(this).data('rule-id');
        const priority = $(this).val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'rem_update_priority',
                rule_id: ruleId,
                priority: priority,
                nonce: '<?php echo wp_create_nonce('rem_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Priorità aggiornata', 'success', 2000);
                } else {
                    showNotice('Errore nell\'aggiornamento: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Errore di connessione', 'error');
            }
        });
    });
    
    // Gestione modal
    $('.rem-modal-close').on('click', function() {
        $(this).closest('.rem-modal-overlay').hide();
    });
    
    // Funzioni globali
    window.remShowImportDialog = function() {
        $('#rem-import-dialog').show();
    };
    
    window.remCreateBackup = function() {
        if (confirm('Creare un backup di tutte le regole?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_create_backup',
                    nonce: '<?php echo wp_create_nonce('rem_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Backup creato con successo', 'success');
                    } else {
                        showNotice('Errore nella creazione del backup: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotice('Errore di connessione', 'error');
                }
            });
        }
    };
    
    window.remOptimizeDatabase = function() {
        if (confirm('Ottimizzare il database? Questa operazione può richiedere alcuni minuti.')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_optimize_database',
                    nonce: '<?php echo wp_create_nonce('rem_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Database ottimizzato con successo', 'success');
                    } else {
                        showNotice('Errore nell\'ottimizzazione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotice('Errore di connessione', 'error');
                }
            });
        }
    };
    
    function showNotice(message, type, timeout) {
        type = type || 'info';
        timeout = timeout || 5000;
        
        const notice = $(`
            <div class="notice notice-${type} is-dismissible rem-admin-notice">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);
        
        $('.rem-rules-page').prepend(notice);
        
        if (timeout > 0) {
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, timeout);
        }
        
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }
});
</script>

<style>
.rem-rules-page {
    max-width: 1400px;
}

.rem-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.rem-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.rem-selector-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rem-selector-code {
    background: #f1f3f4;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    color: #d73502;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.rem-copy-selector {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #666;
    border-radius: 3px;
}

.rem-copy-selector:hover {
    background: #f0f0f1;
    color: #0073aa;
}

.rem-element-detail {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

.rem-deleted-page {
    color: #d63638;
    font-style: italic;
}

.rem-global-indicator {
    color: #7b1fa2;
    font-style: italic;
}

.rem-page-id {
    font-size: 12px;
    color: #666;
}

.rem-breakpoint-indicators {
    display: flex;
    gap: 4px;
    margin-bottom: 4px;
}

.rem-breakpoint-icon {
    font-size: 16px;
    cursor: help;
}

.rem-breakpoint-count {
    font-size: 12px;
    color: #666;
}

.rem-status-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rem-priority-control input {
    width: 60px;
    padding: 4px 6px;
    border: 1px solid #ddd;
    border-radius: 3px;
    text-align: center;
}

.rem-date-info {
    text-align: center;
}

.rem-date-main {
    font-weight: 500;
}

.rem-date-time {
    font-size: 12px;
    color: #666;
}

.rem-rule-row.inactive {
    opacity: 0.6;
}

.rem-rule-row.inactive .rem-selector-code {
    background: #f8f8f8;
    color: #999;
}

@media (max-width: 1200px) {
    .rem-rules-table {
        font-size: 14px;
    }
    
    .rem-col-breakpoints,
    .rem-col-priority {
        display: none;
    }
}

@media (max-width: 768px) {
    .rem-stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .rem-filters {
        flex-direction: column;
        align-items: stretch;
    }
    
    .rem-col-date,
    .rem-col-status {
        display: none;
    }
    
    .rem-selector-code {
        max-width: 120px;
    }
}
</style>