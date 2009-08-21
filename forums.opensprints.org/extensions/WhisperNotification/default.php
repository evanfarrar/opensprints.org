<?php

/*
Extension Name: Whisper Notification
Extension Url: http://lussumo.com/community/discussion/566/does-someone-have-to-respond-to-a-whisper-to-show-a-private-discussion/
Description: Gives users an option to be notified when someone whispers a comment to them.
Version: 0.1
Vanilla Version: 1.0
Author: a_magical_me
Author Url: N/A
*/

CreateArrayEntry($Context->Dictionary, 'TellWhenWhisper', 'Tell me when someone whispers a comment to me');
CreateArrayEntry($Context->Dictionary, 'YouHaveBeenWhisperedIn', 'You have been whispered in the following discussions:');

if ($Context->Configuration['ENABLE_WHISPERS'])
	{
	if (in_array($Context->SelfUrl, array('comments.php', 'index.php', 'categories.php')))
		{
		if ($Context->Session->User->UserID > 0 &&
			$Context->Session->User->Preference('NotifyOnNewWhisper'))
			{
			#$NoticeCollector->AddNotice('This is a page that Whisper Notification affects');
			$s = $Context->ObjectFactory->NewContextObject($Context, 'SqlBuilder');
			#if (!isset($DiscussionManager)) $DiscussionManager = $Context->ObjectFactory->NewContextObject($Context, 'DiscussionManager');
			#$s = $DiscussionManager->GetDiscussionBuilder($s);
			#$s->SetMainTable('Comment', 'c');
			$s->SetMainTable('Discussion', 't');
			$s->AddSelect(array('DiscussionID', 'Name'), 't');
			$s->AddJoin('Comment', 'co', 'DiscussionID', 't', 'DiscussionID', 'left join');
			#$s->AddJoin('DiscussionUserWhisperTo', 'twt', 'LastUserID', 'u', 'UserID', 'left join');
			#$s->AddSelect(array('CommentID', 'DiscussionID', 'WhisperUserID'), 'c');
			$s->AddWhere('co', 'WhisperUserID', '', $Context->Session->User->UserID, '=');
			$s->AddSelect('CommentID', 'co');
			$s->AddSelect('CommentID', 'co', 'WhisperCount', 'count');
			$s->AddGroupBy('DiscussionID', 't');
			#$s->AddJoin('DiscussionUserWhisperFrom', 'uwf', 'DiscussionID', 't', 'DiscussionID', 'left join');
			$s->AddJoin('UserDiscussionWatch', 'tw', 'DiscussionID', 't', 'DiscussionID', 'left join', 'and tw.'.$DatabaseColumns['UserDiscussionWatch']['UserID'].' = '.$Context->Session->User->UserID);
			$s->AddWhere('co', 'DateCreated', 'tw', 'LastViewed', '>', 'and', '', 0);
			#$s->AddWhere('t', 'WhisperUserID', '', 'NULL', '=', 'and', '',  0);
			#$s->AddJoin('UserDiscussionWatch', 'udw', 'UserID', 'u', 'UserID', 'left join');
			#$s->StartWhereGroup();
			#$s->AddWhere('udw', 'DiscussionID', 't', 'DiscussionID', '=', 'and', '', 1, 1);
			#$s->AddWhere('utw', 'DiscussionID', '', 'NULL', 'is', 'and', '', 0, 1);
			#$s->AddWhere('utw', 'CountComments', '', '(twt.CountWhispers + tuwf.CountWhispers + t.CountComments)', '<', 'or', '', 0);
			#$s->EndWhereGroup();
		
			#$d = $Context->ObjectFactory->NewContextObject($Context, 'DiscussionManager');
			#$d->
			$result = $Context->Database->Select($s, '', '', 'An error occurred while querying the database.');
			if ($Context->Database->RowCount($result) != 0)
				{
				$msg = "";
				while ($row = $Context->Database->GetRow($result))
					{
				#echo GetUnreadQuerystring($discussion, $Context->Configuration, $Context->Session->User->Preference('JumpToLastReadComment'));
					$xnew = str_replace('//1', $row['WhisperCount'], $Context->GetDefinition('XNew'));
					$msg .= '<br />' . '<a href="' . GetUrl($Context->Configuration, 'comments.php', '', 'DiscussionID', $row['DiscussionID'], CleanupString($row['Name'])).'#Comment_'.$row['CommentID'] . '">'.$row['Name'].'</a> ('.$xnew.')';
					}
		
				$NoticeCollector->AddNotice($Context->GetDefinition('YouHaveBeenWhisperedIn').$msg);
				}
			}
		}
	elseif ($Context->SelfUrl == 'account.php')
		{
		function PreferencesForm_AddWhisperNotification(&$PreferencesForm)
			{
			$PreferencesForm->AddPreference('Notification', 'TellWhenWhisper', 'NotifyOnNewWhisper');
			}
		$Context->AddToDelegate('PreferencesForm', 'PreRender', 'PreferencesForm_AddWhisperNotification');
		}
	}

?>
