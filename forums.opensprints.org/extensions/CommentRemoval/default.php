<?php
/*
Extension Name: Comment Removal
Extension Url: http://lussumo.com/addons
Description: Allows users to actually delete comments and discussions instead of merely hiding them.
Version: 2.1.2
Author: SirNotAppearingOnThisForum
Author Url: N/A
*/

if(!isset($Head)) return;

if(!$Context->Session->UserID) return;

if($Context->SelfUrl == 'comments.php') $Head->AddScript('extensions/CommentRemoval/comment_removal.js');

$Configuration['PERMISSION_REMOVE_COMMENTS'] = '0';
$Configuration['PERMISSION_REMOVE_OWN_COMMENTS'] = '0';
$Context->Dictionary['PERMISSION_REMOVE_COMMENTS'] = 'Can remove any comment and discussion';
$Context->Dictionary['PERMISSION_REMOVE_OWN_COMMENTS'] = 'Can remove their own comments and discussions (with no replies)';

$Context->AddToDelegate('CommentGrid', 'PostCommentOptionsRender', 'CommentOptions_AddDeleteButton');

$Context->Dictionary['VerifyCommentRemoval'] = 'Are you quite sure you want to permanently remove this comment?';
$Context->Dictionary['VerifyDiscussionRemoval'] = 'Are you quite sure you want to permanently remove this discussion?';
$Context->Dictionary['remove'] = 'remove';

function CommentOptions_AddDeleteButton(&$Form)
{
	$D = &$GLOBALS['CommentGrid']->Discussion;
	$C = &$Form->DelegateParameters['Comment'];
	
	if(!( 
		$Form->Context->Session->User->Permission('PERMISSION_REMOVE_COMMENTS') || 
		(
			$C->AuthUserID == $Form->Context->Session->User->UserID && !$D->Closed && 
			($D->FirstCommentID != $C->CommentID || $D->CountComments == 1) && 
			$Form->Context->Session->User->Permission('PERMISSION_REMOVE_OWN_COMMENTS')
		)
	)) return;
	
	$Form->DelegateParameters['CommentList'] .= 
		'<a href="./" id="RmComment_'.$C->CommentID.'" onclick="if(confirm(\''.
		$Form->Context->GetDefinition($D->FirstCommentID != $C->CommentID 
		? 'VerifyCommentRemoval' : 'VerifyDiscussionRemoval').
		'\')) removecomment(\''.$GLOBALS['Context']->Configuration['WEB_ROOT'].'\', '.
		$C->CommentID.', '.($C->CommentID == $D->FirstCommentID ? '1' : '0').
		'); return false;">'.$Form->Context->GetDefinition('remove').'</a>';
}

?>