{**
 * registrationPolicyForm.tpl
 *
 * Copyright (c) 2003-2006 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Setup registration policies.
 *
 * $Id$
 *}

{assign var="pageTitle" value="director.registrationPolicies"}
{assign var="pageId" value="director.registrationPolicies"}
{include file="common/header.tpl"}

<ul class="menu">
	<li><a href="{url op="registrations"}">{translate key="director.registrations"}</a></li>
	<li><a href="{url op="registrationTypes"}">{translate key="director.registrationTypes"}</a></li>
	<li class="current"><a href="{url op="registrationPolicies"}">{translate key="director.registrationPolicies"}</a></li>
</ul>

{if $registrationPoliciesSaved}
<br/>
{translate key="director.registrationPolicies.registrationPoliciesSaved"}<br />
{/if}

<form method="post" action="{url op="saveRegistrationPolicies"}">
{include file="common/formErrors.tpl"}

	<script type="text/javascript">
		{literal}
		<!--
			function toggleAllowSetDelayedOpenAccessDuration(form) {
				form.delayedOpenAccessDuration.disabled = !form.delayedOpenAccessDuration.disabled;
			}
			function toggleAllowSetBeforeMonthsReminder(form) {
				form.numMonthsBeforeRegistrationExpiryReminder.disabled = !form.numMonthsBeforeRegistrationExpiryReminder.disabled;
			}
			function toggleAllowSetBeforeWeeksReminder(form) {
				form.numWeeksBeforeRegistrationExpiryReminder.disabled = !form.numWeeksBeforeRegistrationExpiryReminder.disabled;
			}
			function toggleAllowSetAfterMonthsReminder(form) {
				form.numMonthsAfterRegistrationExpiryReminder.disabled = !form.numMonthsAfterRegistrationExpiryReminder.disabled;
			}
			function toggleAllowSetAfterWeeksReminder(form) {
				form.numWeeksAfterRegistrationExpiryReminder.disabled = !form.numWeeksAfterRegistrationExpiryReminder.disabled;
			}
		// -->
		{/literal}
	</script>

<h3>{translate key="director.registrationPolicies.registrationContact"}</h3>
<p>{translate key="director.registrationPolicies.registrationContactDescription"}</p>
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="registrationName" key="user.name"}</td>
		<td width="80%" class="value"><input type="text" name="registrationName" id="registrationName" value="{$registrationName|escape}" size="30" maxlength="60" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="registrationEmail" key="user.email"}</td>
		<td width="80%" class="value"><input type="text" name="registrationEmail" id="registrationEmail" value="{$registrationEmail|escape}" size="30" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="registrationPhone" key="user.phone"}</td>
		<td width="80%" class="value"><input type="text" name="registrationPhone" id="registrationPhone" value="{$registrationPhone|escape}" size="15" maxlength="24" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="registrationFax" key="user.fax"}</td>
		<td width="80%" class="value"><input type="text" name="registrationFax" id="registrationFax" value="{$registrationFax|escape}" size="15" maxlength="24" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="registrationMailingAddress" key="common.mailingAddress"}</td>
		<td width="80%" class="value"><textarea name="registrationMailingAddress" id="registrationMailingAddress" rows="6" cols="40" class="textArea">{$registrationMailingAddress|escape}</textarea></td>
	</tr>
</table>


<div class="separator"></div>


<h3>{translate key="director.registrationPolicies.registrationAdditionalInformation"}</h3>
<p>{translate key="director.registrationPolicies.registrationAdditionalInformationDescription"}</p>
<p>
	<textarea name="registrationAdditionalInformation" id="registrationAdditionalInformation" rows="12" cols="60" class="textArea">{$registrationAdditionalInformation|escape}</textarea>
	<br />
	<span class="instruct">{translate key="director.registrationPolicies.htmlInstructions"}</span>
</p>


<div class="separator"></div>


<h3>{translate key="director.registrationPolicies.expiryReminders"}</h3>
<p>{translate key="director.registrationPolicies.expiryRemindersDescription"}</p>

<p>
	<input type="checkbox" name="enableRegistrationExpiryReminderBeforeMonths" id="enableRegistrationExpiryReminderBeforeMonths" value="1" onclick="toggleAllowSetBeforeMonthsReminder(this.form)"{if !$scheduledTasksEnabled} disabled="disabled" {elseif $enableRegistrationExpiryReminderBeforeMonths} checked="checked"{/if} />&nbsp;
	<label for="enableRegistrationExpiryReminderBeforeMonths">{translate key="director.registrationPolicies.expiryReminderBeforeMonths1"}</label>
	<select name="numMonthsBeforeRegistrationExpiryReminder" id="numMonthsBeforeRegistrationExpiryReminder" class="selectMenu"{if not $enableRegistrationExpiryReminderBeforeMonths || !$scheduledTasksEnabled} disabled="disabled"{/if} />{html_options options=$validNumMonthsBeforeExpiry selected=$numMonthsBeforeRegistrationExpiryReminder}</select>
	{translate key="director.registrationPolicies.expiryReminderBeforeMonths2"}
</p>
<p>
	<input type="checkbox" name="enableRegistrationExpiryReminderBeforeWeeks" id="enableRegistrationExpiryReminderBeforeWeeks" value="1" onclick="toggleAllowSetBeforeWeeksReminder(this.form)"{if !$scheduledTasksEnabled} disabled="disabled" {elseif $enableRegistrationExpiryReminderBeforeWeeks} checked="checked"{/if} />&nbsp;
	<label for="enableRegistrationExpiryReminderBeforeWeeks">{translate key="director.registrationPolicies.expiryReminderBeforeWeeks1"}</label>
	<select name="numWeeksBeforeRegistrationExpiryReminder" id="numWeeksBeforeRegistrationExpiryReminder" class="selectMenu"{if not $enableRegistrationExpiryReminderBeforeWeeks || !$scheduledTasksEnabled} disabled="disabled"{/if} />{html_options options=$validNumWeeksBeforeExpiry selected=$numWeeksBeforeRegistrationExpiryReminder}</select>
	{translate key="director.registrationPolicies.expiryReminderBeforeWeeks2"}
</p>
<p>
	<input type="checkbox" name="enableRegistrationExpiryReminderAfterWeeks" id="enableRegistrationExpiryReminderAfterWeeks" value="1" onclick="toggleAllowSetAfterWeeksReminder(this.form)"{if !$scheduledTasksEnabled} disabled="disabled" {elseif $enableRegistrationExpiryReminderAfterWeeks} checked="checked"{/if} />&nbsp;
	<label for="enableRegistrationExpiryReminderAfterWeeks">{translate key="director.registrationPolicies.expiryReminderAfterWeeks1"}</label>
	<select name="numWeeksAfterRegistrationExpiryReminder" id="numWeeksAfterRegistrationExpiryReminder" class="selectMenu"{if not $enableRegistrationExpiryReminderAfterWeeks || !$scheduledTasksEnabled} disabled="disabled"{/if} />{html_options options=$validNumWeeksAfterExpiry selected=$numWeeksAfterRegistrationExpiryReminder}</select>
	{translate key="director.registrationPolicies.expiryReminderAfterWeeks2"}
</p>
<p>
	<input type="checkbox" name="enableRegistrationExpiryReminderAfterMonths" id="enableRegistrationExpiryReminderAfterMonths" value="1" onclick="toggleAllowSetAfterMonthsReminder(this.form)"{if !$scheduledTasksEnabled} disabled="disabled" {elseif $enableRegistrationExpiryReminderAfterMonths} checked="checked"{/if} />&nbsp;
	<label for="enableRegistrationExpiryReminderAfterMonths">{translate key="director.registrationPolicies.expiryReminderAfterMonths1"}</label>
	<select name="numMonthsAfterRegistrationExpiryReminder" id="numMonthsAfterRegistrationExpiryReminder" class="selectMenu"{if not $enableRegistrationExpiryReminderAfterMonths || !$scheduledTasksEnabled} disabled="disabled"{/if} />{html_options options=$validNumMonthsAfterExpiry selected=$numMonthsAfterRegistrationExpiryReminder}</select>
	{translate key="director.registrationPolicies.expiryReminderAfterMonths2"}
</p>

{if !$scheduledTasksEnabled}
	<br/>
	{translate key="director.registrationPolicies.expiryRemindersDisabled"}
{/if}


<div class="separator"></div>


<h3>{translate key="director.registrationPolicies.openAccessOptions"}</h3>
<p>{translate key="director.registrationPolicies.openAccessOptionsDescription"}</p>

	<h4>{translate key="director.registrationPolicies.delayedOpenAccess"}</h4>
	<p>{translate key="director.registrationPolicies.delayedOpenAccessDescription"}</p>
	<input type="checkbox" name="enableDelayedOpenAccess" id="enableDelayedOpenAccess" value="1" onclick="toggleAllowSetDelayedOpenAccessDuration(this.form)" {if $enableDelayedOpenAccess} checked="checked"{/if} />&nbsp;
	<label for="enableDelayedOpenAccess">{translate key="director.registrationPolicies.delayedOpenAccessDescription1"}</label>
	<select name="delayedOpenAccessDuration" id="delayedOpenAccessDuration" class="selectMenu" {if not $enableDelayedOpenAccess} disabled="disabled"{/if} />{html_options options=$validDuration selected=$delayedOpenAccessDuration}</select>
	{translate key="director.registrationPolicies.delayedOpenAccessDescription2"}

	<p>
	<input type="checkbox" name="enableOpenAccessNotification" id="enableOpenAccessNotification" value="1"{if !$scheduledTasksEnabled} disabled="disabled" {elseif $enableOpenAccessNotification} checked="checked"{/if} />&nbsp;
	<label for="enableOpenAccessNotification">{translate key="director.registrationPolicies.openAccessNotificationDescription"}</label>
	{if !$scheduledTasksEnabled}
		<br/>
		{translate key="director.registrationPolicies.openAccessNotificationDisabled"}
	{/if}
	</p>

	<p>{translate key="director.registrationPolicies.delayedOpenAccessPolicyDescription"}</p>
	<p>
	<textarea name="delayedOpenAccessPolicy" id="delayedOpenAccessPolicy" rows="12" cols="60" class="textArea">{$delayedOpenAccessPolicy|escape}</textarea>
	<br />
	<span class="instruct">{translate key="director.registrationPolicies.htmlInstructions"}</span>
	</p>

<p>
	<h4>{translate key="director.registrationPolicies.authorSelfArchive"}</h4>
	<input type="checkbox" name="enableAuthorSelfArchive" id="enableAuthorSelfArchive" value="1"{if $enableAuthorSelfArchive} checked="checked"{/if} />&nbsp;
	<label for="enableAuthorSelfArchive">{translate key="director.registrationPolicies.authorSelfArchiveDescription"}</label>
</p>
<p>
	<textarea name="authorSelfArchivePolicy" id="authorSelfArchivePolicy" rows="12" cols="60" class="textArea">{$authorSelfArchivePolicy|escape}</textarea>
	<br />
	<span class="instruct">{translate key="director.registrationPolicies.htmlInstructions"}</span>
</p>


<div class="separator"></div>


<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="registrationPolicies" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
