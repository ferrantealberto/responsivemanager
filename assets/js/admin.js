/**
 * Admin JavaScript per Responsive Element Manager
 */
(function($) {
    'use strict';
    
    var REMAdmin = {
        
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },
        
        bindEvents: function() {
            // Conferma eliminazione regole
            $(document).on('click', '.rem-delete-rule', this.confirmDeleteRule);
            
            // Esportazione regole
            $(document).on('click', '#rem-export-rules', this.exportRules);
            
            // Importazione regole
            $(document).on('click', '#rem-import-rules', this.showImportDialog);
            $(document).on('change', '#rem-import-file', this.handleFileImport);
            
            // Gestione tab delle impostazioni
            $(document).on('click', '.rem-settings-tab', this.switchSettingsTab);
            
            // Auto-save delle impostazioni
            $(document).on('change', '.rem-auto-save', this.autoSaveSettings);
            
            // Test di connessione
            $(document).on('click', '#rem-test-connection', this.testConnection);
            
            // Pulizia cache
            $(document).on('click', '#rem-clear-cache', this.clearCache);
            
            // Backup regole
            $(document).on('click', '#rem-backup-rules', this.backupRules);
            
            // Ripristino regole
            $(document).on('click', '#rem-restore-rules', this.restoreRules);
            
            // Bulk actions
            $(document).on('change', '#rem-bulk-action-selector', this.handleBulkActions);
            $(document).on('click', '#rem-bulk-apply', this.applyBulkActions);
            
            // Filtri
            $(document).on('change', '.rem-filter', this.applyFilters);
            
            // Ricerca
            $(document).on('input', '#rem-search-rules', this.debounce(this.searchRules, 300));
            
            // Anteprima regole
            $(document).on('click', '.rem-preview-rule', this.previewRule);
            
            // Copy to clipboard
            $(document).on('click', '.rem-copy-selector', this.copyToClipboard);
        },
        
        initializeComponents: function() {
            // Inizializza DataTable se disponibile
            if ($.fn.DataTable && $('#rem-rules-table').length) {
                this.initDataTable();
            }
            
            // Inizializza tooltips
            this.initTooltips();
            
            // Inizializza grafici se disponibili
            if (typeof Chart !== 'undefined') {
                this.initCharts();
            }
            
            // Carica statistiche
            this.loadStatistics();
        },
        
        confirmDeleteRule: function(e) {
            e.preventDefault();
            
            const ruleId = $(this).data('rule-id');
            const ruleName = $(this).data('rule-name') || 'questa regola';
            
            if (confirm(`Sei sicuro di voler eliminare ${ruleName}? Questa azione non può essere annullata.`)) {
                REMAdmin.deleteRule(ruleId);
            }
        },
        
        deleteRule: function(ruleId) {
            const $button = $(`.rem-delete-rule[data-rule-id="${ruleId}"]`);
            const originalText = $button.text();
            
            $button.text('Eliminazione...').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_delete_rule',
                    rule_id: ruleId,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('tr').fadeOut(400, function() {
                            $(this).remove();
                            REMAdmin.updateStatistics();
                        });
                        REMAdmin.showNotice('Regola eliminata con successo', 'success');
                    } else {
                        REMAdmin.showNotice('Errore nell\'eliminazione: ' + response.data, 'error');
                        $button.text(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    REMAdmin.showNotice('Errore di connessione', 'error');
                    $button.text(originalText).prop('disabled', false);
                }
            });
        },
        
        exportRules: function(e) {
            e.preventDefault();
            
            const format = $('#export-format').val() || 'json';
            const scope = $('#export-scope').val() || 'all';
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_export_rules',
                    format: format,
                    scope: scope,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const blob = new Blob([response.data.content], { 
                            type: response.data.mime_type 
                        });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = response.data.filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        
                        REMAdmin.showNotice('Esportazione completata', 'success');
                    } else {
                        REMAdmin.showNotice('Errore nell\'esportazione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        showImportDialog: function(e) {
            e.preventDefault();
            $('#rem-import-dialog').show();
        },
        
        handleFileImport: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    REMAdmin.importRules(data);
                } catch (error) {
                    REMAdmin.showNotice('File non valido: ' + error.message, 'error');
                }
            };
            reader.readAsText(file);
        },
        
        importRules: function(data) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_import_rules',
                    import_data: JSON.stringify(data),
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.showNotice(`Importate ${response.data.imported} regole`, 'success');
                        location.reload();
                    } else {
                        REMAdmin.showNotice('Errore nell\'importazione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        switchSettingsTab: function(e) {
            e.preventDefault();
            
            const targetTab = $(this).data('tab');
            
            $('.rem-settings-tab').removeClass('active');
            $('.rem-settings-panel').removeClass('active');
            
            $(this).addClass('active');
            $(`#${targetTab}`).addClass('active');
        },
        
        autoSaveSettings: function() {
            const setting = $(this).attr('name');
            const value = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_auto_save_setting',
                    setting: setting,
                    value: value,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.showNotice('Impostazione salvata', 'success', 2000);
                    }
                }
            });
        },
        
        testConnection: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.text('Test in corso...').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_test_connection',
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    $button.text(originalText).prop('disabled', false);
                    
                    if (response.success) {
                        REMAdmin.showNotice('Connessione riuscita', 'success');
                    } else {
                        REMAdmin.showNotice('Connessione fallita: ' + response.data, 'error');
                    }
                },
                error: function() {
                    $button.text(originalText).prop('disabled', false);
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        clearCache: function(e) {
            e.preventDefault();
            
            if (!confirm('Sei sicuro di voler pulire la cache? Questo potrebbe rallentare temporaneamente il sito.')) {
                return;
            }
            
            const $button = $(this);
            const originalText = $button.text();
            
            $button.text('Pulizia...').prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_clear_cache',
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    $button.text(originalText).prop('disabled', false);
                    
                    if (response.success) {
                        REMAdmin.showNotice('Cache pulita con successo', 'success');
                    } else {
                        REMAdmin.showNotice('Errore nella pulizia: ' + response.data, 'error');
                    }
                },
                error: function() {
                    $button.text(originalText).prop('disabled', false);
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        backupRules: function(e) {
            e.preventDefault();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_backup_rules',
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.showNotice('Backup creato con successo', 'success');
                        // Aggiorna lista backup se presente
                        REMAdmin.loadBackupList();
                    } else {
                        REMAdmin.showNotice('Errore nel backup: ' + response.data, 'error');
                    }
                },
                error: function() {
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        restoreRules: function(e) {
            e.preventDefault();
            
            const backupId = $(this).data('backup-id');
            
            if (!confirm('Sei sicuro di voler ripristinare questo backup? Le regole attuali verranno sovrascritte.')) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_restore_rules',
                    backup_id: backupId,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.showNotice('Ripristino completato', 'success');
                        location.reload();
                    } else {
                        REMAdmin.showNotice('Errore nel ripristino: ' + response.data, 'error');
                    }
                },
                error: function() {
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        handleBulkActions: function() {
            const action = $(this).val();
            const $applyButton = $('#rem-bulk-apply');
            
            if (action) {
                $applyButton.prop('disabled', false);
            } else {
                $applyButton.prop('disabled', true);
            }
        },
        
        applyBulkActions: function(e) {
            e.preventDefault();
            
            const action = $('#rem-bulk-action-selector').val();
            const selectedRules = $('.rem-rule-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (!action || selectedRules.length === 0) {
                REMAdmin.showNotice('Seleziona un\'azione e almeno una regola', 'warning');
                return;
            }
            
            if (!confirm(`Sei sicuro di voler applicare l'azione "${action}" a ${selectedRules.length} regole?`)) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_bulk_action',
                    bulk_action: action,
                    rule_ids: selectedRules,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.showNotice(`Azione applicata a ${response.data.affected} regole`, 'success');
                        location.reload();
                    } else {
                        REMAdmin.showNotice('Errore nell\'azione: ' + response.data, 'error');
                    }
                },
                error: function() {
                    REMAdmin.showNotice('Errore di connessione', 'error');
                }
            });
        },
        
        applyFilters: function() {
            const filters = {};
            $('.rem-filter').each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (value) {
                    filters[name] = value;
                }
            });
            
            REMAdmin.loadFilteredRules(filters);
        },
        
        searchRules: function() {
            const query = $(this).val();
            
            if (query.length >= 2 || query.length === 0) {
                REMAdmin.performSearch(query);
            }
        },
        
        performSearch: function(query) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_search_rules',
                    query: query,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.updateRulesList(response.data);
                    }
                }
            });
        },
        
        previewRule: function(e) {
            e.preventDefault();
            
            const ruleId = $(this).data('rule-id');
            const postId = $(this).data('post-id');
            
            if (postId) {
                const previewUrl = `${remAdmin.site_url}?p=${postId}&rem_preview=${ruleId}`;
                window.open(previewUrl, '_blank');
            } else {
                REMAdmin.showNotice('Impossibile visualizzare l\'anteprima per regole globali', 'info');
            }
        },
        
        copyToClipboard: function(e) {
            e.preventDefault();
            
            const text = $(this).data('text') || $(this).siblings('code').text();
            
            navigator.clipboard.writeText(text).then(function() {
                REMAdmin.showNotice('Copiato negli appunti', 'success', 2000);
            }).catch(function() {
                // Fallback per browser più vecchi
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                REMAdmin.showNotice('Copiato negli appunti', 'success', 2000);
            });
        },
        
        initDataTable: function() {
            $('#rem-rules-table').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[4, 'desc']], // Ordina per data di modifica
                columnDefs: [
                    { orderable: false, targets: [0, 5] }, // Checkbox e azioni non ordinabili
                    { searchable: false, targets: [0, 5] }
                ],
                language: {
                    url: remAdmin.datatables_lang_url
                }
            });
        },
        
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('[data-tooltip]').tooltip({
                    placement: 'top',
                    trigger: 'hover'
                });
            }
        },
        
        initCharts: function() {
            // Grafico uso breakpoint
            const breakpointCtx = document.getElementById('breakpoint-usage-chart');
            if (breakpointCtx) {
                new Chart(breakpointCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Mobile', 'Tablet', 'Desktop'],
                        datasets: [{
                            data: remAdmin.chart_data.breakpoints,
                            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            // Grafico regole nel tempo
            const timelineCtx = document.getElementById('rules-timeline-chart');
            if (timelineCtx) {
                new Chart(timelineCtx, {
                    type: 'line',
                    data: {
                        labels: remAdmin.chart_data.timeline.labels,
                        datasets: [{
                            label: 'Regole Create',
                            data: remAdmin.chart_data.timeline.data,
                            borderColor: '#0073aa',
                            backgroundColor: 'rgba(0, 115, 170, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },
        
        loadStatistics: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_get_statistics',
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.updateStatisticsDisplay(response.data);
                    }
                }
            });
        },
        
        updateStatistics: function() {
            // Ricarica le statistiche dopo operazioni che le modificano
            this.loadStatistics();
        },
        
        updateStatisticsDisplay: function(stats) {
            $('.rem-stat-total-rules').text(stats.total_rules);
            $('.rem-stat-page-rules').text(stats.page_rules);
            $('.rem-stat-site-rules').text(stats.site_rules);
            $('.rem-stat-pages-affected').text(stats.pages_affected);
        },
        
        loadFilteredRules: function(filters) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_filter_rules',
                    filters: filters,
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.updateRulesList(response.data);
                    }
                }
            });
        },
        
        updateRulesList: function(rulesData) {
            // Aggiorna la tabella delle regole con i nuovi dati
            if ($.fn.DataTable.isDataTable('#rem-rules-table')) {
                const table = $('#rem-rules-table').DataTable();
                table.clear();
                table.rows.add(rulesData).draw();
            } else {
                // Aggiornamento manuale se DataTables non è disponibile
                const tbody = $('#rem-rules-table tbody');
                tbody.empty();
                
                rulesData.forEach(function(rule) {
                    const row = REMAdmin.createRuleRow(rule);
                    tbody.append(row);
                });
            }
        },
        
        createRuleRow: function(rule) {
            // Crea una riga della tabella per una regola
            return `
                <tr>
                    <td><input type="checkbox" class="rem-rule-checkbox" value="${rule.id}"></td>
                    <td><code>${rule.element_selector}</code></td>
                    <td>${rule.post_title || '—'}</td>
                    <td><span class="rem-scope-badge rem-scope-${rule.scope}">${rule.scope === 'site' ? 'Globale' : 'Pagina'}</span></td>
                    <td>${rule.breakpoints_icons}</td>
                    <td>${rule.updated_at}</td>
                    <td>
                        <button class="button button-small rem-preview-rule" data-rule-id="${rule.id}" data-post-id="${rule.post_id}">Anteprima</button>
                        <button class="button button-small button-link-delete rem-delete-rule" data-rule-id="${rule.id}" data-rule-name="${rule.element_selector}">Elimina</button>
                    </td>
                </tr>
            `;
        },
        
        loadBackupList: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'rem_get_backups',
                    nonce: remAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        REMAdmin.updateBackupList(response.data);
                    }
                }
            });
        },
        
        updateBackupList: function(backups) {
            const container = $('#rem-backup-list');
            container.empty();
            
            backups.forEach(function(backup) {
                const item = `
                    <div class="rem-backup-item">
                        <span class="backup-date">${backup.date}</span>
                        <span class="backup-rules">${backup.rules_count} regole</span>
                        <button class="button button-small rem-restore-rules" data-backup-id="${backup.id}">Ripristina</button>
                    </div>
                `;
                container.append(item);
            });
        },
        
        showNotice: function(message, type, timeout) {
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
            
            $('.wrap h1').after(notice);
            
            // Auto-dismiss
            if (timeout > 0) {
                setTimeout(function() {
                    notice.fadeOut(function() {
                        $(this).remove();
                    });
                }, timeout);
            }
            
            // Manual dismiss
            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            });
        },
        
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };
    
    // Inizializza quando il documento è pronto
    $(document).ready(function() {
        REMAdmin.init();
    });
    
    // Espone globalmente per estensioni
    window.REMAdmin = REMAdmin;
    
})(jQuery);