<?php
/*
	This is Textpattern
	Copyright 2005 by Dean Allen
 	All rights reserved.

	Use of this software indicates acceptance of the Textpattern license agreement

$HeadURL: https://textpattern.googlecode.com/svn/releases/4.0.8/source/textpattern/include/txp_list.php $
$LastChangedRevision: 3069 $

*/

	if (!defined('txpinterface')) die('txpinterface is undefined.');

	global $statuses;
	$statuses = array(
		1 => gTxt('draft'),
		2 => gTxt('hidden'),
		3 => gTxt('pending'),
		4 => gTxt('live'),
		5 => gTxt('sticky'),
	);

	if ($event=='list') {
		require_privs('article');

		if(!$step or !in_array($step, array('list_change_pageby','list_list','list_multi_edit','list_list'))){
			list_list();
		} else $step();
	}

//--------------------------------------------------------------

	function list_list($message = '', $post = '')
	{
		global $statuses, $comments_disabled_after, $step, $txp_user, $article_list_pageby;

		pagetop(gTxt('tab_list'), $message);

		extract(gpsa(array('page', 'sort', 'dir', 'crit', 'search_method')));

		$sesutats = array_flip($statuses);

		$dir = ($dir == 'asc') ? 'asc' : 'desc';

		switch ($sort)
		{
			case 'id':
				$sort_sql = 'ID '.$dir;
			break;

			case 'expires':
				$sort_sql = 'Expires '.$dir;
			break;

			case 'title':
				$sort_sql = 'Title '.$dir.', Posted desc';
			break;

			case 'section':
				$sort_sql = 'Section '.$dir.', Posted desc';
			break;

			case 'category1':
				$sort_sql = 'Category1 '.$dir.', Posted desc';
			break;

			case 'category2':
				$sort_sql = 'Category2 '.$dir.', Posted desc';
			break;

			case 'status':
				$sort_sql = 'Status '.$dir.', Posted desc';
			break;

			case 'author':
				$sort_sql = 'AuthorID '.$dir.', Posted desc';
			break;

			case 'comments':
				$sort_sql = 'comments_count '.$dir.', Posted desc';
			break;

			case 'lastmod':
				$sort_sql = 'LastMod '.$dir.', Posted desc';
			break;

			default:
				$sort = 'posted';
				$sort_sql = 'Posted '.$dir;
			break;
		}

		$switch_dir = ($dir == 'desc') ? 'asc' : 'desc';

		$criteria = 1;

		if ($search_method and $crit)
		{
			$crit_escaped = doSlash($crit);
			$critsql = array(
				'id'         => "ID in ('" .join("','", do_list($crit_escaped)). "')",
				'title_body_excerpt' => "Title rlike '$crit_escaped' or Body rlike '$crit_escaped' or Excerpt rlike '$crit_escaped'",
				'section'    => "Section rlike '$crit_escaped'",
				'keywords'   => "FIND_IN_SET('".$crit_escaped."',Keywords)",
				'categories' => "Category1 rlike '$crit_escaped' or Category2 rlike '$crit_escaped'",
				'status'     => "Status = '".(@$sesutats[gTxt($crit_escaped)])."'",
				'author'     => "AuthorID rlike '$crit_escaped'",
				'article_image' => "Image in ('" .join("','", do_list($crit_escaped)). "')",
				'posted'     => "Posted like '$crit_escaped%'",
				'lastmod'    => "LastMod like '$crit_escaped%'"
			);

			if (array_key_exists($search_method, $critsql))
			{
				$criteria = $critsql[$search_method];
				$limit = 500;
			}

			else
			{
				$search_method = '';
				$crit = '';
			}
		}

		else
		{
			$search_method = '';
			$crit = '';
		}

		$total = safe_count('textpattern', "$criteria");

		if ($total < 1)
		{
			if ($criteria != 1)
			{
				echo n.list_search_form($crit, $search_method).
					n.graf(gTxt('no_results_found'), ' class="indicator"');
			}

			else
			{
				echo graf(gTxt('no_articles_recorded'), ' class="indicator"');
			}

			return;
		}

		$limit = max($article_list_pageby, 15);

		list($page, $offset, $numPages) = pager($total, $limit, $page);

		echo n.list_search_form($crit, $search_method);

		$rs = safe_rows_start('*, unix_timestamp(Posted) as posted, unix_timestamp(LastMod) as lastmod, unix_timestamp(Expires) as expires', 'textpattern',
			"$criteria order by $sort_sql limit $offset, $limit"
		);

		if ($rs)
		{
			$total_comments = array();

			// fetch true comment count, not the public comment count
			// maybe we should have another row in the db?
			$rs2 = safe_rows_start('parentid, count(*) as num', 'txp_discuss', "1 group by parentid order by parentid");

			if ($rs2)
			{
				while ($a = nextRow($rs2))
				{
					$pid = $a['parentid'];
					$num = $a['num'];

					$total_comments[$pid] = $num;
				}
			}

			echo n.n.'<form name="longform" method="post" action="index.php" onsubmit="return verify(\''.gTxt('are_you_sure').'\')">'.

				n.startTable('list','','','','90%').
				n.tr(
					n.column_head('ID', 'id', 'list', true, $switch_dir, $crit, $search_method, ('id' == $sort) ? $dir : '').
					column_head('posted', 'posted', 'list', true, $switch_dir, $crit, $search_method, ('posted' == $sort) ? $dir : '').
					column_head('article_modified', 'lastmod', 'list', true, $switch_dir, $crit, $search_method, (('lastmod' == $sort) ? "$dir " : '').'articles_detail').
					column_head('expires', 'expires', 'list', true, $switch_dir, $crit, $search_method, (('expires' == $sort) ? "$dir " : '').'articles_detail').
					column_head('title', 'title', 'list', true, $switch_dir, $crit, $search_method, ('title' == $sort) ? $dir : '').
					column_head('section', 'section', 'list', true, $switch_dir, $crit, $search_method, ('section' == $sort) ? $dir : '').
					column_head('category1', 'category1', 'list', true, $switch_dir, $crit, $search_method, (('category1' == $sort) ? "$dir " : '').'articles_detail').
					column_head('category2', 'category2', 'list', true, $switch_dir, $crit, $search_method, (('category2' == $sort) ? "$dir " : '').'articles_detail').
					column_head('status', 'status', 'list', true, $switch_dir, $crit, $search_method, ('status' == $sort) ? $dir : '').
					column_head('author', 'author', 'list', true, $switch_dir, $crit, $search_method, ('author' == $sort) ? $dir : '').
					column_head('comments', 'comments', 'list', true, $switch_dir, $crit, $search_method, (('comments' == $sort) ? "$dir " : '').'articles_detail').
					hCell()
				);

			include_once txpath.'/publish/taghandlers.php';

			while ($a = nextRow($rs))
			{
				extract($a);

				if (empty($Title))
				{
					$Title = '<em>'.eLink('article', 'edit', 'ID', $ID, gTxt('untitled')).'</em>';
				}

				else
				{
					$Title = eLink('article', 'edit', 'ID', $ID, $Title);
				}

				$Category1 = ($Category1) ? '<span title="'.htmlspecialchars(fetch_category_title($Category1)).'">'.$Category1.'</span>' : '';
				$Category2 = ($Category2) ? '<span title="'.htmlspecialchars(fetch_category_title($Category2)).'">'.$Category2.'</span>' : '';

				$view_url = permlinkurl($a);

				if ($Status != 4 and $Status != 5)
				{
					$view_url .= (strpos($view_url, '?') === FALSE ? '?' : '&amp;') . 'txpreview='.intval($ID).'.'.time();
				}

				$manage = n.'<ul class="articles_detail">'.
						n.t.'<li>'.eLink('article', 'edit', 'ID', $ID, gTxt('edit')).'</li>'.
						n.t.'<li><a href="'.$view_url.'" class="article-view">'.gTxt('view').'</a></li>'.
						n.'</ul>';

				$Status = !empty($Status) ? $statuses[$Status] : '';

				$comments = gTxt('none');

				if (isset($total_comments[$ID]) and $total_comments[$ID] > 0)
				{
					$comments = href(gTxt('manage'), 'index.php?event=discuss'.a.'step=list'.a.'search_method=parent'.a.'crit='.$ID).
						' ('.$total_comments[$ID].')';
				}

				$comment_status = ($Annotate) ? gTxt('on') : gTxt('off');

				if ($comments_disabled_after)
				{
					$lifespan = $comments_disabled_after * 86400;
					$time_since = time() - $posted;

					if ($time_since > $lifespan)
					{
						$comment_status = gTxt('expired');
					}
				}

				$comments = n.'<ul>'.
					n.t.'<li>'.$comment_status.'</li>'.
					n.t.'<li>'.$comments.'</li>'.
					n.'</ul>';

				echo n.n.tr(

					n.td(eLink('article', 'edit', 'ID', $ID, $ID).$manage).

					td(
						gTime($posted), '', $posted < time() ? '' : 'unpublished'
					).

					td(
						gTime($lastmod), '', "articles_detail"
					).

					td(
						($expires ? gTime($expires) : ''), '' ,'articles_detail'
					).

					td($Title).

					td(
						'<span title="'.htmlspecialchars(fetch_section_title($Section)).'">'.$Section.'</span>'
					, 75).

					td($Category1, 100, "articles_detail").
					td($Category2, 100, "articles_detail").
					td(($a['Status'] < 4 ? $Status : '<a href="'.permlinkurl($a).'">'.$Status.'</a>'), 50).

					td(
						'<span title="'.htmlspecialchars(get_author_name($AuthorID)).'">'.htmlspecialchars($AuthorID).'</span>'
					).

					td($comments, 50, "articles_detail").

					td((
						(  ($a['Status'] >= 4 and has_privs('article.edit.published'))
						or ($a['Status'] >= 4 and $AuthorID == $txp_user
											     and has_privs('article.edit.own.published'))
						or ($a['Status'] < 4 and has_privs('article.edit'))
						or ($a['Status'] < 4 and $AuthorID == $txp_user and has_privs('article.edit.own'))
						)
						? fInput('checkbox', 'selected[]', $ID)
						: '&nbsp;'
					))
				);
			}

			echo n.n.tr(
				tda(
					toggle_box('articles_detail'),
					' colspan="2" style="text-align: left; border: none;"'
				).

				tda(
					select_buttons().
					list_multiedit_form($page, $sort, $dir, $crit, $search_method)
				,' colspan="9" style="text-align: right; border: none;"')
			).

			n.endTable().
			n.'</form>'.

			n.nav_form('list', $page, $numPages, $sort, $dir, $crit, $search_method).

			n.pageby_form('list', $article_list_pageby);
		}
	}

// -------------------------------------------------------------
	function list_change_pageby()
	{
		event_change_pageby('article');
		list_list();
	}

// -------------------------------------------------------------

	function list_search_form($crit, $method)
	{
		$methods =	array(
			'id'				 => gTxt('ID'),
			'title_body_excerpt' => gTxt('title_body_excerpt'),
			'section'	 => gTxt('section'),
			'categories' => gTxt('categories'),
			'keywords'	 => gTxt('keywords'),
			'status'	 => gTxt('status'),
			'author'	 => gTxt('author'),
			'article_image' => gTxt('article_image'),
			'posted'	 => gTxt('posted'),
			'lastmod'	 => gTxt('article_modified')
		);

		return search_form('list', 'list', $crit, $methods, $method, 'title_body_excerpt');
	}

// -------------------------------------------------------------

	function list_multiedit_form($page, $sort, $dir, $crit, $search_method)
	{
		$methods = array(
			'changesection'   => gTxt('changesection'),
			'changecategory1' => gTxt('changecategory1'),
			'changecategory2' => gTxt('changecategory2'),
			'changestatus'    => gTxt('changestatus'),
			'changecomments'  => gTxt('changecomments'),
			'changeauthor'    => gTxt('changeauthor'),
			'delete'          => gTxt('delete'),
		);

		return event_multiedit_form('list', $methods, $page, $sort, $dir, $crit, $search_method);
	}

// -------------------------------------------------------------

	function list_multi_edit()
	{
		global $txp_user;

		$selected = ps('selected');

		if (!$selected or !is_array($selected))
		{
			return list_list();
		}

		$selected = array_map('assert_int', $selected);
		$method   = ps('edit_method');
		$changed  = false;
		$ids      = array();

		if ($method == 'delete')
		{
			if (!has_privs('article.delete'))
			{
				$allowed = array();

				if (has_privs('article.delete.own'))
				{
					foreach ($selected as $id)
					{
						$author = safe_field('AuthorID', 'textpattern', "ID = $id");

						if ($author == $txp_user)
						{
							$allowed[] = $id;
						}
					}
				}

				$selected = $allowed;
			}

			foreach ($selected as $id)
			{
				if (safe_delete('textpattern', "ID = $id"))
				{
					$ids[] = $id;
				}
			}

			$changed = join(', ', $ids);

			if ($changed)
			{
				safe_update('txp_discuss', "visible = ".MODERATE, "parentid in($changed)");
			}
		}

		else
		{
			$selected = array_map('assert_int', $selected);
			$selected = safe_rows('ID, AuthorID, Status', 'textpattern',
									  'ID in ('. implode(',',$selected) .')');

			$allowed = array();
			foreach ($selected as $item)
			{
				if ( ($item['Status'] >= 4 and has_privs('article.edit.published'))
				  or ($item['Status'] >= 4 and $item['AuthorID'] == $txp_user and has_privs('article.edit.own.published'))
				  or ($item['Status'] < 4 and has_privs('article.edit'))
				  or ($item['Status'] < 4 and $item['AuthorID'] == $txp_user and has_privs('article.edit.own')))
				{
					$allowed[] = $item['ID'];
				}
			}

			$selected = $allowed; unset($allowed);

			switch ($method)
			{
				// change author
				case 'changeauthor':

					$key = 'AuthorID';
					$val = has_privs('article.edit') ? ps('AuthorID') : '';

					// do not allow to be set to an empty value
					if (!$val)
					{
						$selected = array();
					}

				break;

				// change category1
				case 'changecategory1':
					$key = 'Category1';
					$val = ps('Category1');
				break;

				// change category2
				case 'changecategory2':
					$key = 'Category2';
					$val = ps('Category2');
				break;

				// change comments
				case 'changecomments':
					$key = 'Annotate';
					$val = (int) ps('Annotate');
				break;

				// change section
				case 'changesection':

					$key = 'Section';
					$val = ps('Section');

					// do not allow to be set to an empty value
					if (!$val)
					{
						$selected = array();
					}

				break;

				// change status
				case 'changestatus':

					$key = 'Status';
					$val = ps('Status');
					if (!has_privs('article.publish') && $val>=4) $val = 3;

					// do not allow to be set to an empty value
					if (!$val)
					{
						$selected = array();
					}

				break;

				default:
					$key = '';
					$val = '';
				break;
			}

			if ($selected and $key)
			{
				foreach ($selected as $id)
				{
					if (safe_update('textpattern', "$key = '".doSlash($val)."'", "ID = $id"))
					{
						$ids[] = $id;
					}
				}

				$changed = join(', ', $ids);
			}
		}

		if ($changed)
		{
			update_lastmod();

			return list_list(
				messenger('article', $changed, (($method == 'delete') ? 'deleted' : 'modified' ))
			);
		}

		return list_list();
	}

?>
