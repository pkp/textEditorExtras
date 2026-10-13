<?php
/**
 * @file plugins/generic/textEditorExtras/TextEditorExtrasSettingsForm.php
 *
 * Copyright (c) 2017-2025 Simon Fraser University
 * Copyright (c) 2017-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TextEditorExtrasPlugin
 * @brief Settings form for the TextEditorExtras plugin.
 */

namespace APP\plugins\generic\textEditorExtras;

use PKP\form\Form;
use PKP\form\validation\FormValidatorPost;
use PKP\form\validation\FormValidatorCSRF;
use APP\notification\NotificationManager;
use APP\core\Application;
use APP\template\TemplateManager;
use PKP\context\Context;
use PKP\notification\Notification;

class TextEditorExtrasSettingsForm extends Form {
    public function __construct(public TextEditorExtrasPlugin $plugin) {

        // Define the settings template and store a copy of the plugin object
        parent::__construct($plugin->getTemplateResource('settings.tpl'));

        // Always add POST and CSRF validation to secure your form.
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * Load settings already saved in the database
     *
     * Settings are stored by context, so that each journal or press
     * can have different settings.
     */
    public function initData() {
        $contextId = Application::get()->getRequest()->getContext()->getId();
        $settings = $this->plugin->getSetting($contextId, 'additions');
        $this->setData('additions', is_array($settings) ? $settings : []);
        parent::initData();
    }

    /**
     * Load data that was submitted with the form
     */
    public function readInputData() {
        $this->readUserVars(['additions']);
        parent::readInputData();
    }

    /**
     * Fetch any additional data needed for your form.
     *
     * Data assigned to the form using $this->setData() during the
     * initData() or readInputData() methods will be passed to the
     * template.
     *
     * @return string
     */
    public function fetch($request, $template = null, $display = false) {
        $context = $request->getContext();
        $additions = (array) $this->getData('additions');

        // Build the table rows here so that the template only loops over
        // the fields and controls defined in the plugin class.
        $rows = [];
        foreach ($this->plugin->getFields($context) as $formId => $formFields) {
            foreach ($formFields as $fieldName => $labelKey) {
                $rows[] = [
                    'formId' => $formId,
                    'fieldName' => $fieldName,
                    'label' => __($labelKey),
                    'selected' => (array) ($additions[$formId][$fieldName] ?? []),
                ];
            }
        }

        $controls = [];
        foreach ($this->plugin->getControls() as $controlName => $control) {
            $controls[$controlName] = __($control['label']);
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'pluginName' => $this->plugin->getName(),
            'controls' => $controls,
            'rows' => $rows,
            'missingTags' => implode(', ', $this->plugin->getMissingHtmlTags($additions)),
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Keep only known forms, fields and controls from the submitted data.
     *
     * Settings for fields that are not shown in the form (e.g. announcement
     * fields while announcements are disabled) are preserved.
     */
    protected function sanitizeAdditions(array $input, array $saved, ?Context $context): array
    {
        $controlNames = array_keys($this->plugin->getControls());
        $fields = $this->plugin->getFields($context);

        $additions = [];
        foreach ($saved as $formId => $formFields) {
            foreach ((array) $formFields as $fieldName => $selected) {
                if (!isset($fields[$formId][$fieldName])) {
                    $additions[$formId][$fieldName] = $selected;
                }
            }
        }

        foreach ($fields as $formId => $formFields) {
            foreach ($formFields as $fieldName => $labelKey) {
                $selected = (array) ($input[$formId][$fieldName] ?? []);
                // Keep the order of getControls() regardless of the posted order
                $additions[$formId][$fieldName] = array_values(array_intersect($controlNames, $selected));
            }
        }

        return $additions;
    }

    /**
     * Save the settings
     */
    public function execute(...$functionArgs) {
        $context = Application::get()->getRequest()->getContext();
        $additions = $this->sanitizeAdditions(
            (array) $this->getData('additions'),
            (array) $this->plugin->getSetting($context->getId(), 'additions'),
            $context
        );
        $this->plugin->updateSetting($context->getId(), 'additions', $additions);

        // Tell the user that the save was successful.
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            Application::get()->getRequest()->getUser()->getId(),
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('common.changesSaved')]
        );

        return parent::execute(...$functionArgs);
    }
}
