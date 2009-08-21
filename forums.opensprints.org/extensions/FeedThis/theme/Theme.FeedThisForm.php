<?php
echo '<div id="Form" class="Account GlobalsForm">';
if (ForceIncomingInt('Success', 0)) echo '<div id="Success">'.$this->Context->GetDefinition('ChangesSaved').'</div>';
echo '<fieldset>
	<legend>'.$this->Context->GetDefinition("FTSettings").'</legend>
	'.$this->Get_Warnings().'
	'.$this->Get_PostBackForm('frmFTForm').'
	<ul>
  		<li>
                <p><span><label for="txtFeedSettings">'.$this->Context->GetDefinition("FeedSettings").'</label></span></p>
            </li>	    

            <li>
                <p><span>'.GetDynamicCheckBox('FT_BLOG_FEED', 1, $this->ConfigurationManager->GetSetting('FT_BLOG_FEED'), '', $this->Context->GetDefinition('BlogFeedSetting')).'</span></p>
	   </li>
            <li>
                <p><span>'.GetDynamicCheckBox('FT_BLOG_FEED_EVERY', 1, $this->ConfigurationManager->GetSetting('FT_BLOG_FEED_EVERY'), '', $this->Context->GetDefinition('BlogFeedEverySetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_ALLDISCUSSIONS_FEED', 1, $this->ConfigurationManager->GetSetting('FT_ALLDISCUSSIONS_FEED'), '', $this->Context->GetDefinition('AllDiscussionsFeedSetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_ALLDISCUSSIONS_FEED_EVERY', 1, $this->ConfigurationManager->GetSetting('FT_ALLDISCUSSIONS_FEED_EVERY'), '', $this->Context->GetDefinition('AllDiscussionsFeedEverySetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_DISCUSSION_FEED', 1, $this->ConfigurationManager->GetSetting('FT_DISCUSSION_FEED'), '', $this->Context->GetDefinition('DiscussionFeedSetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_CATEGORY_FEED', 1, $this->ConfigurationManager->GetSetting('FT_CATEGORY_FEED'), '', $this->Context->GetDefinition('CategoryFeedSetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_SEARCHRESULTS_FEED', 1, $this->ConfigurationManager->GetSetting('FT_SEARCHRESULTS_FEED'), '', $this->Context->GetDefinition('SearchResultsFeedSetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_USERBLOG_FEED', 1, $this->ConfigurationManager->GetSetting('FT_USERBLOG_FEED'), '', $this->Context->GetDefinition('UserBlogFeedSetting')).'</span></p>
	   </li>
	   <li>
                <p><span>'.GetDynamicCheckBox('FT_USERCOMMENTS_FEED', 1, $this->ConfigurationManager->GetSetting('FT_USERCOMMENTS_FEED'), '', $this->Context->GetDefinition('UserCommentsFeedSetting')).'</span></p>
	   </li>
            <li>
                <label for="txtFeedPanelPosition">'.$this->Context->GetDefinition("FeedPanelPosition").'</label>
		<input type="text" name="FT_PANEL_POSITION" id="txtFeedPanelPosition"  value="'.$this->ConfigurationManager->GetSetting('FT_PANEL_POSITION').'" maxlength="3" class="SmallInput" />
            </li>
            <li>
                <label for="txtFeedItems">'.$this->Context->GetDefinition("FeedItems").'</label>
		<input type="text" name="FT_FEED_ITEMS" id="txtFeedItems"  value="'.$this->ConfigurationManager->GetSetting('FT_FEED_ITEMS').'" maxlength="3" class="SmallInput" />
            </li>
            <li>
                <label for="txtWordLimit">'.$this->Context->GetDefinition("WordLimit").'</label>
		<input type="text" name="FT_WORD_LIMIT" id="txtCharLimit"  value="'.$this->ConfigurationManager->GetSetting('FT_WORD_LIMIT').'" maxlength="3" class="SmallInput" />
            </li>

            <li>
	    </li>
	</ul>
	<div class="Submit">
            <input type="submit" name="btnSave" value="'.$this->Context->GetDefinition('Save').'" class="Button SubmitButton" />
	    <a href="'.GetUrl($this->Context->Configuration, $this->Context->SelfUrl).'" class="CancelButton">'.$this->Context->GetDefinition('Cancel').'</a>
	</div>
    </form>
    </fieldset>
</div>';

?>
