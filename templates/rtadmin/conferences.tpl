{**
 * conferences.tpl
 *
 * Copyright (c) 2003-2005 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * RTAdmin conferences list
 *
 * $Id$
 *}

{assign var="pageTitle" value="rt.readingTools"}
{include file="common/header.tpl"}

<h3>{translate key="user.myConferences"}</h3>

<ul class="plain">
{foreach from=$conferences item=conference}
<li>&#187; <a href="{url conference=$conference->getPath() event="index" page="rtadmin"}">{$conference->getTitle()|escape}</a></li>
{/foreach}
</ul>

{include file="common/footer.tpl"}
