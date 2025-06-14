<?php
/**
 * Pagina di aiuto del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rem-help-page">
    <div class="rem-help-header">
        <h2>📖 Guida Responsive Element Manager</h2>
        <p>Tutto quello che devi sapere per utilizzare al meglio il plugin</p>
    </div>
    
    <div class="rem-help-tabs">
        <nav class="rem-help-nav">
            <button class="rem-help-tab active" data-tab="getting-started">🚀 Primi Passi</button>
            <button class="rem-help-tab" data-tab="interface">🎨 Interfaccia</button>
            <button class="rem-help-tab" data-tab="features">⚙️ Funzionalità</button>
            <button class="rem-help-tab" data-tab="troubleshooting">🔧 Risoluzione Problemi</button>
            <button class="rem-help-tab" data-tab="advanced">🎓 Avanzato</button>
        </nav>
        
        <div class="rem-help-content">
            <!-- Getting Started -->
            <div id="getting-started" class="rem-help-panel active">
                <h3>🚀 Come Iniziare</h3>
                
                <div class="rem-step-guide">
                    <div class="rem-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Attivazione dell'Editor</h4>
                            <p>Vai su qualsiasi pagina del tuo sito (frontend) e cerca il pulsante 📱 in alto a destra. Clicca per attivare la modalità editor.</p>
                            <div class="rem-tip">
                                <strong>💡 Suggerimento:</strong> Il pulsante appare solo se hai i permessi per modificare i contenuti.
                            </div>
                        </div>
                    </div>
                    
                    <div class="rem-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Selezione Elementi</h4>
                            <p>Con l'editor attivo, clicca su qualsiasi elemento della pagina che vuoi modificare. L'elemento si evidenzierà in rosso.</p>
                            <div class="rem-warning">
                                <strong>⚠️ Attenzione:</strong> Evita di cliccare su elementi del sistema come il menu WordPress o il pulsante del plugin stesso.
                            </div>
                        </div>
                    </div>
                    
                    <div class="rem-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Configurazione</h4>
                            <p>Si aprirà una finestra con le opzioni di configurazione. Seleziona il breakpoint (Mobile/Tablet/Desktop) e configura le proprietà.</p>
                        </div>
                    </div>
                    
                    <div class="rem-step">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Salvataggio</h4>
                            <p>Scegli se applicare le modifiche solo alla pagina corrente o all'intero sito, poi clicca "Salva Regole".</p>
                        </div>
                    </div>
                </div>
                
                <div class="rem-video-placeholder">
                    <div class="video-box">
                        <h4>📹 Video Tutorial</h4>
                        <p>Un video tutorial dettagliato è disponibile nella documentazione online del plugin.</p>
                        <a href="#" class="button button-primary">Guarda il Tutorial</a>
                    </div>
                </div>
            </div>
            
            <!-- Interface -->
            <div id="interface" class="rem-help-panel">
                <h3>🎨 Guida all'Interfaccia</h3>
                
                <div class="rem-interface-guide">
                    <div class="interface-section">
                        <h4>Pulsante di Attivazione</h4>
                        <div class="feature-description">
                            <div class="feature-icon">📱</div>
                            <div class="feature-text">
                                <p>Il pulsante circolare che appare nell'angolo della pagina. Diventa rosso quando l'editor è attivo.</p>
                                <ul>
                                    <li><strong>Clic singolo:</strong> Attiva/disattiva l'editor</li>
                                    <li><strong>Colore blu:</strong> Editor inattivo</li>
                                    <li><strong>Colore rosso:</strong> Editor attivo</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="interface-section">
                        <h4>Finestra di Configurazione</h4>
                        <div class="feature-description">
                            <div class="feature-icon">⚙️</div>
                            <div class="feature-text">
                                <p>Il modal che si apre quando selezioni un elemento:</p>
                                <ul>
                                    <li><strong>Header:</strong> Mostra il selettore CSS dell'elemento</li>
                                    <li><strong>Scope:</strong> Scelta tra "pagina" o "sito"</li>
                                    <li><strong>Tab Breakpoint:</strong> Mobile, Tablet, Desktop</li>
                                    <li><strong>Proprietà:</strong> Font, allineamento, dimensioni</li>
                                    <li><strong>Footer:</strong> Pulsanti salva, annulla, elimina</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="interface-section">
                        <h4>Evidenziazione Elementi</h4>
                        <div class="feature-description">
                            <div class="feature-icon">🎯</div>
                            <div class="feature-text">
                                <p>Sistema di evidenziazione per identificare gli elementi:</p>
                                <ul>
                                    <li><strong>Bordo blu:</strong> Elemento sotto il mouse</li>
                                    <li><strong>Bordo rosso:</strong> Elemento selezionato</li>
                                    <li><strong>Sfondo semi-trasparente:</strong> Elemento attivo</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="rem-shortcuts">
                    <h4>⌨️ Scorciatoie da Tastiera</h4>
                    <div class="shortcuts-grid">
                        <div class="shortcut-item">
                            <kbd>Esc</kbd>
                            <span>Chiude la finestra di configurazione</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl + S</kbd>
                            <span>Salva le regole correnti</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Tab</kbd>
                            <span>Naviga tra i campi del form</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Features -->
            <div id="features" class="rem-help-panel">
                <h3>⚙️ Funzionalità Dettagliate</h3>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <h4>📝 Gestione Font</h4>
                        <ul>
                            <li><strong>Dimensione:</strong> Configura in px, %, em, rem</li>
                            <li><strong>Famiglia:</strong> Scegli tra font comuni o mantieni l'originale</li>
                            <li><strong>Per breakpoint:</strong> Dimensioni diverse per ogni dispositivo</li>
                        </ul>
                        <div class="feature-example">
                            <strong>Esempio:</strong> Titolo 32px su desktop, 24px su tablet, 18px su mobile
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📐 Controllo Dimensioni</h4>
                        <ul>
                            <li><strong>Larghezza:</strong> px, %, em, rem, vw</li>
                            <li><strong>Altezza:</strong> px, %, em, rem, vh</li>
                            <li><strong>Responsive:</strong> Adattamento automatico</li>
                        </ul>
                        <div class="feature-example">
                            <strong>Esempio:</strong> Contenitore 100% su mobile, 80% su desktop
                        </div>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🎯 Allineamento Testo</h4>
                        <ul>
                            <li><strong>Sinistra:</strong> Allineamento predefinito</li>
                            <li><strong>Centro:</strong> Per titoli e elementi centrali</li>
                            <li><strong>Destra:</strong> Per elementi di navigazione</li>
                            <li><strong>Giustificato:</strong> Per paragrafi di testo</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🌐 Gestione Scope</h4>
                        <ul>
                            <li><strong>Pagina:</strong> Modifiche solo sulla pagina corrente</li>
                            <li><strong>Sito:</strong> Modifiche su tutto il sito</li>
                            <li><strong>Priorità:</strong> Le regole specifiche prevalgono su quelle globali</li>
                        </ul>
                        <div class="feature-tip">
                            <strong>💡 Consiglio:</strong> Usa "Sito" per elementi ricorrenti come header e footer
                        </div>
                    </div>
                </div>
                
                <div class="breakpoints-explanation">
                    <h4>📱 Breakpoint Responsive</h4>
                    <div class="breakpoint-info">
                        <div class="bp-item">
                            <div class="bp-icon">📱</div>
                            <div class="bp-details">
                                <strong>Mobile</strong>
                                <span>Fino a 767px di larghezza</span>
                            </div>
                        </div>
                        <div class="bp-item">
                            <div class="bp-icon">📟</div>
                            <div class="bp-details">
                                <strong>Tablet</strong>
                                <span>Da 768px a 1023px</span>
                            </div>
                        </div>
                        <div class="bp-item">
                            <div class="bp-icon">🖥️</div>
                            <div class="bp-details">
                                <strong>Desktop</strong>
                                <span>Oltre 1024px (predefinito)</span>
                            </div>
                        </div>
                    </div>
                    <p class="breakpoint-note">
                        <strong>Nota:</strong> I breakpoint sono personalizzabili nelle impostazioni del plugin.
                    </p>
                </div>
            </div>
            
            <!-- Troubleshooting -->
            <div id="troubleshooting" class="rem-help-panel">
                <h3>🔧 Risoluzione Problemi</h3>
                
                <div class="troubleshooting-section">
                    <h4>❌ Problemi Comuni</h4>
                    
                    <div class="problem-solution">
                        <div class="problem">
                            <strong>Il pulsante di attivazione non appare</strong>
                        </div>
                        <div class="solution">
                            <h5>Possibili cause e soluzioni:</h5>
                            <ul>
                                <li>Verifica di avere i permessi per modificare i contenuti</li>
                                <li>Controlla se sei nell'area amministrativa (il plugin funziona solo nel frontend)</li>
                                <li>Disabilita temporaneamente altri plugin per verificare conflitti</li>
                                <li>Controlla la console del browser per errori JavaScript</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="problem-solution">
                        <div class="problem">
                            <strong>Le modifiche non vengono applicate</strong>
                        </div>
                        <div class="solution">
                            <h5>Possibili cause e soluzioni:</h5>
                            <ul>
                                <li>Pulisci la cache del sito e del browser</li>
                                <li>Verifica che il CSS del tema non sovrascriva le regole</li>
                                <li>Controlla se ci sono plugin di cache attivi</li>
                                <li>Usa gli strumenti sviluppatore per verificare se il CSS è presente</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="problem-solution">
                        <div class="problem">
                            <strong>Errore nel salvataggio delle regole</strong>
                        </div>
                        <div class="solution">
                            <h5>Possibili cause e soluzioni:</h5>
                            <ul>
                                <li>Verifica la connessione internet</li>
                                <li>Controlla i log di errore del server</li>
                                <li>Verifica che il database sia accessibile</li>
                                <li>Prova a disattivare e riattivare il plugin</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="problem-solution">
                        <div class="problem">
                            <strong>L'interfaccia è lenta o non risponde</strong>
                        </div>
                        <div class="solution">
                            <h5>Possibili cause e soluzioni:</h5>
                            <ul>
                                <li>Controlla se ci sono molte regole CSS attive</li>
                                <li>Abilita la minificazione CSS nelle impostazioni</li>
                                <li>Verifica le prestazioni del server</li>
                                <li>Riduci il numero di plugin attivi</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="diagnostic-tools">
                    <h4>🔍 Strumenti di Diagnostica</h4>
                    <div class="tools-grid">
                        <div class="tool-item">
                            <button class="button button-secondary" onclick="remRunDiagnostic()">
                                🔬 Esegui Diagnostica
                            </button>
                            <p>Verifica lo stato del plugin e rileva problemi comuni</p>
                        </div>
                        <div class="tool-item">
                            <button class="button button-secondary" onclick="remExportLogs()">
                                📋 Esporta Log
                            </button>
                            <p>Scarica i log per il supporto tecnico</p>
                        </div>
                        <div class="tool-item">
                            <button class="button button-secondary" onclick="remTestCSS()">
                                🧪 Test CSS
                            </button>
                            <p>Verifica la validità del CSS generato</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Advanced -->
            <div id="advanced" class="rem-help-panel">
                <h3>🎓 Utilizzo Avanzato</h3>
                
                <div class="advanced-section">
                    <h4>🔧 Hook e Filtri per Sviluppatori</h4>
                    <div class="code-examples">
                        <div class="code-example">
                            <h5>Personalizzare i Breakpoint</h5>
                            <pre><code>// Nel functions.php del tema
add_filter('rem_breakpoints', function($breakpoints) {
    $breakpoints['large'] = '(min-width: 1400px)';
    return $breakpoints;
});</code></pre>
                        </div>
                        
                        <div class="code-example">
                            <h5>Aggiungere Proprietà CSS Personalizzate</h5>
                            <pre><code>// Aggiungere supporto per border-radius
add_filter('rem_css_rules', function($css, $rules) {
    if (isset($rules['border_radius'])) {
        $css .= '; border-radius: ' . $rules['border_radius'];
    }
    return $css;
}, 10, 2);</code></pre>
                        </div>
                        
                        <div class="code-example">
                            <h5>Hook per Azioni Personalizzate</h5>
                            <pre><code>// Eseguire azioni dopo il salvataggio
add_action('rem_rule_saved', function($rule_data) {
    // La tua logica personalizzata
    error_log('Regola salvata: ' . print_r($rule_data, true));
});</code></pre>
                        </div>
                    </div>
                </div>
                
                <div class="advanced-section">
                    <h4>📊 Ottimizzazione Performance</h4>
                    <div class="performance-tips">
                        <div class="tip-card">
                            <h5>💾 Cache CSS</h5>
                            <p>Abilita la cache CSS nelle impostazioni per ridurre i tempi di elaborazione su siti con molte regole.</p>
                        </div>
                        <div class="tip-card">
                            <h5>🗜️ Minificazione</h5>
                            <p>La minificazione automatica riduce la dimensione del CSS del 20-30% in media.</p>
                        </div>
                        <div class="tip-card">
                            <h5>🎯 Selettori Specifici</h5>
                            <p>Usa selettori specifici (ID o classi) invece di tag generici per migliorare le performance.</p>
                        </div>
                    </div>
                </div>
                
                <div class="advanced-section">
                    <h4>🔒 Sicurezza e Best Practice</h4>
                    <ul class="best-practices">
                        <li>Limita l'accesso all'editor solo agli utenti autorizzati</li>
                        <li>Fai backup regolari delle configurazioni</li>
                        <li>Testa sempre le modifiche su un ambiente di staging</li>
                        <li>Evita selettori troppo generici che potrebbero influenzare molti elementi</li>
                        <li>Monitora le performance dopo l'implementazione di molte regole</li>
                    </ul>
                </div>
                
                <div class="advanced-section">
                    <h4>🌐 Integrazione con Altri Plugin</h4>
                    <div class="integration-info">
                        <p>Il plugin è compatibile con:</p>
                        <ul>
                            <li><strong>Page Builder:</strong> Elementor, Beaver Builder, Divi</li>
                            <li><strong>Cache Plugin:</strong> WP Rocket, W3 Total Cache, LiteSpeed</li>
                            <li><strong>SEO Plugin:</strong> Yoast, RankMath, All in One SEO</li>
                            <li><strong>Performance:</strong> Autoptimize, WP Optimize</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="rem-help-footer">
        <div class="help-links">
            <a href="#" class="button button-primary">📖 Documentazione Completa</a>
            <a href="#" class="button button-secondary">💬 Forum di Supporto</a>
            <a href="#" class="button button-secondary">🐛 Segnala Bug</a>
        </div>
        
        <div class="help-contact">
            <h4>📞 Hai bisogno di aiuto?</h4>
            <p>Se non riesci a risolvere il tuo problema, contatta il supporto tecnico:</p>
            <ul>
                <li>Email: support@yourplugin.com</li>
                <li>Forum: wordpress.org/support/plugin/responsive-element-manager</li>
                <li>Documentazione: docs.yourplugin.com</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestione tab della guida
    const tabs = document.querySelectorAll('.rem-help-tab');
    const panels = document.querySelectorAll('.rem-help-panel');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetPanel = this.dataset.tab;
            
            // Rimuovi classe active da tutti i tab e panel
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            
            // Aggiungi classe active al tab e panel correnti
            this.classList.add('active');
            document.getElementById(targetPanel).classList.add('active');
        });
    });
});

// Funzioni diagnostica
function remRunDiagnostic() {
    const diagnosticButton = event.target;
    diagnosticButton.textContent = '🔄 Diagnostica in corso...';
    diagnosticButton.disabled = true;
    
    jQuery.post(ajaxurl, {
        action: 'rem_run_diagnostic',
        nonce: '<?php echo wp_create_nonce('rem_diagnostic'); ?>'
    }, function(response) {
        diagnosticButton.textContent = '🔬 Esegui Diagnostica';
        diagnosticButton.disabled = false;
        
        if (response.success) {
            const results = response.data;
            let message = 'Risultati diagnostica:\n\n';
            
            for (const [test, result] of Object.entries(results)) {
                message += `${test}: ${result.status} ${result.message ? '- ' + result.message : ''}\n`;
            }
            
            alert(message);
        } else {
            alert('Errore durante la diagnostica: ' + response.data);
        }
    });
}

function remExportLogs() {
    window.location.href = ajaxurl + '?action=rem_export_logs&nonce=' + '<?php echo wp_create_nonce('rem_export_logs'); ?>';
}

function remTestCSS() {
    jQuery.post(ajaxurl, {
        action: 'rem_test_css',
        nonce: '<?php echo wp_create_nonce('rem_test_css'); ?>'
    }, function(response) {
        if (response.success) {
            alert('CSS valido! ' + response.data.message);
        } else {
            alert('Problemi rilevati nel CSS:\n' + response.data.join('\n'));
        }
    });
}
</script>

<style>
.rem-help-page {
    max-width: 1200px;
}

.rem-help-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.rem-help-nav {
    display: flex;
    background: white;
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #ddd;
    overflow-x: auto;
}

.rem-help-tab {
    background: none;
    border: none;
    padding: 15px 20px;
    cursor: pointer;
    white-space: nowrap;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.rem-help-tab:hover {
    background: #f8f9fa;
}

.rem-help-tab.active {
    border-bottom-color: #0073aa;
    background: #f8f9fa;
    font-weight: 600;
}

.rem-help-content {
    background: white;
    border-radius: 0 0 8px 8px;
    min-height: 500px;
}

.rem-help-panel {
    display: none;
    padding: 30px;
}

.rem-help-panel.active {
    display: block;
}

.rem-step-guide {
    margin: 30px 0;
}

.rem-step {
    display: flex;
    margin-bottom: 30px;
    align-items: flex-start;
}

.step-number {
    background: #0073aa;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 20px;
    flex-shrink: 0;
}

.step-content h4 {
    margin-top: 0;
    color: #333;
}

.rem-tip, .rem-warning {
    margin-top: 15px;
    padding: 10px 15px;
    border-radius: 5px;
    border-left: 4px solid;
}

.rem-tip {
    background: #e8f5e8;
    border-left-color: #4caf50;
}

.rem-warning {
    background: #fff3cd;
    border-left-color: #ffc107;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.feature-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #0073aa;
}

.feature-card h4 {
    margin-top: 0;
    color: #0073aa;
}

.feature-example, .feature-tip {
    margin-top: 15px;
    padding: 10px;
    background: white;
    border-radius: 4px;
    font-size: 14px;
}

.breakpoint-info {
    display: flex;
    gap: 20px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.bp-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    flex: 1;
    min-width: 200px;
}

.bp-icon {
    font-size: 24px;
}

.bp-details strong {
    display: block;
    color: #333;
}

.bp-details span {
    color: #666;
    font-size: 14px;
}

.problem-solution {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.problem {
    background: #fff;
    padding: 15px;
    border-left: 4px solid #dc3545;
    margin-bottom: 15px;
    border-radius: 4px;
}

.solution h5 {
    color: #28a745;
    margin-bottom: 10px;
}

.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.tool-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.code-examples {
    margin: 20px 0;
}

.code-example {
    margin-bottom: 30px;
}

.code-example h5 {
    color: #0073aa;
    margin-bottom: 10px;
}

.code-example pre {
    background: #2d3748;
    color: #e2e8f0;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
    font-size: 14px;
}

.performance-tips {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.tip-card {
    background: #e8f5e8;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #4caf50;
}

.tip-card h5 {
    margin-top: 0;
    color: #2e7d32;
}

.best-practices {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.integration-info {
    background: #e3f2fd;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #2196f3;
}

.rem-help-footer {
    margin-top: 40px;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}

.help-links {
    margin-bottom: 30px;
}

.help-links .button {
    margin: 0 10px 10px 0;
}

.shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.shortcut-item {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}

kbd {
    background: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
}

@media (max-width: 768px) {
    .rem-help-nav {
        flex-direction: column;
    }
    
    .rem-step {
        flex-direction: column;
        text-align: center;
    }
    
    .step-number {
        margin: 0 auto 15px;
    }
    
    .breakpoint-info {
        flex-direction: column;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>