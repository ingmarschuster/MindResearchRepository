{**
* userPackages.tpl
*
* Copyright (c) 2013 University of Potsdam, 2003-2012 John Willinsky
* Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
*
* Display a message indicating that the article was successfuly submitted.
*
* $Id$
*}
{strip}
{assign var="pageTitle" value="plugins.generic.rpository.userPackages"}
{include file="common/header.tpl"}
{/strip}
<form method="post" action="{plugin_url path="userPackages"}">
     <p>{translate key="plugins.generic.rpository.userPackagesDescription"}</p>

{if !isset($user)}
     <p>{translate key="plugins.generic.rpository.noPackagesDescription"}</p>
{else}
<div>
{foreach from=$articles item=article}
    <table class="data" width="100%">  
	  <tr valign="top">
	       <td align="left" width="80%" class="label"> <a href="{$viewLink}{$article->getId()}">{$article->getTitle($locale)}</td>
               <td align="right" width="20%" class="label"> <a href="{$editLink}{$article->getId()}">(edit package)</a> </td>
	  </tr>
          <tr valign="top">
	      <td align="left" width="80%" class="label">{$article->getAuthorString()}</td>
	      <!--<td width="20%" class="label">{translate key="plugins.generic.rpository.nix"}</td>-->
	  </tr>
    </table>
{/foreach}
</div>
{/if}
</form>
{include file="common/footer.tpl"}
