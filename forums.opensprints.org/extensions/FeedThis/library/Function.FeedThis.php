<?php

//Gets Discussion name from ID
function GetDiscussionName ($DiscussionID)
	{
	global $Context;
	$sql = "SELECT Name FROM ".$Context->Configuration['DATABASE_TABLE_PREFIX']."Discussion WHERE `DiscussionID` = \"".FormatStringForDatabaseInput($DiscussionID)."\";";
	$result = $Context->Database->Execute($sql, 'FeedThis', 'GetDiscussionName' , 'An error occurred while fetching the discussion name.');
	$row = $Context->Database->GetRow($result);

	return $row['Name'];
	}

//Gets Category Name from ID
function GetCategoryName ($CategoryID)
	{
	global $Context;
	$sql = "SELECT Name FROM ".$Context->Configuration['DATABASE_TABLE_PREFIX']."Category WHERE `CategoryID` = \"".FormatStringForDatabaseInput($CategoryID)."\";";
	$result = $Context->Database->Execute($sql, 'FeedThis', 'GetCategoryName' , 'An error occurred while fetching the category name.');
	$row = $Context->Database->GetRow($result);

	return $row['Name'];
	}

//Gets User Name from ID
function GetUserName ($UserID)
	{
	global $Context;
	$sql = "SELECT Name FROM ".$Context->Configuration['DATABASE_TABLE_PREFIX']."User WHERE `UserID` = \"".FormatStringForDatabaseInput($UserID)."\";";
	$result = $Context->Database->Execute($sql, 'FeedThis', 'GetUserName' , 'An error occurred while fetching the user name.');		
	$row = $Context->Database->GetRow($result);

	return $row['Name'];
	}

//Adds panel links for each RSS Feed
function AddLinksForEach ($Properties)
	{
	global $Context;
	global $Panel;
	global $Head;
	//Now we have parameters, put the links up
	foreach ($Context->Configuration['FT_PUBLISHED_TYPES'] as $PublishedType)
		{
		$Properties['FeedType'] = $PublishedType;
		if 	(@$Properties['SearchType'] != "Users" && @$Properties['ListName'])
			{
			AddLinks($Context, $Head, $Panel, $Properties);
			}
		}
	}

//Gets the URI for the requested feed
function GetFeedUriForFeed (&$Configuration, $Parameters)
	{
	global $Context;
	if (($Parameters['ListName'] == $Context->GetDefinition('BlogFeed')) && isset($Configuration['FT_BLOGFEED_ALT'] [$Parameters['FeedType']])) {return $Configuration['FT_BLOGFEED_ALT'] [$Parameters['FeedType']];} 
	if (($Parameters['ListName'] == $Context->GetDefinition('AllDiscussionsFeed')) && isset($Configuration['FT_ALLDISCUSSIONS_ALT'] [$Parameters['FeedType']])) {return $Configuration['FT_ALLDISCUSSIONS_ALT'] [$Parameters['FeedType']];} 

	return GetUrl($Configuration, "search.php", '', '', '', '', "PostBackAction=Search&Type=".$Parameters['SearchType']."&Page=1&Feed=" . $Parameters['FeedType'].@$Parameters['Extra'], '');
	}


//Adds the feed links to the side panel
function AddLinks (&$Context, &$Head, &$Panel, $Properties)
	{
	$FeedUrl = GetFeedUriForFeed ($Context->Configuration, $Properties);
	
	$ListName = $Properties['ListName'];
	$ListItem = $Context->GetDefinition($Properties['FeedType'].'Feed');
	$BodyTitle = $Context->GetDefinition('SubscribeFeed');
	
	//Add link
	$Panel->AddList ($ListName, $Context->Configuration['FT_PANEL_POSITION']);
	$Panel->AddListItem($ListName, $ListItem, $FeedUrl, '', 'title="'.$BodyTitle.'" class="'.$Properties['FeedType'].'"');
	
	//Add to HTML HEAD section

	//Quick fix for the MIME type
	if	($Properties['FeedType'] == 'RSS2')
		{
		$MIME = 'rss';
		}
	else	{
		$MIME = strtolower ($Properties['FeedType']);
		}
	
	//Add link
	$HeadLink = '<link rel="alternate" ';
	$HeadLink.= 'type="application/'. $MIME . '+xml" ';
	$HeadLink.= 'href="' . $FeedUrl . '" ';
	$HeadLink.= 'title="' . $ListName . ' ('.$Properties['FeedType'].')"';
	$HeadLink.= ' />';
	$HeadLink = "\n            " . $HeadLink . "\n   ";
	$Head->AddString( $HeadLink );
	}

//Allows to search by CategoryID and DiscussionID and AuthUserID
function Search_FeedAddQuery(&$CommentManager)
	{
	$s = &$CommentManager->DelegateParameters ['SqlBuilder'];

	if 	($AuthUserID = ForceIncomingInt('AuthUserID', ''))
		{
		$s->AddWhere('c', 'AuthUserID', '', FormatStringForDatabaseInput($AuthUserID), '=', 'and');
		}

	if 	($CategoryID = ForceIncomingInt('CategoryID', ''))
		{
		$s->AddWhere('d', 'CategoryID', '', FormatStringForDatabaseInput($CategoryID), '=', 'and');
		}

	if 	($DiscussionID = ForceIncomingInt('DiscussionID', ''))
		{
		$s->AddWhere('d', 'DiscussionID', '', FormatStringForDatabaseInput($DiscussionID), '=', 'and');
		}

	$s->AddOrderBy('DateCreated', 'd', $SortDirection = 'desc');
	}

//Gets the first comment for a discussion and also add search by IDs
function DiscussionManager_GetFirstCommentForFeeds ($DiscussionManager)
	{
	$s = &$DiscussionManager->DelegateParameters ['SqlBuilder'];
	$s->AddJoin ('Comment', 'fc', 'CommentID', 't', 'FirstCommentID', 'left join');

	if 	($AuthUserID = ForceIncomingInt('AuthUserID', ''))
		{
		$s->AddWhere('t', 'AuthUserID', '', FormatStringForDatabaseInput($AuthUserID), '=', 'and');
		}

	if 	($CategoryID = ForceIncomingInt('CategoryID', ''))
		{
		$s->AddWhere('t', 'CategoryID', '', FormatStringForDatabaseInput($CategoryID), '=', 'and');
		}

	if 	($DiscussionID = ForceIncomingInt('DiscussionID', ''))
		{
		$s->AddWhere('t', 'DiscussionID', '', FormatStringForDatabaseInput($DiscussionID), '=', 'and');
		}

	$s->AddSelect (array('FormatType', 'Body'), 'fc');
	}


//Renders Comment Searches
function SearchForm_InjectFeedToCommentSearch($SearchForm)
	{
	global $FeedType;

	if	($SearchForm->Context->WarningCollector->Count() == 0)
		{
		AuthenticateUserForFT ($SearchForm->Context);
	
		$SearchForm->Context->PageTitle	= ForceIncomingString ("FeedTitle", "");

		//Loop through the data
		$Counter = 0;
		$Feed = "";
		$Properties = array();
		$Comment = $SearchForm->Context->ObjectFactory->NewContextObject ($SearchForm->Context, "Comment");
	
		while	($Row = $SearchForm->Context->Database->GetRow ($SearchForm->Data))
			{
			$Comment->Clear();
			$Comment->GetPropertiesFromDataSet( $Row, $SearchForm->Context->Session->UserID );
		
			if	($Counter < $SearchForm->Context->Configuration["SEARCH_RESULTS_PER_PAGE" ])
				{
				$Properties["Title"] = FormatHtmlStringInline (ForceString($Comment->Discussion, "" ));
				$Properties["Link"] = GetUrl ($SearchForm->Context->Configuration, "comments.php", "", "DiscussionID", $Comment->DiscussionID, CleanupString($Comment->Discussion), "Focus=".$Comment->CommentID."#Comment_".$Comment->CommentID);
				$Properties["Published"] = FixDateForFT ($FeedType, @$Row["DateCreated"]);
				$Properties["Updated"] = FixDateForFT ($FeedType, @$Row["DateEdited" ]);
				$Properties["AuthorName"] = $Comment->AuthUsername;
				$Properties["AuthorUrl"] = GetUrl ($SearchForm->Context->Configuration, "account.php", "", "u", $Comment->AuthUserID);
			
				//Format the comment according to the defined formatter for that comment
				$Properties["Content"] = $SearchForm->Context->FormatString (CutShortString($Comment->Body), $Comment, $Comment->FormatType, FORMAT_STRING_FOR_DISPLAY);
				$Properties["Summary"] = FormatStringForFeedSummary (@$Properties["Content"]);
		 
				$Feed.= ReturnFeedItem ($Properties, $FeedType);
				}
			else
				{
				break;									
				}
			$Counter++;
			}
   
		$Feed = ReturnWrappedFeedForSearch( $SearchForm->Context, $Feed, $FeedType );
	
		if	($SearchForm->Context->Configuration['FT_EXIT_AFTER_FEED'])
			{
			// Set the content type to xml and dump the feed
			header("Content-type: text/xml\n");
			echo ($Feed);
  
			// When all finished, unload the context object and stop
			$SearchForm->Context->Unload();
			exit;
			}
		else
			{
			// store the feed in the context object
			$SearchForm->Context->Feed = $Feed;
			}
		}
	}

//Renders Topic Searches
function SearchForm_InjectFeedToTopicSearch ($SearchForm)
	{
	global $FeedType;

	if	($SearchForm->Context->WarningCollector->Count() == 0)
		{
		AuthenticateUserForFT ($SearchForm->Context);

		$SearchForm->Context->PageTitle = ForceIncomingString ("FeedTitle", "");

		//Loop through the data
		$Counter = 0;
		$Feed = "";
		$Properties = array();
		while	($DataSet = $SearchForm->Context->Database->GetRow($SearchForm->Data))
			{
			$Properties["Title"] = FormatHtmlStringInline(ForceString ($DataSet["Name"], ""));
			$Properties["Link"] = GetUrl($SearchForm->Context->Configuration, "comments.php", "", "DiscussionID", ForceInt($DataSet["DiscussionID"], 0), CleanupString($DataSet["Name"]));
			$Properties["Published"] = FixDateForFT ($FeedType, @$DataSet["DateCreated"]);
			$Properties["Updated"] = FixDateForFT ($FeedType, @$DataSet["DateLastActive"]);
			$Properties["AuthorName"] = FormatHtmlStringInline (ForceString ($DataSet["AuthUsername"], ""));
			$Properties["AuthorUrl"] = GetUrl ($SearchForm->Context->Configuration, "account.php", "", "u", ForceInt ($DataSet["AuthUserID"], 0));
		 
			// Format the comment according to the defined formatter for that comment
			$FormatType = ForceString (@$DataSet[ "FormatType" ], $SearchForm->Context->Configuration[ "DEFAULT_FORMAT_TYPE" ] );
			$FirstCommentBody = ForceString(@trim($DataSet[ "Body" ]), '...' );
			$Properties[ "Content" ] = $SearchForm->Context->FormatString (CutShortString($FirstCommentBody), $SearchForm, $FormatType, FORMAT_STRING_FOR_DISPLAY);
			$Properties[ "Summary" ] = FormatStringForFeedSummary (@$Properties[ "Content" ]);
		 
			$Feed.= ReturnFeedItem ($Properties, $FeedType);

			$Counter++;
			}

		$Feed = ReturnWrappedFeedForSearch($SearchForm->Context, $Feed, $FeedType);

		if	($SearchForm->Context->Configuration['FT_EXIT_AFTER_FEED'])
			{
			//Set the content type to xml and dump the feed
			header("Content-type: text/xml\n");
			echo ($Feed);

			//When all finished, unload the context object and stop
			$SearchForm->Context->Unload();
			exit;
			}
		else
			{
			//Store the feed in the context object
			$SearchForm->Context->Feed = $Feed;
			}
		}
	}

function AuthenticateUserForFT (&$Context)
	{
	//Perform some HTTP authentication if public browsing is not enabled.
	if	($Context->Configuration['FT_AUTHENTICATE_USER'])
		{
		//Assume user is not authenticated.
		$UserIsAuthenticated = false;
		
		$PHP_AUTH_USER = ForceString (@$_SERVER['PHP_AUTH_USER'], '');
		$PHP_AUTH_PW = ForceString (@$_SERVER['PHP_AUTH_PW'], '');
		
		if ($PHP_AUTH_USER && $PHP_AUTH_PW)
			{
			$Auth = $Context->ObjectFactory->NewContextObject($Context, "Authenticator");
			$result = $Auth->Authenticate (FormatStringForDatabaseInput( $PHP_AUTH_USER ), FormatStringForDatabaseInput( $PHP_AUTH_PW ), '1');

			if ($result) {$UserIsAuthenticated = true;}
			}

		//Couldn't authenticate		
		if	(!$UserIsAuthenticated)
			{
			header ('WWW-Authenticate: Basic realm="Private"');
			header ('HTTP/1.0 401 Unauthorized');

			echo ('<h1>'.$Context->GetDefinition ('FailedFeedAuthenticationTitle').'</h1><h2>'.$Context->GetDefinition ('FailedFeedAuthenticationText').'</h2>');
			$Context->Unload();
			die();
			}
		}
	}


function CleanBufferAndDisplayFeed ($PageEnd)
	{
	ob_end_clean();
	ob_start();
	header ("Content-type: text/xml\n");
	if	(!empty ($PageEnd->Context->Feed))
		{
		echo $PageEnd->Context->Feed;
		}
	}


//Sort dates out depending on feed type
function FixDateForFT ($FeedType, $Date = '')
	{	
	if 	($FeedType == 'RSS2')
		{
		$DateFormat = 'r';
		}
	elseif	($FeedType == 'ATOM')
		{
		$DateFormat = 'Y-m-d\TH:i:sO';
		}
	else	{
		$DateFormat = 'r';
		}
	
	//Make a date for 'now'
	if	($Date == '')
		{
		$Date = mktime();
		}
	else	
		{
		$Date = UnixTimestamp ($Date);
		}
	
	//Apply formatting to date.
	$FixedDate = date ($DateFormat, $Date);
	
	//Quick fix for ATOM
	if	(strlen ($FixedDate) == 24)
		{
		$FixedDate = substr ($FixedDate, 0, 22).':'.substr ($FixedDate, 22);
		}
	//All done!   	
	return $FixedDate;
	}

//For making the summary section of ATOM feeds.
function FormatStringForFeedSummary ($String)
	{
	global $Context;
	$sReturn = strip_tags ($String);
	$sReturn = htmlspecialchars ($sReturn);
	$sReturn = str_replace ('\r\n', ' ', $sReturn);
	return SliceString ($sReturn, 200);
	}

//For summary creation!
function CutShortString ($String)
	{
	global $Context;

	if  ($Context->Configuration['FT_WORD_LIMIT'])
		{
		$out = explode(" ", $String);
		
		if (count($out) <= $Context->Configuration['BLOG_WORD_LIMIT'])
			{
			$output = $String;
			}
		else
			{
			$output = "";
		
			for ($i=0; $i < $Context->Configuration['FT_WORD_LIMIT'];$i++)
				{
				$output .= $out[$i] . " ";
				}
			
			//take off the last space and add "..."
			$output = trim($output) . "...";
			}
		}
	else
		{
		$output = $String;		
		}

	return $output;
	}


function ReturnWrappedFeedForSearch (&$Context, $FeedItems, $FeedType)
	{
	if 	($FeedType == 'RSS2')	
		{
		return '<?xml version="1.0" encoding="utf-8"?>'.StyleSheetTag( $Context, $FeedType ).'
			<rss version="2.0">
				<channel>
					<title>'
						.htmlspecialchars( $Context->Configuration[ 'APPLICATION_TITLE' ]
						.' - ' . $Context->PageTitle )
					.'</title>
					<lastBuildDate>' . FixDateForFT( $FeedType ) . '</lastBuildDate>
					<link>' . $Context->Configuration[ 'BASE_URL' ] . '</link>
					<description></description>
					<generator>
						Lussumo Vanilla '.APPLICATION_VERSION.' &amp; Feed Publisher
					</generator>
					'.$FeedItems.'
				</channel>
			</rss>';
		}
	elseif	($FeedType == 'ATOM')	
		{
		$p = $Context->ObjectFactory->NewObject ($Context, 'Parameters');
		$p->DefineCollection ($_GET);
		$SelfLink = GetUrl ($Context->Configuration, $Context->SelfUrl, '', '', '', '', $p->GetQueryString());
		$p->Remove ('Feed');
		$AlternateLink = GetUrl($Context->Configuration, $Context->SelfUrl, '', '', '', '', $p->GetQueryString());

		return '<?xml version="1.0" encoding="utf-8"?>'.StyleSheetTag ($Context, $FeedType).'
			<feed xmlns="http://www.w3.org/2005/Atom">
				<title type="text">'
					.htmlspecialchars( $Context->Configuration[ 'APPLICATION_TITLE' ]
					.' - ' . ' ' . $Context->PageTitle )
				.'</title>
				<updated>' . FixDateForFT( $FeedType ) . '</updated>
				<id>' . $Context->Configuration[ 'BASE_URL' ] . '</id>
				<link rel="alternate" type="text/html" hreflang="en"
					href="' . $AlternateLink.'"/>
				<link rel="self" type="application/atom+xml"
					href="' . $SelfLink . '"/>
				<generator
					uri="http://getvanilla.com/"
					version="' . APPLICATION_VERSION . '">
					Lussumo Vanilla &amp; Feed Publisher
				</generator>
				'.$FeedItems.'
			</feed>';
		}
	else
		{
		return '';
		}
	}

function ReturnWrappedFeed (&$Context, $FeedItems, $FeedType)
	{
	if	($FeedType == 'RSS2')
		{
		return '<?xml version="1.0" encoding="utf-8"?>'.StyleSheetTag( $Context, $FeedType ).'
			<rss version="2.0">
				<channel>
					<title>'
						.htmlspecialchars( $Context->Configuration[ 'APPLICATION_TITLE' ]
						.' - ' . $Context->PageTitle )
					.'</title>
					<lastBuildDate>' . FixDateForFT( $FeedType ) . '</lastBuildDate>
					<link>' . $Context->Configuration[ 'BASE_URL' ] . '</link>
					<description></description>
					<generator>
						Lussumo Vanilla ' . APPLICATION_VERSION . ' &amp; Feed Publisher
					</generator>
					' . $FeedItems . '
				</channel>
			</rss>';
		}
	elseif 	($FeedType == 'ATOM')
		{
		$p = $Context->ObjectFactory->NewObject ($Context, 'Parameters');
		$p->DefineCollection ($_GET);
		$SelfLink = GetUrl($Context->Configuration, $Context->SelfUrl,'', '', '', '', $p->GetQueryString());
		$p->Remove ('Feed');
		$AlternateLink = GetUrl($Context->Configuration, $Context->SelfUrl,'', '', '', '', $p->GetQueryString());
		
		return '<?xml version="1.0" encoding="utf-8"?>'.StyleSheetTag( $Context, $FeedType ).'
			<feed xmlns="http://www.w3.org/2005/Atom">
				<title type="text">'
					.htmlspecialchars( $Context->Configuration[ 'APPLICATION_TITLE' ]
					.' - '.$Context->PageTitle )
				.'</title>
				<updated>' . FixDateForFT( $FeedType ) . '</updated>
				<id>' . $Context->Configuration[ 'BASE_URL' ] . '</id>
				<link rel="alternate" type="text/html" hreflang="en"
					href="' . $AlternateLink.'"/>
				<link rel="self" type="application/atom+xml"
					href="' . $SelfLink . '"/>
				<generator
					uri="http://getvanilla.com/"
					version="' . APPLICATION_VERSION . '">
					Lussumo Vanilla &amp; Feed Publisher
				</generator>
				'.$FeedItems.'
			</feed>';
		}
	else
		{	
		return '';
		}
	}

function ReturnFeedItem( $Properties, $FeedType )
	{
	if 	($FeedType == 'RSS2')
		{
		return '<item>
			<title>' . $Properties[ 'Title' ] . '</title>
			<link>' . $Properties[ 'Link' ] . '</link>
			<guid isPermaLink="false">' . $Properties[ 'Link' ] . '</guid>
			<pubDate>' . $Properties[ 'Published' ] . '</pubDate>
			<author>' . $Properties[ 'AuthorName' ] . '</author>
			<description>
				<![CDATA[ ' . $Properties[ 'Content' ] . ' ]]>
			</description>
		</item>
		';
		}
	elseif ($FeedType == 'ATOM')
		{	
		return '<entry>
			<title>'.$Properties['Title'].'</title>
			<link rel="alternate" href="'.$Properties['Link'].'" type="application/xhtml+xml" hreflang="en"/>
			<id>'.$Properties['Link'].'</id>
			<published>'.$Properties['Published'].'</published>
			<updated>'.$Properties['Updated'].'</updated>
			<author>
				<name>'.$Properties['AuthorName'].'</name>
				<uri>'.$Properties['AuthorUrl'].'</uri>
			</author>
			<summary type="text" xml:lang="en">
				'.$Properties['Summary'].'
			</summary>
			<content type="html">
				<![CDATA['.$Properties['Content'].']]>
			</content>
		</entry>
		';
		}
	else
		{	
		return '';
		}
	}

function StyleSheetTag (&$Context, $FeedType)
	{
	$return = '';
	
	$ConfigKey = 'FT_STYLESHEET_'.$FeedType;

	if	(array_key_exists ($ConfigKey, $Context->Configuration) && !empty ($Context->Configuration[$ConfigKey]))
		{
		foreach ($Context->Configuration[$ConfigKey] as $attributes_array)
			{
			if 	(empty ($attributes_array))
				{
				continue;
				}
			$return.= '<?xml-stylesheet';
			
			foreach	($attributes_array as $att => $val)
				{
				$return.= ' '.$att.'="'.$val.'"';
				}

			$return.= '?>';
			}
		}

	return $return;
	}
