{**
 * submitSuccess.tpl
 *
 * Copyright (c) 2013 University of Potsdam, 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a message indicating that the article was successfuly submitted.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.paperPackageEdit.success"}
{include file="common/header.tpl"}
{/strip}

<p>{translate key="plugins.importexport.paperPackageEdit.successDescription"}  <a href="{plugin_url}">{translate key="plugins.importexport.paperPackageEdit.successReturn"}</a></p>

{include file="common/footer.tpl"}
