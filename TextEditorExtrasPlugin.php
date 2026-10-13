<?php
/**
 * @file plugins/generic/textEditorExtras/TextEditorExtrasPlugin.php
 *
 * Copyright (c) 2017-2025 Simon Fraser University
 * Copyright (c) 2017-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TextEditorExtrasPlugin
 * @brief Plugin class for the TextEditorExtras plugin.
 */

namespace APP\plugins\generic\textEditorExtras;

use APP\notification\NotificationManager;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\LinkAction;
use PKP\core\JSONMessage;
use APP\core\Application;
use APP\template\TemplateManager;
use PKP\components\forms\FieldRichTextarea;
use PKP\config\Config;
use PKP\context\Context;

class TextEditorExtrasPlugin extends GenericPlugin {

    /** Settings key for the custom page content in navigation menu items */
    public const LEGACY_NAVIGATION_FORM = 'navigationMenuItem';
    public const LEGACY_NAVIGATION_FIELD = 'content';

    /**
     * @copydoc GenericPlugin::register()
     */
    public function register($category, $path, $mainContextId = null) {
        if (!parent::register($category, $path, $mainContextId)) {
            return false;
        }
        if ($this->getEnabled($mainContextId)) {
            Hook::add('Form::config::before', $this->modifyForms(...));
            Hook::add('TemplateManager::display', $this->addLegacyEditorScript(...));
        }
        return true;
    }

    /**
     * Provide a name for this plugin
     *
     * The name will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     *
     * @return string
     */
    public function getDisplayName() {
        return __('plugins.generic.textEditorExtras.displayName');
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     *
     * @return string
     */
    public function getDescription() {
        return __('plugins.generic.textEditorExtras.description');
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $verb): array
    {
        $actions = parent::getActions($request, $verb);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        $url = $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']);
        array_unshift($actions, new LinkAction('settings', new AjaxModal($url, $this->getDisplayName()), __('manager.plugins.settings')));
        return $actions;
    }

    /**
     * Show and save the settings form when the settings action
     * is clicked.
     *
     * @param array $args
     * @param Request $request
     * @return JSONMessage
     */
    public function manage($args, $request) {
        if ($request->getUserVar('verb') !== 'settings') {
            return parent::manage($args, $request);
        }

        $form = new TextEditorExtrasSettingsForm($this);
        if (!$request->getUserVar('save')) {
            $form->initData();
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->readInputData();
        if (!$form->validate()) {
            return new JSONMessage(true, $form->fetch($request));
        }

        $form->execute();

        return new JSONMessage(true);
    }

    /**
     * The extra controls this plugin can add to a rich text field.
     *
     * - label:   locale key used in the settings table header
     * - toolbar: buttons added to the TinyMCE toolbar
     * - plugins: TinyMCE plugins that must be loaded for the buttons
     * - tags:    HTML tags the control produces; they must be permitted
     *            by [security] allowed_html or they are stripped on output
     *
     * @return array<string, array{label: string, toolbar: string, plugins: string[], tags: string[]}>
     */
    public function getControls(): array
    {
        return [
            'image' => [
                'label' => 'plugins.generic.textEditorExtras.setting.images',
                'toolbar' => 'image',
                'plugins' => ['image'],
                'tags' => ['img'],
            ],
            'code' => [
                'label' => 'plugins.generic.textEditorExtras.setting.code',
                'toolbar' => 'code',
                'plugins' => ['code'],
                'tags' => [],
            ],
            'table' => [
                'label' => 'plugins.generic.textEditorExtras.setting.tables',
                'toolbar' => 'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
                'plugins' => ['table'],
                'tags' => ['table', 'tbody', 'tr', 'td'],
            ],
            'headings' => [
                'label' => 'plugins.generic.textEditorExtras.setting.headings',
                'toolbar' => 'blocks',
                'plugins' => [],
                'tags' => ['h2', 'h3', 'h4', 'h5', 'h6'],
            ],
            'lists' => [
                'label' => 'plugins.generic.textEditorExtras.setting.lists',
                'toolbar' => 'bullist numlist',
                'plugins' => ['lists'],
                'tags' => ['ul', 'ol', 'li'],
            ],
            'blockquote' => [
                'label' => 'plugins.generic.textEditorExtras.setting.blockquote',
                'toolbar' => 'blockquote hr',
                'plugins' => [],
                'tags' => ['blockquote', 'hr'],
            ],
            'removeformat' => [
                'label' => 'plugins.generic.textEditorExtras.setting.removeFormat',
                'toolbar' => 'removeformat',
                'plugins' => [],
                'tags' => [],
            ],
            'charmap' => [
                'label' => 'plugins.generic.textEditorExtras.setting.charmap',
                'toolbar' => 'charmap',
                'plugins' => ['charmap'],
                'tags' => [],
            ],
        ];
    }

    /**
     * The forms and rich text fields that can be configured, with the
     * locale key used to label each field in the settings table.
     *
     * @return array<string, array<string, string>>
     */
    public function getFields(?Context $context = null): array
    {
        $fields = [
            'masthead' => [
                'description' => 'manager.setup.contextSummary',
                'about' => 'manager.setup.contextAbout',
            ],
            'information' => [
                'readerInformation' => 'manager.setup.information.forReaders',
                'authorInformation' => 'manager.setup.information.forAuthors',
                'librarianInformation' => 'manager.setup.information.forLibrarians',
            ],
            'privacy' => ['privacyStatement' => 'manager.setup.privacyStatement'],
            'appearanceAdvanced' => ['additionalHomeContent' => 'manager.setup.additionalContent'],
            'submissionGuidanceSettings' => [
                'authorGuidelines' => 'manager.setup.authorGuidelines',
                'beginSubmissionHelp' => 'submission.wizard.beforeStart',
                'submissionChecklist' => 'manager.setup.submissionPreparationChecklist',
                'uploadFilesHelp' => 'submission.upload.uploadFiles',
                'contributorsHelp' => 'publication.contributors',
                'detailsHelp' => 'common.details',
                'forTheEditorsHelp' => 'submission.forTheEditors',
                'reviewHelp' => 'submission.reviewAndSubmit',
                'copyrightNotice' => 'manager.setup.copyrightNotice',
            ],
            'license' => ['licenseTerms' => 'manager.distribution.licenseTerms'],
            'reviewerGuidance' => [
                'reviewGuidelines' => 'manager.setup.reviewGuidelines',
                'competingInterests' => 'manager.setup.competingInterests',
            ],
            'editEmailTemplate' => ['body' => 'plugins.generic.textEditorExtras.setting.emailTemplateBody'],
            'titleAbstract' => ['abstract' => 'common.abstract'],
            // Not a Vue form: the custom page editor in Website > Navigation
            // uses the legacy TinyMCE, see addLegacyEditorScript()
            self::LEGACY_NAVIGATION_FORM => [
                self::LEGACY_NAVIGATION_FIELD => 'plugins.generic.textEditorExtras.setting.navigationMenuItemContent',
            ],
        ];

        // The reviewer suggestions help text only exists when the
        // application supports reviewer suggestions
        if ($this->contextHasProperty('reviewerSuggestionEnabled')) {
            $fields['submissionGuidanceSettings']['reviewerSuggestionsHelp'] = 'submission.forReviewerSuggestion';
        }

        if ($context && $context->getData('enableAnnouncements')) {
            $fields['announcement'] = [
                'description' => 'announcement.announcements',
                'descriptionShort' => 'plugins.generic.textEditorExtras.setting.announcements.descriptionShort',
            ];
        }

        return $fields;
    }

    /**
     * Get the HTML tags required by the enabled controls that are not
     * permitted by the [security] allowed_html setting.
     *
     * @param array $additions Settings in the form [formId => [fieldName => [control, ...]]]
     *
     * @return string[]
     */
    public function getMissingHtmlTags(array $additions): array
    {
        $controls = $this->getControls();
        $required = [];
        foreach ($additions as $fieldControls) {
            foreach ((array) $fieldControls as $selected) {
                foreach ((array) $selected as $controlName) {
                    if (isset($controls[$controlName])) {
                        $required = array_merge($required, $controls[$controlName]['tags']);
                    }
                }
            }
        }

        $allowed = [];
        foreach (explode(',', (string) Config::getVar('security', 'allowed_html')) as $item) {
            $tag = strtolower(trim(preg_replace('/\[.*\]/', '', $item)));
            if ($tag !== '') {
                $allowed[] = $tag;
            }
        }

        return array_values(array_diff(array_unique($required), $allowed));
    }

    /**
     * Add the enabled extra controls to rich text fields in the form.
     *
     * @param string $hookName Form::config::before
     * @param \PKP\components\forms\FormComponent $form
     */
    public function modifyForms($hookName, $form) {
        $request = Application::get()->getRequest();
        $context = $request->getContext();

        if (!$context) {
            return;
        }

        // Additions arrive as an array of arrays that identify
        // the form id, any fields in that form that we need to
        // add extra controls to, and the controls that should
        // be added:
        // [
        //   'titleAbstract' => [
        //        'abstract' => ['image', 'code']
        //    ]
        // ]
        $allAdditions = $this->getSetting($context->getId(), 'additions');

        if (empty($allAdditions) || empty($allAdditions[$form->id])) {
            return;
        }

        $publicFileApiUrl = $request->getDispatcher()->url($request, Application::ROUTE_API, $context->getPath(), '_uploadPublicFile');
        $controls = $this->getControls();

        foreach ($allAdditions[$form->id] as $fieldName => $fieldControls) {
            $field = $form->getField($fieldName);
            if (!$field instanceof FieldRichTextarea) {
                continue;
            }
            foreach ((array) $fieldControls as $controlName) {
                if (!isset($controls[$controlName])) {
                    continue;
                }
                $control = $controls[$controlName];

                // Only add buttons that are not already in the toolbar
                $toolbar = $this->getMissingButtons($field->toolbar, $control['toolbar']);
                if ($toolbar === '') {
                    continue;
                }

                if ($controlName === 'headings') {
                    // The block format menu is expected at the start of the toolbar
                    $field->toolbar = $toolbar . ' | ' . $field->toolbar;
                    $field->init = array_merge((array) ($field->init ?? []), [
                        'block_formats' => $this->getBlockFormats(),
                    ]);
                } else {
                    $field->toolbar .= ' | ' . $toolbar;
                }

                foreach ($control['plugins'] as $plugin) {
                    if (!in_array($plugin, $field->plugins)) {
                        $field->plugins[] = $plugin;
                    }
                }

                if ($controlName === 'image') {
                    $field->uploadUrl = $publicFileApiUrl;
                }
            }
        }
    }

    /**
     * Get the buttons of a control that are not already in a toolbar.
     *
     * Compares whole toolbar items, so that e.g. "code" does not match
     * "codesample". Groups ("|") of the control toolbar are kept.
     *
     * @return string The missing buttons, or an empty string when all exist
     */
    protected function getMissingButtons(string $toolbar, string $controlToolbar): string
    {
        $existing = preg_split('/[\s|]+/', $toolbar, -1, PREG_SPLIT_NO_EMPTY);

        $groups = [];
        foreach (explode('|', $controlToolbar) as $group) {
            $buttons = array_diff(preg_split('/\s+/', $group, -1, PREG_SPLIT_NO_EMPTY), $existing);
            if (!empty($buttons)) {
                $groups[] = implode(' ', $buttons);
            }
        }

        return implode(' | ', $groups);
    }

    /**
     * Check whether the context schema has a property.
     */
    protected function contextHasProperty(string $property): bool
    {
        try {
            $schema = app()->get('schema'); /** @var \PKP\services\PKPSchemaService $schema */
            return isset($schema->get('context')->properties->{$property});
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Add the enabled controls to the custom page editor in navigation menus.
     *
     * The navigation menu item form is not a Vue form, so it does not pass
     * through Form::config::before. It uses the legacy TinyMCE setup shared by
     * all legacy editors. A small script wraps tinymce.createEditor() and
     * extends the options only for the custom page content textarea.
     *
     * @param string $hookName TemplateManager::display
     */
    public function addLegacyEditorScript($hookName, $args)
    {
        $templateMgr = $args[0]; /** @var \APP\template\TemplateManager $templateMgr */
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context) {
            return false;
        }

        $additions = $this->getSetting($context->getId(), 'additions');
        $selected = (array) ($additions[self::LEGACY_NAVIGATION_FORM][self::LEGACY_NAVIGATION_FIELD] ?? []);
        $controls = $this->getControls();

        $config = ['prepend' => [], 'append' => [], 'plugins' => [], 'blockFormats' => null];
        foreach ($selected as $controlName) {
            if (!isset($controls[$controlName])) {
                continue;
            }
            $control = $controls[$controlName];
            $config['plugins'] = array_merge($config['plugins'], $control['plugins']);
            if ($controlName === 'headings') {
                $config['prepend'][] = $control['toolbar'];
                $config['blockFormats'] = $this->getBlockFormats();
            } else {
                $config['append'][] = $control['toolbar'];
            }
        }

        if (empty($config['prepend']) && empty($config['append'])) {
            return false;
        }

        $templateMgr->addJavaScript(
            'textEditorExtrasLegacy',
            $this->getLegacyEditorScript($config),
            [
                'inline' => true,
                'contexts' => 'backend',
                // After the standalone TinyMCE script and the PKP JS library
                'priority' => TemplateManager::STYLE_SEQUENCE_LAST,
            ]
        );

        return false;
    }

    /**
     * Get the inline script that extends the legacy custom page editor.
     */
    protected function getLegacyEditorScript(array $config): string
    {
        $json = json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return <<<JS
(function() {
    var config = {$json};

    // Only the content of a custom page (Website > Navigation)
    function isCustomPageContent(id) {
        var el = document.getElementById(id);
        return !!(el && el.name && el.name.indexOf('content[') === 0
            && el.closest && el.closest('#NMI_TYPE_CUSTOM'));
    }

    function missingButtons(existing, toolbar) {
        return toolbar.split('|').map(function(group) {
            return group.split(/\s+/).filter(function(button) {
                return button && existing.indexOf(button) === -1;
            }).join(' ');
        }).filter(Boolean).join(' | ');
    }

    function extend(options) {
        // Copy, because the legacy settings object is shared by all editors
        options = Object.assign({}, options);
        var toolbar = String(options.toolbar || '');
        var existing = toolbar.split(/[\s|]+/);
        var prepend = config.prepend.map(function(t) { return missingButtons(existing, t); }).filter(Boolean);
        var append = config.append.map(function(t) { return missingButtons(existing, t); }).filter(Boolean);
        options.toolbar = prepend.concat([toolbar], append).filter(Boolean).join(' | ');

        var plugins = Array.isArray(options.plugins)
            ? options.plugins.slice()
            : String(options.plugins || '').split(/[\s,]+/).filter(Boolean);
        config.plugins.forEach(function(plugin) {
            if (plugins.indexOf(plugin) === -1) {
                plugins.push(plugin);
            }
        });
        options.plugins = plugins;

        if (config.blockFormats) {
            options.block_formats = config.blockFormats;
        }
        return options;
    }

    // Wrap createEditor() of the TinyMCE instance that is global right now.
    // The backend has two instances (the Vue bundle and the standalone
    // script loaded by the TinyMCE plugin) and the global one can change
    // after this script runs, so this is done just before editors are made.
    function wrapCurrentInstance() {
        var tm = window.tinyMCE || window.tinymce;
        var manager = tm && (tm.EditorManager || tm);
        if (!manager || typeof manager.createEditor !== 'function' || manager.textEditorExtrasWrapped) {
            return;
        }
        var createEditor = manager.createEditor;
        manager.createEditor = function(id, options) {
            return createEditor.call(this, id, isCustomPageContent(id) ? extend(options) : options);
        };
        manager.textEditorExtrasWrapped = true;
    }

    // Legacy forms create their editors in Handler.initializeTinyMCE()
    function patchHandler() {
        var pkp = window.jQuery && window.jQuery.pkp;
        var Handler = pkp && pkp.classes && pkp.classes.Handler;
        if (!Handler || typeof Handler.prototype.initializeTinyMCE !== 'function') {
            return false;
        }
        if (!Handler.prototype.textEditorExtrasPatched) {
            var initializeTinyMCE = Handler.prototype.initializeTinyMCE;
            Handler.prototype.initializeTinyMCE = function() {
                wrapCurrentInstance();
                return initializeTinyMCE.apply(this, arguments);
            };
            Handler.prototype.textEditorExtrasPatched = true;
        }
        return true;
    }

    if (!patchHandler()) {
        document.addEventListener('DOMContentLoaded', patchHandler);
    }
})();
JS;
    }

    /**
     * Get the TinyMCE block_formats option for the headings control.
     *
     * H1 is deliberately excluded: the page template already provides the
     * <h1> (journal or article title) and a second one in the content breaks
     * the heading hierarchy.
     */
    protected function getBlockFormats(): string
    {
        // ";" and "=" are separators in the block_formats syntax
        $clean = fn (string $label) => str_replace([';', '='], '', $label);

        $formats = [$clean(__('plugins.generic.textEditorExtras.headings.paragraph')) . '=p'];
        foreach ([2, 3, 4, 5, 6] as $level) {
            $formats[] = $clean(__('plugins.generic.textEditorExtras.headings.heading', ['level' => $level])) . '=h' . $level;
        }

        return implode('; ', $formats);
    }
}
