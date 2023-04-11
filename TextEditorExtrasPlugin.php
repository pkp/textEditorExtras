<?php
/**
 * @file plugins/generic/textEditorExtras/TextEditorExtrasPlugin.php
 *
 * Copyright (c) 2017-2023 Simon Fraser University
 * Copyright (c) 2017-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TextEditorExtrasPlugin
 * @brief Plugin class for the TextEditorExtras plugin.
 */

namespace APP\plugins\generic\textEditorExtras;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\linkAction\request\AjaxModal;
use PKP\linkAction\LinkAction;
use PKP\core\JSONMessage;
use APP\core\Application;

class TextEditorExtrasPlugin extends GenericPlugin {

    /**
     * @copydoc GenericPlugin::register()
     */
    public function register($category, $path, $mainContextId = NULL) {
        $success = parent::register($category, $path);
        if ($success && $this->getEnabled()) {
            Hook::add('Form::config::before', [$this, 'modifyForms']);
        }
        return $success;
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
     * Add a settings action to the plugin's entry in the
     * plugins list.
     *
     * @param Request $request
     * @param array $actionArgs
     * @return array
     */
    public function getActions($request, $actionArgs) {

        // Get the existing actions
        $actions = parent::getActions($request, $actionArgs);

        // Only add the settings action when the plugin is enabled
        if (!$this->getEnabled()) {
            return $actions;
        }

        // Create a LinkAction that will make a request to the
        // plugin's `manage` method with the `settings` verb.
        $router = $request->getRouter();
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url(
                    $request,
                    null,
                    null,
                    'manage',
                    null,
                    [
                        'verb' => 'settings',
                        'plugin' => $this->getName(),
                        'category' => 'generic'
                    ]
                ),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );

        // Add the LinkAction to the existing actions.
        // Make it the first action to be consistent with
        // other plugins.
        array_unshift($actions, $linkAction);

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
        switch ($request->getUserVar('verb')) {
            case 'settings':
                // Load the custom form
                $form = new TextEditorExtrasSettingsForm($this);

                // Fetch the form the first time it loads, before
                // the user has tried to save it
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }

                // Validate and save the form data
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
        }
        return parent::manage($args, $request);
    }

    /**
     * Add the publication statement to the article details page.
     *
     * @param string $hookName API::uploadPublicFile::permissions
     * @param FormComponent $form
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

        $publicFileApiUrl = $request->getDispatcher()->url($request, ROUTE_API, $context->getPath(), '_uploadPublicFile');

        $additions = $allAdditions[$form->id];
        foreach ($additions as $fieldName => $additions) {
            $field = $form->getField($fieldName);
            if (!$field || !is_a($field, 'PKP\components\forms\FieldRichTextArea')) {
                continue;
            }
            foreach ($additions as $addition) {
                // Make sure the extra control has not already been added
                if (strpos($field->toolbar, $addition)) {
                    continue;
                }
                switch ($addition) {
                    case 'code':
                        $field->toolbar .= ' | ' . $addition;
                        $field->plugins .= ',' . $addition;
                        break;
                    case 'image':
                        $field->toolbar .= ' | ' . $addition;
                        $field->plugins .= ',' . $addition;
                        $field->uploadUrl = $publicFileApiUrl;
                        break;
                    case 'table':
                        $field->toolbar .= ' | table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol';
                        $field->plugins .= ',' . $addition;
                }
            }
        }
    }
}
