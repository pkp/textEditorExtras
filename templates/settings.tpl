{**
 * plugins/generic/textEditorExtras/templates/settings.tpl
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the textEditorExtras plugin.
 *}
<script>
	$(function() {ldelim}
		$('#textEditorExtrasSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{translate key="plugins.generic.textEditorExtras.settings.description"}

<form
	class="pkp_form"
	id="textEditorExtrasSettings"
	method="POST"
	action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	<!-- Always add the csrf token to secure your form -->
	{csrf}

	<table class="pkpTable">
		<thead>
			<td></td>
			<th>{translate key="plugins.generic.textEditorExtras.setting.images"}</th>
			<th>{translate key="plugins.generic.textEditorExtras.setting.code"}</th>
			<th>{translate key="plugins.generic.textEditorExtras.setting.tables"}</th>
		</thead>
		<tbody>
			<tr>
				<th scope="row">{translate key="manager.setup.contextSummary"}</th>
				<td><input type="checkbox" name="additions[masthead][description][]" value="image" {if is_array($additions.masthead.description) && in_array('image', $additions.masthead.description)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[masthead][description][]" value="code" {if is_array($additions.masthead.description) && in_array('code', $additions.masthead.description)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[masthead][description][]" value="table" {if is_array($additions.masthead.description) && in_array('table', $additions.masthead.description)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="manager.setup.authorGuidelines"}</th>
				<td><input type="checkbox" name="additions[authorGuidelines][authorGuidelines][]" value="image" {if is_array($additions.authorGuidelines.authorGuidelines) && in_array('image', $additions.authorGuidelines.authorGuidelines)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[authorGuidelines][authorGuidelines][]" value="code" {if is_array($additions.authorGuidelines.authorGuidelines) && in_array('code', $additions.authorGuidelines.authorGuidelines)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[authorGuidelines][authorGuidelines][]" value="table" {if is_array($additions.authorGuidelines.authorGuidelines) && in_array('table', $additions.authorGuidelines.authorGuidelines)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="manager.setup.copyrightNotice"}</th>
				<td><input type="checkbox" name="additions[authorGuidelines][copyrightNotice][]" value="image" {if is_array($additions.authorGuidelines.copyrightNotice) && in_array('image', $additions.authorGuidelines.copyrightNotice)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[authorGuidelines][copyrightNotice][]" value="code" {if is_array($additions.authorGuidelines.copyrightNotice) && in_array('code', $additions.authorGuidelines.copyrightNotice)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[authorGuidelines][copyrightNotice][]" value="table" {if is_array($additions.authorGuidelines.copyrightNotice) && in_array('table', $additions.authorGuidelines.copyrightNotice)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="manager.distribution.licenseTerms"}</th>
				<td><input type="checkbox" name="additions[license][licenseTerms][]" value="image" {if is_array($additions.license.licenseTerms) && in_array('image', $additions.license.licenseTerms)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[license][licenseTerms][]" value="code" {if is_array($additions.license.licenseTerms) && in_array('code', $additions.license.licenseTerms)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[license][licenseTerms][]" value="table" {if is_array($additions.license.licenseTerms) && in_array('table', $additions.license.licenseTerms)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="manager.setup.reviewGuidelines"}</th>
				<td><input type="checkbox" name="additions[reviewerGuidance][reviewGuidelines][]" value="image" {if is_array($additions.reviewerGuidance.reviewGuidelines) && in_array('image', $additions.reviewerGuidance.reviewGuidelines)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[reviewerGuidance][reviewGuidelines][]" value="code" {if is_array($additions.reviewerGuidance.reviewGuidelines) && in_array('code', $additions.reviewerGuidance.reviewGuidelines)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[reviewerGuidance][reviewGuidelines][]" value="table" {if is_array($additions.reviewerGuidance.reviewGuidelines) && in_array('table', $additions.reviewerGuidance.reviewGuidelines)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="manager.setup.competingInterests"}</th>
				<td><input type="checkbox" name="additions[reviewerGuidance][competingInterests][]" value="image" {if is_array($additions.reviewerGuidance.competingInterests) && in_array('image', $additions.reviewerGuidance.competingInterests)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[reviewerGuidance][competingInterests][]" value="code" {if is_array($additions.reviewerGuidance.competingInterests) && in_array('code', $additions.reviewerGuidance.competingInterests)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[reviewerGuidance][competingInterests][]" value="table" {if is_array($additions.reviewerGuidance.competingInterests) && in_array('table', $additions.reviewerGuidance.competingInterests)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="plugins.generic.textEditorExtras.setting.emailTemplateBody"}</th>
				<td><input type="checkbox" name="additions[editEmailTemplate][body][]" value="image" {if is_array($additions.editEmailTemplate.body) && in_array('image', $additions.editEmailTemplate.body)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[editEmailTemplate][body][]" value="code" {if is_array($additions.editEmailTemplate.body) && in_array('code', $additions.editEmailTemplate.body)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[editEmailTemplate][body][]" value="table" {if is_array($additions.editEmailTemplate.body) && in_array('table', $additions.editEmailTemplate.body)}checked="checked"{/if}></td>
			</tr>
			<tr>
				<th scope="row">{translate key="common.abstract"}</th>
				<td><input type="checkbox" name="additions[titleAbstract][abstract][]" value="image" {if is_array($additions.titleAbstract.abstract) && in_array('image', $additions.titleAbstract.abstract)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[titleAbstract][abstract][]" value="code" {if is_array($additions.titleAbstract.abstract) && in_array('code', $additions.titleAbstract.abstract)}checked="checked"{/if}></td>
				<td><input type="checkbox" name="additions[titleAbstract][abstract][]" value="table" {if is_array($additions.titleAbstract.abstract) && in_array('table', $additions.titleAbstract.abstract)}checked="checked"{/if}></td>
			</tr>
			{if $currentContext->getData('enableAnnouncements')}
				<tr>
					<th scope="row">{translate key="announcement.announcements"}</th>
					<td><input type="checkbox" name="additions[announcement][description][]" value="image" {if in_array('image', $additions.announcement.description)}checked="checked"{/if}></td>
					<td><input type="checkbox" name="additions[announcement][description][]" value="code" {if in_array('code', $additions.announcement.description)}checked="checked"{/if}></td>
					<td><input type="checkbox" name="additions[announcement][description][]" value="table" {if in_array('table', $additions.announcement.description)}checked="checked"{/if}></td>
				</tr>
				<tr>
					<th scope="row">{translate key="plugins.generic.textEditorExtras.setting.announcements.descriptionShort"}</th>
					<td><input type="checkbox" name="additions[announcement][descriptionShort][]" value="image" {if in_array('image', $additions.announcement.descriptionShort)}checked="checked"{/if}></td>
					<td><input type="checkbox" name="additions[announcement][descriptionShort][]" value="code" {if in_array('code', $additions.announcement.descriptionShort)}checked="checked"{/if}></td>
					<td><input type="checkbox" name="additions[announcement][descriptionShort][]" value="table" {if in_array('table', $additions.announcement.descriptionShort)}checked="checked"{/if}></td>
				</tr>
			{/if}
		</tbody>
	</table>

	{fbvFormButtons submitText="common.save"}
</form>
