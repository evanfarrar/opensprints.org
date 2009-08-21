<?php
/*
Extension Name: AddMember
Extension Url: http://lussumo.com/addons
Description: Allows an administrator to add members directly without having to use the 'Apply for membership' link on the front page. 
Version: 1.0
Author: MightyMango
Author Url: http://mightymango.com

Version 1.0
- Added terms_of_service tag to email

Version 0.9
- Fixed problem with blank 'Add a Member' page.

Version 0.8.1
- Minor code changes
- Fixed problem with incorrect version number showing on Extensions page

Version 0.7
- Now uses $Context->SetDefinition

Version 0.6
- Added option to choose new role
- Removed 'Automatically approve the new member' option (handled by choosing the role).
- Added link to new account in the 'success' message.

Version 0.5
- First release

*/

$Context->SetDefinition('AddMember', 'Add A New Member');
$Context->SetDefinition('AddMemberUserName', 'Username');
$Context->SetDefinition('AddMemberUserNameInfo', 'The username will appear next to the member\'s discussions and comments.');

$Context->SetDefinition('AddMemberFirstName', 'First name');
$Context->SetDefinition('AddMemberFirstNameInfo', 'This should be the member\'s "real" first name. This will only be visible from the account page.');
$Context->SetDefinition('AddMemberLastName', 'Last name');
$Context->SetDefinition('AddMemberLastNameInfo', 'This should be the member\'s "real" last name. This will only be visible from the account page.');
$Context->SetDefinition('AddMemberEmail', 'Email address');
$Context->SetDefinition('AddMemberEmailInfo', 'You must provide a valid email address so that the member can retrieve their password should they lose it and so that they can be notified of their new account details.');
$Context->SetDefinition('AddMemberPassword', 'Password');
$Context->SetDefinition('AddMemberPasswordInfo', 'Do not use birth-dates, bank-card pin numbers, telephone numbers, or anything that can be easily guessed.');
$Context->SetDefinition('AddMemberConfirmPassword', 'Password again');
$Context->SetDefinition('AddMemberConfirmPasswordInfo', 'Re-enter the password to be sure that you have not made any mistakes.');
$Context->SetDefinition('AddMemberInfo', 'Define your new account profile');
$Context->SetDefinition('AddMemberRequired', ' (Required)');
$Context->SetDefinition('AddMemberSendMail', 'Send a confirmation email to the member');

$Context->SetDefinition('AddMemberSuccess', 'A new member\'s account for {UserName} has been created.');


$Context->SetDefinition('AddMemberEmailSubject', 'New Account');
$Context->SetDefinition('AddMemberRole', 'Choose a New Role');
$Context->SetDefinition('AddMemberRoleInfo', 'Choose the required role for this new member. The member will be automatically approved for all roles other than \''.$Context->GetDefinition('Unauthenticated').'\'.');
$Context->SetDefinition('AddMemberRoleNotes', 'Role change notes');
$Context->SetDefinition('AddMemberRoleNotesInfo', 'Please provide some notes regarding this role change. These notes will be visible to all users in the role-history for this user.');
$Context->SetDefinition('AddMemberRoleChange', 'Updated by the AddMember extension.');

$Context->SetDefinition('UserExtensions', 'Account Options');
$Context->SetDefinition('PERMISSION_CREATE_MEMBER', 'Can create a new member');
$Context->Configuration['PERMISSION_CREATE_MEMBER'] = '0';


if ($Context->SelfUrl == "account.php" && $Context->Session->User->Permission('PERMISSION_CREATE_MEMBER')) {

	class CreateMemberForm extends PostBackControl {

		function CreateMemberForm(&$Context) {
		
			$this->Name = 'CreateMemberForm';
			$this->ValidActions = array('AddMember', 'ProcessNewMember');
			$this->Constructor($Context);
			if (!$this->Context->Session->User->Permission('PERMISSION_CREATE_MEMBER')) {
				$this->IsPostBack = 0;
			} elseif( $this->IsPostBack ) {
				if ($this->PostBackAction == 'ProcessNewMember') {
				
		//Create the new member

        //Create User Object
        $newuser = $this->Context->ObjectFactory->NewContextObject($this->Context, 'User');
        $newuser->Clear();
        
        $newuser->Name            = ForceIncomingString("Username", "");
        $newuser->FirstName       = ForceIncomingString("Firstname", "");
        $newuser->LastName        = ForceIncomingString("Lastname", "");
        $newuser->NewPassword     = ForceIncomingString("NewPassword", "");
        $newuser->ConfirmPassword = ForceIncomingString("ConfirmPassword", "");
        $newuser->Email           = ForceIncomingString("Email", ""); 
 
        $newuser->AgreeToTerms = 1;
		$newuser->ReadTerms = 0;
		    
		$newrole  = ForceIncomingString("NewRole", "0");
		$sendmail = ForceIncomingString("SendMail", "0");
		    
        //Create UserManager
        $usermanager= $this->Context->ObjectFactory->NewContextObject($this->Context, 'UserManager');
        
        //Save User
        if ($usermanager->CreateUser($newuser)) 
        {	
        
          //Approve the new user and set the new role
          if ($newrole)
          {
          $usermanager->ApproveApplicant($usermanager->GetUserIdByName($newuser->Name));
                    
            //Set new role if the new role has not already been set by the 'ApproveApplicant' function
            if ($this->Context->Configuration['APPROVAL_ROLE'] != $newrole) { 
            $userrolehistory = $this->Context->ObjectFactory->NewContextObject($this->Context, 'UserRoleHistory');
            $userrolehistory->UserID   = $usermanager->GetUserIdByName($newuser->Name);
            $userrolehistory->Username = $newuser->Name;
            $userrolehistory->FullName = $newuser->FullName;
            $userrolehistory->RoleID   = ForceIncomingString("NewRole", "0");
            $userrolehistory->Notes = ForceIncomingString("NewRoleNotes", $this->Context->GetDefinition("AddMemberRoleChange"));
	        
            $usermanager->AssignRole($userrolehistory);
            }
            
				  }
                    
          //Send EMail
          if ($sendmail)
          {
          
          $getme = $this->Context->ObjectFactory->NewContextObject($this->Context, 'UserManager');
          $me = $getme->GetUserById(ForceIncomingInt('u', $this->Context->Session->UserID));
          $From_Name = $me->FullName;
          $From_Email = $me->Email;
          
          //Check email address
          if($From_Email == '') $From_Email = $this->Context->Configuration['SUPPORT_EMAIL'];
			    
          $Subject = $this->Context->Configuration['APPLICATION_TITLE'].': '.$this->Context->GetDefinition("AddMemberEmailSubject");
          
          //Open the template file
          $Message = file_get_contents($Configuration['APPLICATION_PATH'].'extensions/AddMember/email_new_member.txt');
          
          //Create eMail
          $mail = $this->Context->ObjectFactory->NewContextObject($this->Context, 'Email');
					$mail->HtmlOn = 0;
					$mail->WarningCollector = &$this->Context->WarningCollector;
					$mail->ErrorManager = &$this->Context->ErrorManager;
					$mail->AddFrom($From_Email, $From_Name);
					$mail->AddRecipient($newuser->Email, $newuser->FirstName.' '.$newuser->FirstName);
					
					//Set up our replacement tags
					$searcharray = array("{user_name}","{user_password}","{first_name}","{last_name}","{Email}","{forum_name}","{forum_url}","{terms_of_service}","{my_first_name}","{my_last_name}","{my_user_name}");
					
					//Search and replace tags
					$replacearray = array($newuser->Name,$newuser->NewPassword,$newuser->FirstName,$newuser->LastName,$newuser->Email,$this->Context->Configuration['APPLICATION_TITLE'],$this->Context->Configuration['BASE_URL'],$this->Context->Configuration['BASE_URL'].'termsofservice.php',$me->FirstName,$me->LastName,$me->Name);
					
					$Subjectnew = str_replace($searcharray,$replacearray,$Subject);
					$Messagenew = str_replace($searcharray,$replacearray,$Message);
					$mail->Subject = $Subjectnew;
					$mail->Body = $Messagenew;
					$mail->Send();
          }
          
          header('Location: '.GetUrl($this->Context->Configuration, 'account.php', '', '', '', '', 'PostBackAction=AddMember&Success=1&u='.$newuser->UserID).'&name='.$newuser->Name);
				}
				else
				
				{
				$this->PostBackAction = 'AddMember';
				}
			}
			$this->CallDelegate('Constructor');
		}
    }
    
    function Render() {
			if ($this->IsPostBack) {
				$this->CallDelegate('PreRender');
				$this->PostBackParams->Clear();
				if ($this->PostBackAction == 'AddMember') {
					$this->PostBackParams->Set('PostBackAction', 'ProcessNewMember');
					
					$name        = ForceIncomingString("Username", "");
                    $firstname   = ForceIncomingString("Firstname", "");
					$lastname    = ForceIncomingString("Lastname", "");
					$password    = ForceIncomingString("NewPassword", "");
					$confirm     = ForceIncomingString("ConfirmPassword", "");
					$email       = ForceIncomingString("Email", "");

					$sendmail    = ForceIncomingString("SendMail", "0");
          
					$newrole     = ForceIncomingString("NewRole", "0");
					$rolenotes   = ForceIncomingString("NewRoleNotes", "");
                  
					echo '
					<div id="Form" class="Account Identity">';
					if (ForceIncomingInt('Success', 0)) {
					    $newuserurl = '<a href="'.GetUrl($this->Context->Configuration, 'account.php', '', '', '', '', 'u='.ForceIncomingInt('u', 0)).'">'.ForceIncomingString("name", "").'</a>';
              $successtext = str_replace("{UserName}", $newuserurl, $this->Context->GetDefinition('AddMemberSuccess'));
              echo '<div id="Success">'.$successtext.'</div>';
              }
					echo '
						<fieldset>
							<legend>'.$this->Context->GetDefinition("AddMember").'</legend>
							'.$this->Get_Warnings().'
							'.$this->Get_PostBackForm('frmAddMember');
							
							
							echo '<h2>'.$this->Context->GetDefinition("AddMemberInfo").'</h2>
							<ul>
             <li>
      						<label for="txtUsername">
      							'.$this->Context->GetDefinition("AddMemberUserName").'<small>'.$this->Context->GetDefinition("AddMemberRequired").
      						'</small></label>
     	 						<input id="txtUsername" type="text" name="Username" class="PanelInput" maxlength="20" value="'.$name.'" />
     	 					<p class="Description">'.$this->Context->GetDefinition("AddMemberUserNameInfo").'</p>	
   						</li>
   						
							<li>
      						<label for="txtFirstname">
      							'.$this->Context->GetDefinition("AddMemberFirstName").
      						'</label>
     	 						<input id="txtFirstname" type="text" name="Firstname" class="PanelInput" maxlength="50" value="'.$firstname.'" />
     	 						<p class="Description">'.$this->Context->GetDefinition("AddMemberFirstNameInfo").'</p>	
   						</li>
							
							<li>
      						<label for="txtLastname">
      						 '.$this->Context->GetDefinition("AddMemberLastName").'
      						</label>
     	 						<input id="txtLastname" type="text" name="Lastname" class="PanelInput" maxlength="50" value="'.$lastname.'" />
     	 						<p class="Description">'.$this->Context->GetDefinition("AddMemberLastNameInfo").'</p>	
   						</li>   						

							<li>
      						<label for="txtEmail">
      							'.$this->Context->GetDefinition("AddMemberEmail").'<small> '.$this->Context->GetDefinition("AddMemberRequired").'</small>
      						</label>
     	 						<input id="txtEmail" type="text" name="Email" class="PanelInput" maxlength="200" value="'.$email.'" />
     	 						<p class="Description">'.$this->Context->GetDefinition("AddMemberEmailInfo").'</p>	
   						</li>  
   						
							<li>
      						<label for="txtPassword">
      							'.$this->Context->GetDefinition("AddMemberPassword").'<small> '.$this->Context->GetDefinition("AddMemberRequired").'</small>
      						</label>
     	 						<input id="txtPassword" type="text" name="NewPassword" class="PanelInput" maxlength="50" value="'.$password.'" />
     	 						<p class="Description">'.$this->Context->GetDefinition("AddMemberPasswordInfo").'</p>
   						</li> 

							<li>
      						<label for="txtPassword2">
      							'.$this->Context->GetDefinition("AddMemberConfirmPassword").'<small> '.$this->Context->GetDefinition("AddMemberRequired").'</small>
      						</label>
     	 						<input id="txtPassword2" type="text" name="ConfirmPassword" class="PanelInput" maxlength="50" value="'.$confirm.'" />
     	 						<p class="Description">'.$this->Context->GetDefinition("AddMemberConfirmPasswordInfo").'</p>
   						</li>'; 

				//Get and display the roles
				if ($this->Context->Session->User->Permission('PERMISSION_APPROVE_APPLICANTS') and $this->Context->Session->User->Permission('PERMISSION_CHANGE_USER_ROLE')){
				echo '<li><label for="lstRoles">'.$this->Context->GetDefinition("AddMemberRole").'<small>'.$this->Context->GetDefinition("AddMemberRequired").'</small></label><select name="NewRole" class="PanelInput" id="lstRoles">';
				$RoleMng = $this->Context->ObjectFactory->NewContextObject($this->Context, 'RoleManager');
				$RoleData = $RoleMng->GetRoles();
				if($RoleData)
				{
					echo '<option value="0" ';
					if ($newrole == 0) echo 'selected';
					echo '>'.FormatStringForDisplay($this->Context->GetDefinition('Unauthenticated')).'</option>';
					while($Row = $this->Context->Database->GetRow($RoleData)) {
						echo '<option value="'.$Row['RoleID'].'" ';
						if ($newrole == $Row['RoleID']) echo 'selected';
						echo '>'.FormatStringForDisplay($Row['Name']).'</option>';
						}
				}
				echo '</select>
				     <p class="Description">'.$this->Context->GetDefinition("AddMemberRoleInfo").'</p>
				     </li>
				     
							<li>
      						<label for="txtRoleNotes">
      							'.$this->Context->GetDefinition("AddMemberRoleNotes").'
      							
      						</small></label>
     	 						<input id="txtRoleNotes" type="text" name="NewRoleNotes" class="PanelInput" value="'.$rolenotes.'" />
     	 						<p class="Description">'.$this->Context->GetDefinition("AddMemberRoleNotesInfo").'</p>
   						</li>'; 	
   			}			     


echo '<li>
									<p><span>'.GetDynamicCheckBox('SendMail', 1, $sendmail, '', $this->Context->GetDefinition('AddMemberSendMail')).'</span></p>
</li>';

   				echo '<li>
   							<div class="Submit">
								<input type="submit" name="btnSave" value="'.$this->Context->GetDefinition('Save').'" class="Button SubmitButton" />
								<a href="'.GetUrl($this->Context->Configuration, $this->Context->SelfUrl).'" class="CancelButton">'.$this->Context->GetDefinition('Cancel').'</a>
							  </div>
   						</li>							
							</ul>
							</form>
						</fieldset>
					</div>
					';
				}
				$this->CallDelegate('PostRender');
			}
		}
	}
	
}


if (in_array($Context->SelfUrl, array('account.php'))) {
	if ($Context->Session->User && $Context->Session->User->Permission("PERMISSION_CREATE_MEMBER")){
			
		$CreateMemberForm = $Context->ObjectFactory->NewContextObject($Context, 'CreateMemberForm');
		$Page->AddRenderControl($CreateMemberForm, $Configuration["CONTROL_POSITION_BODY_ITEM"] + 1);
	  	
		$UserExtensions = $Context->GetDefinition("UserExtensions");
		$Panel->AddList($UserExtensions, $Position = '15', $ForcePosition = '1');
		$Panel->AddListItem($UserExtensions, $Context->GetDefinition('AddMember'), GetUrl($Configuration, $Context->SelfUrl, "", "", "", "", "PostBackAction=AddMember"), "", "", 93);	
						
	}
}

?>