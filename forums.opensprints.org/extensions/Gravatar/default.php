<?php
/**
Extension Name: Gravatar
Extension Url: http://lussumo.com/addons/index.php?PostBackAction=AddOn&AddOnID=335
Description: Add gravatar icon to discussion
Version: 0.1.2
Author: Dinoboff
Author Url: http://lussumo.com/community/account/2469/
 *
 * @package Extensions
 * @subpackage Gravatar
 */



/**
 * Change definition of IconNotes and Icon in the dictionary.
 */
$Context->SetDefinition('Gravatar_Icon', 'Icon');
$Context->SetDefinition(
	'Gravatar_IconNotes',
	'You can enter any valid URL to an image here, such as: <strong>http://www.mywebsite.com/myicon.jpg</strong>.
	An icon will appear next to your name in discussion comments and on your account page.
	We will try to get your icon from <a href="http://site.gravatar.com/">Gravatar</a>. If an icon
	is associated to your email address on <a href="http://site.gravatar.com/">Gravatar</a>,
	You do not need to enter anything.'
);

$Context->Dictionary['IconNotes']	= $Context->GetDefinition('Gravatar_IconNotes');
$Context->Dictionary['Icon']		= $Context->GetDefinition('Gravatar_Icon');

/**
 * Create gravatar request URL to fletch the image associated to an email
 *
 * @param string $Email
 * @param string $DefaultIconUrl
 * @param array $ArgOption
 * @return string
 * @todo update documentation about new setting (GRAVATAR_URL) and about GRAVATAR_DEFAULT_ICON default value.
 */
function Gravatar_GetGravatarUrl($Email, $DefaultIconUrl='', $ArgOptions = array())
{
	$DefaultOptions = array(
		'GRAVATAR_URL'			=> 'http://www.gravatar.com/avatar/%s?s=%s&r=%s&d=%s',
		'GRAVATAR_RATING'		=> 'PG',
		'GRAVATAR_SIZE'			=> 32,
		'GRAVATAR_DEFAULT_ICON'	=> '',
	);
	$Options = array_merge($DefaultOptions,$ArgOptions);

	$DefaultIconUrl = ForceString($DefaultIconUrl, $Options['GRAVATAR_DEFAULT_ICON']);
	$DefaultIconUrl = urlencode($DefaultIconUrl);

	return sprintf(
		$Options['GRAVATAR_URL'],
		md5($Email),
		$Options['GRAVATAR_SIZE'],
		$Options['GRAVATAR_RATING'],
		$DefaultIconUrl
	);
}

/**
 * Delegation for the CommentManager::GetCommentBuilder() CommentBuilder_PreWhere delegation.
 * We need the author email.
 *
 * @param CommentManager $ComentManager
 */
function Gravatar_AddEmailToCommentBuilder(&$ComentManager)
{
	$s = &$ComentManager->DelegateParameters['SqlBuilder'];
	$s->AddSelect('Email', 'a', 'AuthEmail');
}

/**
 * Function to add to Comment::GetPropertiesFromDataSet() PreAssignAuthRoleIcon delegation.
 *
 * @param Comment $Comment
 */
function Gravatar_SetAuthIconToGravatarUrl(&$Comment)
{
	$DataSet = $Comment->DelegateParameters['DataSet'];
	$Email = ForceString(@$DataSet['AuthEmail'], '');

	if ($Email) {
		$Comment->AuthIcon = Gravatar_GetGravatarUrl($Email, $Comment->AuthIcon, $Comment->Context->Configuration);
	}
}

/**
 * Function to add to Account::Account() Constructor delegation
 *
 * @param Account $Account
 */
function Gravatar_SetUserDisplayIconToGravatarUrl(&$Account)
{
	// Don't overwrite the DisplayIcon property if a Role Icon should be displayed
	if (empty($Account->User->RoleIcon)) {
		$Account->User->DisplayIcon = Gravatar_GetGravatarUrl(
			$Account->User->Email,
			$Account->User->DisplayIcon,
			$Account->Context->Configuration
		);
	}
}

/**
 * Add delegations
 */
$Context->AddToDelegate('CommentManager', 'CommentBuilder_PreWhere', 'Gravatar_AddEmailToCommentBuilder');
$Context->AddToDelegate('Account', 'Constructor', 'Gravatar_SetUserDisplayIconToGravatarUrl');
// for Vanilla 1.1.4-a
$Context->AddToDelegate('Comment', 'PreAssignAuthRoleIcon', 'Gravatar_SetAuthIconToGravatarUrl');
// for Vanilla 1.1.5-rc1+
$Context->AddToDelegate('Comment', 'PostGetPropertiesFromDataSet', 'Gravatar_SetAuthIconToGravatarUrl');