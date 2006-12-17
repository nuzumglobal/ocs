{**
 * management.tpl
 *
 * Copyright (c) 2003-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission management table.
 *
 * $Id$
 *}

<a name="submission"></a>
<h3>{translate key="paper.submission"}</h3>

{assign var="submissionFile" value=$submission->getSubmissionFile()}
{assign var="suppFiles" value=$submission->getSuppFiles()}

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="paper.authors"}</td>
		<td width="80%" colspan="2" class="value">
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl authorsPaperId=$submission->getPaperId() paperId=$submission->getPaperId()}
			{$submission->getAuthorString()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="paper.title"}</td>
		<td colspan="2" class="value">{$submission->getPaperTitle()|strip_unsafe_html}</td>
	</tr>
	<tr>
		<td class="label">{translate key="submission.originalFile"}</td>
		<td colspan="2" class="value">
			{if $submissionFile}
				<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$submissionFile->getFileId()}" class="file">{$submissionFile->getFileName()|escape}</a>&nbsp;&nbsp;{$submissionFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="common.none"}
			{/if}
		</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="paper.suppFilesAbbrev"}</td>
		<td colspan="2" class="value">
			{foreach name="suppFiles" from=$suppFiles item=suppFile}
				<a href="{url op="editSuppFile" path=$submission->getPaperId()|to_array:$suppFile->getSuppFileId()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;{$suppFile->getDateModified()|date_format:$dateFormatShort}&nbsp;&nbsp;<a href="{url op="editSuppFile" path=$submission->getPaperId()|to_array:$suppFile->getSuppFileId()}" class="action">{translate key="common.edit"}</a>&nbsp;&nbsp;&nbsp;&nbsp;{if !$notFirst}&nbsp;&nbsp;&nbsp;&nbsp;<a href="{url op="addSuppFile" path=$submission->getPaperId()}" class="action">{translate key="submission.addSuppFile"}</a>{/if}<br />
				{assign var=notFirst value=1}
			{foreachelse}
				{translate key="common.none"}&nbsp;&nbsp;&nbsp;&nbsp;<a href="{url op="addSuppFile" path=$submission->getPaperId()}" class="action">{translate key="submission.addSuppFile"}</a>
			{/foreach}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="submission.submitter"}</td>
		<td colspan="2" class="value">
			{assign var="submitter" value=$submission->getUser()}
			{assign var=emailString value="`$submitter->getFullName()` <`$submitter->getEmail()`>"}
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getPaperTitle|strip_tags paperId=$submission->getPaperId()}
			{$submitter->getFullName()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="common.dateSubmitted"}</td>
		<td>{$submission->getDateSubmitted()|date_format:$dateFormatShort}</td>
	</tr>
	<tr>
		<td class="label">{translate key="track.track"}</td>
		<td class="value">{$submission->getTrackTitle()|escape}</td>
		<td class="value"><form action="{url op="updateTrack" path=$submission->getPaperId()}" method="post">{translate key="submission.changeTrack"} <select name="track" size="1" class="selectMenu">{html_options options=$tracks selected=$submission->getTrackId()}</select> <input type="submit" value="{translate key="common.record"}" class="button" /></form></td>
	</tr>
	<tr>
		<td class="label">{translate key="track.secondaryTrack"}</td>
		<td class="value">
			{if $submission->getSecondaryTrackId() == NULL}
				{translate key="common.none"}
			{else}
				{$submission->getSecondaryTrackTitle()|escape}
			{/if}
		</td>
		<td class="value"><form action="{url op="updateSecondaryTrack" path=$submission->getPaperId()}" method="post">{translate key="submission.changeTrack"}
			<select name="secondaryTrack" size="1" class="selectMenu">
				{html_options options=$secondaryTracks selected=$submission->getSecondaryTrackId()}
			</select> <input type="submit" value="{translate key="common.record"}" class="button" /></form></td>
	</tr>
	{if $submission->getCommentsToEditor()}
	<tr valign="top">
		<td width="20%" class="label">{translate key="paper.commentsToEditor"}</td>
		<td width="80%" colspan="2" class="data">{$submission->getCommentsToEditor()|strip_unsafe_html|nl2br}</td>
	</tr>
	{/if}
	{if $publishedPaper}
	<tr>
		<td class="label">{translate key="submission.abstractViews"}</td>
		<td>{$publishedPaper->getViews()}</td>
	</tr>
	{/if}
</table>
