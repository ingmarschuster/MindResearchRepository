{**
 * article.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Article View.
 *}
{include file="article/header.tpl"}

{if $galley}
	{if $galley->isHTMLGalley()}
		{$galley->getHTMLContents()}
	{elseif $galley->isPdfGalley()}
		{url|assign:"pdfUrl" op="viewFile" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal) escape=false}
		{translate|assign:"noPluginText" key='article.pdf.pluginMissing'}
		<script type="text/javascript"><!--{literal}
			$(document).ready(function(){
				if ($.browser.webkit) { // PDFObject does not correctly work with safari's built-in PDF viewer
					var embedCode = "<object id='pdfObject' type='application/pdf' data='{/literal}{$pdfUrl|escape:'javascript'}{literal}' width='99%' height='99%'><div id='pluginMissing'>{/literal}{$noPluginText|escape:'javascript'}{literal}</div></object>";
					$("#articlePdf").html(embedCode);
					if($("#pluginMissing").is(":hidden")) {
						$('#fullscreenShow').show();
						$("#articlePdf").resizable({ containment: 'parent', handles: 'se' });
					} else { // Chrome Mac hides the embed object, obscuring the text.  Reinsert.
						$("#articlePdf").html('{/literal}{$noPluginText|escape:"javascript"}{literal}');
					}
				} else {
					var success = new PDFObject({ url: "{/literal}{$pdfUrl|escape:'javascript'}{literal}" }).embed("articlePdf");
					if (success) {
						// PDF was embedded; enbale fullscreen mode and the resizable widget
						$('#fullscreenShow').show();
						$("#articlePdfResizer").resizable({ containment: 'parent', handles: 'se' });
					}
				}
			});
		{/literal}
		// -->
		</script>
		<div id="articlePdfResizer">
			<div id="articlePdf" class="ui-widget-content">
				{translate key="article.pdf.pluginMissing"}
			</div>
		</div>
		<p>
			{* The target="_parent" is for the sake of iphones, which present scroll problems otherwise. *}
			<a class="action" target="_parent" href="{url op="download" path=$articleId|to_array:$galley->getBestGalleyId($currentJournal)}">{translate key="article.pdf.download"}</a>
			<a class="action" href="#" id="fullscreenShow">{translate key="common.fullscreen"}</a>
			<a class="action" href="#" id="fullscreenHide">{translate key="common.fullscreenOff"}</a>
		</p>
	{/if}
{else}
	<div id="topBar">
		{assign var=galleys value=$article->getGalleys()}
		{if $galleys && $subscriptionRequired && $showGalleyLinks}
			<div id="accessKey">
				<img src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
				{translate key="reader.openAccess"}&nbsp;
				<img src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
				{if $purchaseArticleEnabled}
					{translate key="reader.subscriptionOrFeeAccess"}
				{else}
					{translate key="reader.subscriptionAccess"}
				{/if}
			</div>
		{/if}
	</div>
	{if $coverPagePath}
		<div id="articleCoverImage"><img src="{$coverPagePath|escape}{$coverPageFileName|escape}"{if $coverPageAltText != ''} alt="{$coverPageAltText|escape}"{else} alt="{translate key="article.coverPage.altText"}"{/if}{if $width} width="{$width|escape}"{/if}{if $height} height="{$height|escape}"{/if}/>
		</div>
	{/if}
	{call_hook name="Templates::Article::Article::ArticleCoverImage"}
	<div id="articleTitle"><h3>{$article->getLocalizedTitle()|strip_unsafe_html}</h3></div>
	<div id="authorString"><em>{$article->getAuthorString()|escape}</em></div>
	<br />
        {if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain)}
                {assign var=hasAccess value=1}
        {else}
                {assign var=hasAccess value=0}
        {/if}
	{if $galleys}
                {translate key="reader.fullText"}
                {if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
                        {foreach from=$article->getGalleys() item=galley name=galleyList}
				<a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file" target="_parent">{$galley->getGalleyLabel()|escape}</a> &nbsp; Paper Package: {$fileName} <span onmouseover="$('#filesList').show()" onmouseout="$('#filesList').hide()"><a href="{$rpositoryBase}{$tarFile}">tar.gz</a> <a href="{$rpositoryBase}{$zipFile}">zip</a></span> {if $userIsEditor} or <a href="{$paperPackageEditPlugin}">edit</a> {/if} 

<!--   <table class="data" width="100%">
       <tr valign="top">
                {if $pid ne 0}
                   <td width="50%" class="label">PID: {$pid}</td> 
                {else}
                   <td width="50%" class="label">PID: not assigned yet</td>  
                {/if}
            <td width="50%" class="label">Package Name:</td>
	</tr>
    </table> -->

                                {if $pid ne 0}
                                      <span style="float:right">PID: {$pid}</span>
                                {else}
                                      <span style="float:right">PID: none yet</span>
                                {/if}
<!--                                <p>Complete Paper Package: <a href="{$rpositoryBase}{$fileName}">download</a></p>-->
                                {if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
                                        {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}
                                                <img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
                                        {else}
                                                <img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
                                        {/if}
                                {/if}
                        {/foreach}
                        {if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
                                {if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
                                        <img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
                                {else}
                                        <img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
                                {/if}
                        {/if}
                {else}
                        &nbsp;<a href="{url page="about" op="subscriptions"}" target="_parent">{translate key="reader.subscribersOnly"}</a>
                {/if}
        {/if}

        <div id="filesList">
	<h4>Package Content</h4>
	     <ul>
  	     {foreach name=filesList from=$filesList key=filesListIndex item=files}
			 <li> {$files} </li>
	     {/foreach} 
             </ul>
	</div>

	{if $article->getLocalizedAbstract()}
		<div id="articleAbstract">
		<h4>{translate key="article.abstract"}</h4>
		<br />
		<div>{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}</div>
		<br />
		</div>
	{/if}

	{if $article->getLocalizedSubject()}
		<div id="articleSubject">
		<h4>{translate key="article.subject"}</h4>
		<br />
		<div>{$article->getLocalizedSubject()|escape}</div>
		<br />
		</div>
	{/if}

	{if $citationFactory->getCount()}
		<div id="articleCitations">
		<h4>{translate key="submission.citations"}</h4>
		<br />
		<div>
			{iterate from=citationFactory item=citation}
				<p>{$citation->getRawCitation()|strip_unsafe_html}</p>
			{/iterate}
		</div>
		<br />
		</div>
	{/if}

<!--	{if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain)}
		{assign var=hasAccess value=1}
	{else}
		{assign var=hasAccess value=0}
	{/if}

	{if $galleys}
		{translate key="reader.fullText"}
		{if $hasAccess || ($subscriptionRequired && $showGalleyLinks)}
			{foreach from=$article->getGalleys() item=galley name=galleyList}
				<a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" class="file" target="_parent">{$galley->getGalleyLabel()|escape}</a>
				{if $subscriptionRequired && $showGalleyLinks && $restrictOnlyPdf}
					{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || !$galley->isPdfGalley()}
						<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
					{else}
						<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
					{/if}
				{/if}
			{/foreach}
			{if $subscriptionRequired && $showGalleyLinks && !$restrictOnlyPdf}
				{if $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN}
					<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_open_medium.gif" alt="{translate key="article.accessLogoOpen.altText"}" />
				{else}
					<img class="accessLogo" src="{$baseUrl}/lib/pkp/templates/images/icons/fulltext_restricted_medium.gif" alt="{translate key="article.accessLogoRestricted.altText"}" />
				{/if}
			{/if}
		{else}
			&nbsp;<a href="{url page="about" op="subscriptions"}" target="_parent">{translate key="reader.subscribersOnly"}</a>
		{/if}
	{/if}-->
{/if}

<!--{include file="article/comments.tpl"}-->

{include file="article/footer.tpl"}

