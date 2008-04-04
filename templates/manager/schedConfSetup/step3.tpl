{**
 * step3.tpl
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of conference setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="manager.schedConfSetup.review.title"}
{include file="manager/schedConfSetup/setupHeader.tpl"}

<form name="setupForm" method="post" action="{url op="saveSchedConfSetup" path="3"}">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"setupFormUrl" op="schedConfSetup" path="3"}
			{form_language_chooser form="setupForm" url=$setupFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
</table>
{/if}

<h3>3.1 {translate key="manager.schedConfSetup.review.reviewPolicy"}</h3>

<p>{translate key="manager.schedConfSetup.review.reviewDescription"}</p>

<h4>{translate key="manager.schedConfSetup.review.reviewPolicy"}</h4>

<p><textarea name="reviewPolicy[{$formLocale|escape}]" id="reviewPolicy" rows="12" cols="60" class="textArea">{$reviewPolicy[$formLocale]|escape}</textarea></p>

<div class="separator"></div>

<h3>3.2 {translate key="manager.schedConfSetup.review.peerReview"}</h3>

<p>{translate key="manager.schedConfSetup.review.reviewGuidelinesDescription"}</p>

<p><textarea name="reviewGuidelines[{$formLocale|escape}]" id="reviewGuidelines" rows="12" cols="60" class="textArea">{$reviewGuidelines[$formLocale]|escape}</textarea></p>

<script type="text/javascript">
	{literal}
	<!--
		function toggleAllowSetInviteReminder(form) {
			form.numDaysBeforeInviteReminder.disabled = !form.numDaysBeforeInviteReminder.disabled;
		}
		function toggleAllowSetSubmitReminder(form) {
			form.numDaysBeforeSubmitReminder.disabled = !form.numDaysBeforeSubmitReminder.disabled;
		}
	// -->
	{/literal}
</script>

<p>
	{translate key="manager.schedConfSetup.review.numWeeksPerReview1"}&nbsp;<input type="text" name="numWeeksPerReview" id="numWeeksPerReview" value="{$numWeeksPerReview|escape}" size="2" maxlength="8" class="textField" />&nbsp;{translate key="manager.schedConfSetup.review.numWeeksPerReview2"}<br/>
	<input type="checkbox" name="restrictReviewerFileAccess" id="restrictReviewerFileAccess" value="1"{if $restrictReviewerFileAccess} checked="checked"{/if} />&nbsp;<label for="restrictReviewerFileAccess">{translate key="manager.schedConfSetup.review.restrictReviewerFileAccess"}</label>
</p>

<p>
	<input type="checkbox" name="reviewerAccessKeysEnabled" id="reviewerAccessKeysEnabled" value="1"{if $reviewerAccessKeysEnabled} checked="checked"{/if} />&nbsp;<label for="reviewerAccessKeysEnabled">{translate key="manager.schedConfSetup.review.reviewerAccessKeysEnabled"}</label><br/>
	<span class="instruct">{translate key="manager.schedConfSetup.review.reviewerAccessKeysEnabled.description"}</span>
</p>

<p>
	{translate key="manager.schedConfSetup.review.automatedReminders"}:<br/>
	<input type="checkbox" {if !$scheduledTasksEnabled}disabled="disabled" {/if} name="remindForInvite" id="remindForInvite" value="1" onclick="toggleAllowSetInviteReminder(this.form)"{if $remindForInvite} checked="checked"{/if} />&nbsp;
	<label for="remindForInvite">{translate key="manager.schedConfSetup.review.remindForInvite1"}</label>
	<select name="numDaysBeforeInviteReminder" size="1" class="selectMenu"{if not $remindForInvite} disabled="disabled"{/if}>
		{section name="inviteDayOptions" start=3 loop=11}
		<option value="{$smarty.section.inviteDayOptions.index}"{if $numDaysBeforeInviteReminder eq $smarty.section.inviteDayOptions.index or ($smarty.section.inviteDayOptions.index eq 5 and not $remindForInvite)} selected="selected"{/if}>{$smarty.section.inviteDayOptions.index}</option>
		{/section}
	</select>
	{translate key="manager.schedConfSetup.review.remindForInvite2"}
	<br/>

	<input type="checkbox" {if !$scheduledTasksEnabled}disabled="disabled" {/if}name="remindForSubmit" id="remindForSubmit" value="1" onclick="toggleAllowSetSubmitReminder(this.form)"{if $remindForSubmit} checked="checked"{/if} />&nbsp;
	<label for="remindForSubmit">{translate key="manager.schedConfSetup.review.remindForSubmit1"}</label>
	<select name="numDaysBeforeSubmitReminder" size="1" class="selectMenu"{if not $remindForSubmit} disabled="disabled"{/if}>
		{section name="submitDayOptions" start=0 loop=11}
			<option value="{$smarty.section.submitDayOptions.index}"{if $numDaysBeforeSubmitReminder eq $smarty.section.submitDayOptions.index} selected="selected"{/if}>{$smarty.section.submitDayOptions.index}</option>
	{/section}
	</select>
	{translate key="manager.schedConfSetup.review.remindForSubmit2"}

	{if !$scheduledTasksEnabled}
	<br/>
	{translate key="manager.schedConfSetup.review.automatedRemindersDisabled"}
	{/if}
</p>

<p>
	<input type="checkbox" name="rateReviewerOnQuality" id="rateReviewerOnQuality" value="1"{if $rateReviewerOnQuality} checked="checked"{/if} />&nbsp;<label for="rateReviewerOnQuality">{translate key="manager.schedConfSetup.review.onQuality"}</label>
</p>

<div class="separator"></div>

<h3>3.3 {translate key="manager.schedConfSetup.review.directorDecision"}</h3>

<p>
	<input type="checkbox" name="notifyAllPresentersOnDecision" id="notifyAllPresentersOnDecision" value="1"{if $notifyAllPresentersOnDecision} checked="checked"{/if} />&nbsp;<label for="notifyAllPresentersOnDecision">{translate key="manager.schedConfSetup.review.notifyAllPresentersOnDecision"}</label>
</p>

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="schedConfSetup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
