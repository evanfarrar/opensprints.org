<?php
/*
Extension Name: Quotations
Extension Url: http://lussumo.com/community/discussion/2069/
Description: Adds a quote button to every post
Version: 1.6
Author: Joel Bernstein
Author Url: http://pong.uwstout.edu/
*/

$Context->Dictionary['Quote'] = 'quote';
$Context->Dictionary['PostedBy'] = 'Posted By: ';

if (in_array($Context->SelfUrl, array("comments.php", "search.php", "post.php")))
{
   $Head->AddScript("extensions/Quotations/quote.js");
   $Head->AddStyleSheet("extensions/Quotations/quote.css");
}

// Add the options to the grid
function CommentGrid_AddQuoteButtons(&$CommentGrid)
{
   if ($CommentGrid->Context->Session->UserID > 0)
   {
      $Comment = &$CommentGrid->DelegateParameters['Comment'];
   
      $CommentList = &$CommentGrid->DelegateParameters["CommentList"];
      $CommentList .= '<a onmousedown="quote('.$Comment->CommentID.', '.$Comment->AuthUserID.", '". $CommentGrid->Context->GetDefinition("PostedBy").$Comment->AuthUsername."');\">". $CommentGrid->Context->GetDefinition("Quote").'</a>';
   }
}

$Context->AddToDelegate("CommentGrid", "PostCommentOptionsRender", "CommentGrid_AddQuoteButtons");