<?php
// Note: This file is included from the library/Vanilla/Vanilla.Control.Foot.php class.

	echo '</div>
    </div>
	<a id="pgbottom" name="pgbottom">&nbsp;</a>
	</div>
</div>
<div id="footer">
  <span class="button">
    <a href="http://twitter.com/opensprints">Follow us on twitter</a>
  </span>
  <span class="button">
    <a href="http://www.facebook.com/pages/Goldsprint/36441748980">Add us on facebook</a>
  </span>
  <span>
    &copy; 2009 OpenSprints L.L.C. Some rights reserved.
    <a href="http://creativecommons.org/licenses/by/3.0/us/" rel="license">
      <img alt="Creative Commons License" src="http://i.creativecommons.org/l/by/3.0/us/80x15.png" style="border-width:0" />
    </a>
  </span>
</div>';

$AllowDebugInfo = 0;
if ($this->Context->Session->User) {
	if ($this->Context->Session->User->Permission('PERMISSION_ALLOW_DEBUG_INFO')) $AllowDebugInfo = 1;
}
if ($this->Context->Mode == MODE_DEBUG && $AllowDebugInfo) {
	echo '<div class="DebugBar" id="DebugBar">
	<b>Debug Options</b> | Resize: <a href="javascript:window.resizeTo(800,600);">800x600</a>, <a href="javascript:window.resizeTo(1024, 768);">1024x768</a> | <a href="'
	."javascript:HideElement('DebugBar');"
	.'">Hide This</a>';
	echo $this->Context->SqlCollector->GetMessages();
	echo '</div>';
}
?>
