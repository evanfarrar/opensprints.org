<?php
/**
 * Default interface for user authentication.
 * Applications utilizing this file: Vanilla;
 *
 * Copyright 2003 Mark O'Sullivan
 * This file is part of Lussumo's Software Library.
 * Lussumo's Software Library is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
 * Lussumo's Software Library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with Vanilla; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * The latest source code is available at www.lussumo.com
 * Contact Mark O'Sullivan at mark [at] lussumo [dot] com
 *
 * @author Mark O'Sullivan
 * @copyright 2003 Mark O'Sullivan
 * @license http://lussumo.com/community/gpl.txt GPL 2
 * @package People
 * @version 1.1.5
 */


/**
 * Default interface for user authentication. This class may be
 * replaced with another using the "AUTHENTICATION_MODULE"
 * and "AUTHENTICATION_CLASS" configuration settings.
 * @package People
 */
class Authenticator {
	/**
	 * @var Context
	 */
	var $Context;
	
	/**
	 * @var PeoplePasswordHash
	 */
	var $PasswordHash;
	
	// Returning '0' indicates that the username and password combination weren't found.
	// Returning '-1' indicates that the user does not have permission to sign in.
	// Returning '-2' indicates that a fatal error has occurred while querying the database.
	function Authenticate($Username, $Password, $PersistentSession) {
		// Validate the username and password that have been set
		$UserID = 0;
		$UserManager = $this->Context->ObjectFactory->NewContextObject(
			$this->Context, 'UserManager');
		$User = $UserManager->GetUserCredentials(0, $Username);

		if (!$User === null) {
			$UserID = -2;
		} elseif ($User) {
			if ($User->VerificationKey == '') $User->VerificationKey = DefineVerificationKey();
			
			if ($this->PasswordHash->CheckPassword($User, $Password)) {
				if (!$User->PERMISSION_SIGN_IN) {
					$UserID = -1;
				} else {
					$UserID = $User->UserID;
					$VerificationKey = $User->VerificationKey;
					
					// 1. Update the user's information
					$UserManager->UpdateUserLastVisit($UserID, $VerificationKey);
	
					// 2. Log the user's IP address
					$UserManager->AddUserIP($UserID);
	
					// Assign the session value
					$this->AssignSessionUserID($UserID);
	
					// Set the 'remember me' cookies
					if ($PersistentSession) $this->SetCookieCredentials($UserID, $VerificationKey);
				}
			}
		}
		return $UserID;
	}

	function Authenticator(&$Context) {
		$this->Context = &$Context;
		$this->PasswordHash = $this->Context->ObjectFactory->NewContextObject(
				$this->Context, 'PeoplePasswordHash');
	}

	function DeAuthenticate() {
		if (session_id()) session_destroy();

		// Destroy the cookies as well
		setcookie($this->Context->Configuration['COOKIE_USER_KEY'],
			' ',
			time()-3600,
			$this->Context->Configuration['COOKIE_PATH'],
			$this->Context->Configuration['COOKIE_DOMAIN']);
		unset($_COOKIE[$this->Context->Configuration['COOKIE_USER_KEY']]);
		setcookie($this->Context->Configuration['COOKIE_VERIFICATION_KEY'],
			' ',
			time()-3600,
			$this->Context->Configuration['COOKIE_PATH'],
			$this->Context->Configuration['COOKIE_DOMAIN']);
		unset($_COOKIE[$this->Context->Configuration['COOKIE_VERIFICATION_KEY']]);
		return true;
	}

	function GetIdentity() {
		if (!session_id()) {
			if ( $this->Context->Configuration['SESSION_NAME'] ) {
				session_name($this->Context->Configuration['SESSION_NAME']);
			}
			session_set_cookie_params(0, $this->Context->Configuration['COOKIE_PATH'], $this->Context->Configuration['COOKIE_DOMAIN']);
			session_start();
		}

		$UserID = ForceInt(@$_SESSION[$this->Context->Configuration['SESSION_USER_IDENTIFIER']], 0);
		if ($UserID == 0) {
			// UserID wasn't found in the session, so attempt to retrieve it from the cookies
			// Retrieve cookie values
			$CookieUserID = ForceIncomingCookieString($this->Context->Configuration['COOKIE_USER_KEY'], '');
			$VerificationKey = ForceIncomingCookieString($this->Context->Configuration['COOKIE_VERIFICATION_KEY'], '');
			$UserManager = $this->Context->ObjectFactory->NewContextObject(
				$this->Context, 'UserManager');

			$UserID = $UserManager->ValidateVerificationKey($CookieUserID, $VerificationKey);
			if ($UserID > 0) {
				// 1. Update the user's information
				$UserManager->UpdateUserLastVisit($UserID, $VerificationKey);

				// 2. Log the user's IP address
				$UserManager->AddUserIP($UserID);

				// If it has now been found, set up the session.
				$this->AssignSessionUserID($UserID);
			}
		}
		return $UserID;
	}

	// All methods below this point are specific to this authenticator and
	// should not be treated as interface methods. The only required interface
	// properties and methods appear above.

	function AssignSessionUserID($UserID) {
		if ($UserID > 0) {
			@$_SESSION[$this->Context->Configuration['SESSION_USER_IDENTIFIER']] = $UserID;
		}
	}

	/**
	 * Log user ip
	 *
	 * @deprecated 
	 * @param int $UserID
	 */
	function LogIp($UserID) {
		if ($this->Context->Configuration['LOG_ALL_IPS']) {
			$UserManager = $this->Context->ObjectFactory->NewContextObject(
				$this->Context, 'UserManager');
			$UserManager->AddUserIP($UserID);
		}
	}

	function SetCookieCredentials($CookieUserID, $VerificationKey) {
		// Note: 2592000 is 60*60*24*30 or 30 days
		setcookie($this->Context->Configuration['COOKIE_USER_KEY'],
			$CookieUserID,
			time()+2592000,
			$this->Context->Configuration['COOKIE_PATH'],
			$this->Context->Configuration['COOKIE_DOMAIN']);
		setcookie($this->Context->Configuration['COOKIE_VERIFICATION_KEY'],
			$VerificationKey,
			time()+2592000,
			$this->Context->Configuration['COOKIE_PATH'],
			$this->Context->Configuration['COOKIE_DOMAIN']);
	}

	/**
	 * Update user last visit
	 * 
	 * @deprecated
	 * @param int $UserID
	 * @param string $VerificationKey
	 */
	function UpdateLastVisit($UserID, $VerificationKey = '') {
		$UserManager = $this->Context->ObjectFactory->NewContextObject(
			$this->Context, 'UserManager');
		$UserManager->UpdateUserLastVisit($UserID, $VerificationKey);
	}
	
}
?>