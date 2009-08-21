<?php
/*
Extension Name: Who's Online
Extension Url: http://www.thirty5.net/code/vanilla/whosonline/
Description: Adds a "who's online" list to the panel.
Version: 1.2
Author: David Knowles,  Julien Cassignol, Michael Schieben
Author Url: mailto:dknowles2@gmail.com

Additional Author: Michael Schieben - michael@twoantennas.com (Adaption to Vanilla 1.0)

*/
$Context->Dictionary["MenuOptions"] = "Menu Options";
$Context->Dictionary["HideWhosOnline"] = "Hide the \"Who's Online\" panel";
$Context->Dictionary["Phantom"] = "Hide my username from the \"Who's Online\" panel";

 
class WhosOnline {
  var $Name;
  var $Context;

  function WhosOnline(&$Context) {
    $this->Name = "WhosOnline";
    $this->Context = &$Context;
  }

  function GetWhosOnline() {
    $s = $this->Context->ObjectFactory->NewContextObject($this->Context, "SqlBuilder");
    $s->SetMainTable("User", "u");
    $s->AddSelect(array("Name", "UserID", "DateLastActive", "Preferences"), "u");
    $s->AddWhere("u", "DateLastActive", "", "DATE_SUB(NOW(), INTERVAL 5 MINUTE)", ">=", NULL, NULL, 0);
    $result = $this->Context->Database->Select($s, $this->Name, "GetRecentUsers", "An error occurred while attempting to retrieve the requested information.");
    if ($this->Context->Database->RowCount($result) == 0) {
      return NULL;
    } else { 
      $my_array = array();
      while ($rows = $this->Context->Database->GetRow($result)) {
	  if ($rows["Preferences"]) { 
	    $settings = unserialize($rows["Preferences"]);
	    if (array_key_exists("Phantom", $settings))
	      $phantom = ForceBool($settings["Phantom"], 0);
	    else
	      $phantom = false;
	  } else { 
	    $phantom = false;
	  }
	  array_push($my_array, array("Name" => $rows["Name"], "UserID" => $rows["UserID"], 
				      "DateLastActive" => $rows["DateLastActive"], "Phantom" => $phantom));
	}
      return $my_array;
    }
  }
  
  function GetGuestCount() {
    $s = $this->Context->ObjectFactory->NewContextObject($this->Context, "SqlBuilder");
    $s->SetMainTable("IpHistory", "i");
    $s->AddSelect("IpHistoryID", "i", "GuestCount", "COUNT");
    $s->AddWhere("i", "UserID", "", 0, "=");
    $s->AddWhere("i", "DateLogged", "", "DATE_SUB(NOW(), INTERVAL 1 MINUTE)", ">=", 'and', NULL, 0);
    $result = $this->Context->Database->Select($s, $this->Name, "GetGuestCount", "An error occurred while attempting to retrieve the requested information.");
    $row =  $this->Context->Database->GetRow($result);
    return $row["GuestCount"];
  }

  function GetNextIpLetter($my_ip) { 
    $s = $this->Context->ObjectFactory->NewContextObject($this->Context, "SqlBuilder");
    $s->SetMainTable("IpHistory", "i");
    $s->AddSelect("RemoteIp", "i");
    $s->AddWhere("i", "UserID", "", 0, "=");
    $s->AddWhere("i", "RemoteIp", "", "$my_ip([a-z])*$", "regexp");
    $s->AddWhere("i", "DateLogged", "", "DATE_SUB(NOW(), INTERVAL 30 MINUTE)", ">=", 'and', NULL, 0);
    $s->AddOrderBy("RemoteIp", "i", "desc");
    $s->AddLimit(0, 1);
    $result = $this->Context->Database->Select($s, $this->Name, "UpdateGuestLastActive", "An error occurred while logging user data.");

    if ($this->Context->Database->RowCount($result) > 0) { 
      $row = $this->Context->Database->GetRow($result);
      if (preg_match("/([a-z])+$/", $row["RemoteIp"], $ip_letter))
	return ++$ip_letter[0];
      else
        return "a";
    } else { 
      return "";
    }
  }

  function GetIpHistoryID($my_ip) {
    $s = $this->Context->ObjectFactory->NewContextObject($this->Context, "SqlBuilder");
    $s->SetMainTable("IpHistory", "i");
    $s->AddSelect("IpHistoryID", "i");
    $s->AddWhere("i", "UserID", "", 0, "=");
    $s->AddWhere("i", "RemoteIp", "", $my_ip, "=");
    $s->AddOrderBy("IpHistoryID", "i", "desc");
    $s->AddLimit(0, 1);
    $result = $this->Context->Database->Select($s, $this->Name, "GetIpHistoryID", 
					       "An error occurred while attempting to retrieve the requested information.");
    $row = $this->Context->Database->GetRow($result);
    return $row["IpHistoryID"];
  }

  function UpdateDateLastActive() {
    $s = $this->Context->ObjectFactory->NewContextObject($this->Context, "SqlBuilder");

    if (isset($_COOKIE['IpHistoryID'])) { 
      $s->SetMainTable("IpHistory", "i");
      $s->AddWhere("i", "IpHistoryID", "", $_COOKIE['IpHistoryID'], "=");
      $this->Context->Database->Delete($s, $this->Name, "UpdateDateLastActive", "An error occurred while deleting guest profile");
      setcookie('IpHistoryID', '', time() - 3600);
    }
    $s->Clear();
    $s->SetMainTable("User", "u");
    $s->AddFieldNameValue("DateLastActive", "now()", 0);
    $s->AddWhere("u", "UserID", "", $this->Context->Session->UserID, "=");
    $result = $this->Context->Database->Update($s, $this->Name, "UpdateDateLastActive", "An error occurred while updating your account.");
  }

  function UpdateGuestLastActive() {
    $s = $this->Context->ObjectFactory->NewContextObject($this->Context, "SqlBuilder");
    if (isset($_COOKIE['IpHistoryID'])) {
      $s->Clear();
      $s->SetMainTable("IpHistory", "i");
      $s->AddFieldNameValue("DateLogged", "now()", 0);
      $s->AddWhere("i", "IpHistoryID", "", $_COOKIE['IpHistoryID'], "=");
      $result = $this->Context->Database->Update($s, $this->Name, "UpdateGuestLastActive", "An error occurred while logging user data.");
    } else { 
      $my_ip = GetRemoteIp(1);
      $ip_letter = $this->GetNextIpLetter($my_ip);
      $s->Clear();
      $s->SetMainTable("IpHistory", "i");
      $s->AddFieldNameValue("UserID", 0);
      $s->AddFieldNameValue("RemoteIp", $my_ip . $ip_letter);
      $s->AddFieldNameValue("DateLogged", "Now()", 0);
      $this->Context->Database->Insert($s, $this->Name, "UpdateGuestLastActive", "An error occurred while logging user data.");
      setcookie('IpHistoryID', $this->GetIpHistoryID($my_ip . $ip_letter));
    }
  }

}

$WhosOnline = $Context->ObjectFactory->NewContextObject($Context, "WhosOnline");

if (!in_array($Context->SelfUrl, array("signin.php", "leave.php", "post.php", "passwordrequest.php", "passwordreset.php"))) { 
  if ($Context->Session->UserID > 0)
    $WhosOnline->UpdateDateLastActive();
  else
    $WhosOnline->UpdateGuestLastActive();
 }

if (in_array($Context->SelfUrl, array("account.php", "categories.php", "comments.php", "index.php", "post.php", "search.php", "settings.php")) 
    && $Context->Session->UserID > 0 && !$Context->Session->User->Preference("HideWhosOnline")) {
  $ListName = $Context->GetDefinition("Who's Online");
  $Panel->AddList($ListName);
  $online_list = $WhosOnline->GetWhosOnline();
  $guest_count = $WhosOnline->GetGuestCount();
  $phantom_count = 0;
  if ($online_list) {
    foreach ($online_list as $name) {
      if ($name["Phantom"]) { 
	$phantom_count++;
      } 
      if (!$name["Phantom"] || !isset($name["Phantom"]) || $Context->Session->User->Permission("PERMISSION_WHOS_PHANTOM")) {
	$TimePast = TimeDiff($Context, unixtimestamp($name["DateLastActive"]));
	$Panel->AddListItem($ListName, $Context->GetDefinition($name["Name"]), "account.php?u=" . $name["UserID"],NULL,"title=\"$TimePast\"");
      }
    }
    if ($phantom_count > 0) {
      $phantom_string = "$phantom_count phantom user";
      if ($phantom_count > 1)
	$phantom_string .= "s";
      $Panel->AddListItem($ListName,'','',$phantom_string);
    }
    if ($guest_count > 0) { 
      $guest_string = "$guest_count guest";
      if ($guest_count > 1)
	$guest_string .= "s";
      $Panel->AddListItem($ListName,'','',$guest_string);
    }
  } else { 
    $Panel->AddListItem($ListName,'','',"Nobody's online.");
  }
}

// Add the Who's Online setting to the forum preferences form
if ($Context->SelfUrl == "account.php" && $Context->Session->UserID > 0) {
        $PostBackAction = ForceIncomingString("PostBackAction", "");
        if ($PostBackAction == "Functionality") {
                function PreferencesForm_AddWhosOnlinePreference(&$PreferencesForm) {
                        $PreferencesForm->AddPreference("MenuOptions", "HideWhosOnline", "HideWhosOnline", 0);
			$PreferencesForm->AddPreference("MenuOptions", "Phantom", "Phantom", 0);
                }

                $Context->AddToDelegate("PreferencesForm",
                        "Constructor",
                        "PreferencesForm_AddWhosOnlinePreference");
        }
}


?>
