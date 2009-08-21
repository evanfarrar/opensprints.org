<?php

//This is for the administration controls

class FTForm extends PostBackControl
	{
	var $ConfigurationManager;

    	function FTForm(&$Context)
    		{
        	$this->Name = 'FTForm';
        	$this->ValidActions = array('FeedThis','ProcessFeedThis');
		$this->Constructor($Context);

        	if 	($this->IsPostBack)
        		{
            		$SettingsFile = $this->Context->Configuration['APPLICATION_PATH'].'conf/settings.php';
            		$this->ConfigurationManager = $this->Context->ObjectFactory->NewContextObject($this->Context, 'ConfigurationManager');
            		
			if 	($this->PostBackAction == 'ProcessFeedThis')
            			{
                		$this->ConfigurationManager->GetSettingsFromForm($SettingsFile);
                		$this->ConfigurationManager->DefineSetting('FT_BLOG_FEED', ForceIncomingBool('FT_BLOG_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_BLOG_FEED_EVERY', ForceIncomingBool('FT_BLOG_FEED_EVERY', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_ALLDISCUSSIONS_FEED', ForceIncomingBool('FT_ALLDISCUSSIONS_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_ALLDISCUSSIONS_FEED_EVERY', ForceIncomingBool('FT_ALLDISCUSSIONS_FEED_EVERY', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_DISCUSSION_FEED', ForceIncomingBool('FT_DISCUSSION_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_CATEGORY_FEED', ForceIncomingBool('FT_CATEGORY_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_SEARCHRESULTS_FEED', ForceIncomingBool('FT_SEARCHRESULTS_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_USERBLOG_FEED', ForceIncomingBool('FT_USERBLOG_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_USERCOMMENTS_FEED', ForceIncomingBool('FT_USERCOMMENTS_FEED', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_PANEL_POSITION', ForceIncomingInt('FT_PANEL_POSITION', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_FEED_ITEMS', ForceIncomingInt('FT_FEED_ITEMS', 0), 0);
                		$this->ConfigurationManager->DefineSetting('FT_WORD_LIMIT', ForceIncomingInt('FT_WORD_LIMIT', 0), 0);
				$this->DelegateParameters['ConfigurationManager'] = &$this->ConfigurationManager;

		                // And save everything
				if 	($this->ConfigurationManager->SaveSettingsToFile($SettingsFile))
					{
					header('location: '.GetUrl($this->Context->Configuration, 'settings.php', '', '', '', '', 'PostBackAction=FeedThis&Success=1'));
					}
				else
					{
		    			$this->PostBackAction = 'FeedThis';
					}
            			}
        		}
    		}

    	function Render()
    		{
		if 	($this->IsPostBack)
			{
            		$this->PostBackParams->Clear();
	    		if 	($this->PostBackAction == 'FeedThis')
            			{
                		$this->PostBackParams->Set('PostBackAction', 'ProcessFeedThis');
                		$ThemeFilePath = $this->Context->Configuration['EXTENSIONS_PATH'].'FeedThis/theme/Theme.FeedThisForm.php';
				include($ThemeFilePath);
            			}
        		}
    		}
	}
?>
