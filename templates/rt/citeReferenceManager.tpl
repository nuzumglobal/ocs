{**
 * citeReferenceManager.tpl
 *
 * Copyright (c) 2003-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Reference Manager citation format generator
 *
 * $Id$
 *}
{if $galleyId}
	{url|assign:"paperUrl" page="paper" op="view" path=$paperId|to_array:$galleyId}
{else}
	{url|assign:"paperUrl" page="paper" op="view" path=$paperId}
{/if}
TY  - JOUR
{foreach from=$paper->getAuthors() item=author}
AU  - {$author->getFullName(true)|escape}
{/foreach}
PY  - {$paper->getDatePublished()|date_format:"%Y"}
TI  - {$paper->getPaperTitle()|strip_tags}
JF  - {$conference->getTitle()|escape}; {$issue->getIssueIdentification()|escape}
Y2  - {$paper->getDatePublished()|date_format:"%Y"}
KW  - {$paper->getSubject()|replace:';':','|escape}
N2  - {$paper->getPaperAbstract()|strip_tags|replace:"\n":" "|replace:"\r":" "}
UR  - {$paperUrl}

