<?php //{{MediaWikiExtension}}<source lang="php">
/*
 * YouTubeTag.php - Provides youtube tag for embedding a YouTube video into a page.
  * @author Jim R. Wilson
   * @version 0.1
    * @copyright Copyright (C) 2007 Jim R. Wilson
     * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
      * -----------------------------------------------------------------------
       * Description:
        *     This is a MediaWiki extension which adds an additional tag, <youtube>, for embedding
	 *     YouTube videos into wiki articles.
	  * Requirements:
	   *     MediaWiki 1.6.x, 1.8.x, 1.9.x or higher
	    *     PHP 4.x, 5.x or higher
	     * Installation:
	      *     1. Drop this script (YouTubeTag.php) in $IP/extensions
	       *         Note: $IP is your MediaWiki install dir.
	        *     2. Enable the extension by adding this line to your LocalSettings.php:
		 *            require_once('extensions/YouTubeTag.php');
		  * Usage:
		   *     Once installed, you may utilize YouTubeTag by placing the <youtube> tag in an
		    *     article's text:
		     *         <youtube v="aYouTubeId" />
		      * -----------------------------------------------------------------------
		       * Copyright (c) 2007 Jim R. Wilson
		        * 
			 * Permission is hereby granted, free of charge, to any person obtaining a copy 
			  * of this software and associated documentation files (the "Software"), to deal 
			   * in the Software without restriction, including without limitation the rights to 
			    * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of 
			     * the Software, and to permit persons to whom the Software is furnished to do 
			      * so, subject to the following conditions:
			       * 
			        * The above copyright notice and this permission notice shall be included in all 
				 * copies or substantial portions of the Software.
				  * 
				   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
				    * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
				     * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
				      * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
				       * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, 
				        * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
					 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR 
					  * OTHER DEALINGS IN THE SOFTWARE. 
					   * -----------------------------------------------------------------------
					    */
					     
					     # Confirm MW environment
					     if (defined('MEDIAWIKI')) {
					      
					      # Credits
					      $wgExtensionCredits['parserhook'][] = array(
					          'name'=>'YouTubeTag',
						      'author'=>'Jim Wilson (wilson.jim.r&lt;at&gt;gmail.com)',
						          'url'=>'http://jimbojw.com/wiki/index.php?title=YouTubeTag',
							      'description'=>'Provides youtube tag embedding videos into a page.',
							          'version'=>'0.1'
								  );

								  # Register Extension initializer
								  $wgExtensionFunctions[] = "wfYouTubeTagExtension";
								   
								   # Extension initializer
								   function wfYouTubeTagExtension() {
								       global $wgParser, $wgMessageCache;
								           $wgParser->setHook( "youtube", "renderYouTubeTag" );
									       $wgMessageCache->addMessage('youtubetag-bad-id', 'Invalid YouTube video ID supplied: [$1]');
									       }
									        
										/**
										 * Callback function for embedding video.
										  * @param String $input Text between open and close tags - should always be empty or null.
										   * @param Array $params Array of tag attributes.
										    * @param Parser $parser Instance of Parser performing the parse.
										     */
										     function renderYouTubeTag( $input, $params, &$parser ) {

										         # Check for 'v' parameter and ensure it has a valid value
											     $v = htmlspecialchars($params['v']);
											         if ($v==null || preg_match('%[^A-Za-z0-9_\\-]%',$v)) {
												         return '<div class="errorbox">'.wfMsgForContent('youtubetag-bad-id', $v).'</div>';
													     }

													         # Build URL and output embedded flash object
														     $url = "http://www.youtube.com/v/$v";
														         return
															         '<object width="425" height="350">'.
																         '<param name="movie" value="'.$url.'"></param>'.
																	         '<param name="wmode" value="transparent"></param>'.
																		         '<embed src="'.$url.'" type="application/x-shockwave-flash" '.
																			         'wmode="transparent" width="425" height="350">'.
																				         '</embed></object>';
																					 }
																					  
																					  } # Closing MW Environment wrapper
																					  //</source>
