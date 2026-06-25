<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Cloudari_BioEnergy_Controller_Contact_Widgets {
    const OPTION_NAME = 'cloudari_bioenergy_contact_widgets';

    public static function register() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        add_action('admin_post_cloudari_bioenergy_contact_save', array(__CLASS__, 'save'));
        add_shortcode('eera_contact_widget_1', array(__CLASS__, 'render_widget_1'));
        add_shortcode('eera_contact_widget_2', array(__CLASS__, 'render_widget_2'));
        add_shortcode('eera_contact_widget_3', array(__CLASS__, 'render_widget_3'));
    }

    public static function register_menu() {
        add_menu_page(
            'Datos Contacto',
            'Datos Contacto',
            'manage_options',
            'cloudari-bioenergy-contact-data',
            array(__CLASS__, 'render_admin_page'),
            'dashicons-email-alt',
            58
        );
    }

    public static function enqueue_admin_assets($hook_suffix) {
        if ('toplevel_page_cloudari-bioenergy-contact-data' !== $hook_suffix) {
            return;
        }

        wp_enqueue_editor();
    }

    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $data = self::data();
        $saved = isset($_GET['cloudari_contact_saved']);
        $active_tab = isset($_GET['cloudari_contact_tab']) ? sanitize_key(wp_unslash($_GET['cloudari_contact_tab'])) : 'widget1';
        $active_tab = in_array($active_tab, array('widget1', 'widget2', 'widget3'), true) ? $active_tab : 'widget1';
        ?>
        <div class="wrap cloudari-contact-admin">
            <div class="cloudari-contact-admin__header">
                <div>
                    <h1>Datos Contacto</h1>
                    <p>Edita los datos y revisa el resultado en directo antes de guardar.</p>
                </div>
                <div class="cloudari-contact-shortcodes" aria-label="Shortcodes disponibles">
                    <code>[eera_contact_widget_1]</code>
                    <code>[eera_contact_widget_2]</code>
                    <code>[eera_contact_widget_3]</code>
                </div>
            </div>
            <?php if ($saved) : ?>
                <div class="notice notice-success is-dismissible"><p>Datos guardados.</p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('cloudari_bioenergy_contact_save'); ?>
                <input type="hidden" name="action" value="cloudari_bioenergy_contact_save">
                <input type="hidden" name="cloudari_contact_tab" value="<?php echo esc_attr($active_tab); ?>" data-cloudari-active-tab>

                <div class="cloudari-contact-workspace">
                    <div class="cloudari-contact-editor">
                        <div class="cloudari-contact-tabs" role="tablist" aria-label="Elegir widget">
                            <button class="button <?php echo 'widget1' === $active_tab ? 'button-primary' : ''; ?>" type="button" data-cloudari-tab="widget1">Widget 1</button>
                            <button class="button <?php echo 'widget2' === $active_tab ? 'button-primary' : ''; ?>" type="button" data-cloudari-tab="widget2">Widget 2</button>
                            <button class="button <?php echo 'widget3' === $active_tab ? 'button-primary' : ''; ?>" type="button" data-cloudari-tab="widget3">Widget 3</button>
                        </div>

                        <section class="cloudari-contact-panel <?php echo 'widget1' === $active_tab ? 'is-active' : ''; ?>" data-cloudari-panel="widget1">
                            <div class="cloudari-contact-panel__title">
                                <span>Widget 1 <em class="cloudari-contact-unsaved" data-cloudari-unsaved>Sin guardar cambios</em></span>
                                <code>[eera_contact_widget_1]</code>
                            </div>
                            <p>
                                <label>Titulo<br>
                                    <input class="large-text" type="text" name="contact[widget1][title]" value="<?php echo esc_attr($data['widget1']['title']); ?>">
                                </label>
                            </p>
                            <?php self::render_rich_editor('Contenido', 'contact[widget1][content]', $data['widget1']['content'], 'cloudari_contact_widget1_content'); ?>
                        </section>

                        <section class="cloudari-contact-panel <?php echo 'widget2' === $active_tab ? 'is-active' : ''; ?>" data-cloudari-panel="widget2">
                            <div class="cloudari-contact-panel__title">
                                <span>Widget 2 <em class="cloudari-contact-unsaved" data-cloudari-unsaved>Sin guardar cambios</em></span>
                                <code>[eera_contact_widget_2]</code>
                            </div>
                            <?php foreach ($data['widget2']['cards'] as $index => $card) : ?>
                                <details class="cloudari-contact-card-editor" <?php echo 0 === $index ? 'open' : ''; ?>>
                                    <summary>Card <?php echo esc_html((string) ($index + 1)); ?>: <?php echo esc_html($card['title']); ?></summary>
                                    <p>
                                        <label>Titulo<br>
                                            <input class="large-text" type="text" name="contact[widget2][cards][<?php echo esc_attr((string) $index); ?>][title]" value="<?php echo esc_attr($card['title']); ?>">
                                        </label>
                                    </p>
                                    <?php self::render_rich_editor('Contenido', 'contact[widget2][cards][' . $index . '][content]', $card['content'], 'cloudari_contact_widget2_card_' . $index); ?>
                                </details>
                            <?php endforeach; ?>
                        </section>

                        <section class="cloudari-contact-panel <?php echo 'widget3' === $active_tab ? 'is-active' : ''; ?>" data-cloudari-panel="widget3">
                            <div class="cloudari-contact-panel__title">
                                <span>Widget 3 <em class="cloudari-contact-unsaved" data-cloudari-unsaved>Sin guardar cambios</em></span>
                                <code>[eera_contact_widget_3]</code>
                            </div>
                            <?php foreach ($data['widget3']['cards'] as $index => $card) : ?>
                                <details class="cloudari-contact-card-editor" <?php echo 0 === $index ? 'open' : ''; ?>>
                                    <summary>Card <?php echo esc_html((string) ($index + 1)); ?>: <?php echo esc_html($card['title']); ?></summary>
                                    <input type="hidden" name="contact[widget3][cards][<?php echo esc_attr((string) $index); ?>][type]" value="<?php echo esc_attr($card['type']); ?>">
                                    <p>
                                        <label>Titulo<br>
                                            <input class="large-text" type="text" name="contact[widget3][cards][<?php echo esc_attr((string) $index); ?>][title]" value="<?php echo esc_attr($card['title']); ?>">
                                        </label>
                                    </p>
                                    <?php self::render_rich_editor('Contenido', 'contact[widget3][cards][' . $index . '][content]', $card['content'], 'cloudari_contact_widget3_card_' . $index); ?>
                                </details>
                            <?php endforeach; ?>
                        </section>

                        <div class="cloudari-contact-actions">
                            <?php submit_button('Guardar datos contacto', 'primary', 'submit', false); ?>
                        </div>
                    </div>

                    <aside class="cloudari-contact-preview" aria-label="Vista previa del widget">
                        <div class="cloudari-contact-preview__bar">
                            <strong>Vista previa</strong>
                            <span data-cloudari-preview-label><?php echo esc_html(str_replace('widget', 'Widget ', $active_tab)); ?></span>
                        </div>
                        <div class="cloudari-contact-preview__frame <?php echo 'widget1' === $active_tab ? 'is-active' : ''; ?>" data-cloudari-preview="widget1">
                            <?php echo self::render_widget_1(); ?>
                        </div>
                        <div class="cloudari-contact-preview__frame <?php echo 'widget2' === $active_tab ? 'is-active' : ''; ?>" data-cloudari-preview="widget2">
                            <?php echo self::render_widget_2(); ?>
                        </div>
                        <div class="cloudari-contact-preview__frame <?php echo 'widget3' === $active_tab ? 'is-active' : ''; ?>" data-cloudari-preview="widget3">
                            <?php echo self::render_widget_3(); ?>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
        <style>
            .cloudari-contact-admin { --cloudari-blue: #1c5986; --cloudari-green: #bed431; --cloudari-line: #dcdcde; --cloudari-soft: #f6f8f4; }
            .cloudari-contact-admin__header { align-items: flex-start; display: flex; gap: 18px; justify-content: space-between; margin: 18px 0 16px; max-width: 1320px; }
            .cloudari-contact-admin__header h1 { margin-bottom: 4px; }
            .cloudari-contact-admin__header p { color: #646970; margin: 0; }
            .cloudari-contact-shortcodes { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; max-width: 560px; padding-top: 8px; }
            .cloudari-contact-shortcodes code, .cloudari-contact-panel__title code { background: #eef4e7; border: 1px solid #d9e7ca; color: #24445f; }
            .cloudari-contact-workspace { align-items: start; display: grid; gap: 18px; grid-template-columns: minmax(360px, 470px) minmax(0, 1fr); max-width: 1320px; }
            .cloudari-contact-editor, .cloudari-contact-preview { min-width: 0; }
            .cloudari-contact-tabs { background: #fff; border: 1px solid var(--cloudari-line); display: flex; gap: 8px; margin: 0 0 12px; padding: 10px; position: sticky; top: 32px; z-index: 5; }
            .cloudari-contact-tabs .button { flex: 1; justify-content: center; min-height: 34px; }
            .cloudari-contact-panel { background: #fff; border: 1px solid var(--cloudari-line); display: none; margin: 0; padding: 16px 18px 18px; }
            .cloudari-contact-panel.is-active { display: block; }
            .cloudari-contact-panel__title { align-items: center; border-bottom: 1px solid #edf0f2; display: flex; font-size: 15px; font-weight: 600; justify-content: space-between; margin: 0 0 16px; padding: 0 0 12px; }
            .cloudari-contact-unsaved { color: #b32d2e; display: none; font-size: 12px; font-style: normal; font-weight: 600; margin-left: 8px; }
            .cloudari-contact-admin.has-unsaved-changes .cloudari-contact-panel.is-active [data-cloudari-unsaved] { display: inline; }
            .cloudari-contact-card-editor { border: 1px solid #e2e6ea; margin: 0 0 10px; padding: 0; }
            .cloudari-contact-card-editor summary { background: var(--cloudari-soft); color: #1d2327; cursor: pointer; font-weight: 600; padding: 12px 14px; }
            .cloudari-contact-card-editor p, .cloudari-contact-rich-editor, .cloudari-contact-repeatable { margin-left: 14px; margin-right: 14px; }
            .cloudari-contact-rich-editor { margin-bottom: 16px; }
            .cloudari-contact-rich-editor__label { color: #1d2327; display: block; font-weight: 600; margin: 14px 0 8px; }
            .cloudari-contact-rich-editor .wp-editor-wrap { max-width: 100%; }
            .cloudari-contact-rich-editor .wp-editor-area { min-height: 118px; }
            .cloudari-contact-repeatable__row { align-items: center; display: grid; gap: 8px; grid-template-columns: minmax(0, 1fr) auto; margin: 0 0 8px; }
            .cloudari-contact-repeatable__row input { width: 100%; }
            .cloudari-contact-actions { background: #fff; border: 1px solid var(--cloudari-line); border-top: 0; padding: 14px 18px; }
            .cloudari-contact-preview { background: #fff; border: 1px solid var(--cloudari-line); position: sticky; top: 32px; }
            .cloudari-contact-preview__bar { align-items: center; border-bottom: 1px solid var(--cloudari-line); display: flex; justify-content: space-between; padding: 12px 14px; }
            .cloudari-contact-preview__bar span { color: #646970; font-size: 12px; text-transform: uppercase; }
            .cloudari-contact-preview__frame { display: none; overflow: auto; padding: 24px; }
            .cloudari-contact-preview__frame.is-active { display: block; }
            .cloudari-contact-preview__frame .eera-contact-card-widget { padding-top: 34px !important; }
            .cloudari-contact-preview__frame .eera-subprogrammes-widget, .cloudari-contact-preview__frame .eera-jp-secretariat-widget { padding: 34px 24px 32px !important; }
            .cloudari-contact-preview__frame .eera-subprogrammes-widget__grid, .cloudari-contact-preview__frame .eera-jp-secretariat-widget__grid { gap: 58px 18px !important; }
            .cloudari-contact-preview__frame .eera-subprogrammes-widget__grid--two { margin-top: 58px !important; }
            @media (max-width: 1180px) { .cloudari-contact-workspace { grid-template-columns: 1fr; } .cloudari-contact-tabs, .cloudari-contact-preview { position: static; } }
            @media (max-width: 782px) { .cloudari-contact-admin__header { display: block; } .cloudari-contact-shortcodes { justify-content: flex-start; } .cloudari-contact-repeatable__row { grid-template-columns: 1fr; } }
        </style>
        <script>
            (function () {
                var form = document.querySelector('.cloudari-contact-admin form');
                var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-cloudari-tab]'));
                var panels = Array.prototype.slice.call(document.querySelectorAll('[data-cloudari-panel]'));
                var previews = Array.prototype.slice.call(document.querySelectorAll('[data-cloudari-preview]'));
                var previewLabel = document.querySelector('[data-cloudari-preview-label]');
                var activeTabField = document.querySelector('[data-cloudari-active-tab]');
                var admin = document.querySelector('.cloudari-contact-admin');
                var hasUnsavedChanges = false;
                var syncQueued = false;

                function byName(name) {
                    return form ? form.querySelector('[name="' + name + '"]') : null;
                }

                function editorContent(name) {
                    var field = byName(name);
                    if (!field) {
                        return '';
                    }
                    if (window.tinymce) {
                        var editor = window.tinymce.get(field.id);
                        if (editor && !editor.isHidden()) {
                            return editor.getContent();
                        }
                    }
                    var iframe = field.id ? document.getElementById(field.id + '_ifr') : null;
                    if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
                        return iframe.contentDocument.body.innerHTML;
                    }
                    return field.value;
                }

                function syncVisualEditorsToTextareas() {
                    Array.prototype.slice.call(document.querySelectorAll('.cloudari-contact-rich-editor textarea[id]')).forEach(function (field) {
                        if (window.tinymce) {
                            var editor = window.tinymce.get(field.id);
                            if (editor && !editor.isHidden()) {
                                field.value = editor.getContent();
                                return;
                            }
                        }
                        var iframe = document.getElementById(field.id + '_ifr');
                        if (iframe && iframe.contentDocument && iframe.contentDocument.body) {
                            field.value = iframe.contentDocument.body.innerHTML;
                        }
                    });
                }

                function setActive(widget) {
                    if (activeTabField) {
                        activeTabField.value = widget;
                    }
                    tabs.forEach(function (tab) {
                        tab.classList.toggle('button-primary', tab.dataset.cloudariTab === widget);
                    });
                    panels.forEach(function (panel) {
                        panel.classList.toggle('is-active', panel.dataset.cloudariPanel === widget);
                    });
                    previews.forEach(function (preview) {
                        preview.classList.toggle('is-active', preview.dataset.cloudariPreview === widget);
                    });
                    if (previewLabel) {
                        previewLabel.textContent = widget.replace('widget', 'Widget ');
                    }
                }

                function setDirty() {
                    hasUnsavedChanges = true;
                    if (admin) {
                        admin.classList.add('has-unsaved-changes');
                    }
                }

                function syncWidget1() {
                    var preview = document.querySelector('[data-cloudari-preview="widget1"]');
                    var title = byName('contact[widget1][title]');
                    var titleNode = preview && preview.querySelector('.eera-contact-card-widget__title');
                    var details = preview && preview.querySelector('.eera-contact-card-widget__details');
                    if (titleNode && title) {
                        titleNode.textContent = title.value;
                    }
                    if (details) {
                        details.innerHTML = editorContent('contact[widget1][content]');
                    }
                }

                function syncWidget2() {
                    var preview = document.querySelector('[data-cloudari-preview="widget2"]');
                    var cards = preview ? Array.prototype.slice.call(preview.querySelectorAll('.eera-subprogrammes-widget__card')) : [];
                    cards.forEach(function (card, index) {
                        var title = byName('contact[widget2][cards][' + index + '][title]');
                        var titleNode = card.querySelector('.eera-subprogrammes-widget__card-title');
                        var details = card.querySelector('.eera-subprogrammes-widget__details');
                        if (titleNode && title) {
                            titleNode.textContent = title.value;
                        }
                        if (details) {
                            details.innerHTML = editorContent('contact[widget2][cards][' + index + '][content]');
                        }
                    });
                }

                function syncWidget3() {
                    var preview = document.querySelector('[data-cloudari-preview="widget3"]');
                    var cards = preview ? Array.prototype.slice.call(preview.querySelectorAll('.eera-jp-secretariat-widget__card')) : [];
                    cards.forEach(function (card, index) {
                        var title = byName('contact[widget3][cards][' + index + '][title]');
                        var titleNode = card.querySelector('.eera-jp-secretariat-widget__card-title');
                        var details = card.querySelector('.eera-jp-secretariat-widget__details');
                        if (titleNode && title) {
                            titleNode.textContent = title.value;
                        }
                        if (details) {
                            details.innerHTML = editorContent('contact[widget3][cards][' + index + '][content]');
                        }
                    });
                }

                function syncPreview() {
                    if (!form) {
                        return;
                    }
                    syncWidget1();
                    syncWidget2();
                    syncWidget3();
                }

                function queueSync(markDirty) {
                    if (markDirty) {
                        setDirty();
                    }
                    if (syncQueued) {
                        return;
                    }
                    syncQueued = true;
                    window.requestAnimationFrame(function () {
                        syncQueued = false;
                        syncPreview();
                    });
                }

                function bindEditor(editor) {
                    if (!editor || editor.cloudariContactBound) {
                        return;
                    }
                    editor.cloudariContactBound = true;
                    editor.on('keyup change input undo redo SetContent ExecCommand Paste NodeChange', function () {
                        queueSync(true);
                    });
                    editor.on('init', function () {
                        queueSync(false);
                    });
                }

                function bindExistingEditors() {
                    if (!window.tinymce) {
                        return;
                    }
                    var editors = window.tinymce.editors || [];
                    if (typeof editors.forEach === 'function') {
                        editors.forEach(bindEditor);
                    } else {
                        Object.keys(editors).forEach(function (key) {
                            bindEditor(editors[key]);
                        });
                    }
                }

                function bindEditorFrames() {
                    Array.prototype.slice.call(document.querySelectorAll('.cloudari-contact-rich-editor iframe[id$="_ifr"]')).forEach(function (iframe) {
                        if (iframe.cloudariContactBound || !iframe.contentDocument || !iframe.contentDocument.body) {
                            return;
                        }
                        iframe.cloudariContactBound = true;
                        ['input', 'keyup', 'change', 'paste'].forEach(function (eventName) {
                            iframe.contentDocument.body.addEventListener(eventName, function () {
                                queueSync(true);
                            });
                        });
                    });
                }

                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        setActive(tab.dataset.cloudariTab);
                    });
                });

                if (form) {
                    form.addEventListener('input', function () {
                        queueSync(true);
                    });
                    form.addEventListener('change', function () {
                        queueSync(true);
                    });
                    form.addEventListener('submit', function () {
                        if (window.tinymce) {
                            window.tinymce.triggerSave();
                        }
                        syncVisualEditorsToTextareas();
                        hasUnsavedChanges = false;
                        if (admin) {
                            admin.classList.remove('has-unsaved-changes');
                        }
                    });
                }

                if (window.tinymce) {
                    window.tinymce.on('AddEditor', function (event) {
                        bindEditor(event.editor);
                    });
                    bindExistingEditors();
                    window.setTimeout(bindExistingEditors, 500);
                    window.setTimeout(bindExistingEditors, 1500);
                }
                bindEditorFrames();
                window.setTimeout(bindEditorFrames, 500);
                window.setTimeout(bindEditorFrames, 1500);

                setActive(activeTabField && activeTabField.value ? activeTabField.value : 'widget1');
                syncPreview();
            }());
        </script>
        <?php
    }

    public static function save() {
        if (!current_user_can('manage_options')) {
            wp_die('Forbidden');
        }

        check_admin_referer('cloudari_bioenergy_contact_save');

        $raw = isset($_POST['contact']) && is_array($_POST['contact']) ? wp_unslash($_POST['contact']) : array();
        update_option(self::OPTION_NAME, self::sanitize_data($raw), false);

        $active_tab = isset($_POST['cloudari_contact_tab']) ? sanitize_key(wp_unslash($_POST['cloudari_contact_tab'])) : 'widget1';
        $active_tab = in_array($active_tab, array('widget1', 'widget2', 'widget3'), true) ? $active_tab : 'widget1';

        wp_safe_redirect(add_query_arg(
            array(
                'cloudari_contact_saved' => '1',
                'cloudari_contact_tab' => $active_tab,
            ),
            admin_url('admin.php?page=cloudari-bioenergy-contact-data')
        ));
        exit;
    }

    public static function render_widget_1() {
        $data = self::data();
        $card = $data['widget1'];

        ob_start();
        ?>
        <div class="eera-contact-card-widget" role="group" aria-label="<?php echo esc_attr($card['title']); ?>">
            <?php echo self::widget_1_styles(); ?>
            <article class="eera-contact-card-widget__card">
                <span class="eera-contact-card-widget__icon" aria-hidden="true"><?php echo self::icon_svg('email'); ?></span>
                <h3 class="eera-contact-card-widget__title"><?php echo esc_html($card['title']); ?></h3>
                <div class="eera-contact-card-widget__details"><?php echo self::render_rich_content($card['content']); ?></div>
            </article>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_widget_2() {
        $data = self::data();
        $cards = $data['widget2']['cards'];

        ob_start();
        ?>
        <section class="eera-subprogrammes-widget" aria-label="Subprogrammes Coordinators">
            <?php echo self::widget_2_styles(); ?>
            <div class="eera-subprogrammes-widget__grid">
                <?php foreach (array_slice($cards, 0, 3) as $card) : ?>
                    <?php echo self::subprogramme_card($card); ?>
                <?php endforeach; ?>
            </div>
            <?php if (count($cards) > 3) : ?>
                <div class="eera-subprogrammes-widget__grid eera-subprogrammes-widget__grid--two">
                    <?php foreach (array_slice($cards, 3) as $card) : ?>
                        <?php echo self::subprogramme_card($card); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_widget_3() {
        $data = self::data();

        ob_start();
        ?>
        <section class="eera-jp-secretariat-widget" aria-label="Joint Programme Secretariat contact cards">
            <?php echo self::widget_3_styles(); ?>
            <div class="eera-jp-secretariat-widget__grid">
                <?php foreach ($data['widget3']['cards'] as $card) : ?>
                    <?php echo self::secretariat_card($card); ?>
                <?php endforeach; ?>
            </div>
            <?php echo self::copy_script(); ?>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    private static function render_repeatable_admin($label, $name, array $values, $type) {
        $values = $values ? $values : array('');
        ?>
        <div class="cloudari-contact-repeatable" data-cloudari-repeatable data-name="<?php echo esc_attr($name); ?>" data-type="<?php echo esc_attr($type); ?>">
            <p><strong><?php echo esc_html($label); ?></strong></p>
            <div data-cloudari-repeatable-items>
                <?php foreach ($values as $value) : ?>
                    <div class="cloudari-contact-repeatable__row">
                        <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($value); ?>">
                        <button type="button" class="button" data-cloudari-remove-row>Eliminar</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button" data-cloudari-add-row>Anadir linea</button>
        </div>
        <?php
    }

    private static function render_rich_editor($label, $name, $value, $editor_id) {
        ?>
        <div class="cloudari-contact-rich-editor">
            <span class="cloudari-contact-rich-editor__label"><?php echo esc_html($label); ?></span>
            <?php
            wp_editor(
                $value,
                $editor_id,
                array(
                    'textarea_name' => $name,
                    'textarea_rows' => 5,
                    'media_buttons' => false,
                    'teeny' => true,
                    'quicktags' => true,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                        'toolbar2' => '',
                    ),
                )
            );
            ?>
        </div>
        <?php
    }

    private static function data() {
        return self::merge_data(get_option(self::OPTION_NAME, array()));
    }

    private static function merge_data($stored) {
        $defaults = self::defaults();
        if (!is_array($stored)) {
            return $defaults;
        }

        $data = array_replace_recursive($defaults, $stored);
        if (!isset($stored['widget1']['content'])) {
            $data['widget1']['content'] = self::content_from_emails($data['widget1']['emails'] ?? array());
        }
        foreach ($defaults['widget2']['cards'] as $index => $card) {
            if (!isset($stored['widget2']['cards'][$index]['content'])) {
                $data['widget2']['cards'][$index]['content'] = self::content_from_subprogramme($data['widget2']['cards'][$index] ?? $card);
            }
        }
        foreach ($defaults['widget3']['cards'] as $index => $card) {
            if (!isset($stored['widget3']['cards'][$index]['content'])) {
                $data['widget3']['cards'][$index]['content'] = self::content_from_secretariat($data['widget3']['cards'][$index] ?? $card);
            }
        }

        return self::sanitize_data($data);
    }

    private static function sanitize_data($raw) {
        $defaults = self::defaults();
        $raw = is_array($raw) ? $raw : array();

        $data = $defaults;
        $data['widget1']['title'] = self::text($raw['widget1']['title'] ?? $defaults['widget1']['title']);
        $data['widget1']['emails'] = self::emails($raw['widget1']['emails'] ?? $defaults['widget1']['emails']);
        $data['widget1']['content'] = self::rich_html($raw['widget1']['content'] ?? self::content_from_emails($data['widget1']['emails']));

        foreach ($defaults['widget2']['cards'] as $index => $card) {
            $raw_card = $raw['widget2']['cards'][$index] ?? array();
            $data['widget2']['cards'][$index] = array(
                'title' => self::text($raw_card['title'] ?? $card['title']),
                'name' => self::text($raw_card['name'] ?? $card['name']),
                'organization' => self::text($raw_card['organization'] ?? $card['organization']),
                'emails' => self::emails($raw_card['emails'] ?? $card['emails']),
                'content' => '',
            );
            $data['widget2']['cards'][$index]['content'] = self::rich_html($raw_card['content'] ?? self::content_from_subprogramme($data['widget2']['cards'][$index]));
        }

        foreach ($defaults['widget3']['cards'] as $index => $card) {
            $raw_card = $raw['widget3']['cards'][$index] ?? array();
            $type = in_array(($raw_card['type'] ?? $card['type']), array('email', 'phone', 'address'), true) ? ($raw_card['type'] ?? $card['type']) : $card['type'];
            $data['widget3']['cards'][$index] = array(
                'type' => $type,
                'title' => self::text($raw_card['title'] ?? $card['title']),
                'emails' => self::emails($raw_card['emails'] ?? $card['emails']),
                'phones' => self::texts($raw_card['phones'] ?? $card['phones']),
                'lines' => self::textarea_lines($raw_card['lines'] ?? $card['lines']),
                'content' => '',
            );
            $data['widget3']['cards'][$index]['content'] = self::rich_html($raw_card['content'] ?? self::content_from_secretariat($data['widget3']['cards'][$index]));
        }

        return $data;
    }

    private static function defaults() {
        return array(
            'widget1' => array(
                'title' => 'Joint Programme Coordinator',
                'emails' => array('mchrist@cres.gr'),
                'content' => '<p><a href="mailto:mchrist@cres.gr">mchrist@cres.gr</a></p>',
            ),
            'widget2' => array(
                'cards' => array(
                    array('title' => 'Subprogramme 1 (Sustainable biomass production)', 'name' => 'Dr. Wolter Elbersen', 'organization' => 'Wageningen, University & Research (WUR)', 'emails' => array('wolter.elbersen@wur.nl'), 'content' => '<p>Dr. Wolter Elbersen<br>Wageningen, University &amp; Research (WUR)<br><a href="mailto:wolter.elbersen@wur.nl">wolter.elbersen@wur.nl</a></p>'),
                    array('title' => 'Subprogramme 2 (Thermochemical platform)', 'name' => 'Berend Vreugdenhil', 'organization' => 'TNO', 'emails' => array('berend.vreugdenhil@tno.nl'), 'content' => '<p>Berend Vreugdenhil<br>TNO<br><a href="mailto:berend.vreugdenhil@tno.nl">berend.vreugdenhil@tno.nl</a></p>'),
                    array('title' => 'Subprogramme 3 (Biochemical platform)', 'name' => 'Dr. Marcelo E. Domine', 'organization' => 'Institute of Chemical Technology - ITQ (UPV-CSIC)', 'emails' => array('mdomine@itq.upv.es'), 'content' => '<p>Dr. Marcelo E. Domine<br>Institute of Chemical Technology - ITQ (UPV-CSIC)<br><a href="mailto:mdomine@itq.upv.es">mdomine@itq.upv.es</a></p>'),
                    array('title' => 'Subprogramme 4 (Stationary bioenergy)', 'name' => 'Berend Vreugdenhil', 'organization' => 'TNO', 'emails' => array('berend.vreugdenhil@tno.nl'), 'content' => '<p>Berend Vreugdenhil<br>TNO<br><a href="mailto:berend.vreugdenhil@tno.nl">berend.vreugdenhil@tno.nl</a></p>'),
                    array('title' => 'Subprogramme 5 (Sustainability / Techno-Economic Analysis / Public Acceptance)', 'name' => 'Dr. Raquel S. Jorge', 'organization' => 'Norwegian University of Science and Technology (NTNU)', 'emails' => array('raquel.s.jorge@ntnu.no'), 'content' => '<p>Dr. Raquel S. Jorge<br>Norwegian University of Science and Technology (NTNU)<br><a href="mailto:raquel.s.jorge@ntnu.no">raquel.s.jorge@ntnu.no</a></p>'),
                ),
            ),
            'widget3' => array(
                'cards' => array(
                    array('type' => 'email', 'title' => 'Email', 'emails' => array('margadegregorio@bioplat.org'), 'phones' => array(), 'lines' => array(), 'content' => '<p><a href="mailto:margadegregorio@bioplat.org">margadegregorio@bioplat.org</a></p>'),
                    array('type' => 'phone', 'title' => 'Phone', 'emails' => array(), 'phones' => array('+34 629 485 629'), 'lines' => array(), 'content' => '<p><a href="tel:+34629485629">+34 629 485 629</a></p>'),
                    array('type' => 'address', 'title' => 'Where', 'emails' => array(), 'phones' => array(), 'lines' => array('c/ Cedaceros 11 2C', '28014 Madrid, Spain'), 'content' => '<p>c/ Cedaceros 11 2C<br>28014 Madrid, Spain</p>'),
                ),
            ),
        );
    }

    private static function subprogramme_card(array $card) {
        ob_start();
        ?>
        <article class="eera-subprogrammes-widget__card">
            <span class="eera-subprogrammes-widget__icon" aria-hidden="true"><?php echo self::icon_svg('email'); ?></span>
            <h3 class="eera-subprogrammes-widget__card-title"><?php echo esc_html($card['title']); ?></h3>
            <div class="eera-subprogrammes-widget__details"><?php echo self::render_rich_content($card['content']); ?></div>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    private static function secretariat_card(array $card) {
        ob_start();
        ?>
        <article class="eera-jp-secretariat-widget__card">
            <span class="eera-jp-secretariat-widget__icon" aria-hidden="true"><?php echo self::icon_svg($card['type']); ?></span>
            <h3 class="eera-jp-secretariat-widget__card-title"><?php echo esc_html($card['title']); ?></h3>
            <div class="eera-jp-secretariat-widget__details"><?php echo self::secretariat_details($card); ?></div>
            <?php if ('email' === $card['type'] && !empty($card['emails'])) : ?>
                <button class="eera-jp-secretariat-widget__copy" type="button" data-copy-email="<?php echo esc_attr(implode(', ', $card['emails'])); ?>">Copy email</button>
            <?php endif; ?>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    private static function secretariat_details(array $card) {
        if (!empty($card['content'])) {
            return self::render_rich_content($card['content']);
        }

        if ('email' === $card['type']) {
            return self::email_links($card['emails'], 'eera-jp-secretariat-widget__link');
        }

        if ('phone' === $card['type']) {
            $links = array();
            foreach ($card['phones'] as $phone) {
                $tel = preg_replace('/[^0-9+]/', '', (string) $phone);
                $links[] = '<a class="eera-jp-secretariat-widget__link" href="tel:' . esc_attr($tel) . '">' . esc_html($phone) . '</a>';
            }
            return implode('<br>', $links);
        }

        return implode('<br>', array_map('esc_html', $card['lines']));
    }

    private static function render_rich_content($content) {
        return wp_kses_post(wpautop((string) $content));
    }

    private static function rich_html($content) {
        return wp_kses_post((string) $content);
    }

    private static function content_from_subprogramme(array $card) {
        $lines = array();
        if (!empty($card['name'])) {
            $lines[] = esc_html($card['name']);
        }
        if (!empty($card['organization'])) {
            $lines[] = esc_html($card['organization']);
        }

        foreach (self::emails($card['emails'] ?? array()) as $email) {
            $lines[] = '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
        }

        return '<p>' . implode('<br>', $lines) . '</p>';
    }

    private static function content_from_secretariat(array $card) {
        if ('email' === ($card['type'] ?? '')) {
            return self::content_from_emails($card['emails'] ?? array());
        }

        if ('phone' === ($card['type'] ?? '')) {
            $links = array();
            foreach (self::texts($card['phones'] ?? array()) as $phone) {
                $tel = preg_replace('/[^0-9+]/', '', (string) $phone);
                $links[] = '<a href="tel:' . esc_attr($tel) . '">' . esc_html($phone) . '</a>';
            }
            return '<p>' . implode('<br>', $links) . '</p>';
        }

        return '<p>' . implode('<br>', array_map('esc_html', self::textarea_lines($card['lines'] ?? array()))) . '</p>';
    }

    private static function content_from_emails($emails) {
        $links = array();
        foreach (self::emails($emails) as $email) {
            $links[] = '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
        }
        return '<p>' . implode('<br>', $links) . '</p>';
    }

    private static function email_links(array $emails, $class) {
        $links = array();
        foreach ($emails as $email) {
            $links[] = '<a class="' . esc_attr($class) . '" href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
        }
        return implode('<br>', $links);
    }

    private static function emails($values) {
        $emails = array();
        foreach ((array) $values as $value) {
            foreach (preg_split('/[,;\r\n]+/', (string) $value) as $candidate) {
                $email = sanitize_email(trim($candidate));
                if ($email && is_email($email)) {
                    $emails[] = $email;
                }
            }
        }
        return array_values(array_unique($emails));
    }

    private static function texts($values) {
        return array_values(array_filter(array_map(array(__CLASS__, 'text'), (array) $values), 'strlen'));
    }

    private static function textarea_lines($values) {
        if (is_array($values)) {
            return self::texts($values);
        }

        return self::texts(preg_split('/\r\n|\r|\n/', (string) $values));
    }

    private static function text($value) {
        return sanitize_text_field((string) $value);
    }

    private static function icon_svg($type) {
        if ('phone' === $type) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.82 19.82 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.82 19.82 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.91.33 1.79.63 2.63a2 2 0 0 1-.45 2.11L8.09 9.66a16 16 0 0 0 6.25 6.25l1.2-1.2a2 2 0 0 1 2.11-.45c.84.3 1.72.51 2.63.63A2 2 0 0 1 22 16.92Z"></path></svg>';
        }

        if ('address' === $type) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
        }

        return '<svg viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a2 2 0 0 1-2.06 0L2 7"></path></svg>';
    }

    private static function widget_1_styles() {
        return '<style>.eera-contact-card-widget{--eera-card-green:#bed431;--eera-card-blue:#1c5986;--eera-card-title:#363636;--eera-card-text:#818181;--eera-card-border:#e5e5e5;box-sizing:border-box!important;display:block!important;font-family:"Source Sans Pro",Helvetica,Arial,sans-serif!important;margin:0!important;max-width:100%!important;padding:40px 0 20px!important;width:100%!important}.eera-contact-card-widget *,.eera-contact-card-widget *::before,.eera-contact-card-widget *::after{box-sizing:border-box!important;text-shadow:none!important}.eera-contact-card-widget__card{align-items:center!important;background:rgba(255,255,255,.81)!important;border:1px solid var(--eera-card-border)!important;border-radius:3px!important;display:flex!important;flex-direction:column!important;margin:0!important;min-height:0!important;padding:36px 20px 28px!important;position:relative!important;text-align:center!important;width:100%!important}.eera-contact-card-widget__icon{align-items:center!important;background:var(--eera-card-green)!important;border-radius:30px!important;color:var(--eera-card-blue)!important;display:inline-flex!important;height:56px!important;justify-content:center!important;left:50%!important;margin-left:-27px!important;padding:15px 16px 17px!important;position:absolute!important;top:-30px!important;width:54px!important}.eera-contact-card-widget__icon svg{display:block!important;height:100%!important;stroke:currentColor!important;width:100%!important}.eera-contact-card-widget__title{color:var(--eera-card-title)!important;font-size:20px!important;font-weight:400!important;letter-spacing:0!important;line-height:30px!important;margin:0 0 10px!important}.eera-contact-card-widget__details{color:var(--eera-card-text)!important;font-family:"Varela",Helvetica,Arial,sans-serif!important;font-size:14px!important;font-weight:400!important;line-height:1.8!important;margin:0 0 12px!important;overflow-wrap:anywhere!important}.eera-contact-card-widget__details p{margin:0!important}.eera-contact-card-widget__link,.eera-contact-card-widget__details a{color:var(--eera-card-text)!important;font-weight:400!important;text-decoration:none!important}.eera-contact-card-widget__link:hover,.eera-contact-card-widget__details a:hover{color:#00aeef!important}</style>';
    }

    private static function widget_2_styles() {
        return '<style>.eera-subprogrammes-widget{--eera-section-bg:#e6efde;--eera-card-green:#bed431;--eera-card-blue:#1c5986;--eera-card-title:#363636;--eera-card-text:#687783;--eera-card-border:#e5e5e5;background:#e6efde!important;background-color:#e6efde!important;box-sizing:border-box!important;color:var(--eera-card-title)!important;display:block!important;font-family:"Source Sans Pro",Helvetica,Arial,sans-serif!important;margin:0!important;max-width:100%!important;overflow:hidden!important;padding:31px 44px 40px!important;width:100%!important}.eera-subprogrammes-widget *,.eera-subprogrammes-widget *::before,.eera-subprogrammes-widget *::after{box-sizing:border-box!important;text-shadow:none!important}.eera-subprogrammes-widget__grid{column-gap:32px!important;display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;margin:0!important;row-gap:0!important;width:100%!important}.eera-subprogrammes-widget__grid--two{grid-template-columns:repeat(2,minmax(0,1fr))!important;margin-top:100px!important}.eera-subprogrammes-widget__card{align-items:center!important;background:rgba(255,255,255,.86)!important;border:1px solid var(--eera-card-border)!important;border-radius:3px!important;display:flex!important;flex-direction:column!important;justify-content:flex-start!important;margin:0!important;min-height:194px!important;min-width:0!important;padding:42px 24px 24px!important;position:relative!important;text-align:center!important;width:100%!important}.eera-subprogrammes-widget__icon{align-items:center!important;background:var(--eera-card-green)!important;border-radius:30px!important;color:var(--eera-card-blue)!important;display:inline-flex!important;height:56px!important;justify-content:center!important;left:50%!important;margin-left:-27px!important;padding:15px 16px 17px!important;position:absolute!important;top:-30px!important;width:54px!important}.eera-subprogrammes-widget__icon svg{display:block!important;height:100%!important;stroke:currentColor!important;width:100%!important}.eera-subprogrammes-widget__card-title{color:var(--eera-card-title)!important;font-size:20px!important;font-weight:400!important;letter-spacing:0!important;line-height:30px!important;margin:0 0 12px!important;white-space:normal!important}.eera-subprogrammes-widget__details{color:var(--eera-card-text)!important;font-family:"Varela",Helvetica,Arial,sans-serif!important;font-size:15px!important;font-weight:400!important;line-height:1.65!important;margin:0!important;overflow-wrap:anywhere!important}.eera-subprogrammes-widget__details p{margin:0!important}.eera-subprogrammes-widget__link,.eera-subprogrammes-widget__details a{color:var(--eera-card-text)!important;font-weight:400!important;text-decoration:none!important}.eera-subprogrammes-widget__link:hover,.eera-subprogrammes-widget__details a:hover{color:#00aeef!important}@media(max-width:1024px){.eera-subprogrammes-widget{padding-left:24px!important;padding-right:24px!important}.eera-subprogrammes-widget__grid,.eera-subprogrammes-widget__grid--two{column-gap:28px!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;row-gap:72px!important}.eera-subprogrammes-widget__grid--two{margin-top:72px!important}}@media(max-width:640px){.eera-subprogrammes-widget{padding-left:16px!important;padding-right:16px!important}.eera-subprogrammes-widget__grid,.eera-subprogrammes-widget__grid--two{grid-template-columns:1fr!important;row-gap:72px!important}.eera-subprogrammes-widget__grid--two{margin-top:72px!important}}</style>';
    }

    private static function widget_3_styles() {
        return '<style>.eera-jp-secretariat-widget{--eera-section-bg:#fff;--eera-card-green:#bed431;--eera-card-blue:#1c5986;--eera-card-title:#363636;--eera-card-text:#687783;--eera-card-border:#e5e5e5;background:#fff!important;background-color:#fff!important;box-sizing:border-box!important;color:var(--eera-card-title)!important;display:block!important;font-family:"Source Sans Pro",Helvetica,Arial,sans-serif!important;margin:0!important;max-width:100%!important;overflow:hidden!important;padding:31px 44px 40px!important;width:100%!important}.eera-jp-secretariat-widget *,.eera-jp-secretariat-widget *::before,.eera-jp-secretariat-widget *::after{box-sizing:border-box!important;text-shadow:none!important}.eera-jp-secretariat-widget__grid{column-gap:32px!important;display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;margin:0!important;row-gap:0!important;width:100%!important}.eera-jp-secretariat-widget__card{align-items:center!important;background:rgba(255,255,255,.86)!important;border:1px solid var(--eera-card-border)!important;border-radius:3px!important;display:flex!important;flex-direction:column!important;justify-content:flex-start!important;margin:0!important;min-height:194px!important;min-width:0!important;padding:42px 24px 24px!important;position:relative!important;text-align:center!important;width:100%!important}.eera-jp-secretariat-widget__icon{align-items:center!important;background:var(--eera-card-green)!important;border-radius:30px!important;color:var(--eera-card-blue)!important;display:inline-flex!important;height:56px!important;justify-content:center!important;left:50%!important;margin-left:-27px!important;padding:15px 16px 17px!important;position:absolute!important;top:-30px!important;width:54px!important}.eera-jp-secretariat-widget__icon svg{display:block!important;height:100%!important;stroke:currentColor!important;width:100%!important}.eera-jp-secretariat-widget__card-title{color:var(--eera-card-title)!important;font-size:20px!important;font-weight:400!important;letter-spacing:0!important;line-height:30px!important;margin:0 0 12px!important;white-space:normal!important}.eera-jp-secretariat-widget__details{color:var(--eera-card-text)!important;font-family:"Varela",Helvetica,Arial,sans-serif!important;font-size:15px!important;font-weight:400!important;line-height:1.65!important;margin:0!important;overflow-wrap:anywhere!important}.eera-jp-secretariat-widget__details p{margin:0!important}.eera-jp-secretariat-widget__link,.eera-jp-secretariat-widget__details a{color:var(--eera-card-text)!important;font-weight:400!important;text-decoration:none!important}.eera-jp-secretariat-widget__link:hover,.eera-jp-secretariat-widget__details a:hover{color:#00aeef!important}.eera-jp-secretariat-widget__copy{background:transparent!important;border:0!important;color:#00aeef!important;cursor:pointer!important;display:inline-flex!important;font:inherit!important;font-size:12px!important;font-weight:400!important;justify-content:center!important;line-height:23px!important;margin:8px 0 0!important;min-height:0!important;padding:3px!important;text-transform:none!important}.eera-jp-secretariat-widget__copy.is-copied{color:#ed7676!important}@media(max-width:1024px){.eera-jp-secretariat-widget{padding-left:24px!important;padding-right:24px!important}.eera-jp-secretariat-widget__grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;row-gap:72px!important}}@media(max-width:640px){.eera-jp-secretariat-widget{padding-left:16px!important;padding-right:16px!important}.eera-jp-secretariat-widget__grid{grid-template-columns:1fr!important}}</style>';
    }

    private static function copy_script() {
        return '<script>(function(){var widget=document.currentScript&&document.currentScript.closest(".eera-jp-secretariat-widget");if(!widget||!navigator.clipboard){return;}widget.addEventListener("click",function(event){var button=event.target.closest("[data-copy-email]");if(!button||!widget.contains(button)){return;}var originalText=button.textContent;navigator.clipboard.writeText(button.getAttribute("data-copy-email")).then(function(){button.textContent="Copied";button.classList.add("is-copied");window.setTimeout(function(){button.textContent=originalText;button.classList.remove("is-copied");},1600);});});}());</script>';
    }
}
