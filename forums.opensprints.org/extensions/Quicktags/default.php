<?php
/*
Extension Name: Quicktags
Extension Url: http://lussumo.com/addons/
Description: Inserts an HTML formatting bar above the input field, using Alex King's Quicktags. Requires SirNot's Html Formatter extension. See http://www.tamba2.org.uk/wordpress/quicktags/ for guidance on adding custom Quicktags.
Version: 0.5
Author: James Greig
Author Url: http://www.3stripe.net
*/

$Configuration["QUICKTAGS_PATH"] = 'extensions/Quicktags/';

if(!in_array($Context->SelfUrl, array("post.php", "comments.php"))) return;

if (in_array($Context->SelfUrl, array("post.php", "comments.php"))) {
	class QuicktagsBar {
			function QuicktagsBar_Create() {
				echo '
				<div id="quicktags">
					<script type="text/javascript">edToolbar();</script>
				</div>
				';
			}
			function QuicktagsBarApres_Create() {
				echo '
					<script type="text/javascript">var edCanvas = document.getElementById(\'CommentBox\');</script>
				';
			}
	}
}

function AddQuicktagstoCommentForm(&$DiscussionForm)
	{
	$QuicktagsBar = new QuicktagsBar($DiscussionForm->Context);
	$QuicktagsBar-> QuicktagsBar_Create();
}

function AddQuicktagsJavascriptafterCommentForm(&$DiscussionForm)
	{
	$QuicktagsBar = new QuicktagsBar($DiscussionForm->Context);
	$QuicktagsBar-> QuicktagsBarApres_Create();
}

if( $Context->Session->UserID > 0) 	{
	$Head->AddScript($Context->Configuration["BASE_URL"].$Context->Configuration["QUICKTAGS_PATH"].'quicktags.js');
	$GLOBALS['Head']->AddStyleSheet($Context->Configuration["QUICKTAGS_PATH"].'quicktags.css');
	
	$Context->AddToDelegate("DiscussionForm", "CommentForm_PreCommentsInputRender", 'AddQuicktagstoCommentForm');
	$Context->AddToDelegate("DiscussionForm", "DiscussionForm_PreCommentRender",'AddQuicktagstoCommentForm');
	
	$Context->AddToDelegate("DiscussionForm", "CommentForm_PreButtonsRender", 'AddQuicktagsJavascriptafterCommentForm');
	$Context->AddToDelegate("DiscussionForm", "DiscussionForm_PreButtonsRender",'AddQuicktagsJavascriptafterCommentForm');
}
?>