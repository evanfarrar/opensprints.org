<?php
/*
Extension Name: AjaxQuote
Extension Url: http://lussumo.com/community/discussion/3853/ajaxquote/
Description: Adds quote option to posts. Works with any format type without complex JS parsing (retrives original data from DB on the fly). Redirects to last(CommentBox) page if needed.
Version: 1.0
Author: Ivan Weiler a.k.a. Scip
Author Url: http://www.studioat.hr/
*/

/*
Change ajaxqoute_format in ajaxquote.js to define format type (Html or BBCode);
For BBCode you need BetterBBCode extension.
*/

if (in_array($Context->SelfUrl, array("comments.php"))) {
	
	//customize:
	$Configuration["AJAXQUOTE_LOGINREQUIRED"] = true;          	//if u are using Add Comments Extension set this to false
	$Context->Dictionary['Quote'] = 'quote';
	//

if( $Configuration["AJAXQUOTE_LOGINREQUIRED"]===false or $Context->Session->UserID > 0 ){

	$Head->AddStyleSheet("extensions/AjaxQuote/ajaxquote.css");
	$Head->AddScript('extensions/AjaxQuote/ajaxquote.js');

function CommentGrid_AddAjaxQuoteButton(&$CommentGrid){      
      $Comment = &$CommentGrid->DelegateParameters['Comment'];   
      $CommentList = &$CommentGrid->DelegateParameters["CommentList"];
      
      
      $Url = GetUrl($CommentGrid->pl->Context->Configuration,
			$CommentGrid->pl->Context->SelfUrl,
			'',
			$CommentGrid->pl->UrlIdName,
			$CommentGrid->pl->UrlIdValue,
			$CommentGrid->pl->PageCount,
			'aq_quote='.$Comment->CommentID.'&aq_author='.$Comment->AuthUsername,
			'');
            
    $CommentList .= '<a id="AjaxQuote_'.$Comment->CommentID.'" href="'.$Url.'" onclick="return ajaxquote(\''.$Comment->Context->Configuration['WEB_ROOT'].'\','.$Comment->CommentID.',\''.$Comment->AuthUsername.'\');">'. $CommentGrid->Context->GetDefinition("Quote").'</a>';
}

$Context->AddToDelegate("CommentGrid", "PostCommentOptionsRender", "CommentGrid_AddAjaxQuoteButton");


function CommentForm_AjaxQuoteCall(&$CommentForm){
if(isset($_GET['aq_quote']) and $_GET['aq_quote']>0) echo '
<script type="text/javascript">
ajaxquote(\''.$CommentForm->Context->Configuration['WEB_ROOT'].'\','.$_GET['aq_quote'].',\''.$_GET['aq_author'].'\');
</script>
';
}

$Context->AddToDelegate("DiscussionForm", "CommentForm_PreButtonsRender", "CommentForm_AjaxQuoteCall");


function remove_AjaxQuoteGet(&$CommentGrid){
$CommentGrid->pl->QueryStringParams->Remove('aq_quote');
$CommentGrid->pl->QueryStringParams->Remove('aq_author');
}
$Context->AddToDelegate("CommentGrid", "PreRender", "remove_AjaxQuoteGet");


}

}

?>