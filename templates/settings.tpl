{**
 * plugins/generic/textEditorExtras/templates/settings.tpl
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the textEditorExtras plugin.
 *
 * The fields and controls are defined in TextEditorExtrasPlugin::getFields()
 * and TextEditorExtrasPlugin::getControls().
 *}
<script>
	$(function() {ldelim}
		$('#textEditorExtrasSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{translate key="plugins.generic.textEditorExtras.settings.description"}

{if $missingTags}
	<div class="pkp_notification" role="alert">
		<div class="notifyWarning">
			<p>{translate key="plugins.generic.textEditorExtras.settings.missingTags" tags=$missingTags|escape}</p>
		</div>
	</div>
{/if}

<form
	class="pkp_form"
	id="textEditorExtrasSettings"
	method="POST"
	action="{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	<!-- Always add the csrf token to secure your form -->
	{csrf}

	<table class="pkpTable">
		<thead>
			<tr>
				<td></td>
				{foreach from=$controls item=controlLabel}
					<th scope="col">{$controlLabel|escape}</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach from=$rows item=row}
				<tr>
					<th scope="row">{$row.label|escape}</th>
					{foreach from=$controls key=controlName item=controlLabel}
						<td>
							<input
								type="checkbox"
								name="additions[{$row.formId|escape}][{$row.fieldName|escape}][]"
								value="{$controlName|escape}"
								aria-label="{$row.label|escape}: {$controlLabel|escape}"
								{if in_array($controlName, $row.selected)}checked="checked"{/if}
							>
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</tbody>
	</table>

	{fbvFormButtons submitText="common.save"}
</form>
