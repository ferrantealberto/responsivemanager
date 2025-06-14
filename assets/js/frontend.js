/**
 * Frontend JavaScript CORRETTO per Responsive Element Manager
 * Include salvataggio funzionante, allineamento, proporzioni automatiche e posizionamento x,y
 */
(function() {
    'use strict';
    
    /**
     * Classe principale per la gestione del frontend
     */
    class ResponsiveElementManager {
        constructor() {
            this.isActive = false;
            this.selectedElement = null;
            this.currentSelector = '';
            this.currentBreakpoint = 'mobile';
            this.currentRules = {};
            this.selectorMode = 'self';
            this.childElements = [];
            this.hoveredElement = null;
            this.autoProportions = true; // Nuova opzione per proporzioni automatiche
            this.deviceProportions = {
                mobile: { width: 375, height: 667 },
                tablet: { width: 768, height: 1024 },
                desktop: { width: 1920, height: 1080 }
            };
            
            this.init();
        }
        
        init() {
            this.createInterface();
            this.bindEvents();
            this.loadExistingRules();
        }
        
        /**
         * Crea l'interfaccia completa del plugin
         */
        createInterface() {
            // Toggle Button
            const toggleBtn = document.createElement('div');
            toggleBtn.id = 'rem-toggle-btn';
            toggleBtn.innerHTML = '📱';
            toggleBtn.title = 'Attiva/Disattiva Editor Responsive';
            document.body.appendChild(toggleBtn);
            
            // Modal completo
            const modal = document.createElement('div');
            modal.id = 'rem-modal';
            modal.innerHTML = this.getModalHTML();
            document.body.appendChild(modal);
            
            // Aggiungi CSS necessario
            this.addCustomCSS();
        }
        
        /**
         * HTML del modal migliorato con nuovi controlli
         */
        getModalHTML() {
            return `
                <div class="rem-modal-content">
                    <div class="rem-modal-header">
                        <h3>🎨 Editor Responsive Avanzato</h3>
                        <div class="rem-header-controls">
                            <button id="rem-auto-proportions" class="rem-toggle-btn ${this.autoProportions ? 'active' : ''}" 
                                    title="Proporzioni Automatiche">🔄 Auto</button>
                            <span id="rem-close">&times;</span>
                        </div>
                    </div>
                    <div class="rem-modal-body">
                        <!-- Informazioni elemento selezionato -->
                        <div class="rem-element-info">
                            <div class="rem-selector-section">
                                <h4>🎯 Selezione Elemento</h4>
                                <div class="rem-selector-options">
                                    <button class="rem-selector-btn active" data-type="self">
                                        <span>📍</span> Elemento Corrente
                                    </button>
                                    <button class="rem-selector-btn" data-type="parent">
                                        <span>⬆️</span> Elemento Padre
                                    </button>
                                    <button class="rem-selector-btn" data-type="children">
                                        <span>⬇️</span> Elementi Figli
                                    </button>
                                </div>
                                <div id="rem-current-selector-display">
                                    <strong>Selettore CSS:</strong> <code id="rem-current-selector">Nessun elemento</code>
                                    <button id="rem-copy-selector" class="rem-copy-btn" title="Copia selettore">📋</button>
                                </div>
                                <div id="rem-children-container" style="display: none;">
                                    <div class="rem-children-list"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Scope di applicazione -->
                        <div class="rem-scope-section">
                            <label for="rem-scope"><strong>📊 Applica modifiche a:</strong></label>
                            <select id="rem-scope">
                                <option value="page">📄 Solo questa pagina</option>
                                <option value="site">🌐 Tutto il sito</option>
                            </select>
                        </div>
                        
                        <!-- Breakpoint Tabs -->
                        <div class="rem-breakpoint-section">
                            <div class="rem-breakpoint-tabs">
                                <button class="rem-tab-btn active" data-breakpoint="mobile">
                                    📱 Mobile<br><small>(max 767px)</small>
                                </button>
                                <button class="rem-tab-btn" data-breakpoint="tablet">
                                    📟 Tablet<br><small>(768px - 1023px)</small>
                                </button>
                                <button class="rem-tab-btn" data-breakpoint="desktop">
                                    🖥️ Desktop<br><small>(1024px+)</small>
                                </button>
                            </div>
                            
                            <div id="rem-breakpoint-content">
                                <!-- Content will be generated by showBreakpointControls() -->
                            </div>
                        </div>
                        
                        <!-- Preview Section -->
                        <div class="rem-preview-section">
                            <button id="rem-preview-changes" class="rem-btn rem-btn-secondary">
                                👁️ Anteprima Modifiche
                            </button>
                            <button id="rem-reset-element" class="rem-btn rem-btn-danger">
                                🔄 Reset Elemento
                            </button>
                            <button id="rem-apply-auto-proportions" class="rem-btn rem-btn-info">
                                🔄 Applica Proporzioni Auto
                            </button>
                        </div>
                    </div>
                    <div class="rem-modal-footer">
                        <button id="rem-save" class="rem-btn rem-btn-primary">💾 Salva Regole</button>
                        <button id="rem-cancel" class="rem-btn rem-btn-secondary">❌ Annulla</button>
                    </div>
                </div>
            `;
        }
        
        /**
         * Genera controlli per il breakpoint corrente - VERSIONE CORRETTA
         */
        showBreakpointControls() {
            const container = document.getElementById('rem-breakpoint-content');
            if (!container) return;
            
            container.innerHTML = `
                <div class="rem-controls-container">
                    <div class="rem-controls-grid">
                        
                        <!-- Posizionamento e Layout -->
                        <div class="rem-control-group">
                            <h4>📐 Posizionamento e Layout</h4>
                            
                            <!-- Posizione -->
                            <div class="rem-form-group">
                                <label>Posizione:</label>
                                <select id="position-${this.currentBreakpoint}">
                                    <option value="">Predefinito</option>
                                    <option value="static">Static</option>
                                    <option value="relative">Relative</option>
                                    <option value="absolute">Absolute</option>
                                    <option value="fixed">Fixed</option>
                                    <option value="sticky">Sticky</option>
                                </select>
                            </div>
                            
                            <!-- Coordinate X e Y -->
                            <div id="position-controls-${this.currentBreakpoint}" class="rem-position-controls" style="display: none;">
                                <div class="rem-form-row">
                                    <div class="rem-form-group">
                                        <label>Posizione X (Left):</label>
                                        <div class="rem-input-group">
                                            <input type="number" id="position-x-${this.currentBreakpoint}" 
                                                   placeholder="0" step="1">
                                            <select id="position-x-unit-${this.currentBreakpoint}">
                                                <option value="px">px</option>
                                                <option value="%">%</option>
                                                <option value="em">em</option>
                                                <option value="rem">rem</option>
                                                <option value="vw">vw</option>
                                                <option value="auto">auto</option>
                                            </select>
                                            <button type="button" class="rem-auto-btn" data-property="position-x" 
                                                    title="Applica proporzione automatica">🔄</button>
                                        </div>
                                    </div>
                                    <div class="rem-form-group">
                                        <label>Posizione Y (Top):</label>
                                        <div class="rem-input-group">
                                            <input type="number" id="position-y-${this.currentBreakpoint}" 
                                                   placeholder="0" step="1">
                                            <select id="position-y-unit-${this.currentBreakpoint}">
                                                <option value="px">px</option>
                                                <option value="%">%</option>
                                                <option value="em">em</option>
                                                <option value="rem">rem</option>
                                                <option value="vh">vh</option>
                                                <option value="auto">auto</option>
                                            </select>
                                            <button type="button" class="rem-auto-btn" data-property="position-y" 
                                                    title="Applica proporzione automatica">🔄</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Allineamento Universale -->
                            <div class="rem-form-group">
                                <label>Allineamento Elemento:</label>
                                <div class="rem-alignment-grid">
                                    <button type="button" class="rem-align-btn" data-align="left" title="Sinistra">⬅️</button>
                                    <button type="button" class="rem-align-btn" data-align="center" title="Centro">↔️</button>
                                    <button type="button" class="rem-align-btn" data-align="right" title="Destra">➡️</button>
                                    <button type="button" class="rem-align-btn" data-align="justify" title="Giustificato">↕️</button>
                                </div>
                            </div>
                            
                            <!-- Display -->
                            <div class="rem-form-group">
                                <label>Display:</label>
                                <select id="display-${this.currentBreakpoint}">
                                    <option value="">Predefinito</option>
                                    <option value="block">Block</option>
                                    <option value="inline">Inline</option>
                                    <option value="inline-block">Inline Block</option>
                                    <option value="flex">Flex Container</option>
                                    <option value="grid">Grid Container</option>
                                    <option value="none">🚫 Nascosto</option>
                                </select>
                            </div>
                            
                            <!-- Controlli Flex -->
                            <div id="flex-controls-${this.currentBreakpoint}" class="rem-flex-controls" style="display: none;">
                                <div class="rem-form-group">
                                    <label>Direzione Flex:</label>
                                    <select id="flex-direction-${this.currentBreakpoint}">
                                        <option value="">Predefinito</option>
                                        <option value="row">Riga →</option>
                                        <option value="column">Colonna ↓</option>
                                        <option value="row-reverse">Riga ←</option>
                                        <option value="column-reverse">Colonna ↑</option>
                                    </select>
                                </div>
                                <div class="rem-form-group">
                                    <label>Allineamento Orizzontale:</label>
                                    <select id="justify-content-${this.currentBreakpoint}">
                                        <option value="">Predefinito</option>
                                        <option value="flex-start">⬅️ Inizio</option>
                                        <option value="center">↔️ Centro</option>
                                        <option value="flex-end">➡️ Fine</option>
                                        <option value="space-between">↔️ Spazio Tra</option>
                                        <option value="space-around">↔️ Spazio Attorno</option>
                                    </select>
                                </div>
                                <div class="rem-form-group">
                                    <label>Allineamento Verticale:</label>
                                    <select id="align-items-${this.currentBreakpoint}">
                                        <option value="">Predefinito</option>
                                        <option value="flex-start">⬆️ Inizio</option>
                                        <option value="center">↕️ Centro</option>
                                        <option value="flex-end">⬇️ Fine</option>
                                        <option value="stretch">↕️ Estendi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tipografia -->
                        <div class="rem-control-group">
                            <h4>🔤 Tipografia</h4>
                            <div class="rem-form-group">
                                <label>Dimensione Font:</label>
                                <div class="rem-input-group">
                                    <input type="number" id="font-size-${this.currentBreakpoint}" 
                                           placeholder="16" min="8" max="200" step="0.5">
                                    <select id="font-unit-${this.currentBreakpoint}">
                                        <option value="px">px</option>
                                        <option value="%">%</option>
                                        <option value="em">em</option>
                                        <option value="rem">rem</option>
                                    </select>
                                    <button type="button" class="rem-auto-btn" data-property="font-size" 
                                            title="Applica proporzione automatica">🔄</button>
                                </div>
                            </div>
                            <div class="rem-form-group">
                                <label>Famiglia Font:</label>
                                <select id="font-family-${this.currentBreakpoint}">
                                    <option value="">Predefinito</option>
                                    <option value="Arial, sans-serif">Arial</option>
                                    <option value="Georgia, serif">Georgia</option>
                                    <option value="'Times New Roman', serif">Times New Roman</option>
                                    <option value="'Helvetica Neue', sans-serif">Helvetica</option>
                                    <option value="'Roboto', sans-serif">Roboto (Google)</option>
                                    <option value="'Open Sans', sans-serif">Open Sans (Google)</option>
                                    <option value="'Lato', sans-serif">Lato (Google)</option>
                                    <option value="'Montserrat', sans-serif">Montserrat (Google)</option>
                                </select>
                            </div>
                            <div class="rem-form-group">
                                <label>Peso Font:</label>
                                <select id="font-weight-${this.currentBreakpoint}">
                                    <option value="">Predefinito</option>
                                    <option value="300">Light (300)</option>
                                    <option value="400">Normal (400)</option>
                                    <option value="500">Medium (500)</option>
                                    <option value="600">Semi Bold (600)</option>
                                    <option value="700">Bold (700)</option>
                                    <option value="800">Extra Bold (800)</option>
                                </select>
                            </div>
                            <div class="rem-form-group">
                                <label>Allineamento Testo:</label>
                                <div class="rem-alignment-buttons">
                                    <button type="button" class="rem-text-align-btn" data-align="left" title="Sinistra">⬅️</button>
                                    <button type="button" class="rem-text-align-btn" data-align="center" title="Centro">↔️</button>
                                    <button type="button" class="rem-text-align-btn" data-align="right" title="Destra">➡️</button>
                                    <button type="button" class="rem-text-align-btn" data-align="justify" title="Giustificato">↕️</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Colori -->
                        <div class="rem-control-group">
                            <h4>🎨 Colori</h4>
                            <div class="rem-form-group">
                                <label>Colore Testo:</label>
                                <div class="rem-color-control">
                                    <input type="color" id="text-color-${this.currentBreakpoint}" 
                                           class="rem-color-input" value="#000000">
                                    <input type="text" id="text-color-hex-${this.currentBreakpoint}" 
                                           placeholder="#000000" class="rem-hex-input">
                                    <button type="button" class="rem-color-reset" data-target="text-color">🔄</button>
                                </div>
                            </div>
                            <div class="rem-form-group">
                                <label>Colore Sfondo:</label>
                                <div class="rem-color-control">
                                    <input type="color" id="bg-color-${this.currentBreakpoint}" 
                                           class="rem-color-input" value="#ffffff">
                                    <input type="text" id="bg-color-hex-${this.currentBreakpoint}" 
                                           placeholder="#ffffff" class="rem-hex-input">
                                    <button type="button" class="rem-color-reset" data-target="bg-color">🔄</button>
                                </div>
                            </div>
                            <div class="rem-form-group">
                                <label>Colore Bordo:</label>
                                <div class="rem-color-control">
                                    <input type="color" id="border-color-${this.currentBreakpoint}" 
                                           class="rem-color-input" value="#cccccc">
                                    <input type="text" id="border-color-hex-${this.currentBreakpoint}" 
                                           placeholder="#cccccc" class="rem-hex-input">
                                    <button type="button" class="rem-color-reset" data-target="border-color">🔄</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dimensioni con Auto -->
                        <div class="rem-control-group">
                            <h4>📏 Dimensioni</h4>
                            <div class="rem-form-group">
                                <label>Larghezza:</label>
                                <div class="rem-input-group">
                                    <input type="number" id="width-${this.currentBreakpoint}" 
                                           placeholder="auto" min="0" max="2000">
                                    <select id="width-unit-${this.currentBreakpoint}">
                                        <option value="px">px</option>
                                        <option value="%">%</option>
                                        <option value="em">em</option>
                                        <option value="rem">rem</option>
                                        <option value="vw">vw</option>
                                        <option value="auto">auto</option>
                                    </select>
                                    <button type="button" class="rem-auto-btn" data-property="width" 
                                            title="Applica proporzione automatica">🔄</button>
                                </div>
                            </div>
                            <div class="rem-form-group">
                                <label>Altezza:</label>
                                <div class="rem-input-group">
                                    <input type="number" id="height-${this.currentBreakpoint}" 
                                           placeholder="auto" min="0" max="2000">
                                    <select id="height-unit-${this.currentBreakpoint}">
                                        <option value="px">px</option>
                                        <option value="%">%</option>
                                        <option value="em">em</option>
                                        <option value="rem">rem</option>
                                        <option value="vh">vh</option>
                                        <option value="auto">auto</option>
                                    </select>
                                    <button type="button" class="rem-auto-btn" data-property="height" 
                                            title="Applica proporzione automatica">🔄</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Spaziatura con Auto -->
                        <div class="rem-control-group">
                            <h4>📦 Spaziatura</h4>
                            <div class="rem-spacing-visual">
                                <div class="rem-spacing-label">Margini:</div>
                                <div class="rem-spacing-box">
                                    <div class="rem-margin-controls">
                                        <div class="rem-spacing-input-group">
                                            <input type="number" id="margin-top-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-margin-top">
                                            <button type="button" class="rem-auto-btn-small" data-property="margin-top">🔄</button>
                                        </div>
                                        <div class="rem-spacing-input-group">
                                            <input type="number" id="margin-right-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-margin-right">
                                            <button type="button" class="rem-auto-btn-small" data-property="margin-right">🔄</button>
                                        </div>
                                        <div class="rem-spacing-input-group">
                                            <input type="number" id="margin-bottom-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-margin-bottom">
                                            <button type="button" class="rem-auto-btn-small" data-property="margin-bottom">🔄</button>
                                        </div>
                                        <div class="rem-spacing-input-group">
                                            <input type="number" id="margin-left-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-margin-left">
                                            <button type="button" class="rem-auto-btn-small" data-property="margin-left">🔄</button>
                                        </div>
                                    </div>
                                    <div class="rem-padding-box">
                                        <div class="rem-spacing-label">Padding:</div>
                                        <div class="rem-padding-controls">
                                            <div class="rem-spacing-input-group">
                                                <input type="number" id="padding-top-${this.currentBreakpoint}" 
                                                       placeholder="0" class="rem-spacing-input rem-padding-top">
                                                <button type="button" class="rem-auto-btn-small" data-property="padding-top">🔄</button>
                                            </div>
                                            <div class="rem-spacing-input-group">
                                                <input type="number" id="padding-right-${this.currentBreakpoint}" 
                                                       placeholder="0" class="rem-spacing-input rem-padding-right">
                                                <button type="button" class="rem-auto-btn-small" data-property="padding-right">🔄</button>
                                            </div>
                                            <div class="rem-spacing-input-group">
                                                <input type="number" id="padding-bottom-${this.currentBreakpoint}" 
                                                       placeholder="0" class="rem-spacing-input rem-padding-bottom">
                                                <button type="button" class="rem-auto-btn-small" data-property="padding-bottom">🔄</button>
                                            </div>
                                            <div class="rem-spacing-input-group">
                                                <input type="number" id="padding-left-${this.currentBreakpoint}" 
                                                       placeholder="0" class="rem-spacing-input rem-padding-left">
                                                <button type="button" class="rem-auto-btn-small" data-property="padding-left">🔄</button>
                                            </div>
                                        </div>
                                        <div class="rem-element-box">Elemento</div>
                                    </div>
                                </div>
                                <div class="rem-spacing-units">
                                    <select id="spacing-unit-${this.currentBreakpoint}">
                                        <option value="px">px</option>
                                        <option value="em">em</option>
                                        <option value="rem">rem</option>
                                        <option value="%">%</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Effetti -->
                        <div class="rem-control-group">
                            <h4>✨ Effetti</h4>
                            <div class="rem-form-group">
                                <label>Opacità: <span id="opacity-value-${this.currentBreakpoint}">1</span></label>
                                <input type="range" id="opacity-${this.currentBreakpoint}" 
                                       class="rem-range-input" min="0" max="1" step="0.1" value="1">
                            </div>
                            <div class="rem-form-group">
                                <label>Ombra:</label>
                                <select id="box-shadow-${this.currentBreakpoint}">
                                    <option value="">Nessuna</option>
                                    <option value="0 1px 3px rgba(0,0,0,0.1)">Sottile</option>
                                    <option value="0 2px 6px rgba(0,0,0,0.15)">Leggera</option>
                                    <option value="0 4px 12px rgba(0,0,0,0.2)">Media</option>
                                    <option value="0 8px 24px rgba(0,0,0,0.25)">Forte</option>
                                    <option value="0 12px 36px rgba(0,0,0,0.3)">Molto Forte</option>
                                </select>
                            </div>
                            <div class="rem-form-group">
                                <label>Raggio Bordo:</label>
                                <div class="rem-input-group">
                                    <input type="number" id="border-radius-${this.currentBreakpoint}" 
                                           placeholder="0" min="0" max="200">
                                    <select id="border-radius-unit-${this.currentBreakpoint}">
                                        <option value="px">px</option>
                                        <option value="%">%</option>
                                        <option value="em">em</option>
                                    </select>
                                    <button type="button" class="rem-auto-btn" data-property="border-radius" 
                                            title="Applica proporzione automatica">🔄</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Aggiungi event listeners per questo breakpoint
            this.bindBreakpointEvents();
        }
        
        /**
         * Eventi per i controlli del breakpoint - VERSIONE CORRETTA
         */
        bindBreakpointEvents() {
            const bp = this.currentBreakpoint;
            
            // Position change - mostra/nascondi controlli x,y
            const positionSelect = document.getElementById(`position-${bp}`);
            if (positionSelect) {
                positionSelect.addEventListener('change', (e) => {
                    const positionControls = document.getElementById(`position-controls-${bp}`);
                    if (positionControls) {
                        const showControls = ['relative', 'absolute', 'fixed', 'sticky'].includes(e.target.value);
                        positionControls.style.display = showControls ? 'block' : 'none';
                    }
                    this.applyPreviewStyles();
                });
            }
            
            // Display change
            const displaySelect = document.getElementById(`display-${bp}`);
            if (displaySelect) {
                displaySelect.addEventListener('change', (e) => {
                    const flexControls = document.getElementById(`flex-controls-${bp}`);
                    if (flexControls) {
                        flexControls.style.display = e.target.value === 'flex' ? 'block' : 'none';
                    }
                    this.applyPreviewStyles();
                });
            }
            
            // Opacity range
            const opacityRange = document.getElementById(`opacity-${bp}`);
            const opacityValue = document.getElementById(`opacity-value-${bp}`);
            if (opacityRange && opacityValue) {
                opacityRange.addEventListener('input', (e) => {
                    opacityValue.textContent = e.target.value;
                    this.applyPreviewStyles();
                });
            }
            
            // Color sync between color picker and hex input
            this.syncColorInputs(bp);
            
            // Alignment buttons - UNIVERSALE
            document.querySelectorAll('.rem-align-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.rem-align-btn').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    this.applyAlignment(e.target.dataset.align);
                });
            });
            
            // Text alignment buttons
            document.querySelectorAll('.rem-text-align-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.rem-text-align-btn').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    this.applyPreviewStyles();
                });
            });
            
            // Auto proportion buttons
            document.querySelectorAll('.rem-auto-btn, .rem-auto-btn-small').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const property = e.target.dataset.property;
                    this.applyAutoProportions(property);
                });
            });
            
            // Color reset buttons
            document.querySelectorAll('.rem-color-reset').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const target = e.target.dataset.target;
                    const colorInput = document.getElementById(`${target}-${bp}`);
                    const hexInput = document.getElementById(`${target}-hex-${bp}`);
                    if (colorInput && hexInput) {
                        colorInput.value = '';
                        hexInput.value = '';
                        this.applyPreviewStyles();
                    }
                });
            });
            
            // Live preview per tutti gli input
            const inputs = document.querySelectorAll(`#rem-breakpoint-content input, #rem-breakpoint-content select`);
            inputs.forEach(input => {
                input.addEventListener('input', () => this.applyPreviewStyles());
                input.addEventListener('change', () => this.applyPreviewStyles());
            });
        }
        
        /**
         * NUOVO: Applica allineamento universale all'elemento
         */
        applyAlignment(alignment) {
            if (!this.selectedElement) return;
            
            const element = this.selectedElement;
            const parent = element.parentElement;
            
            // Rimuovi classi di allineamento esistenti
            element.classList.remove('rem-align-left', 'rem-align-center', 'rem-align-right', 'rem-align-justify');
            
            switch (alignment) {
                case 'left':
                    element.style.marginLeft = '0';
                    element.style.marginRight = 'auto';
                    element.classList.add('rem-align-left');
                    break;
                case 'center':
                    element.style.marginLeft = 'auto';
                    element.style.marginRight = 'auto';
                    element.classList.add('rem-align-center');
                    break;
                case 'right':
                    element.style.marginLeft = 'auto';
                    element.style.marginRight = '0';
                    element.classList.add('rem-align-right');
                    break;
                case 'justify':
                    element.style.width = '100%';
                    element.classList.add('rem-align-justify');
                    break;
            }
            
            this.applyPreviewStyles();
        }
        
        /**
         * NUOVO: Applica proporzioni automatiche per una proprietà specifica
         */
        applyAutoProportions(property) {
            if (!this.autoProportions) {
                this.showNotification('Proporzioni automatiche disabilitate', 'warning');
                return;
            }
            
            const sourceBreakpoint = this.currentBreakpoint;
            const sourceValue = this.getPropertyValue(property, sourceBreakpoint);
            
            if (!sourceValue || !sourceValue.value) {
                this.showNotification('Nessun valore da cui calcolare proporzioni', 'warning');
                return;
            }
            
            // Calcola proporzioni per altri breakpoint
            Object.keys(this.deviceProportions).forEach(targetBreakpoint => {
                if (targetBreakpoint !== sourceBreakpoint) {
                    const proportionalValue = this.calculateProportionalValue(
                        sourceValue, 
                        sourceBreakpoint, 
                        targetBreakpoint, 
                        property
                    );
                    
                    if (proportionalValue) {
                        this.setPropertyValue(property, targetBreakpoint, proportionalValue);
                    }
                }
            });
            
            this.showNotification(`Proporzioni applicate per ${property}`, 'success');
        }
        
        /**
         * NUOVO: Calcola valore proporzionale tra dispositivi
         */
        calculateProportionalValue(sourceValue, sourceBreakpoint, targetBreakpoint, property) {
            const sourceDevice = this.deviceProportions[sourceBreakpoint];
            const targetDevice = this.deviceProportions[targetBreakpoint];
            
            if (!sourceDevice || !targetDevice) return null;
            
            let ratio;
            
            // Determina il ratio in base al tipo di proprietà
            if (['width', 'position-x', 'margin-left', 'margin-right', 'padding-left', 'padding-right'].includes(property)) {
                // Usa ratio larghezza
                ratio = targetDevice.width / sourceDevice.width;
            } else if (['height', 'position-y', 'margin-top', 'margin-bottom', 'padding-top', 'padding-bottom'].includes(property)) {
                // Usa ratio altezza
                ratio = targetDevice.height / sourceDevice.height;
            } else if (['font-size'].includes(property)) {
                // Per font, usa una media pesata
                ratio = Math.sqrt((targetDevice.width * targetDevice.height) / (sourceDevice.width * sourceDevice.height));
            } else {
                // Default: usa ratio larghezza
                ratio = targetDevice.width / sourceDevice.width;
            }
            
            const newValue = Math.round(sourceValue.value * ratio * 100) / 100;
            
            return {
                value: newValue,
                unit: sourceValue.unit
            };
        }
        
        /**
         * NUOVO: Ottiene valore di una proprietà per un breakpoint
         */
        getPropertyValue(property, breakpoint) {
            let valueInput, unitSelect;
            
            if (property.includes('-')) {
                // Proprietà composite (es. position-x)
                valueInput = document.getElementById(`${property}-${breakpoint}`);
                unitSelect = document.getElementById(`${property}-unit-${breakpoint}`);
            } else {
                // Proprietà semplici
                valueInput = document.getElementById(`${property}-${breakpoint}`);
                unitSelect = document.getElementById(`${property.replace('_', '-')}-unit-${breakpoint}`) || 
                           document.getElementById(`${property}-unit-${breakpoint}`);
            }
            
            if (!valueInput || !valueInput.value) return null;
            
            return {
                value: parseFloat(valueInput.value),
                unit: unitSelect ? unitSelect.value : 'px'
            };
        }
        
        /**
         * NUOVO: Imposta valore di una proprietà per un breakpoint
         */
        setPropertyValue(property, breakpoint, valueObj) {
            let valueInput, unitSelect;
            
            if (property.includes('-')) {
                valueInput = document.getElementById(`${property}-${breakpoint}`);
                unitSelect = document.getElementById(`${property}-unit-${breakpoint}`);
            } else {
                valueInput = document.getElementById(`${property}-${breakpoint}`);
                unitSelect = document.getElementById(`${property.replace('_', '-')}-unit-${breakpoint}`) || 
                           document.getElementById(`${property}-unit-${breakpoint}`);
            }
            
            if (valueInput) {
                valueInput.value = valueObj.value;
            }
            
            if (unitSelect) {
                unitSelect.value = valueObj.unit;
            }
        }
        
        /**
         * Applica gli stili in anteprima - VERSIONE CORRETTA
         */
        applyPreviewStyles() {
            if (!this.selectedElement) return;
            
            const rules = this.collectCurrentRules();
            const css = this.generateCSSFromRules(rules[this.currentBreakpoint] || {});
            
            // Applica gli stili direttamente all'elemento per anteprima
            this.selectedElement.setAttribute('style', css);
        }
        
        /**
         * Raccoglie tutte le regole dai controlli - VERSIONE CORRETTA
         */
        collectCurrentRules() {
            const rules = {};
            const bp = this.currentBreakpoint;
            
            if (!document.getElementById(`font-size-${bp}`)) return rules;
            
            rules[bp] = {};
            
            // Posizione
            const position = document.getElementById(`position-${bp}`);
            if (position && position.value) {
                rules[bp].position = position.value;
                
                // Coordinate X e Y
                const posX = document.getElementById(`position-x-${bp}`);
                const posXUnit = document.getElementById(`position-x-unit-${bp}`);
                if (posX && posX.value) {
                    rules[bp].position_x = { 
                        value: parseFloat(posX.value), 
                        unit: posXUnit ? posXUnit.value : 'px' 
                    };
                }
                
                const posY = document.getElementById(`position-y-${bp}`);
                const posYUnit = document.getElementById(`position-y-unit-${bp}`);
                if (posY && posY.value) {
                    rules[bp].position_y = { 
                        value: parseFloat(posY.value), 
                        unit: posYUnit ? posYUnit.value : 'px' 
                    };
                }
            }
            
            // Font
            const fontSize = document.getElementById(`font-size-${bp}`);
            const fontUnit = document.getElementById(`font-unit-${bp}`);
            if (fontSize && fontSize.value) {
                rules[bp].font_size = { 
                    value: parseFloat(fontSize.value), 
                    unit: fontUnit ? fontUnit.value : 'px' 
                };
            }
            
            const fontFamily = document.getElementById(`font-family-${bp}`);
            if (fontFamily && fontFamily.value) {
                rules[bp].font_family = fontFamily.value;
            }
            
            const fontWeight = document.getElementById(`font-weight-${bp}`);
            if (fontWeight && fontWeight.value) {
                rules[bp].font_weight = fontWeight.value;
            }
            
            // Text alignment
            const activeTextAlign = document.querySelector('.rem-text-align-btn.active');
            if (activeTextAlign) {
                rules[bp].text_align = activeTextAlign.dataset.align;
            }
            
            // Element alignment
            const activeAlign = document.querySelector('.rem-align-btn.active');
            if (activeAlign) {
                rules[bp].element_align = activeAlign.dataset.align;
            }
            
            // Colors
            const textColorHex = document.getElementById(`text-color-hex-${bp}`);
            if (textColorHex && textColorHex.value) {
                rules[bp].text_color = textColorHex.value;
            }
            
            const bgColorHex = document.getElementById(`bg-color-hex-${bp}`);
            if (bgColorHex && bgColorHex.value) {
                rules[bp].background_color = bgColorHex.value;
            }
            
            const borderColorHex = document.getElementById(`border-color-hex-${bp}`);
            if (borderColorHex && borderColorHex.value) {
                rules[bp].border_color = borderColorHex.value;
            }
            
            // Layout
            const display = document.getElementById(`display-${bp}`);
            if (display && display.value) {
                rules[bp].display = display.value;
                
                if (display.value === 'flex') {
                    const flexDirection = document.getElementById(`flex-direction-${bp}`);
                    const justifyContent = document.getElementById(`justify-content-${bp}`);
                    const alignItems = document.getElementById(`align-items-${bp}`);
                    
                    if (flexDirection && flexDirection.value) rules[bp].flex_direction = flexDirection.value;
                    if (justifyContent && justifyContent.value) rules[bp].justify_content = justifyContent.value;
                    if (alignItems && alignItems.value) rules[bp].align_items = alignItems.value;
                }
            }
            
            // Dimensions
            const width = document.getElementById(`width-${bp}`);
            const widthUnit = document.getElementById(`width-unit-${bp}`);
            if (width && width.value) {
                rules[bp].width = { 
                    value: parseFloat(width.value), 
                    unit: widthUnit ? widthUnit.value : 'px' 
                };
            }
            
            const height = document.getElementById(`height-${bp}`);
            const heightUnit = document.getElementById(`height-unit-${bp}`);
            if (height && height.value) {
                rules[bp].height = { 
                    value: parseFloat(height.value), 
                    unit: heightUnit ? heightUnit.value : 'px' 
                };
            }
            
            // Spacing
            const spacingUnit = document.getElementById(`spacing-unit-${bp}`);
            const unit = spacingUnit ? spacingUnit.value : 'px';
            
            ['margin', 'padding'].forEach(type => {
                ['top', 'right', 'bottom', 'left'].forEach(side => {
                    const input = document.getElementById(`${type}-${side}-${bp}`);
                    if (input && input.value) {
                        rules[bp][`${type}_${side}`] = { 
                            value: parseFloat(input.value), 
                            unit: unit 
                        };
                    }
                });
            });
            
            // Effects
            const opacity = document.getElementById(`opacity-${bp}`);
            if (opacity && opacity.value && opacity.value !== '1') {
                rules[bp].opacity = parseFloat(opacity.value);
            }
            
            const boxShadow = document.getElementById(`box-shadow-${bp}`);
            if (boxShadow && boxShadow.value) {
                rules[bp].box_shadow = boxShadow.value;
            }
            
            const borderRadius = document.getElementById(`border-radius-${bp}`);
            const borderRadiusUnit = document.getElementById(`border-radius-unit-${bp}`);
            if (borderRadius && borderRadius.value) {
                rules[bp].border_radius = { 
                    value: parseFloat(borderRadius.value), 
                    unit: borderRadiusUnit ? borderRadiusUnit.value : 'px' 
                };
            }
            
            return rules;
        }
        
        /**
         * Genera CSS dalle regole - VERSIONE CORRETTA
         */
        generateCSSFromRules(rules) {
            const css = [];
            
            // Posizione
            if (rules.position) {
                css.push(`position: ${rules.position}`);
                
                if (rules.position_x) {
                    css.push(`left: ${rules.position_x.value}${rules.position_x.unit}`);
                }
                if (rules.position_y) {
                    css.push(`top: ${rules.position_y.value}${rules.position_y.unit}`);
                }
            }
            
            // Font
            if (rules.font_size) {
                css.push(`font-size: ${rules.font_size.value}${rules.font_size.unit}`);
            }
            if (rules.font_family) {
                css.push(`font-family: ${rules.font_family}`);
            }
            if (rules.font_weight) {
                css.push(`font-weight: ${rules.font_weight}`);
            }
            if (rules.text_align) {
                css.push(`text-align: ${rules.text_align}`);
            }
            
            // Element alignment
            if (rules.element_align) {
                switch (rules.element_align) {
                    case 'left':
                        css.push('margin-left: 0', 'margin-right: auto');
                        break;
                    case 'center':
                        css.push('margin-left: auto', 'margin-right: auto');
                        break;
                    case 'right':
                        css.push('margin-left: auto', 'margin-right: 0');
                        break;
                    case 'justify':
                        css.push('width: 100%');
                        break;
                }
            }
            
            // Colors
            if (rules.text_color) {
                css.push(`color: ${rules.text_color}`);
            }
            if (rules.background_color) {
                css.push(`background-color: ${rules.background_color}`);
            }
            if (rules.border_color) {
                css.push(`border-color: ${rules.border_color}`);
            }
            
            // Display and layout
            if (rules.display) {
                css.push(`display: ${rules.display}`);
                
                if (rules.display === 'flex') {
                    if (rules.flex_direction) {
                        css.push(`flex-direction: ${rules.flex_direction}`);
                    }
                    if (rules.justify_content) {
                        css.push(`justify-content: ${rules.justify_content}`);
                    }
                    if (rules.align_items) {
                        css.push(`align-items: ${rules.align_items}`);
                    }
                }
            }
            
            // Dimensions
            if (rules.width) {
                css.push(`width: ${rules.width.value}${rules.width.unit}`);
            }
            if (rules.height) {
                css.push(`height: ${rules.height.value}${rules.height.unit}`);
            }
            
            // Spacing
            ['margin', 'padding'].forEach(type => {
                ['top', 'right', 'bottom', 'left'].forEach(side => {
                    const prop = `${type}_${side}`;
                    if (rules[prop]) {
                        css.push(`${type}-${side}: ${rules[prop].value}${rules[prop].unit}`);
                    }
                });
            });
            
            // Effects
            if (rules.opacity) {
                css.push(`opacity: ${rules.opacity}`);
            }
            if (rules.box_shadow) {
                css.push(`box-shadow: ${rules.box_shadow}`);
            }
            if (rules.border_radius) {
                css.push(`border-radius: ${rules.border_radius.value}${rules.border_radius.unit}`);
            }
            
            return css.join('; ');
        }
        
        /**
         * Bind degli eventi principali - VERSIONE CORRETTA
         */
        bindEvents() {
            const toggleBtn = document.getElementById('rem-toggle-btn');
            const modal = document.getElementById('rem-modal');
            const closeBtn = document.getElementById('rem-close');
            const cancelBtn = document.getElementById('rem-cancel');
            const saveBtn = document.getElementById('rem-save');
            
            // Toggle editor
            toggleBtn.addEventListener('click', () => this.toggleEditor());
            
            // Close modal
            [closeBtn, cancelBtn].forEach(btn => {
                if (btn) {
                    btn.addEventListener('click', () => this.closeModal());
                }
            });
            
            // Save rules - CORRETTO
            if (saveBtn) {
                saveBtn.addEventListener('click', () => this.saveRules());
            }
            
            // Auto proportions toggle
            document.addEventListener('click', (e) => {
                if (e.target.id === 'rem-auto-proportions') {
                    this.autoProportions = !this.autoProportions;
                    e.target.classList.toggle('active', this.autoProportions);
                    this.showNotification(
                        `Proporzioni automatiche ${this.autoProportions ? 'abilitate' : 'disabilitate'}`, 
                        'info'
                    );
                }
                
                if (e.target.id === 'rem-apply-auto-proportions') {
                    this.applyAutoProportionsToAll();
                }
            });
            
            // Breakpoint tabs
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('rem-tab-btn')) {
                    this.switchBreakpoint(e.target.dataset.breakpoint);
                }
                
                if (e.target.classList.contains('rem-selector-btn')) {
                    this.switchSelectorMode(e.target.dataset.type);
                }
                
                if (e.target.classList.contains('rem-child-option')) {
                    this.selectChildElement(e.target);
                }
            });
            
            // Copy selector
            document.addEventListener('click', (e) => {
                if (e.target.id === 'rem-copy-selector') {
                    this.copySelector();
                }
            });
            
            // Preview and reset
            document.addEventListener('click', (e) => {
                if (e.target.id === 'rem-preview-changes') {
                    this.previewChanges();
                }
                if (e.target.id === 'rem-reset-element') {
                    this.resetElement();
                }
            });
            
            // Element selection
            document.addEventListener('click', (e) => {
                if (this.isActive && 
                    !modal.contains(e.target) && 
                    e.target.id !== 'rem-toggle-btn') {
                    e.preventDefault();
                    e.stopPropagation();
                    this.selectElement(e.target);
                }
            });
            
            // Hover effects
            document.addEventListener('mouseover', (e) => {
                if (this.isActive && 
                    !modal.contains(e.target) && 
                    e.target.id !== 'rem-toggle-btn') {
                    this.highlightElement(e.target);
                }
            });
            
            document.addEventListener('mouseout', (e) => {
                if (this.isActive) {
                    this.removeHighlight(e.target);
                }
            });
        }
        
        /**
         * NUOVO: Applica proporzioni automatiche a tutte le proprietà
         */
        applyAutoProportionsToAll() {
            if (!this.autoProportions) {
                this.showNotification('Proporzioni automatiche disabilitate', 'warning');
                return;
            }
            
            const properties = [
                'font-size', 'width', 'height', 'position-x', 'position-y',
                'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
                'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
                'border-radius'
            ];
            
            let appliedCount = 0;
            
            properties.forEach(property => {
                const sourceValue = this.getPropertyValue(property, this.currentBreakpoint);
                if (sourceValue && sourceValue.value) {
                    this.applyAutoProportions(property);
                    appliedCount++;
                }
            });
            
            this.showNotification(`Proporzioni applicate a ${appliedCount} proprietà`, 'success');
        }
        
        /**
         * Sincronizza color picker e input hex
         */
        syncColorInputs(breakpoint) {
            const colorTypes = ['text-color', 'bg-color', 'border-color'];
            
            colorTypes.forEach(type => {
                const colorInput = document.getElementById(`${type}-${breakpoint}`);
                const hexInput = document.getElementById(`${type}-hex-${breakpoint}`);
                
                if (colorInput && hexInput) {
                    colorInput.addEventListener('input', (e) => {
                        hexInput.value = e.target.value;
                        this.applyPreviewStyles();
                    });
                    
                    hexInput.addEventListener('input', (e) => {
                        if (/^#([0-9A-F]{3}){1,2}$/i.test(e.target.value)) {
                            colorInput.value = e.target.value;
                            this.applyPreviewStyles();
                        }
                    });
                }
            });
        }
        
        /**
         * Toggle dell'editor
         */
        toggleEditor() {
            this.isActive = !this.isActive;
            const toggleBtn = document.getElementById('rem-toggle-btn');
            
            if (this.isActive) {
                document.body.classList.add('rem-selecting');
                toggleBtn.classList.add('active');
                toggleBtn.style.background = '#dc3232';
                toggleBtn.innerHTML = '❌';
            } else {
                document.body.classList.remove('rem-selecting');
                toggleBtn.classList.remove('active');
                toggleBtn.style.background = '#0073aa';
                toggleBtn.innerHTML = '📱';
                this.clearSelection();
            }
        }
        
        /**
         * Selezione elemento
         */
        selectElement(element) {
            this.clearSelection();
            this.selectedElement = element;
            this.currentSelector = this.generateSelector(element);
            this.childElements = this.getElementChildren(element);
            
            element.classList.add('rem-selected');
            this.updateElementInfo();
            this.showModal();
        }
        
        /**
         * Genera selettore CSS
         */
        generateSelector(element) {
            if (element.id) {
                return '#' + element.id;
            } else if (element.className) {
                const classes = element.className.split(' ')
                    .filter(c => c && !c.startsWith('rem-') && !c.startsWith('wp-') && c !== 'active');
                if (classes.length > 0) {
                    return '.' + classes[0];
                }
            }
            
            // Genera selettore basato sul percorso
            return this.getElementPath(element);
        }
        
        /**
         * Ottiene il percorso dell'elemento nel DOM
         */
        getElementPath(element) {
            const path = [];
            let current = element;
            
            while (current && current.nodeType === Node.ELEMENT_NODE && current !== document.body) {
                let selector = current.nodeName.toLowerCase();
                
                if (current.id) {
                    selector = '#' + current.id;
                    path.unshift(selector);
                    break;
                } else if (current.className) {
                    const classes = current.className.split(' ')
                        .filter(c => c && !c.startsWith('rem-') && !c.startsWith('wp-'));
                    if (classes.length > 0) {
                        selector += '.' + classes[0];
                    }
                }
                
                // Aggiungi nth-of-type se necessario
                let siblings = Array.from(current.parentNode?.children || []);
                siblings = siblings.filter(sibling => sibling.nodeName === current.nodeName);
                if (siblings.length > 1) {
                    const index = siblings.indexOf(current) + 1;
                    selector += `:nth-of-type(${index})`;
                }
                
                path.unshift(selector);
                current = current.parentNode;
            }
            
            return path.join(' > ');
        }
        
        /**
         * Ottiene elementi figli
         */
        getElementChildren(element) {
            const children = [];
            Array.from(element.children).forEach((child, index) => {
                if (child.nodeType === Node.ELEMENT_NODE) {
                    children.push({
                        element: child,
                        selector: this.generateSelector(child),
                        tagName: child.tagName.toLowerCase(),
                        text: this.getElementText(child),
                        index: index
                    });
                }
            });
            return children;
        }
        
        /**
         * Ottiene testo rappresentativo dell'elemento
         */
        getElementText(element) {
            let text = '';
            
            // Prova a ottenere testo significativo
            if (element.alt) text = element.alt;
            else if (element.title) text = element.title;
            else if (element.textContent) {
                text = element.textContent.trim().substring(0, 30);
                if (element.textContent.length > 30) text += '...';
            }
            
            if (!text) text = `<${element.tagName.toLowerCase()}>`;
            
            return text;
        }
        
        /**
         * Aggiorna info elemento
         */
        updateElementInfo() {
            const selectorDisplay = document.getElementById('rem-current-selector');
            if (selectorDisplay) {
                selectorDisplay.textContent = this.currentSelector;
            }
            
            this.updateChildrenList();
        }
        
        /**
         * Aggiorna lista elementi figli
         */
        updateChildrenList() {
            const container = document.querySelector('.rem-children-list');
            if (!container) return;
            
            container.innerHTML = this.childElements.map((child, index) => `
                <div class="rem-child-option" data-index="${index}" data-selector="${child.selector}">
                    <span class="rem-child-tag">${child.tagName}</span>
                    <span class="rem-child-text">${child.text}</span>
                    <small class="rem-child-selector">${child.selector}</small>
                </div>
            `).join('');
        }
        
        /**
         * Switch modalità selettore
         */
        switchSelectorMode(mode) {
            document.querySelectorAll('.rem-selector-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-type="${mode}"]`).classList.add('active');
            
            this.selectorMode = mode;
            const childrenContainer = document.getElementById('rem-children-container');
            
            switch(mode) {
                case 'self':
                    childrenContainer.style.display = 'none';
                    this.currentSelector = this.generateSelector(this.selectedElement);
                    break;
                case 'parent':
                    childrenContainer.style.display = 'none';
                    if (this.selectedElement.parentElement) {
                        this.currentSelector = this.generateSelector(this.selectedElement.parentElement);
                    }
                    break;
                case 'children':
                    childrenContainer.style.display = 'block';
                    break;
            }
            
            this.updateElementInfo();
        }
        
        /**
         * Seleziona elemento figlio
         */
        selectChildElement(childOption) {
            const index = parseInt(childOption.dataset.index);
            const child = this.childElements[index];
            
            if (child) {
                this.currentSelector = child.selector;
                document.getElementById('rem-current-selector').textContent = this.currentSelector;
                
                // Evidenzia l'elemento figlio
                this.clearSelection();
                child.element.classList.add('rem-selected');
            }
        }
        
        /**
         * Switch breakpoint
         */
        switchBreakpoint(breakpoint) {
            document.querySelectorAll('.rem-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[data-breakpoint="${breakpoint}"]`).classList.add('active');
            
            this.currentBreakpoint = breakpoint;
            this.showBreakpointControls();
            this.loadBreakpointData();
        }
        
        /**
         * Carica dati per il breakpoint
         */
        loadBreakpointData() {
            // Qui caricheresti i dati esistenti per il breakpoint corrente
            // e popoleresti i controlli
        }
        
        /**
         * Evidenzia elemento
         */
        highlightElement(element) {
            if (this.hoveredElement) {
                this.hoveredElement.classList.remove('rem-highlight');
            }
            
            element.classList.add('rem-highlight');
            this.hoveredElement = element;
        }
        
        /**
         * Rimuovi evidenziazione
         */
        removeHighlight(element) {
            element.classList.remove('rem-highlight');
            if (this.hoveredElement === element) {
                this.hoveredElement = null;
            }
        }
        
        /**
         * Pulisci selezione
         */
        clearSelection() {
            document.querySelectorAll('.rem-selected, .rem-highlight').forEach(el => {
                el.classList.remove('rem-selected', 'rem-highlight');
            });
            this.selectedElement = null;
            this.hoveredElement = null;
        }
        
        /**
         * Mostra modal
         */
        showModal() {
            const modal = document.getElementById('rem-modal');
            modal.style.display = 'block';
            this.showBreakpointControls();
        }
        
        /**
         * Chiudi modal
         */
        closeModal() {
            const modal = document.getElementById('rem-modal');
            modal.style.display = 'none';
            this.clearSelection();
        }
        
        /**
         * Copia selettore
         */
        copySelector() {
            if (this.currentSelector) {
                navigator.clipboard.writeText(this.currentSelector).then(() => {
                    this.showNotification('Selettore copiato negli appunti!', 'success');
                });
            }
        }
        
        /**
         * Anteprima modifiche
         */
        previewChanges() {
            this.applyPreviewStyles();
            this.showNotification('Anteprima applicata', 'info');
        }
        
        /**
         * Reset elemento
         */
        resetElement() {
            if (this.selectedElement) {
                this.selectedElement.style.cssText = '';
                this.showNotification('Elemento resettato', 'info');
            }
        }
        
        /**
         * Salva regole - VERSIONE CORRETTA
         */
        saveRules() {
            if (!this.selectedElement || !this.currentSelector) {
                this.showNotification('Nessun elemento selezionato', 'error');
                return;
            }
            
            const rules = this.collectCurrentRules();
            const scope = document.getElementById('rem-scope').value;
            
            const ruleData = {
                selector: this.currentSelector,
                element_id: this.selectedElement.id || '',
                element_class: this.selectedElement.className || '',
                scope: scope,
                post_id: (typeof rem_ajax !== 'undefined' && rem_ajax.current_post_id) ? 
                         rem_ajax.current_post_id : 0,
                rules: rules
            };
            
            // Debug log
            console.log('Saving rule data:', ruleData);
            
            // Verifica che rem_ajax sia definito
            if (typeof rem_ajax === 'undefined') {
                this.showNotification('Errore: configurazione AJAX mancante', 'error');
                return;
            }
            
            // Mostra indicatore di caricamento
            const saveBtn = document.getElementById('rem-save');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = '💾 Salvando...';
            saveBtn.disabled = true;
            
            // Invia via AJAX
            fetch(rem_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'rem_save_rule',
                    nonce: rem_ajax.nonce,
                    rule_data: JSON.stringify(ruleData)
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                
                if (data.success) {
                    this.showNotification('Regole salvate con successo!', 'success');
                    this.closeModal();
                    
                    // Applica le regole permanentemente
                    this.applyPermanentStyles();
                } else {
                    this.showNotification('Errore nel salvataggio: ' + (data.data || 'Errore sconosciuto'), 'error');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                this.showNotification('Errore di connessione: ' + error.message, 'error');
            })
            .finally(() => {
                // Ripristina il pulsante
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        }
        
        /**
         * NUOVO: Applica stili permanentemente
         */
        applyPermanentStyles() {
            if (!this.selectedElement) return;
            
            // Rimuovi stili temporanei
            this.selectedElement.removeAttribute('style');
            
            // Aggiungi una classe per identificare l'elemento modificato
            this.selectedElement.classList.add('rem-styled-element');
            
            // Trigger per aggiornare il CSS nella pagina
            this.refreshPageCSS();
        }
        
        /**
         * NUOVO: Aggiorna CSS della pagina
         */
        refreshPageCSS() {
            // Rimuovi foglio di stile esistente se presente
            const existingStyle = document.getElementById('rem-dynamic-css');
            if (existingStyle) {
                existingStyle.remove();
            }
            
            // Ricarica il CSS custom dalla fonte
            if (typeof rem_ajax !== 'undefined') {
                fetch(rem_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'rem_get_css',
                        nonce: rem_ajax.nonce,
                        post_id: rem_ajax.current_post_id || 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.css) {
                        const style = document.createElement('style');
                        style.id = 'rem-dynamic-css';
                        style.textContent = data.data.css;
                        document.head.appendChild(style);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing CSS:', error);
                });
            }
        }
        
        /**
         * Carica regole esistenti
         */
        loadExistingRules() {
            // Implementa il caricamento delle regole esistenti
            if (typeof rem_ajax !== 'undefined') {
                fetch(rem_ajax.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'rem_get_rules',
                        nonce: rem_ajax.nonce,
                        post_id: rem_ajax.current_post_id || 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.existingRules = data.data || [];
                        console.log('Loaded existing rules:', this.existingRules);
                    }
                })
                .catch(error => {
                    console.error('Error loading existing rules:', error);
                });
            }
        }
        
        /**
         * Mostra notifica
         */
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `rem-notification rem-notification-${type}`;
            notification.innerHTML = `
                <span class="rem-notification-icon">${this.getNotificationIcon(type)}</span>
                <span class="rem-notification-message">${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('rem-notification-show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('rem-notification-show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        /**
         * Ottieni icona notifica
         */
        getNotificationIcon(type) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            return icons[type] || icons.info;
        }
        
        /**
         * Aggiungi CSS personalizzato
         */
        addCustomCSS() {
            const style = document.createElement('style');
            style.textContent = this.getCustomCSS();
            document.head.appendChild(style);
        }
        
        /**
         * CSS personalizzato per l'interfaccia - VERSIONE ESTESA
         */
        getCustomCSS() {
            return `
                /* Responsive Element Manager Styles - VERSIONE CORRETTA */
                #rem-toggle-btn {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    width: 60px;
                    height: 60px;
                    background: #0073aa;
                    color: white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    z-index: 999999;
                    font-size: 24px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    transition: all 0.3s ease;
                    border: none;
                    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                }
                
                #rem-toggle-btn:hover {
                    background: #005177;
                    transform: scale(1.05);
                }
                
                #rem-toggle-btn.active {
                    background: #dc3232;
                    animation: pulse 1.5s infinite;
                }
                
                @keyframes pulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                
                /* Modal */
                #rem-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 1000000;
                    overflow-y: auto;
                    padding: 20px;
                }
                
                .rem-modal-content {
                    background: white;
                    width: 100%;
                    max-width: 1400px;
                    margin: 0 auto;
                    border-radius: 12px;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                    animation: slideIn 0.3s ease;
                }
                
                @keyframes slideIn {
                    from { opacity: 0; transform: translateY(-30px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                .rem-modal-header {
                    padding: 20px 30px;
                    border-bottom: 1px solid #e0e0e0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border-radius: 12px 12px 0 0;
                }
                
                .rem-modal-header h3 {
                    margin: 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .rem-header-controls {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                
                .rem-toggle-btn {
                    background: rgba(255,255,255,0.2);
                    color: white;
                    border: 1px solid rgba(255,255,255,0.3);
                    padding: 6px 12px;
                    border-radius: 20px;
                    cursor: pointer;
                    font-size: 12px;
                    font-weight: 500;
                    transition: all 0.3s;
                }
                
                .rem-toggle-btn.active {
                    background: rgba(255,255,255,0.3);
                    border-color: rgba(255,255,255,0.5);
                }
                
                #rem-close {
                    font-size: 28px;
                    cursor: pointer;
                    color: rgba(255,255,255,0.8);
                    transition: color 0.2s;
                    background: none;
                    border: none;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                #rem-close:hover {
                    color: white;
                }
                
                .rem-modal-body {
                    padding: 30px;
                    max-height: 70vh;
                    overflow-y: auto;
                }
                
                /* Selezione elementi */
                .rem-element-info {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 25px;
                    border-left: 4px solid #0073aa;
                }
                
                .rem-selector-section h4 {
                    margin: 0 0 15px 0;
                    color: #333;
                    font-size: 16px;
                }
                
                .rem-selector-options {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 15px;
                    flex-wrap: wrap;
                }
                
                .rem-selector-btn {
                    background: white;
                    border: 2px solid #ddd;
                    padding: 8px 16px;
                    border-radius: 20px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.3s;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                .rem-selector-btn:hover {
                    border-color: #0073aa;
                    color: #0073aa;
                }
                
                .rem-selector-btn.active {
                    background: #0073aa;
                    border-color: #0073aa;
                    color: white;
                }
                
                #rem-current-selector-display {
                    background: white;
                    padding: 15px;
                    border-radius: 6px;
                    border: 1px solid #ddd;
                    font-family: monospace;
                    font-size: 14px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin-bottom: 15px;
                }
                
                #rem-current-selector {
                    background: #f1f3f4;
                    padding: 4px 8px;
                    border-radius: 4px;
                    color: #d73502;
                }
                
                .rem-copy-btn {
                    background: #f8f9fa;
                    border: 1px solid #ddd;
                    padding: 4px 8px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    transition: all 0.2s;
                }
                
                .rem-copy-btn:hover {
                    background: #e9ecef;
                }
                
                .rem-children-list {
                    max-height: 200px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    background: white;
                }
                
                .rem-child-option {
                    padding: 12px;
                    cursor: pointer;
                    border-bottom: 1px solid #eee;
                    transition: background 0.2s;
                }
                
                .rem-child-option:hover {
                    background: #f8f9fa;
                }
                
                .rem-child-option:last-child {
                    border-bottom: none;
                }
                
                .rem-child-tag {
                    font-weight: 600;
                    color: #0073aa;
                    text-transform: uppercase;
                    font-size: 12px;
                }
                
                .rem-child-text {
                    margin-left: 10px;
                    color: #666;
                }
                
                .rem-child-selector {
                    display: block;
                    font-family: monospace;
                    color: #999;
                    font-size: 11px;
                    margin-top: 4px;
                }
                
                /* Scope */
                .rem-scope-section {
                    margin-bottom: 25px;
                }
                
                .rem-scope-section label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 600;
                    color: #333;
                }
                
                #rem-scope {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    font-size: 14px;
                    background: white;
                }
                
                /* Breakpoints */
                .rem-breakpoint-section {
                    margin-bottom: 25px;
                }
                
                .rem-breakpoint-tabs {
                    display: flex;
                    border-bottom: 2px solid #e0e0e0;
                    margin-bottom: 25px;
                    gap: 5px;
                }
                
                .rem-tab-btn {
                    background: none;
                    border: none;
                    padding: 15px 20px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    color: #666;
                    border-bottom: 3px solid transparent;
                    transition: all 0.3s;
                    text-align: center;
                    border-radius: 6px 6px 0 0;
                }
                
                .rem-tab-btn:hover {
                    background: #f8f9fa;
                    color: #333;
                }
                
                .rem-tab-btn.active {
                    color: #0073aa;
                    border-bottom-color: #0073aa;
                    background: #f8f9fa;
                }
                
                .rem-tab-btn small {
                    display: block;
                    font-size: 11px;
                    opacity: 0.7;
                }
                
                /* Controls */
                .rem-controls-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                    gap: 20px;
                }
                
                .rem-control-group {
                    border: 1px solid #e0e0e0;
                    border-radius: 8px;
                    padding: 20px;
                    background: #fafafa;
                }
                
                .rem-control-group h4 {
                    margin: 0 0 15px 0;
                    font-size: 14px;
                    font-weight: 600;
                    color: #333;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                
                .rem-form-group {
                    margin-bottom: 15px;
                }
                
                .rem-form-group label {
                    display: block;
                    margin-bottom: 6px;
                    font-weight: 500;
                    color: #555;
                    font-size: 13px;
                }
                
                .rem-input-group {
                    display: flex;
                    gap: 5px;
                    align-items: center;
                }
                
                .rem-input-group input {
                    flex: 2;
                }
                
                .rem-input-group select {
                    flex: 1;
                    min-width: 80px;
                }
                
                .rem-auto-btn {
                    background: #0073aa;
                    color: white;
                    border: none;
                    padding: 6px 8px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    transition: all 0.2s;
                    flex-shrink: 0;
                }
                
                .rem-auto-btn:hover {
                    background: #005177;
                }
                
                .rem-form-group input,
                .rem-form-group select {
                    width: 100%;
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    transition: border-color 0.2s, box-shadow 0.2s;
                }
                
                .rem-form-group input:focus,
                .rem-form-group select:focus {
                    outline: none;
                    border-color: #0073aa;
                    box-shadow: 0 0 0 2px rgba(0,115,170,0.1);
                }
                
                /* Position controls */
                .rem-position-controls {
                    background: #e8f4f8;
                    padding: 15px;
                    border-radius: 6px;
                    margin-top: 10px;
                    border-left: 3px solid #0073aa;
                }
                
                .rem-form-row {
                    display: flex;
                    gap: 15px;
                }
                
                .rem-form-row .rem-form-group {
                    flex: 1;
                    margin-bottom: 0;
                }
                
                /* Alignment controls */
                .rem-alignment-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 4px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    overflow: hidden;
                }
                
                .rem-align-btn {
                    background: white;
                    border: none;
                    padding: 10px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: all 0.2s;
                    border-right: 1px solid #ddd;
                }
                
                .rem-align-btn:last-child {
                    border-right: none;
                }
                
                .rem-align-btn:hover {
                    background: #f8f9fa;
                }
                
                .rem-align-btn.active {
                    background: #0073aa;
                    color: white;
                }
                
                .rem-alignment-buttons {
                    display: flex;
                    gap: 4px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    overflow: hidden;
                }
                
                .rem-text-align-btn {
                    background: white;
                    border: none;
                    padding: 8px 12px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: all 0.2s;
                    flex: 1;
                }
                
                .rem-text-align-btn:hover {
                    background: #f8f9fa;
                }
                
                .rem-text-align-btn.active {
                    background: #0073aa;
                    color: white;
                }
                
                /* Color controls */
                .rem-color-control {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                
                .rem-color-input {
                    width: 50px !important;
                    height: 38px;
                    padding: 2px !important;
                    border-radius: 6px;
                    cursor: pointer;
                }
                
                .rem-hex-input {
                    flex: 1 !important;
                    font-family: monospace;
                }
                
                .rem-color-reset {
                    background: #f8f9fa;
                    border: 1px solid #ddd;
                    padding: 8px 10px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    transition: all 0.2s;
                }
                
                .rem-color-reset:hover {
                    background: #e9ecef;
                }
                
                /* Flex controls */
                .rem-flex-controls {
                    background: #e8f4f8;
                    padding: 15px;
                    border-radius: 6px;
                    margin-top: 10px;
                    border-left: 3px solid #0073aa;
                }
                
                /* Spacing visual */
                .rem-spacing-visual {
                    text-align: center;
                }
                
                .rem-spacing-box {
                    border: 2px dashed #ff6b6b;
                    padding: 15px;
                    margin: 10px 0;
                    position: relative;
                    background: rgba(255, 107, 107, 0.1);
                }
                
                .rem-padding-box {
                    border: 2px dashed #4ecdc4;
                    padding: 15px;
                    background: rgba(78, 205, 196, 0.1);
                    position: relative;
                }
                
                .rem-element-box {
                    background: #95e1d3;
                    padding: 10px;
                    font-weight: 600;
                    color: #2c3e50;
                }
                
                .rem-spacing-input {
                    width: 50px !important;
                    margin: 2px !important;
                    text-align: center;
                    font-size: 11px !important;
                    padding: 4px !important;
                }
                
                .rem-spacing-input-group {
                    display: flex;
                    align-items: center;
                    gap: 2px;
                }
                
                .rem-auto-btn-small {
                    background: #0073aa;
                    color: white;
                    border: none;
                    padding: 2px 4px;
                    border-radius: 2px;
                    cursor: pointer;
                    font-size: 8px;
                    transition: all 0.2s;
                }
                
                .rem-auto-btn-small:hover {
                    background: #005177;
                }
                
                .rem-margin-top, .rem-padding-top { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); }
                .rem-margin-right, .rem-padding-right { position: absolute; right: -25px; top: 50%; transform: translateY(-50%); }
                .rem-margin-bottom, .rem-padding-bottom { position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); }
                .rem-margin-left, .rem-padding-left { position: absolute; left: -25px; top: 50%; transform: translateY(-50%); }
                
                .rem-spacing-units {
                    margin-top: 10px;
                }
                
                /* Range inputs */
                .rem-range-input {
                    width: 100% !important;
                    margin: 8px 0 !important;
                }
                
                /* Preview section */
                .rem-preview-section {
                    display: flex;
                    gap: 10px;
                    margin: 20px 0;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    flex-wrap: wrap;
                }
                
                /* Modal footer */
                .rem-modal-footer {
                    padding: 20px 30px;
                    border-top: 1px solid #e0e0e0;
                    display: flex;
                    gap: 15px;
                    justify-content: flex-end;
                    background: #f8f9fa;
                    border-radius: 0 0 12px 12px;
                }
                
                .rem-btn {
                    padding: 12px 24px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .rem-btn-primary {
                    background: #0073aa;
                    color: white;
                }
                
                .rem-btn-primary:hover {
                    background: #005177;
                    transform: translateY(-1px);
                }
                
                .rem-btn-secondary {
                    background: #6c757d;
                    color: white;
                }
                
                .rem-btn-secondary:hover {
                    background: #545b62;
                }
                
                .rem-btn-danger {
                    background: #dc3545;
                    color: white;
                }
                
                .rem-btn-danger:hover {
                    background: #c82333;
                }
                
                .rem-btn-info {
                    background: #17a2b8;
                    color: white;
                }
                
                .rem-btn-info:hover {
                    background: #138496;
                }
                
                .rem-btn:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    transform: none !important;
                }
                
                /* Element states */
                body.rem-selecting {
                    cursor: crosshair !important;
                }
                
                body.rem-selecting * {
                    cursor: crosshair !important;
                }
                
                .rem-highlight {
                    outline: 2px solid #0073aa !important;
                    outline-offset: 2px !important;
                    background: rgba(0, 115, 170, 0.1) !important;
                }
                
                .rem-selected {
                    outline: 3px solid #dc3232 !important;
                    outline-offset: 2px !important;
                    background: rgba(220, 50, 50, 0.1) !important;
                }
                
                /* Classi di allineamento */
                .rem-align-left {
                    margin-left: 0 !important;
                    margin-right: auto !important;
                }
                
                .rem-align-center {
                    margin-left: auto !important;
                    margin-right: auto !important;
                }
                
                .rem-align-right {
                    margin-left: auto !important;
                    margin-right: 0 !important;
                }
                
                .rem-align-justify {
                    width: 100% !important;
                }
                
                /* Notifications */
                .rem-notification {
                    position: fixed;
                    top: 100px;
                    right: 30px;
                    background: white;
                    padding: 15px 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 1000001;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    transform: translateX(400px);
                    opacity: 0;
                    transition: all 0.3s ease;
                    border-left: 4px solid #0073aa;
                    max-width: 300px;
                }
                
                .rem-notification-show {
                    transform: translateX(0);
                    opacity: 1;
                }
                
                .rem-notification-success {
                    border-left-color: #28a745;
                }
                
                .rem-notification-error {
                    border-left-color: #dc3545;
                }
                
                .rem-notification-warning {
                    border-left-color: #ffc107;
                }
                
                .rem-notification-info {
                    border-left-color: #17a2b8;
                }
                
                .rem-notification-icon {
                    font-size: 18px;
                    flex-shrink: 0;
                }
                
                .rem-notification-message {
                    font-weight: 500;
                    color: #333;
                    word-wrap: break-word;
                }
                
                /* Responsive */
                @media (max-width: 1400px) {
                    .rem-controls-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                }
                
                @media (max-width: 768px) {
                    #rem-modal {
                        padding: 10px;
                    }
                    
                    .rem-modal-content {
                        max-width: 100%;
                    }
                    
                    .rem-modal-header,
                    .rem-modal-body,
                    .rem-modal-footer {
                        padding: 15px 20px;
                    }
                    
                    .rem-controls-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .rem-selector-options {
                        flex-direction: column;
                    }
                    
                    .rem-breakpoint-tabs {
                        overflow-x: auto;
                    }
                    
                    .rem-tab-btn {
                        min-width: 100px;
                    }
                    
                    .rem-modal-footer {
                        flex-direction: column;
                    }
                    
                    .rem-btn {
                        width: 100%;
                        justify-content: center;
                    }
                    
                    .rem-form-row {
                        flex-direction: column;
                        gap: 0;
                    }
                    
                    .rem-preview-section {
                        flex-direction: column;
                    }
                    
                    #rem-toggle-btn {
                        width: 50px;
                        height: 50px;
                        font-size: 20px;
                    }
                    
                    .rem-alignment-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                    
                    .rem-input-group {
                        flex-direction: column;
                        gap: 8px;
                    }
                    
                    .rem-spacing-input-group {
                        flex-direction: column;
                        gap: 2px;
                    }
                    
                    .rem-auto-btn-small {
                        align-self: center;
                    }
                }
                
                @media (max-width: 480px) {
                    .rem-modal-header h3 {
                        font-size: 16px;
                    }
                    
                    .rem-header-controls {
                        flex-direction: column;
                        gap: 8px;
                    }
                    
                    .rem-color-control {
                        flex-wrap: wrap;
                    }
                    
                    .rem-spacing-input {
                        position: static !important;
                        transform: none !important;
                        margin: 5px !important;
                        width: 70px !important;
                    }
                    
                    .rem-spacing-box,
                    .rem-padding-box {
                        padding: 25px 10px;
                    }
                    
                    .rem-notification {
                        right: 10px;
                        left: 10px;
                        max-width: none;
                        transform: translateY(-100px);
                    }
                    
                    .rem-notification-show {
                        transform: translateY(0);
                    }
                }
                
                /* Loading animation */
                .rem-loading-spinner {
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    border: 2px solid #f3f3f3;
                    border-top: 2px solid #0073aa;
                    border-radius: 50%;
                    animation: remSpin 1s linear infinite;
                }
                
                @keyframes remSpin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                /* Accessibility improvements */
                .rem-btn:focus,
                .rem-form-group input:focus,
                .rem-form-group select:focus {
                    outline: 2px solid #0073aa;
                    outline-offset: 2px;
                }
                
                .rem-modal-content:focus {
                    outline: none;
                }
                
                /* High contrast mode support */
                @media (prefers-contrast: high) {
                    .rem-modal-content {
                        border: 2px solid #000;
                    }
                    
                    .rem-btn {
                        border: 2px solid currentColor;
                    }
                    
                    .rem-form-group input,
                    .rem-form-group select {
                        border: 2px solid #000;
                    }
                }
                
                /* Reduced motion support */
                @media (prefers-reduced-motion: reduce) {
                    .rem-modal-content,
                    .rem-notification,
                    .rem-btn,
                    #rem-toggle-btn {
                        animation: none;
                        transition: none;
                    }
                }
                
                /* Dark mode support */
                @media (prefers-color-scheme: dark) {
                    .rem-modal-content {
                        background: #2c3e50;
                        color: #ecf0f1;
                    }
                    
                    .rem-modal-header {
                        border-bottom-color: #34495e;
                    }
                    
                    .rem-modal-footer {
                        border-top-color: #34495e;
                        background: #34495e;
                    }
                    
                    .rem-element-info {
                        background: #34495e;
                        border-left-color: #3498db;
                    }
                    
                    .rem-control-group {
                        background: #34495e;
                        border-color: #4a5f7a;
                    }
                    
                    .rem-form-group input,
                    .rem-form-group select {
                        background: #4a5f7a;
                        border-color: #5a6f8a;
                        color: #ecf0f1;
                    }
                    
                    .rem-notification {
                        background: #34495e;
                        color: #ecf0f1;
                    }
                    
                    .rem-selector-btn {
                        background: #4a5f7a;
                        border-color: #5a6f8a;
                        color: #ecf0f1;
                    }
                    
                    .rem-align-btn,
                    .rem-text-align-btn {
                        background: #4a5f7a;
                        color: #ecf0f1;
                    }
                }
            `;
        }
    }
    
    // Inizializza quando il DOM è pronto
    document.addEventListener('DOMContentLoaded', function() {
        // Verifica che gli script WordPress siano caricati
        if (typeof rem_ajax !== 'undefined') {
            window.REM = new ResponsiveElementManager();
            console.log('Responsive Element Manager inizializzato correttamente');
        } else {
            console.error('rem_ajax non definito - controllare enqueue degli script');
        }
    });
    
})();