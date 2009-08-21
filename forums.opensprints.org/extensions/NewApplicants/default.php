<?php
/*
Extension Name: New Applicants
Extension Url: http://lussumo.com/addons/
Description: Places notification above discussion list for users with sufficient priviledges that there are new applicants seeking membership approval.
Version: 1.3
Author: Mark O'Sullivan
Author Url: http://www.markosullivan.ca/
*/

// Define the required Customizations for this extension
$Context->SetDefinition('NewApplicants_XNewApplicants', 'There are \\1 new applicants awaiting approval.');
$Context->SetDefinition('NewApplicants_OneNewApplicant', 'There is 1 new applicant awaiting approval.');
$Context->SetDefinition('NewApplicants_ClickHereToReviewApplicants', 'Click here to review applicants');

// Attach to the user account being viewed if there is no postback action
if ($Context->SelfUrl == 'index.php' && $Context->Session->UserID > 0 && $Context->Session->User->Permission('PERMISSION_APPROVE_APPLICANTS')) {
   $UserManager = $Context->ObjectFactory->NewContextObject($Context, 'UserManager');
   $ApplicantCount = $UserManager->GetApplicantCount();
   if ($ApplicantCount > 0) {
		$NoticeCollector->AddNotice(($ApplicantCount != 1 ? str_replace('\\1', $ApplicantCount, $Context->GetDefinition('NewApplicants_XNewApplicants')) : $Context->GetDefinition('NewApplicants_OneNewApplicant')).'
         <a href="'.GetUrl($Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=Applicants').'">'.$Context->GetDefinition('NewApplicants_ClickHereToReviewApplicants').'</a>');
   }
}
?>