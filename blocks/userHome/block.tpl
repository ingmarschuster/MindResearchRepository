{**
 * block.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- "Developed By" block.
 *
 * $Id$
 *}
     <h2>{translate key="plugins.block.userHome.userPackages"}</h2>
     <p>{translate key="plugins.block.userHome.userPackagesDescription"}</p>
     <p>{translate key="plugins.block.userHome.UploadDescription} <a href="{$uploadLink}">{translate key="plugins.block.userHome.UploadPlugin"}</a></p>
{if !isset($user)}
     <p>{translate key="plugins.block.userHome.noPackagesDescription"}</p>
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
           </tr>
      </table>
{/foreach}
</div>
{/if}
<!--</form>-->

