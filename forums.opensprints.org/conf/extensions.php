<?php
// Make sure this file was not accessed directly and prevent register_globals configuration array attack
if (!defined('IN_VANILLA')) exit();
// Enabled Extensions
include($Configuration['EXTENSIONS_PATH']."AddMember/default.php");
include($Configuration['EXTENSIONS_PATH']."AjaxQuote/default.php");
include($Configuration['EXTENSIONS_PATH']."CommentRemoval/default.php");
include($Configuration['EXTENSIONS_PATH']."FeedThis/default.php");
include($Configuration['EXTENSIONS_PATH']."Gravatar/default.php");
include($Configuration['EXTENSIONS_PATH']."HtmlFormatter/default.php");
include($Configuration['EXTENSIONS_PATH']."Legends/default.php");
include($Configuration['EXTENSIONS_PATH']."NewApplicants/default.php");
include($Configuration['EXTENSIONS_PATH']."Quicktags/default.php");
include($Configuration['EXTENSIONS_PATH']."WhisperNotification/default.php");
include($Configuration['EXTENSIONS_PATH']."WhosOnline/default.php");
?>