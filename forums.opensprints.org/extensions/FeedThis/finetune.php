<?php

//Array of published types - remove any you don't want
$ft_conf[ 'PUBLISHED_TYPES' ] = array( 'RSS2', 'ATOM' );

//Whether to stop Vanilla processing or not when displaying a feed.
//True will save some resources, while False will allow you to use SimpleCache add-on.

$ft_conf[ 'EXIT_AFTER_FEED' ] = false;

//FeedBurner Support
//Here you can put alternative URLs to be used for the Blog Feed and All Discussions Feed on a per Feed Type Basis. This is useful if you'd rather go through FeedBurner!
//Examples:

//$ft_conf[ 'BLOGFEED_ALT' ] ['RSS2'] = "http://feeds.feedburner.com/myblogfeed-rss2";
//$ft_conf[ 'BLOGFEED_ALT' ] ['ATOM'] = "http://feeds.feedburner.com/myblogfeed-atom";

//$ft_conf[ 'ALLDISCUSSIONS_ALT' ] ['RSS2'] = "http://feeds.feedburner.com/mydiscfeed-rss2";
//$ft_conf[ 'ALLDISCUSSIONS_ALT' ] ['ATOM'] = "http://feeds.feedburner.com/mydiscfeed-atom";

/*
Array of stylesheet attributes

Add attributes with values if you want to format your feed with CSS or XSL, leave empty arrays if you don't need formatting.

IMPORTANT: Make sure the files you link to exist.

NOTE:  You can use several calls to  $ft_conf[ 'StyleSheet_<type>' ][] for use of several style sheets.
 */

/*
EXAMPLES:

// To Add
// < ?xml-stylesheet href="/rss.css" type="text/css"? >, use

$ft_conf[ 'STYLESHEET_RSS2' ][] = array(
                                  'href' => '/rss.css',
                                  'type' => 'text/css' );

// To Add
// < ?xml-stylesheet type="text/xsl" href="style.xsl" media="screen"? >
// < ?xml-stylesheet href="http://www.site.com/atom.css" type="text/css"? >, use

$ft_conf[ 'STYLESHEET_ATOM' ][] = array(
                                  'type' => 'text/xsl',
                                  'href' => 'style.xsl',
                                  'media'=> 'screen' );
$ft_conf[ 'STYLESHEET_ATOM' ][] = array(
                                  'href' => 'http://www.site.com/atom.css',
                                  'type' => 'text/css' );
*/

