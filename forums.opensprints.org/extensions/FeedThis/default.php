<?php
/*
Extension Name: FeedThis
Extension Url: http://lussumo.com/addons/index.php?PostBackAction=AddOn&AddOnID=419
Description: Publish RSS2/ATOM Feeds of Discussions, Comments, Blogs and Searches.
Version: 1.02
Author: Andrew Miller (Spode)
Author Url: http://www.spodesabode.com
*/

/*
Based on Feed Publisher by Christophe Gragnic which was based on extensions by Dan Richman (CrudeRSS) and Mark O'Sullivan (RSS2, ATOM).
*/

if (!defined('IN_VANILLA')) exit();

//Get language (change this to whatever language file is available to you)
include ($Configuration['EXTENSIONS_PATH'].'FeedThis/languages/english.php');

//Include libraries
include ($Configuration['EXTENSIONS_PATH'].'FeedThis/library/Function.FeedThis.php');
include ($Configuration['EXTENSIONS_PATH'].'FeedThis/library/PostBackControl.FeedThis.php');

//Configuration defaults
if( !array_key_exists('FT_PANEL_POSITION', $Configuration)) {AddConfigurationSetting($Context, 'FT_PANEL_POSITION', '5');}
if( !array_key_exists('FT_FEED_ITEMS', $Configuration)) {AddConfigurationSetting($Context, 'FT_FEED_ITEMS', '25');}
if( !array_key_exists('FT_WORD_LIMIT', $Configuration)) {AddConfigurationSetting($Context, 'FT_WORD_LIMIT', '0');}
if( !array_key_exists('FT_BLOG_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_BLOG_FEED', '1');}
if( !array_key_exists('FT_BLOG_FEED_EVERY', $Configuration)) {AddConfigurationSetting($Context, 'FT_BLOG_FEED_EVERY', '0');}
if( !array_key_exists('FT_ALLDISCUSSIONS_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_ALLDISCUSSIONS_FEED', '1');}
if( !array_key_exists('FT_ALLDISCUSSIONS_FEED_EVERY', $Configuration)) {AddConfigurationSetting($Context, 'FT_ALLDISCUSSIONS_FEED_EVERY', '0');}
if( !array_key_exists('FT_DISCUSSION_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_DISCUSSION_FEED', '1');}
if( !array_key_exists('FT_CATEGORY_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_CATEGORY_FEED', '1');}
if( !array_key_exists('FT_SEARCHRESULTS_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_SEARCHRESULTS_FEED', '0');}
if( !array_key_exists('FT_USERBLOG_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_USERBLOG_FEED', '0');}
if( !array_key_exists('FT_USERCOMMENTS_FEED', $Configuration)) {AddConfigurationSetting($Context, 'FT_USERCOMMENTS_FEED', '0');}

//Grab the finetune configuration file
include ($Configuration['EXTENSIONS_PATH'].'FeedThis/finetune.php');

//Incorporate finetune settings into the Vanilla configuration with our 'fp_' namespace
if	(is_array($ft_conf) && !empty($ft_conf))
	{
	foreach	($ft_conf as $key => $value)
		{
		$Context->Configuration['FT_'.$key] = $value;
		}
	unset($ft_conf);
	}

//Settings panel for Feed Publisher
if	(($Context->SelfUrl == 'settings.php') && $Context->Session->User->Permission('PERMISSION_CHANGE_APPLICATION_SETTINGS'))
	{
	$FPForm = $Context->ObjectFactory->NewContextObject($Context, 'FTForm');
	$Page->AddRenderControl($FPForm, $Configuration["CONTROL_POSITION_BODY_ITEM"]);
	$ExtensionOptions = $Context->GetDefinition('Extension Options');
	$Panel->AddList($ExtensionOptions);
	$Panel->AddListItem($ExtensionOptions, $Context->GetDefinition('FTSettings'), GetUrl($Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=FeedThis'));
	}

//Adds ability to search by CategoryID, DiscussionID or AuthUserID
$Context->AddToDelegate("CommentManager", "SearchBuilder_PostWhere", "Search_FeedAddQuery");
	
//Is this a feed, or normal browsing?
$FeedType = ForceIncomingString ('Feed', '');

//If this is a feed, is it one of the configured to be allowed?
if	(!in_array ($FeedType, $Context->Configuration['FT_PUBLISHED_TYPES']))
	{
	$FeedType = '';
	}

//Pages that should be processed.
$processed_pages = array( 'index.php', 'search.php', 'comments.php', 'categories.php', 'extension.php', 'account.php');

if 	(in_array ($Context->SelfUrl, $processed_pages))
	{
	//For non-feed
	if	($FeedType == '')
		{
		//display all discussions
		if 	(($Context->SelfUrl == "index.php" || $Context->SelfUrl == "categories.php" || $Context->Configuration['FT_ALLDISCUSSIONS_FEED_EVERY']) && !ForceIncomingInt("CategoryID", '') && ($Context->Configuration['FT_ALLDISCUSSIONS_FEED']) || $Context->Configuration['FT_ALLDISCUSSIONS_FEED_EVERY'])
			{
			unset ($Properties);
			$Properties['SearchType'] = "Topics";
			$Properties['ListName'] = $Context->GetDefinition('AllDiscussionsFeed');
			$Properties['Extra'] = "&FeedTitle=".urlencode($Properties['ListName']);
			AddLinksForEach($Properties);
			}
		//display category listing
		if 	(($Context->SelfUrl == "index.php" || $Context->SelfUrl == "categories.php") && ($CategoryID = ForceIncomingInt("CategoryID", '')) && $Context->Configuration['FT_CATEGORY_FEED'])
			{
			unset ($Properties);
			$Properties['SearchType'] = "Topics";
			$Properties['ListName'] = $Context->GetDefinition('CategoryFeed');
			$Properties['Extra'] = "&CategoryID=".$CategoryID."&FeedTitle=".urlencode($Properties['ListName']." (".GetCategoryName($CategoryID).")");
			AddLinksForEach($Properties);
			}
		//BlogThis feed
		if 	(($Context->SelfUrl == "extension.php" && (ForceIncomingString("PostBackAction", '') == 'Blog') && $Context->Configuration['FT_BLOG_FEED']) || $Context->Configuration['FT_BLOG_FEED_EVERY'])
			{
			unset ($Properties);
			//Support for per-user Blog RSS Feed
			$BlogUser = ForceIncomingString("BlogUser", '');		
			if 	($BlogUser)
				{
				$Extra = " (".GetUserName($BlogUser).")";				
				$BlogUser = "&AuthUserID=".$BlogUser;
				}

			$Properties['SearchType'] = "Comments";
			$Properties['ListName'] = $Context->GetDefinition('BlogFeed');
			$Properties['Extra'] = "&BlogSearch=1".@$BlogUser."&FeedTitle=".urlencode($Properties['ListName'].@$Extra);
			AddLinksForEach($Properties);
			}
		if 	($Context->SelfUrl == "account.php")
			{
			$UserID = ForceIncomingString('u', $Context->Session->UserID);
			$UserManager = $Context->ObjectFactory->NewContextObject($Context, 'UserManager');
			$User = $UserManager->GetUserById($UserID);

			//Add user Blog Feed
			if 	(array_key_exists('BLOGTHIS', $Configuration) && $Context->Configuration['FT_USERBLOG_FEED'] && ($User->Permission('PERMISSION_CANBLOGTHIS') || $User->Permission('PERMISSION_CANBLOGALL')))
				{
				unset ($Properties);
				$Properties['SearchType'] = "Comments";
				$Properties['ListName'] = $Context->GetDefinition('UserBlogFeed');
				$Properties['Extra'] = "&BlogSearch=1&AuthUserID=".($UserID)."&FeedTitle=".urlencode($Properties['ListName']." (".GetUserName($UserID).")");
				AddLinksForEach($Properties);
				}
			//Add user's Comments
			if 	($Context->Configuration['FT_USERCOMMENTS_FEED'])
				{
				unset ($Properties);
				$Properties['SearchType'] = "Comments";
				$Properties['ListName'] = $Context->GetDefinition('UserCommentsFeed');
				$Properties['Extra'] = "&AuthUserID=".($UserID)."&FeedTitle=".urlencode($Properties['ListName']." (".GetUserName($UserID).")");	
				AddLinksForEach($Properties);
				}
			}
		//Discussion comments
		if 	($Context->SelfUrl == "comments.php" && ($DiscussionID = ForceIncomingInt("DiscussionID", '')) && $Context->Configuration['FT_DISCUSSION_FEED'])
			{
			unset ($Properties);
			$Properties['SearchType'] = "Comments";
			$Properties['ListName'] = $Context->GetDefinition('DiscussionFeed');
			$Properties['Extra'] = "&DiscussionID=".$DiscussionID."&FeedTitle=".urlencode($Properties['ListName']." (".GetDiscussionName($DiscussionID).")");
			AddLinksForEach($Properties);
			}
		//Search Results
		if 	($Context->SelfUrl == "search.php" && ForceIncomingString("PostBackAction", '') == 'Search' && $Context->Configuration['FT_SEARCHRESULTS_FEED'])
			{
			unset ($Properties);
		 	$Tag = ForceIncomingString("Tag", '');
		 	$Keywords = ForceIncomingString("Keywords", '');
		 	$Authusername = ForceIncomingString("AuthUsername", '');
		 	$Categories = ForceIncomingString("Categories", '');
		 	$Advanced = ForceIncomingInt("Advanced", '');
			$Type = ForceIncomingString("Type", '');

			$Extra = "";

			//Add them to the search string.
			if	($Tag)
				{
				$Extra.= "Tag: ".$Tag." ";
				$Tag = "&Tag=".$Tag;
				}

			if 	($Keywords)
				{
				$Extra.= "Keywords: ".$Keywords.", ";
				$Keywords = "&Keywords=".$Keywords;
				}

			if 	($Authusername)
				{
				$Extra.= "Author: ".$Authusername.", ";
				$Authusername = "&AuthUsername=".$Authusername;
				}

			if 	($Categories)
				{
				$Extra.= "Category: ".$Categories.", ";
				$Categories = "&Categories=".$Categories;
				}

			if 	($Advanced)
				{
				$Advanced = "&Advanced=".$Advanced;
				}


			if 	($Extra == "")
				{
				unset($Extra);
				}
			else
				{
				$Extra = " (".substr($Extra,0,strlen($Extra)-1).")";
				}

			$Properties['SearchType'] = $Type;
			$Properties['ListName'] = $Context->GetDefinition('SearchFeed');
			$Properties['Extra'] = @$Tag.@$Keywords.@$Authusername.@$Categories.@$Advanced."&FeedTitle=".urlencode($Properties['ListName'].@$Extra);
			AddLinksForEach($Properties);
			}
		}
	//For feed
	else
		{
		// Make sure that page is not redirected if the user is not signed in and this is not a public forum
		if	($Context->Session->UserID == 0 && !$Configuration["PUBLIC_BROWSING"])
			{
			// Temporarily make the PUBLIC_BROWSING enabled, but make sure to validate this user
			$Configuration[ "PUBLIC_BROWSING" ] = 1;
			$Context->Configuration['FT_AUTHENTICATE_USER'] = 1;
			}
		else
			{
			$Context->Configuration[ 'FT_AUTHENTICATE_USER' ] = 0;
			}
		
		//Exit the feed? For compatibility with SimpleCache
		if 	(!$Context->Configuration['FT_EXIT_AFTER_FEED'])
			{
			$Context->AddToDelegate('PageEnd', 'PostRender', 'CleanBufferAndDisplayFeed');
			}
		}
	
	//This is where the magic happens. ALL feeds originate from a search.
	if	($Context->SelfUrl == "search.php" && $FeedType)
		{
		//Override the number of search results.
		$Context->Configuration['SEARCH_RESULTS_PER_PAGE'] = $Context->Configuration['FT_FEED_ITEMS'];

		//Get search parameters from the URL
		$SearchType = ForceIncomingString( "Type", "" );
		$SearchID = ForceIncomingInt( "SearchID", 0);
	
		//If no search type in the url but we found an 'id', so grab the search type from the 'id'
		if	($SearchType == "" && $SearchID > 0)
			{
			$SearchManager = $Context->ObjectFactory->NewContextObject($Context, "SearchManager");
			$Search = $SearchManager->GetSearchById($SearchID);
			if	($Search)
				{
				$SearchType = $Search->Type;
				}
			}
	
		if	($SearchType == "Topics" || $SearchType == "Discussions")
			{
			//Think this could be a bug in Vanilla itself - so a work around...
			$SearchType = "Topics";

			//Make sure that the first comment is also grabbed from the search
			$Context->AddToDelegate("DiscussionManager", "PostGetDiscussionBuilder", "DiscussionManager_GetFirstCommentForFeeds" );		 
			
			$Context->AddToDelegate("SearchForm", "PostLoadData", "SearchForm_InjectFeedToTopicSearch");
			}
		elseif	($SearchType == "Comments")
			{		 
			$Context->AddToDelegate ("SearchForm", "PostLoadData", "SearchForm_InjectFeedToCommentSearch");
			}
		}
	}
