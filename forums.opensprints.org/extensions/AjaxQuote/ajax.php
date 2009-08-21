<?php

include('../../appg/settings.php');
include('../../appg/init_ajax.php');

if( !(ctype_digit( (string)$_GET['CommentID'] ) ) ){ 
echo 'ERROR';
exit();
}


$ComTbl = $Configuration['DATABASE_TABLE_PREFIX'].$DatabaseTables['Comment'];
$ComFields = $DatabaseColumns['Comment'];

$Query ='
				select '.$ComFields['Body'].' 
				from '.$ComTbl.' 
				where '.$ComTbl.'.'.$ComFields['CommentID'].' = '.$_GET['CommentID'].' 
				limit 1
				';

		$Data = $Context->Database->Execute($Query, 'BodyRetrival', 'x', 'An error occured while attempting to retrieve the comment status');


if(!$Data or !($Info = $Context->Database->GetRow($Data)) ) echo 'ERROR';
else echo $Info["Body"];


?>