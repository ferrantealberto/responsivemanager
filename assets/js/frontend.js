/**
 * Frontend JavaScript migliorato per Responsive Element Manager
 * Include controlli avanzati e selezione elementi migliorata
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
            this.selectorMode = 'self'; // self, parent, children
            this.childElements = [];
            this.hoveredElement = null;
            
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
         * HTML del modal migliorato
         */
        getModalHTML() {
            return `
                <div class="rem-modal-content">
                    <div class="rem-modal-header">
                        <h3>🎨 Editor Responsive Avanzato</h3>
                        <span id="rem-close">&times;</span>
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
         * Genera controlli per il breakpoint corrente
         */
        showBreakpointControls() {
            const container = document.getElementById('rem-breakpoint-content');
            if (!container) return;
            
            container.innerHTML = `
                <div class="rem-controls-container">
                    <div class="rem-controls-grid">
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
                                <label>Altezza Linea:</label>
                                <div class="rem-input-group">
                                    <input type="number" id="line-height-${this.currentBreakpoint}" 
                                           placeholder="1.5" min="0.5" max="5" step="0.1">
                                    <select id="line-height-unit-${this.currentBreakpoint}">
                                        <option value="">numero</option>
                                        <option value="px">px</option>
                                        <option value="em">em</option>
                                        <option value="rem">rem</option>
                                    </select>
                                </div>
                            </div>
                            <div class="rem-form-group">
                                <label>Allineamento Testo:</label>
                                <div class="rem-alignment-buttons">
                                    <button type="button" class="rem-align-btn" data-align="left" title="Sinistra">⬅️</button>
                                    <button type="button" class="rem-align-btn" data-align="center" title="Centro">↔️</button>
                                    <button type="button" class="rem-align-btn" data-align="right" title="Destra">➡️</button>
                                    <button type="button" class="rem-align-btn" data-align="justify" title="Giustificato">↕️</button>
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
                        
                        <!-- Layout -->
                        <div class="rem-control-group">
                            <h4>📐 Layout e Visibilità</h4>
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
                        
                        <!-- Dimensioni -->
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
                                </div>
                            </div>
                        </div>
                        
                        <!-- Spaziatura -->
                        <div class="rem-control-group">
                            <h4>📦 Spaziatura</h4>
                            <div class="rem-spacing-visual">
                                <div class="rem-spacing-label">Margini:</div>
                                <div class="rem-spacing-box">
                                    <div class="rem-margin-controls">
                                        <input type="number" id="margin-top-${this.currentBreakpoint}" 
                                               placeholder="0" class="rem-spacing-input rem-margin-top">
                                        <input type="number" id="margin-right-${this.currentBreakpoint}" 
                                               placeholder="0" class="rem-spacing-input rem-margin-right">
                                        <input type="number" id="margin-bottom-${this.currentBreakpoint}" 
                                               placeholder="0" class="rem-spacing-input rem-margin-bottom">
                                        <input type="number" id="margin-left-${this.currentBreakpoint}" 
                                               placeholder="0" class="rem-spacing-input rem-margin-left">
                                    </div>
                                    <div class="rem-padding-box">
                                        <div class="rem-spacing-label">Padding:</div>
                                        <div class="rem-padding-controls">
                                            <input type="number" id="padding-top-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-padding-top">
                                            <input type="number" id="padding-right-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-padding-right">
                                            <input type="number" id="padding-bottom-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-padding-bottom">
                                            <input type="number" id="padding-left-${this.currentBreakpoint}" 
                                                   placeholder="0" class="rem-spacing-input rem-padding-left">
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
         * Eventi per i controlli del breakpoint
         */
        bindBreakpointEvents() {
            const bp = this.currentBreakpoint;
            
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
            
            // Alignment buttons
            document.querySelectorAll('.rem-align-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('.rem-align-btn').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    this.applyPreviewStyles();
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
         * Applica gli stili in anteprima
         */
        applyPreviewStyles() {
            if (!this.selectedElement) return;
            
            const rules = this.collectCurrentRules();
            const css = this.generateCSSFromRules(rules[this.currentBreakpoint] || {});
            
            // Applica gli stili direttamente all'elemento per anteprima
            this.selectedElement.style.cssText += '; ' + css;
        }
        
        /**
         * Raccoglie tutte le regole dai controlli
         */
        collectCurrentRules() {
            const rules = {};
            const bp = this.currentBreakpoint;
            
            if (!document.getElementById(`font-size-${bp}`)) return rules;
            
            rules[bp] = {};
            
            // Font
            const fontSize = document.getElementById(`font-size-${bp}`).value;
            const fontUnit = document.getElementById(`font-unit-${bp}`).value;
            if (fontSize) {
                rules[bp].font_size = { value: parseFloat(fontSize), unit: fontUnit };
            }
            
            const fontFamily = document.getElementById(`font-family-${bp}`).value;
            if (fontFamily) {
                rules[bp].font_family = fontFamily;
            }
            
            const fontWeight = document.getElementById(`font-weight-${bp}`).value;
            if (fontWeight) {
                rules[bp].font_weight = fontWeight;
            }
            
            const lineHeight = document.getElementById(`line-height-${bp}`).value;
            const lineHeightUnit = document.getElementById(`line-height-unit-${bp}`).value;
            if (lineHeight) {
                rules[bp].line_height = { value: parseFloat(lineHeight), unit: lineHeightUnit };
            }
            
            // Text alignment
            const activeAlign = document.querySelector('.rem-align-btn.active');
            if (activeAlign) {
                rules[bp].text_align = activeAlign.dataset.align;
            }
            
            // Colors
            const textColor = document.getElementById(`text-color-hex-${bp}`).value;
            if (textColor) {
                rules[bp].text_color = textColor;
            }
            
            const bgColor = document.getElementById(`bg-color-hex-${bp}`).value;
            if (bgColor) {
                rules[bp].background_color = bgColor;
            }
            
            const borderColor = document.getElementById(`border-color-hex-${bp}`).value;
            if (borderColor) {
                rules[bp].border_color = borderColor;
            }
            
            // Layout
            const display = document.getElementById(`display-${bp}`).value;
            if (display) {
                rules[bp].display = display;
                
                if (display === 'flex') {
                    const flexDirection = document.getElementById(`flex-direction-${bp}`).value;
                    const justifyContent = document.getElementById(`justify-content-${bp}`).value;
                    const alignItems = document.getElementById(`align-items-${bp}`).value;
                    
                    if (flexDirection) rules[bp].flex_direction = flexDirection;
                    if (justifyContent) rules[bp].justify_content = justifyContent;
                    if (alignItems) rules[bp].align_items = alignItems;
                }
            }
            
            // Dimensions
            const width = document.getElementById(`width-${bp}`).value;
            const widthUnit = document.getElementById(`width-unit-${bp}`).value;
            if (width) {
                rules[bp].width = { value: parseFloat(width), unit: widthUnit };
            }
            
            const height = document.getElementById(`height-${bp}`).value;
            const heightUnit = document.getElementById(`height-unit-${bp}`).value;
            if (height) {
                rules[bp].height = { value: parseFloat(height), unit: heightUnit };
            }
            
            // Spacing
            const spacingUnit = document.getElementById(`spacing-unit-${bp}`).value || 'px';
            
            ['margin', 'padding'].forEach(type => {
                ['top', 'right', 'bottom', 'left'].forEach(side => {
                    const value = document.getElementById(`${type}-${side}-${bp}`).value;
                    if (value) {
                        rules[bp][`${type}_${side}`] = { 
                            value: parseFloat(value), 
                            unit: spacingUnit 
                        };
                    }
                });
            });
            
            // Effects
            const opacity = document.getElementById(`opacity-${bp}`).value;
            if (opacity && opacity !== '1') {
                rules[bp].opacity = parseFloat(opacity);
            }
            
            const boxShadow = document.getElementById(`box-shadow-${bp}`).value;
            if (boxShadow) {
                rules[bp].box_shadow = boxShadow;
            }
            
            const borderRadius = document.getElementById(`border-radius-${bp}`).value;
            const borderRadiusUnit = document.getElementById(`border-radius-unit-${bp}`).value;
            if (borderRadius) {
                rules[bp].border_radius = { value: parseFloat(borderRadius), unit: borderRadiusUnit };
            }
            
            return rules;
        }
        
        /**
         * Genera CSS dalle regole
         */
        generateCSSFromRules(rules) {
            const css = [];
            
            if (rules.font_size) {
                css.push(`font-size: ${rules.font_size.value}${rules.font_size.unit}`);
            }
            if (rules.font_family) {
                css.push(`font-family: ${rules.font_family}`);
            }
            if (rules.font_weight) {
                css.push(`font-weight: ${rules.font_weight}`);
            }
            if (rules.line_height) {
                css.push(`line-height: ${rules.line_height.value}${rules.line_height.unit}`);
            }
            if (rules.text_align) {
                css.push(`text-align: ${rules.text_align}`);
            }
            if (rules.text_color) {
                css.push(`color: ${rules.text_color}`);
            }
            if (rules.background_color) {
                css.push(`background-color: ${rules.background_color}`);
            }
            if (rules.border_color) {
                css.push(`border-color: ${rules.border_color}`);
            }
            if (rules.display) {
                css.push(`display: ${rules.display}`);
            }
            if (rules.flex_direction) {
                css.push(`flex-direction: ${rules.flex_direction}`);
            }
            if (rules.justify_content) {
                css.push(`justify-content: ${rules.justify_content}`);
            }
            if (rules.align_items) {
                css.push(`align-items: ${rules.align_items}`);
            }
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
         * Bind degli eventi principali
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
                btn.addEventListener('click', () => this.closeModal());
            });
            
            // Save rules
            saveBtn.addEventListener('click', () => this.saveRules());
            
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
         * Salva regole
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
                post_id: rem_ajax.current_post_id || 0,
                rules: rules
            };
            
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showNotification('Regole salvate con successo!', 'success');
                    this.closeModal();
                } else {
                    this.showNotification('Errore nel salvataggio: ' + (data.data || 'Errore sconosciuto'), 'error');
                }
            })
            .catch(error => {
                this.showNotification('Errore di connessione: ' + error.message, 'error');
            });
        }
        
        /**
         * Carica regole esistenti
         */
        loadExistingRules() {
            // Implementa il caricamento delle regole esistenti
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
         * CSS personalizzato per l'interfaccia
         */
        getCustomCSS() {
            return `
                /* Responsive Element Manager Styles */
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
                    max-width: 1200px;
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
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
                    gap: 8px;
                }
                
                .rem-input-group input {
                    flex: 2;
                }
                
                .rem-input-group select {
                    flex: 1;
                    min-width: 80px;
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
                
                /* Alignment buttons */
                .rem-alignment-buttons {
                    display: flex;
                    gap: 4px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    overflow: hidden;
                }
                
                .rem-align-btn {
                    background: white;
                    border: none;
                    padding: 8px 12px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: all 0.2s;
                    flex: 1;
                }
                
                .rem-align-btn:hover {
                    background: #f8f9fa;
                }
                
                .rem-align-btn.active {
                    background: #0073aa;
                    color: white;
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
                
                .rem-margin-top { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); }
                .rem-margin-right { position: absolute; right: -25px; top: 50%; transform: translateY(-50%); }
                .rem-margin-bottom { position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); }
                .rem-margin-left { position: absolute; left: -25px; top: 50%; transform: translateY(-50%); }
                
                .rem-padding-top { position: absolute; top: -10px; left: 50%; transform: translateX(-50%); }
                .rem-padding-right { position: absolute; right: -20px; top: 50%; transform: translateY(-50%); }
                .rem-padding-bottom { position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); }
                .rem-padding-left { position: absolute; left: -20px; top: 50%; transform: translateY(-50%); }
                
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
                
                .rem-notification-icon {
                    font-size: 18px;
                }
                
                .rem-notification-message {
                    font-weight: 500;
                    color: #333;
                }
                
                /* Responsive */
                @media (max-width: 1200px) {
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
                    
                    #rem-toggle-btn {
                        width: 50px;
                        height: 50px;
                        font-size: 20px;
                    }
                }
                
                @media (max-width: 480px) {
                    .rem-input-group {
                        flex-direction: column;
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
                }
            `;
        }
    }
    
    // Inizializza quando il DOM è pronto
    document.addEventListener('DOMContentLoaded', function() {
        // Verifica che gli script WordPress siano caricati
        if (typeof rem_ajax !== 'undefined') {
            window.REM = new ResponsiveElementManager();
        }
    });
    
})();