<?php

/*
	This is Textpattern

	Copyright 2005 by Dean Allen
	www.textpattern.com
	All rights reserved

	Use of this software indicates acceptance of the Textpattern license agreement

$HeadURL: https://textpattern.googlecode.com/svn/releases/4.0.8/source/textpattern/include/txp_admin.php $
$LastChangedRevision: 3013 $

*/

	if (!defined('txpinterface'))
	{
		die('txpinterface is undefined.');
	}

	$levels = array(
		1 => gTxt('publisher'),
		2 => gTxt('managing_editor'),
		3 => gTxt('copy_editor'),
		4 => gTxt('staff_writer'),
		5 => gTxt('freelancer'),
		6 => gTxt('designer'),
		0 => gTxt('none')
	);

	if ($event == 'admin')
	{
		require_privs('admin');

		include_once txpath.'/lib/txplib_admin.php';

		$available_steps = array(
			'admin_multi_edit',
			'admin_change_pageby',
			'author_list',
			'author_save',
			'author_save_new',
			'change_email',
			'change_pass'
		);

		if (!$step or !in_array($step, $available_steps))
		{
			admin();
		}

		else
		{
			$step();
		}
	}

// -------------------------------------------------------------

	function admin($message = '')
	{
		global $txp_user;

		pagetop(gTxt('site_administration'), $message);

		if (is_disabled('mail'))
		{
			echo tag(gTxt('warn_mail_unavailable'), 'p',' id="warning" ');
		}

		$email = fetch('email', 'txp_users', 'name', $txp_user);

		if (has_privs('admin.edit'))
		{
			echo author_form();
		}

		if (has_privs('admin.list'))
		{
			echo author_list();
		}

		echo new_pass_form();

		if (!has_privs('admin.edit'))
		{
			echo change_email_form($email);
		}
	}

// -------------------------------------------------------------

	function change_email()
	{
		global $txp_user;

		$new_email = gps('new_email');

		if (!is_valid_email($new_email))
		{
			admin(gTxt('email_required'));
			return;
		}

		$rs = safe_update('txp_users', "email = '".doSlash($new_email)."'", "name = '".doSlash($txp_user)."'");

		if ($rs)
		{
			admin(
				gTxt('email_changed', array('{email}' => $new_email))
			);
		}
	}

// -------------------------------------------------------------

	function author_save()
	{
		require_privs('admin.edit');

		extract(doSlash(psa(array('privs', 'user_id', 'RealName', 'email'))));
		$privs   = assert_int($privs);
		$user_id = assert_int($user_id);

		if (!is_valid_email($email))
		{
			admin(gTxt('email_required'));
			return;
		}

		$rs = safe_update('txp_users', "
			privs    = $privs,
			RealName = '$RealName',
			email    = '$email'",
			"user_id = $user_id"
		);

		if ($rs)
		{
			admin(
				gTxt('author_updated', array('{name}' => $RealName))
			);
		}
	}

// -------------------------------------------------------------

	function change_pass()
	{
		global $txp_user;

		extract(doSlash(psa(array('new_pass', 'mail_password'))));

		if (empty($new_pass))
		{
			admin(gTxt('password_required'));
			return;
		}

		$rs = safe_update('txp_users', "pass = password(lower('$new_pass'))", "name = '".doSlash($txp_user)."'");

		if ($rs)
		{
			$message = gTxt('password_changed');

			if ($mail_password)
			{
				$email = fetch('email', 'txp_users', 'name', $txp_user);

				send_new_password($new_pass, $email, $txp_user);

				$message .= sp.gTxt('and_mailed_to').sp.$email;
			}

			else
			{
				echo comment(mysql_error());
			}

			$message .= '.';

			admin($message);
		}
	}

// -------------------------------------------------------------

	function author_save_new()
	{
		require_privs('admin.edit');

		extract(doSlash(psa(array('privs', 'name', 'email', 'RealName'))));

		$privs  = assert_int($privs);
		$length = function_exists('mb_strlen') ? mb_strlen($name, '8bit') : strlen($name);

		if ($name and $length <= 64 and is_valid_email($email))
		{
			$password = doSlash(generate_password(6));
			$nonce    = doSlash(md5(uniqid(mt_rand(), TRUE)));

			$rs = safe_insert('txp_users', "
				privs    = $privs,
				name     = '$name',
				email    = '$email',
				RealName = '$RealName',
				nonce    = '$nonce',
				pass     = password(lower('$password'))
			");

			if ($rs)
			{
				send_password($RealName, $name, $email, $password);

				admin(
					gTxt('password_sent_to').sp.$email
				);

				return;
			}
		}

		admin(gTxt('error_adding_new_author'));
	}

// -------------------------------------------------------------

	function privs($priv = '')
	{
		global $levels;
		return selectInput('privs', $levels, $priv);
	}

// -------------------------------------------------------------

	function get_priv_level($priv)
	{
		global $levels;
		return $levels[$priv];
	}

// -------------------------------------------------------------

	function new_pass_form()
	{
		return '<div style="margin: 3em auto auto auto; text-align: center;">'.
		form(
			tag(gTxt('change_password'), 'h3').

			graf('<label for="new_pass">'.gTxt('new_password').'</label> '.
				fInput('password', 'new_pass', '', 'edit', '', '', '20', '1', 'new_pass').
				checkbox('mail_password', '1', true, '', 'mail_password').'<label for="mail_password">'.gTxt('mail_it').'</label> '.
				fInput('submit', 'change_pass', gTxt('submit'), 'smallerbox').
				eInput('admin').
				sInput('change_pass')
			,' style="text-align: center;"')
		).'</div>';
	}

// -------------------------------------------------------------

	function change_email_form($email)
	{
		return '<div style="margin: 3em auto auto auto; text-align: center;">'.
		form(
			tag(gTxt('change_email_address'), 'h3').
			graf('<label for="new_email">'.gTxt('new_email').'</label> '.
				fInput('text', 'new_email', $email, 'edit', '', '', '20', '2', 'new_email').
				fInput('submit', 'change_email', gTxt('submit'), 'smallerbox').
				eInput('admin').
				sInput('change_email')
			,' style="text-align: center;"')
		).'</div>';
	}

// -------------------------------------------------------------

	function author_list()
	{
		global $txp_user, $author_list_pageby;

		extract(gpsa(array('page', 'sort', 'dir', 'crit', 'search_method')));

		if (!in_array($sort, array('name', 'RealName', 'email', 'privs', 'last_login'))) $sort = 'name';

		$dir        = ($dir == 'desc') ? 'desc' : 'asc';
		$sort_sql   = $sort.' '.$dir;
		$switch_dir = ($dir == 'desc') ? 'asc' : 'desc';

		$total = getCount('txp_users', '1=1');
		$limit = max($author_list_pageby, 15);

		list($page, $offset, $numPages) = pager($total, $limit, $page);

		$rs = safe_rows_start('*, unix_timestamp(last_access) as last_login', 'txp_users', '1 = 1 order by '.$sort_sql.' limit '.$offset.', '.$limit);

		if ($rs)
		{
			echo '<form action="index.php" method="post" name="longform" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">'.

			startTable('list').

			tr(
				column_head('login_name', 'name', 'admin', true, $switch_dir, '', '', ('name' == $sort) ? $dir : '').
				column_head('real_name', 'RealName', 'admin', true, $switch_dir, '', '', ('RealName' == $sort) ? $dir : '').
				column_head('email', 'email', 'admin', true, $switch_dir, '', '', ('email' == $sort) ? $dir : '').
				column_head('privileges', 'privs', 'admin', true, $switch_dir, '', '', ('privs' == $sort) ? $dir : '').
				column_head('last_login', 'last_login', 'admin', true, $switch_dir, '', '', ('last_login' == $sort) ? $dir : '').
				hCell().
				hCell()
			);

			while ($a = nextRow($rs))
			{
				extract(doSpecial($a));

				echo tr(
					td($name).
					td($RealName).
					td('<a href="mailto:'.$email.'">'.$email.'</a>').
					td(get_priv_level($privs)).
					td($last_login ? safe_strftime('%b&#160;%Y', $last_login) : '').
					td((has_privs('admin.edit')) ? eLink('admin', 'author_edit', 'user_id', $user_id, gTxt('edit')) : '').
					td((has_privs('admin.edit') and $txp_user != $a['name']) ? fInput('checkbox', 'selected[]', $a['name']) : '')
				);
			}

			echo n.n.tr(
				tda(
					select_buttons().
					author_multiedit_form($page, $sort, $dir, $crit, $search_method)
				, ' colspan="6" style="text-align: right; border: none;"')
			).

			endTable().
			'</form>'.

			nav_form('admin', $page, $numPages, $sort, $dir, $crit, $search_method).

			pageby_form('admin', $author_list_pageby);
		}
	}

// -------------------------------------------------------------

	function author_form()
	{
		global $step, $txp_user;

		$vars = array('user_id', 'name', 'RealName', 'email', 'privs');

		extract(gpsa($vars));

		if ($user_id && $step == 'author_edit')
		{
			$user_id = assert_int($user_id);
			extract(safe_row('*', 'txp_users', "user_id = $user_id"));
		}

		if ($step == 'author_save' or $step == 'author_save_new')
		{
			foreach ($vars as $var)
			{
				$$var = '';
			}
		}

		$caption = gTxt(($step == 'author_edit') ? 'edit_author' : 'add_new_author');

		return form(

			hed($caption, 3,' style="text-align: center;"').

			startTable('edit').

			tr(
				fLabelCell('login_name').
				($user_id && $step == 'author_edit' ? td(strong($name)) : fInputCell('name', $name))
			).

			tr(
				fLabelCell('real_name').
				fInputCell('RealName', $RealName)
			).

			tr(
				fLabelCell('email').
				fInputCell('email', $email)
			).

			tr(
				fLabelCell('privileges').
				td(
					($txp_user != $name
						? privs($privs)
						: hInput('privs', $privs).strong(get_priv_level($privs))
					)
					.sp.popHelp('about_privileges')
				)
			).

			tr(
				td().
				td(
					fInput('submit', '', gTxt('save'), 'publish').($user_id ? '' : sp.popHelp('add_new_author'))
				)
			).

			endTable().

			eInput('admin').
			($user_id ? hInput('user_id', $user_id).sInput('author_save') : sInput('author_save_new'))
		);
	}

// -------------------------------------------------------------

	function admin_change_pageby()
	{
		event_change_pageby('author');
		admin();
	}

// -------------------------------------------------------------

	function author_multiedit_form($page, $sort, $dir, $crit, $search_method)
	{
		$methods = array(
			'changeprivilege' => gTxt('changeprivilege'),
			'resetpassword' => gTxt('resetpassword'),
			'delete' => gTxt('delete')
		);

		return event_multiedit_form('admin', $methods, $page, $sort, $dir, $crit, $search_method);
	}

// -------------------------------------------------------------

	function admin_multi_edit()
	{
		global $txp_user;

		require_privs('admin.edit');

		$selected = ps('selected');
		$method   = ps('edit_method');
		$changed  = array();

		if (!$selected or !is_array($selected))
                {
                	return admin();
		}

		$names = safe_column('name', 'txp_users', "name IN ('".join("','", doSlash($selected))."') AND name != '".doSlash($txp_user)."'");

		if (!$names) return admin();

		switch ($method)
		{
			case 'delete':

				if (safe_delete('txp_users', "name IN ('".join("','", doSlash($names))."')"))
				{
					$changed = $names;
					$msg = 'author_deleted';
				}

				break;

			case 'changeprivilege':

				global $levels;

				$privilege = ps('privs');

				if (!isset($levels[$privilege])) return admin();

				if (safe_update('txp_users', 'privs = '.intval($privilege), "name IN ('".join("','", doSlash($names))."')"))
				{
					$changed = $names;
					$msg = 'author_updated';
				}

				break;

			case 'resetpassword':

				$failed  = array();

				foreach ($names as $name)
				{
					$passwd = generate_password(6);

					if (safe_update('txp_users', "pass = password(lower('".doSlash($passwd)."'))", "name = '".doSlash($name)."'"));
					{
						$email = safe_field('email', 'txp_users', "name = '".doSlash($name)."'");

						if (send_new_password($passwd, $email, $name))
						{
							$changed[] = $name;
							$msg = 'author_updated';
						}
						else
						{
							return admin(gTxt('could_not_mail').' '.htmlspecialchars($name));
						}
					}
				}

				break;
		}

		if ($changed)
		{
			return admin(gTxt($msg, array('{name}' => htmlspecialchars(join(', ', $changed)))));
		}

		admin();
	}
?>
