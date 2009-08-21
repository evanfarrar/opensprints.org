<?php
/*
Extension Name: Legends
Extension Url: http://lussumo.com/docs/
Description: Adds legends to the panel for the discussion, search, and category pages.
Version: 1.0
Author: Mark O'Sullivan
Author Url: http://www.markosullivan.ca/

Copyright 2003 - 2005 Mark O'Sullivan
This file is part of Vanilla.
Vanilla is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
Vanilla is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Vanilla; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
The latest source code for Vanilla is available at www.lussumo.com
Contact Mark O'Sullivan at mark [at] lussumo [dot] com

You should cut & paste these language definitions into your
conf/your_language.php file (replace "your_language" with your chosen language,
of course):
*/

$Context->Dictionary["Legend"] = "Legend";
$Context->Dictionary["NewComments"] = "New comments";
$Context->Dictionary["NoNewComments"] = "No new comments";
$Context->Dictionary["YouWhispered"] = "You whispered";
$Context->Dictionary["WhisperedToYou"] = "Whispered to you";
$Context->Dictionary["UnblockedCategory"] = "Unblocked category";
$Context->Dictionary["BlockedCategory"] = "Blocked category";
$Context->Dictionary["DisplayListAppendices"] = "Display list appendices in the control panel";



// Add the preference switch to the account functionality form
if ($Context->SelfUrl == "account.php" && ForceIncomingString("PostBackAction", "") == "Functionality") {
	function PreferencesForm_AddLegendSwitch(&$PreferencesForm) {
		$PreferencesForm->AddPreference("ControlPanel", "DisplayListAppendices", "ShowAppendices");
	}
	$Context->AddToDelegate("PreferencesForm",
		"Constructor",
		"PreferencesForm_AddLegendSwitch");
}

if ($Context->SelfUrl == "index.php" && $Context->Session->UserID > 0 && $Context->Session->User->Preference("ShowAppendices")) {
	$Head->AddStyleSheet('extensions/Legends/style.css');
	$Panel->AddString("<h2>".$Context->GetDefinition("Legend")."</h2>
		<ul id=\"Legend\">
			<li class=\"Legend NewComments\">".$Context->GetDefinition("NewComments")."</li>
			<li class=\"Legend NoNewComments\">".$Context->GetDefinition("NoNewComments")."</li>
		</ul>", 100);
} elseif ($Context->SelfUrl == "categories.php" && $Context->Session->UserID > 0 && $Context->Session->User->Preference("ShowAppendices")) {
	$Head->AddStyleSheet('extensions/Legends/style.css');
	$Panel->AddString("<h2>".$Context->GetDefinition("Legend")."</h2>
		<ul id=\"Legend\">
			<li class=\"Legend UnblockedCategory\">".$Context->GetDefinition("UnblockedCategory")."</li>
			<li class=\"Legend BlockedCategory\">".$Context->GetDefinition("BlockedCategory")."</li>
   	</ul>", 100);
} elseif ($Configuration["ENABLE_WHISPERS"] && $Context->SelfUrl == "comments.php" && $Context->Session->UserID > 0 && $Context->Session->User->Preference("ShowAppendices")) {
	$Head->AddStyleSheet('extensions/Legends/style.css');
	$Panel->AddString("<h2>".$Context->GetDefinition("Legend")."</h2>
      <ul id=\"Legend\">
         <li class=\"Legend WhisperFrom\">".$Context->GetDefinition("YouWhispered")."</li>
         <li class=\"Legend WhisperTo\">".$Context->GetDefinition("WhisperedToYou")."</li>
      </ul>", 100);
}

?>