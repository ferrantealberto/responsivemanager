<?php
/**
 * Sistema di Preset per Responsive Element Manager
 * File: includes/class-rem-presets.php
 */

// Impedisce l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe per gestire i preset di configurazione
 */
class REM_Presets {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // AJAX endpoints per i preset
        add_action('wp_ajax_rem_get_presets', array($this, 'ajax_get_presets'));
        add_action('wp_ajax_rem_apply_preset', array($this, 'ajax_apply_preset'));
        add_action('wp_ajax_rem_save_custom_preset', array($this, 'ajax_save_custom_preset'));
        add_action('wp_ajax_rem_delete_custom_preset', array($this, 'ajax_delete_custom_preset'));
        
        // Hook nel frontend per aggiungere controlli preset
        add_action('rem_frontend_enqueue_scripts', array($this, 'enqueue_preset_scripts'));
    }
    
    /**
     * Ottiene tutti i preset disponibili
     */
    public static function get_all_presets() {
        $built_in_presets = self::get_builtin_presets();
        $custom_presets = self::get_custom_presets();
        
        return array_merge($built_in_presets, $custom_presets);
    }
    
    /**
     * Preset predefiniti per scenari comuni
     */
    public static function get_builtin_presets() {
        return array(
            'hide_mobile' => array(
                'name' => '📱 Nascondi su Mobile',
                'description' => 'Nasconde l\'elemento sui dispositivi mobili',
                'category' => 'visibility',
                'rules' => array(
                    'mobile' => array(
                        'display' => 'none'
                    )
                )
            ),
            
            'hide_desktop' => array(
                'name' => '🖥️ Nascondi su Desktop',
                'description' => 'Nasconde l\'elemento sui desktop',
                'category' => 'visibility',
                'rules' => array(
                    'desktop' => array(
                        'display' => 'none'
                    )
                )
            ),
            
            'center_element' => array(
                'name' => '↔️ Centra Elemento',
                'description' => 'Centra l\'elemento orizzontalmente su tutti i dispositivi',
                'category' => 'alignment',
                'rules' => array(
                    'mobile' => array(
                        'element_align' => 'center'
                    ),
                    'tablet' => array(
                        'element_align' => 'center'
                    ),
                    'desktop' => array(
                        'element_align' => 'center'
                    )
                )
            ),
            
            'responsive_text' => array(
                'name' => '🔤 Testo Responsive',
                'description' => 'Font size che scala automaticamente con il dispositivo',
                'category' => 'typography',
                'rules' => array(
                    'mobile' => array(
                        'font_size' => array('value' => 16, 'unit' => 'px')
                    ),
                    'tablet' => array(
                        'font_size' => array('value' => 20, 'unit' => 'px')
                    ),
                    'desktop' => array(
                        'font_size' => array('value' => 24, 'unit' => 'px')
                    )
                )
            ),
            
            'responsive_spacing' => array(
                'name' => '📦 Spaziatura Responsive',
                'description' => 'Padding che si adatta alle dimensioni dello schermo',
                'category' => 'spacing',
                'rules' => array(
                    'mobile' => array(
                        'padding_top' => array('value' => 15, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 15, 'unit' => 'px'),
                        'padding_left' => array('value' => 15, 'unit' => 'px'),
                        'padding_right' => array('value' => 15, 'unit' => 'px')
                    ),
                    'tablet' => array(
                        'padding_top' => array('value' => 25, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 25, 'unit' => 'px'),
                        'padding_left' => array('value' => 25, 'unit' => 'px'),
                        'padding_right' => array('value' => 25, 'unit' => 'px')
                    ),
                    'desktop' => array(
                        'padding_top' => array('value' => 40, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 40, 'unit' => 'px'),
                        'padding_left' => array('value' => 40, 'unit' => 'px'),
                        'padding_right' => array('value' => 40, 'unit' => 'px')
                    )
                )
            ),
            
            'stack_mobile' => array(
                'name' => '📱 Stack su Mobile',
                'description' => 'Cambia layout da orizzontale a verticale su mobile',
                'category' => 'layout',
                'rules' => array(
                    'mobile' => array(
                        'display' => 'flex',
                        'flex_direction' => 'column',
                        'width' => array('value' => 100, 'unit' => '%')
                    ),
                    'tablet' => array(
                        'display' => 'flex',
                        'flex_direction' => 'row'
                    ),
                    'desktop' => array(
                        'display' => 'flex',
                        'flex_direction' => 'row'
                    )
                )
            ),
            
            'full_width_mobile' => array(
                'name' => '📱 Larghezza Piena Mobile',
                'description' => 'Rende l\'elemento a larghezza piena su mobile',
                'category' => 'layout',
                'rules' => array(
                    'mobile' => array(
                        'width' => array('value' => 100, 'unit' => '%'),
                        'margin_left' => array('value' => 0, 'unit' => 'px'),
                        'margin_right' => array('value' => 0, 'unit' => 'px')
                    )
                )
            ),
            
            'button_responsive' => array(
                'name' => '🔘 Bottone Responsive',
                'description' => 'Stile ottimizzato per pulsanti su tutti i dispositivi',
                'category' => 'components',
                'rules' => array(
                    'mobile' => array(
                        'width' => array('value' => 100, 'unit' => '%'),
                        'padding_top' => array('value' => 15, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 15, 'unit' => 'px'),
                        'font_size' => array('value' => 16, 'unit' => 'px'),
                        'text_align' => 'center'
                    ),
                    'tablet' => array(
                        'width' => array('value' => 'auto', 'unit' => 'auto'),
                        'padding_left' => array('value' => 30, 'unit' => 'px'),
                        'padding_right' => array('value' => 30, 'unit' => 'px'),
                        'font_size' => array('value' => 16, 'unit' => 'px')
                    ),
                    'desktop' => array(
                        'width' => array('value' => 'auto', 'unit' => 'auto'),
                        'padding_left' => array('value' => 40, 'unit' => 'px'),
                        'padding_right' => array('value' => 40, 'unit' => 'px'),
                        'font_size' => array('value' => 18, 'unit' => 'px')
                    )
                )
            ),
            
            'image_responsive' => array(
                'name' => '🖼️ Immagine Responsive',
                'description' => 'Ottimizza le immagini per tutti i dispositivi',
                'category' => 'components',
                'rules' => array(
                    'mobile' => array(
                        'width' => array('value' => 100, 'unit' => '%'),
                        'height' => array('value' => 'auto', 'unit' => 'auto'),
                        'element_align' => 'center'
                    ),
                    'tablet' => array(
                        'width' => array('value' => 80, 'unit' => '%'),
                        'element_align' => 'center'
                    ),
                    'desktop' => array(
                        'width' => array('value' => 60, 'unit' => '%'),
                        'element_align' => 'center'
                    )
                )
            ),
            
            'card_responsive' => array(
                'name' => '📄 Card Responsive',
                'description' => 'Stile ottimizzato per card e contenitori',
                'category' => 'components',
                'rules' => array(
                    'mobile' => array(
                        'width' => array('value' => 100, 'unit' => '%'),
                        'padding_top' => array('value' => 20, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 20, 'unit' => 'px'),
                        'padding_left' => array('value' => 15, 'unit' => 'px'),
                        'padding_right' => array('value' => 15, 'unit' => 'px'),
                        'margin_bottom' => array('value' => 15, 'unit' => 'px'),
                        'border_radius' => array('value' => 8, 'unit' => 'px'),
                        'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)'
                    ),
                    'tablet' => array(
                        'width' => array('value' => 48, 'unit' => '%'),
                        'padding_top' => array('value' => 25, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 25, 'unit' => 'px'),
                        'padding_left' => array('value' => 20, 'unit' => 'px'),
                        'padding_right' => array('value' => 20, 'unit' => 'px'),
                        'margin_bottom' => array('value' => 20, 'unit' => 'px')
                    ),
                    'desktop' => array(
                        'width' => array('value' => 30, 'unit' => '%'),
                        'padding_top' => array('value' => 30, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 30, 'unit' => 'px'),
                        'padding_left' => array('value' => 25, 'unit' => 'px'),
                        'padding_right' => array('value' => 25, 'unit' => 'px'),
                        'margin_bottom' => array('value' => 25, 'unit' => 'px')
                    )
                )
            ),
            
            'navigation_mobile' => array(
                'name' => '🧭 Menu Mobile',
                'description' => 'Ottimizza la navigazione per mobile',
                'category' => 'navigation',
                'rules' => array(
                    'mobile' => array(
                        'display' => 'flex',
                        'flex_direction' => 'column',
                        'width' => array('value' => 100, 'unit' => '%'),
                        'padding_top' => array('value' => 10, 'unit' => 'px'),
                        'padding_bottom' => array('value' => 10, 'unit' => 'px')
                    ),
                    'tablet' => array(
                        'display' => 'flex',
                        'flex_direction' => 'row',
                        'width' => array('value' => 'auto', 'unit' => 'auto')
                    ),
                    'desktop' => array(
                        'display' => 'flex',
                        'flex_direction' => 'row',
                        'width' => array('value' => 'auto', 'unit' => 'auto')
                    )
                )
            ),
            
            'sticky_header' => array(
                'name' => '📌 Header Sticky',
                'description' => 'Rende l\'header fisso in cima alla pagina',
                'category' => 'positioning',
                'rules' => array(
                    'mobile' => array(
                        'position' => 'sticky',
                        'position_y' => array('value' => 0, 'unit' => 'px'),
                        'width' => array('value' => 100, 'unit' => '%'),
                        'background_color' => '#ffffff',
                        'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)'
                    ),
                    'tablet' => array(
                        'position' => 'sticky',
                        'position_y' => array('value' => 0, 'unit' => 'px'),
                        'width' => array('value' => 100, 'unit' => '%')
                    ),
                    'desktop' => array(
                        'position' => 'sticky',
                        'position_y' => array('value' => 0, 'unit' => 'px'),
                        'width' => array('value' => 100, 'unit' => '%')
                    )
                )
            ),
            
            'floating_button' => array(
                'name' => '🎈 Bottone Fluttuante',
                'description' => 'Posiziona un pulsante fisso in basso a destra',
                'category' => 'positioning',
                'rules' => array(
                    'mobile' => array(
                        'position' => 'fixed',
                        'position_x' => array('value' => 20, 'unit' => 'px'),
                        'position_y' => array('value' => 20, 'unit' => 'px'),
                        'width' => array('value' => 60, 'unit' => 'px'),
                        'height' => array('value' => 60, 'unit' => 'px'),
                        'border_radius' => array('value' => 50, 'unit' => '%'),
                        'box_shadow' => '0 4px 12px rgba(0,0,0,0.3)'
                    ),
                    'tablet' => array(
                        'position' => 'fixed',
                        'position_x' => array('value' => 30, 'unit' => 'px'),
                        'position_y' => array('value' => 30, 'unit' => 'px')
                    ),
                    'desktop' => array(
                        'position' => 'fixed',
                        'position_x' => array('value' => 30, 'unit' => 'px'),
                        'position_y' => array('value' => 30, 'unit' => 'px')
                    )
                )
            )
        );
    }
    
    /**
     * Ottiene preset personalizzati dall'utente
     */
    public static function get_custom_presets() {
        $custom = get_option('rem_custom_presets', array());
        
        // Aggiungi metadati ai preset personalizzati
        foreach ($custom as $key => &$preset) {
            $preset['custom'] = true;
            $preset['category'] = $preset['category'] ?? 'custom';
        }
        
        return $custom;
    }
    
    /**
     * Salva un preset personalizzato
     */
    public static function save_custom_preset($name, $description, $rules, $category = 'custom') {
        $custom_presets = get_option('rem_custom_presets', array());
        
        $preset_key = sanitize_key($name);
        $custom_presets[$preset_key] = array(
            'name' => sanitize_text_field($name),
            'description' => sanitize_textarea_field($description),
            'category' => sanitize_text_field($category),
            'rules' => $rules,
            'custom' => true,
            'created_at' => current_time('mysql'),
            'author' => get_current_user_id()
        );
        
        return update_option('rem_custom_presets', $custom_presets);
    }
    
    /**
     * Elimina un preset personalizzato
     */
    public static function delete_custom_preset($preset_key) {
        $custom_presets = get_option('rem_custom_presets', array());
        
        if (isset($custom_presets[$preset_key])) {
            unset($custom_presets[$preset_key]);
            return update_option('rem_custom_presets', $custom_presets);
        }
        
        return false;
    }
    
    /**
     * Applica un preset ad un elemento
     */
    public static function apply_preset($preset_key, $selector, $scope = 'page', $post_id = 0) {
        $all_presets = self::get_all_presets();
        
        if (!isset($all_presets[$preset_key])) {
            return new WP_Error('preset_not_found', 'Preset non trovato');
        }
        
        $preset = $all_presets[$preset_key];
        
        // Crea i dati della regola
        $rule_data = array(
            'selector' => $selector,
            'scope' => $scope,
            'post_id' => $post_id,
            'rules' => $preset['rules'],
            'element_id' => '',
            'element_class' => '',
            'priority' => 10
        );
        
        return REM_Rule_Manager::save_rule($rule_data);
    }
    
    /**
     * Ottiene le categorie di preset
     */
    public static function get_preset_categories() {
        return array(
            'visibility' => array(
                'name' => '👁️ Visibilità',
                'description' => 'Controlli per mostrare/nascondere elementi'
            ),
            'alignment' => array(
                'name' => '↔️ Allineamento',
                'description' => 'Allineamento e posizionamento elementi'
            ),
            'typography' => array(
                'name' => '🔤 Tipografia',
                'description' => 'Font, dimensioni e stili di testo'
            ),
            'spacing' => array(
                'name' => '📦 Spaziatura',
                'description' => 'Margini, padding e distanze'
            ),
            'layout' => array(
                'name' => '📐 Layout',
                'description' => 'Disposizione e struttura elementi'
            ),
            'components' => array(
                'name' => '🧩 Componenti',
                'description' => 'Stili per bottoni, card, immagini'
            ),
            'navigation' => array(
                'name' => '🧭 Navigazione',
                'description' => 'Menu e elementi di navigazione'
            ),
            'positioning' => array(
                'name' => '📌 Posizionamento',
                'description' => 'Posizione fissa, sticky, assoluta'
            ),
            'custom' => array(
                'name' => '⭐ Personalizzati',
                'description' => 'Preset creati da te'
            )
        );
    }
    
    /**
     * AJAX: Ottieni preset
     */
    public function ajax_get_presets() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error('Nonce non valido');
            return;
        }
        
        $presets = self::get_all_presets();
        $categories = self::get_preset_categories();
        
        wp_send_json_success(array(
            'presets' => $presets,
            'categories' => $categories
        ));
    }
    
    /**
     * AJAX: Applica preset
     */
    public function ajax_apply_preset() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error('Nonce non valido');
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }
        
        $preset_key = sanitize_text_field($_POST['preset_key'] ?? '');
        $selector = sanitize_text_field($_POST['selector'] ?? '');
        $scope = sanitize_text_field($_POST['scope'] ?? 'page');
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (empty($preset_key) || empty($selector)) {
            wp_send_json_error('Dati mancanti');
            return;
        }
        
        $result = self::apply_preset($preset_key, $selector, $scope, $post_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Salva preset personalizzato
     */
    public function ajax_save_custom_preset() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error('Nonce non valido');
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }
        
        $name = sanitize_text_field($_POST['name'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'custom');
        $rules = json_decode(stripslashes($_POST['rules'] ?? '{}'), true);
        
        if (empty($name) || empty($rules)) {
            wp_send_json_error('Nome e regole sono obbligatori');
            return;
        }
        
        $result = self::save_custom_preset($name, $description, $rules, $category);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Preset salvato con successo',
                'preset_key' => sanitize_key($name)
            ));
        } else {
            wp_send_json_error('Errore nel salvare il preset');
        }
    }
    
    /**
     * AJAX: Elimina preset personalizzato
     */
    public function ajax_delete_custom_preset() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'rem_nonce')) {
            wp_send_json_error('Nonce non valido');
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permessi insufficienti');
            return;
        }
        
        $preset_key = sanitize_text_field($_POST['preset_key'] ?? '');
        
        if (empty($preset_key)) {
            wp_send_json_error('Chiave preset mancante');
            return;
        }
        
        $result = self::delete_custom_preset($preset_key);
        
        if ($result) {
            wp_send_json_success('Preset eliminato con successo');
        } else {
            wp_send_json_error('Errore nell\'eliminare il preset');
        }
    }
    
    /**
     * Carica script per i preset nel frontend
     */
    public function enqueue_preset_scripts() {
        wp_add_inline_script('rem-frontend', '
        // Integra sistema preset nel frontend
        if (typeof window.REM !== "undefined") {
            window.REM.Presets = {
                available: {},
                categories: {},
                
                init: function() {
                    this.loadPresets();
                    this.addPresetControls();
                },
                
                loadPresets: function() {
                    fetch(rem_ajax.ajax_url, {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: new URLSearchParams({
                            action: "rem_get_presets",
                            nonce: rem_ajax.nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.available = data.data.presets;
                            this.categories = data.data.categories;
                            this.updatePresetUI();
                        }
                    })
                    .catch(error => console.error("Error loading presets:", error));
                },
                
                addPresetControls: function() {
                    // Aggiunge sezione preset al modal
                    const presetSection = `
                        <div class="rem-preset-section">
                            <h4>⭐ Preset Rapidi</h4>
                            <div class="rem-preset-categories">
                                <select id="rem-preset-category">
                                    <option value="">Tutte le categorie</option>
                                </select>
                            </div>
                            <div class="rem-preset-grid" id="rem-preset-grid">
                                <div class="rem-preset-loading">Caricamento preset...</div>
                            </div>
                            <div class="rem-preset-actions">
                                <button id="rem-save-preset" class="rem-btn rem-btn-secondary">
                                    💾 Salva come Preset
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Inserisce nella sezione preview
                    const previewSection = document.querySelector(".rem-preview-section");
                    if (previewSection) {
                        previewSection.insertAdjacentHTML("beforebegin", presetSection);
                    }
                },
                
                updatePresetUI: function() {
                    this.updateCategorySelect();
                    this.updatePresetGrid();
                    this.bindPresetEvents();
                },
                
                updateCategorySelect: function() {
                    const select = document.getElementById("rem-preset-category");
                    if (!select) return;
                    
                    // Pulisci opzioni esistenti (mantieni "Tutte")
                    select.innerHTML = `<option value="">Tutte le categorie</option>`;
                    
                    Object.entries(this.categories).forEach(([key, category]) => {
                        const option = document.createElement("option");
                        option.value = key;
                        option.textContent = category.name;
                        select.appendChild(option);
                    });
                },
                
                updatePresetGrid: function(filterCategory = "") {
                    const grid = document.getElementById("rem-preset-grid");
                    if (!grid) return;
                    
                    let filteredPresets = Object.entries(this.available);
                    
                    if (filterCategory) {
                        filteredPresets = filteredPresets.filter(([key, preset]) => 
                            preset.category === filterCategory
                        );
                    }
                    
                    if (filteredPresets.length === 0) {
                        grid.innerHTML = `<div class="rem-preset-empty">Nessun preset trovato</div>`;
                        return;
                    }
                    
                    grid.innerHTML = filteredPresets.map(([key, preset]) => `
                        <div class="rem-preset-card" data-preset="${key}">
                            <div class="rem-preset-name">${preset.name}</div>
                            <div class="rem-preset-description">${preset.description}</div>
                            ${preset.custom ? `<div class="rem-preset-custom">Personalizzato</div>` : ""}
                            <div class="rem-preset-actions">
                                <button class="rem-preset-apply" data-preset="${key}">Applica</button>
                                ${preset.custom ? `<button class="rem-preset-delete" data-preset="${key}">Elimina</button>` : ""}
                            </div>
                        </div>
                    `).join("");
                },
                
                bindPresetEvents: function() {
                    // Filtro categoria
                    const categorySelect = document.getElementById("rem-preset-category");
                    if (categorySelect) {
                        categorySelect.addEventListener("change", (e) => {
                            this.updatePresetGrid(e.target.value);
                        });
                    }
                    
                    // Applica preset
                    document.addEventListener("click", (e) => {
                        if (e.target.classList.contains("rem-preset-apply")) {
                            const presetKey = e.target.dataset.preset;
                            this.applyPreset(presetKey);
                        }
                        
                        if (e.target.classList.contains("rem-preset-delete")) {
                            const presetKey = e.target.dataset.preset;
                            this.deletePreset(presetKey);
                        }
                        
                        if (e.target.id === "rem-save-preset") {
                            this.showSavePresetDialog();
                        }
                    });
                },
                
                applyPreset: function(presetKey) {
                    if (!window.REM.selectedElement || !window.REM.currentSelector) {
                        window.REM.showNotification("Seleziona un elemento prima", "warning");
                        return;
                    }
                    
                    const preset = this.available[presetKey];
                    if (!preset) {
                        window.REM.showNotification("Preset non trovato", "error");
                        return;
                    }
                    
                    // Applica le regole del preset ai controlli
                    Object.entries(preset.rules).forEach(([breakpoint, rules]) => {
                        window.REM.currentBreakpoint = breakpoint;
                        window.REM.showBreakpointControls();
                        
                        // Popola i controlli con i valori del preset
                        Object.entries(rules).forEach(([property, value]) => {
                            this.setControlValue(property, value, breakpoint);
                        });
                    });
                    
                    // Applica anteprima
                    window.REM.applyPreviewStyles();
                    window.REM.showNotification(`Preset "${preset.name}" applicato`, "success");
                },
                
                setControlValue: function(property, value, breakpoint) {
                    // Logica per popolare i controlli in base al tipo di proprietà
                    if (typeof value === "object" && value.value !== undefined) {
                        // Proprietà con valore e unità
                        const valueInput = document.getElementById(`${property.replace("_", "-")}-${breakpoint}`);
                        const unitSelect = document.getElementById(`${property.replace("_", "-")}-unit-${breakpoint}`);
                        
                        if (valueInput) valueInput.value = value.value;
                        if (unitSelect) unitSelect.value = value.unit;
                    } else {
                        // Proprietà semplici
                        const input = document.getElementById(`${property.replace("_", "-")}-${breakpoint}`);
                        if (input) {
                            input.value = value;
                            
                            // Trigger eventi per aggiornare UI
                            input.dispatchEvent(new Event("change"));
                        }
                    }
                },
                
                showSavePresetDialog: function() {
                    // Mostra dialog per salvare preset personalizzato
                    const dialog = prompt("Nome del preset:", "");
                    if (!dialog) return;
                    
                    const description = prompt("Descrizione (opzionale):", "") || "";
                    const rules = window.REM.collectCurrentRules();
                    
                    this.saveCustomPreset(dialog, description, rules);
                },
                
                saveCustomPreset: function(name, description, rules) {
                    fetch(rem_ajax.ajax_url, {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: new URLSearchParams({
                            action: "rem_save_custom_preset",
                            nonce: rem_ajax.nonce,
                            name: name,
                            description: description,
                            rules: JSON.stringify(rules)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.REM.showNotification("Preset salvato!", "success");
                            this.loadPresets(); // Ricarica preset
                        } else {
                            window.REM.showNotification("Errore: " + data.data, "error");
                        }
                    })
                    .catch(error => {
                        window.REM.showNotification("Errore di connessione", "error");
                    });
                },
                
                deletePreset: function(presetKey) {
                    if (!confirm("Eliminare questo preset personalizzato?")) return;
                    
                    fetch(rem_ajax.ajax_url, {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: new URLSearchParams({
                            action: "rem_delete_custom_preset",
                            nonce: rem_ajax.nonce,
                            preset_key: presetKey
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.REM.showNotification("Preset eliminato!", "success");
                            this.loadPresets(); // Ricarica preset
                        } else {
                            window.REM.showNotification("Errore: " + data.data, "error");
                        }
                    })
                    .catch(error => {
                        window.REM.showNotification("Errore di connessione", "error");
                    });
                }
            };
            
            // Inizializza preset quando REM è pronto
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(() => {
                    if (window.REM && window.REM.Presets) {
                        window.REM.Presets.init();
                    }
                }, 1000);
            });
        }');
        
        // Aggiungi CSS per i preset
        wp_add_inline_style('rem-frontend', '
        .rem-preset-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        
        .rem-preset-section h4 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
        }
        
        .rem-preset-categories {
            margin-bottom: 15px;
        }
        
        .rem-preset-categories select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .rem-preset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .rem-preset-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            transition: all 0.2s;
        }
        
        .rem-preset-card:hover {
            border-color: #0073aa;
            box-shadow: 0 2px 8px rgba(0,115,170,0.1);
        }
        
        .rem-preset-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .rem-preset-description {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .rem-preset-custom {
            font-size: 10px;
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .rem-preset-actions {
            display: flex;
            gap: 8px;
        }
        
        .rem-preset-apply {
            background: #0073aa;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            flex: 1;
        }
        
        .rem-preset-apply:hover {
            background: #005177;
        }
        
        .rem-preset-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .rem-preset-delete:hover {
            background: #c82333;
        }
        
        .rem-preset-loading,
        .rem-preset-empty {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
            grid-column: 1 / -1;
        }
        
        .rem-preset-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        #rem-save-preset {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .rem-preset-grid {
                grid-template-columns: 1fr;
                max-height: 250px;
            }
            
            .rem-preset-card {
                padding: 12px;
            }
        }');
    }
}

// Inizializza il sistema preset
REM_Presets::get_instance();