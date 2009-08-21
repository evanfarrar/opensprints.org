<?php

include('../../appg/settings.php');
include('../../appg/init_ajax.php');

if(!isset($_GET['CommentID'])) exit();

function RemoveComment($CommentID)
{
	global $Context;
	
	$ComTbl = $GLOBALS['Configuration']['DATABASE_TABLE_PREFIX'].$GLOBALS['DatabaseTables']['Comment'];
	$DiscTbl = $GLOBALS['Configuration']['DATABASE_TABLE_PREFIX'].$GLOBALS['DatabaseTables']['Discussion'];
	//$UserTbl = $GLOBALS['DatabaseTables']['User'];
	$ComFields = &$GLOBALS['DatabaseColumns']['Comment'];
	$DiscFields = &$GLOBALS['DatabaseColumns']['Discussion'];
	//$UserFields = &$GLOBALS['DatabaseColumns']['User'];
	$ComInfo = array();
	$r = 0;
	
	do
	{
		//first find retrieve information about comment and discussion
		$Data = $Context->Database->Execute(
			'select 
				c.'.$ComFields['WhisperUserID'].' as com_iswhisper, 
				d.'.$DiscFields['Closed'].' as closed, 
				c.'.$ComFields['AuthUserID'].' as user, 
				d.'.$DiscFields['DiscussionID'].' as id, 
				d.'.$DiscFields['CountComments'].' as com_num, 
				d.'.$DiscFields['FirstCommentID'].' as first_com, 
				d.'.$DiscFields['WhisperUserID'].' as disc_iswhisper, 
				d.'.$DiscFields['CountComments'].' as com_count, 
				d.'.$DiscFields['TotalWhisperCount'].' as whisper_count, 
				c.'.$ComFields['Deleted'].' as deleted 
			from '.$DiscTbl.' as d 
			join '.$ComTbl.' as c on d.'.$DiscFields['DiscussionID'].' = c.'.$ComFields['DiscussionID'].' 
			where c.'.$ComFields['CommentID'].' = '.$CommentID.' 
			limit 1;', 
			'', '', 'An error occured while attempting to retrieve the comment status');
		
		//check comment's existence
		if(!$Data || !($ComInfo = $Context->Database->GetRow($Data)) ) {$r = -1; break;}
		
		//permissions...
		if(!( 
			$Context->Session->User->Permission('PERMISSION_REMOVE_COMMENTS') || 
			(
				$ComInfo['user'] == $Context->Session->User->UserID && !$ComInfo['closed'] && 
				($ComInfo['first_com'] != $CommentID || $ComInfo['com_num'] == 1) && 
				$Context->Session->User->Permission('PERMISSION_REMOVE_OWN_COMMENTS')
			) 
		)) {$r = -2; break;}
		
		//remove discussion
		if($CommentID == $ComInfo['first_com'])
		{
			if(!$Context->Database->Execute(
				'delete from '.$DiscTbl.' 
				where '.$DiscFields['FirstCommentID'].' = '.$CommentID.'
				limit 1;', '', '', 
				'An error occured while attempting to check (and remove) discussion')) break;
			
			if(!$Context->Database->Execute(
				'delete from '.$ComTbl.' 
				where '.$ComFields['DiscussionID'].' = '.$ComInfo['id'].';', 
				'', '', 'An error occured while attempting to remove comment')) break;
		}
		else //remove comment only
		{
			//removal
			if(!$Context->Database->Execute(
				'delete from '.$ComTbl.' 
				where '.$ComFields['CommentID'].' = '.$CommentID.'
				limit 1;', '', '', 
				'An error occured while attempting to remove comment')) break;
			
			if($ComInfo['deleted']) {$r = 1; break;} //already done for us
			if($ComInfo['com_iswhisper'])
			{
				$Data = $Context->Database->Execute(
					'select 
						'.$ComFields['WhisperUserID'].' as whisperto, 
						'.$ComFields['AuthUserID'].' as whisperuser, 
						'.$ComFields['DateCreated'].' as whispercreated 
					from '.$ComTbl.' 
					where '.$ComFields['WhisperUserID'].' != 0 
						and '.$ComFields['DiscussionID'].' = '.$ComInfo['id'].' 
					order by '.$ComFields['DateCreated'].' desc 
					limit 1;', 
					'', '', 'An error occured while attempting to retrieve latest comment information');
					
					//more likely that no whispers were found than an error encountered.
					if(!$Data || !($NextCom = $Context->Database->GetRow($Data)) ) 
						$NextCom = array('whisperto' => '', 'whisperuser' => '', 'whispercreated' => '');
			}
			else
			{
				$Data = $Context->Database->Execute(
					'select 
						'.$ComFields['AuthUserID'].' as user, 
						'.$ComFields['DateCreated'].' as created 
					from '.$ComTbl.' 
					where '.$ComFields['DiscussionID'].' = '.$ComInfo['id'].' 
						'.($ComInfo['disc_iswhisper'] ? '' : ('and '.$ComFields['WhisperUserID'].' = 0') ).'
					order by '.$ComFields['DateCreated'].' desc 
					limit 1;', '', '', 'An error occured while attempting to retrieve latest comment information [2]');
				
				if(!$Data || !($NextCom = $Context->Database->GetRow($Data)) ) break;
			}
			
			//the updating
			if($Context->Database->Execute(
				'update '.$DiscTbl.' 
				set 
					'.($ComInfo['com_iswhisper'] ? (
						$DiscFields['TotalWhisperCount'].' = '.($ComInfo['whisper_count'] - 1).', 
						'.$DiscFields['WhisperToLastUserID'].' = '.$NextCom['whisperto'].', 
						'.$DiscFields['WhisperFromLastUserID'].' = '.$NextCom['whisperuser'].', 
						'.$DiscFields['DateLastWhisper'].' = \''.$NextCom['whispercreated'].'\''
					) : (
						$DiscFields['CountComments'].' = '.($ComInfo['com_count'] - 1).', 
						'.$DiscFields['LastUserID'].' = '.$NextCom['user'].', 
						'.$DiscFields['DateLastActive'].' = \''.$NextCom['created'].'\''
					)).'
				where '.$DiscFields['DiscussionID'].' = '.$ComInfo['id'].' 
				limit 1;', '', '', 
				'An error occured while attempting to update discussion') === false) break;
		}
		
		$r = 1;
	}
	while(0);
	/*
	find comment and gather info about it and the discussion (are they whispered, for instance?)
	
	if commentID = firstcommentID, then delete discussion and all associated comments
	
	else simply remove the comment, and update discussion
	
	to update discussion:
		if discussion is a whisper, set last active and last user values to latest comment; don't bother with whispered details
		if not, set last active and last user values to latest non-whispered comment; set last whisper date/to user/from user to last whispered comment
		finally reset user discussion watch if comment is not a whisper
	
	*/
	return $r;
}

if(($r = RemoveComment(ForceIncomingInt('CommentID', -1))) == 1) echo('itwasasuccess');
else
{
	switch($r)
	{
		case -1 : echo('That comment does not exist'); break;
		case -2 : echo('Insufficient privileges'); break;
		case 0 : default : echo(strip_tags(implode("\n", $Context->ErrorManager->Errors))); break;
	}
}

?>