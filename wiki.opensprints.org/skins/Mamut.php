<?php
/**
 * MonoBook nouveau
 *
 * Translated from gwicke's previous TAL template version to remove
 * dependency on PHPTAL.
 *
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/** */
require_once('includes/SkinTemplate.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class SkinMamut extends SkinTemplate {
	/** Using monobook. */
	function initPage( &$out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'mamut';
		$this->stylename = 'mamut';
		$this->template  = 'MamutTemplate';
	}
}

/**
 * @todo document
 * @package MediaWiki
 * @subpackage Skins
 */
class MamutTemplate extends QuickTemplate {
	/**
	 * Template filter callback for MonoBook skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php $this->text('lang') ?>" lang="<?php $this->text('lang') ?>" dir="<?php $this->text('dir') ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php $this->text('mimetype') ?>; charset=<?php $this->text('charset') ?>" />
		<?php $this->html('headlinks') ?>
		<title><?php $this->text('pagetitle') ?></title>
		<style type="text/css" media="screen,projection">/*<![CDATA[*/ @import "<?php $this->text('stylepath') ?>/<?php $this->text('stylename') ?>/main.css?7"; /*]]>*/</style>
		<link rel="stylesheet" type="text/css" <?php if(empty($this->data['printable']) ) { ?>media="print"<?php } ?> href="<?php $this->text('stylepath') ?>/common/commonPrint.css" />
		<script type="<?php $this->text('jsmimetype') ?>">var skin = '<?php $this->text('skinname')?>';var stylepath = '<?php $this->text('stylepath')?>';</script>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('stylepath' ) ?>/common/wikibits.js"><!-- wikibits js --></script>
<?php	if($this->data['jsvarurl'  ]) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('jsvarurl'  ) ?>"><!-- site js --></script>
<?php	} ?>
<?php	if($this->data['pagecss'   ]) { ?>
		<style type="text/css"><?php $this->html('pagecss'   ) ?></style>
<?php	}
		if($this->data['usercss'   ]) { ?>
		<style type="text/css"><?php $this->html('usercss'   ) ?></style>
<?php	}
		if($this->data['userjs'    ]) { ?>
		<script type="<?php $this->text('jsmimetype') ?>" src="<?php $this->text('userjs' ) ?>"></script>
<?php	}
		if($this->data['userjsprev']) { ?>
		<script type="<?php $this->text('jsmimetype') ?>"><?php $this->html('userjsprev') ?></script>
<?php	}
		if($this->data['trackbackhtml']) print $this->data['trackbackhtml']; ?>
		<!-- Head Scripts -->
		<?php $this->html('headscripts') ?>
	</head>

<body <?php if($this->data['body_ondblclick']) { ?>ondblclick="<?php $this->text('body_ondblclick') ?>"<?php } ?>
<?php if($this->data['body_onload'    ]) { ?>onload="<?php     $this->text('body_onload')     ?>"<?php } ?>>

		<div id="main" class="show-all">
			<div id="columns">
				<div class="cols-wrapper">
					<div class="float-wrapper">
						<div id="col-a">
							<div class="main-content">
								<h4 class="in-title"><?php $this->text('title') ?></h4>
								<h3 id="siteSub"><?php $this->msg('tagline') ?></h3>
								<div id="contentSub"><?php $this->html('subtitle') ?></div>
								<div style="text-align: justify">
									<?php $this->html('bodytext') ?>
								</div>
							</div> <!---.main-content--->
						</div> <!---#col-a--->
						<div id="col-b" class="sidecol">
							<div class="box">
								<h4 class="in-title">Erlang по-русски</h4>
								<br />
								<a <?php
									?>href="<?php echo htmlspecialchars($this->data['nav_urls']['mainpage']['href'])?>" <?php
									?>title="<?php $this->msg('mainpage') ?>"><img src="<?php $this->text('logopath') ?>" alt="<?php $this->msg('mainpage') ?>" title="<?php $this->msg('mainpage') ?>" style="border: none" /></a>
							</div>
							<div class="box" id="p-personal">
								<h6><?php $this->msg('personaltools') ?></h6>

						<?php 			foreach($this->data['personal_urls'] as $key => $item) { ?>
										<span <?php
					if ($item['active']) { ?> class="active"<?php } ?>><a href="<?php
										echo htmlspecialchars($item['href']) ?>"<?php
										if(!empty($item['class'])) { ?> class="<?php
										echo htmlspecialchars($item['class']) ?>"<?php } ?>><?php
										echo htmlspecialchars($item['text']) ?></a></span><br />
						<?php			} ?>
							</div>
							<?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
							<div class='box' id='p-<?php echo htmlspecialchars($bar) ?>'>
								<h6><?php $out = wfMsg( $bar ); if (wfEmptyMsg($bar, $out)) echo $bar; else echo $out; ?></h6>
						<?php 			foreach($cont as $key => $val) { ?>
										<span id="<?php echo htmlspecialchars($val['id']) ?>"<?php
											if ( $val['active'] ) { ?> class="active" <?php }
										?>><a href="<?php echo htmlspecialchars($val['href']) ?>"><?php echo htmlspecialchars($val['text']) ?></a></span><br />
						<?php			} ?>
							</div>
							<?php } ?>

						</div>
					</div><!---.float-wrapper--->
					<div id="col-c" class="sidecol">
						<div class="box">
							<?php if($this->data['catlinks']) { ?><div id="catlinks"><?php       $this->html('catlinks') ?></div><?php } ?>
						</div>
						<br />
						<div class="box">
							<?php if($this->data['undelete']) { ?><div id="contentSub2"><?php     $this->html('undelete') ?></div><?php } ?>
							<?php if($this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk')  ?></div><?php } ?>
							<?php if($this->data['showjumplinks']) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>
						</div>
						<br />
						<div class="box	">
						<?php			foreach($this->data['content_actions'] as $key => $tab) { ?>
										<span id="ca-<?php echo htmlspecialchars($key) ?>"<?php
											if($tab['class']) { ?> class="<?php echo htmlspecialchars($tab['class']) ?>"<?php }
										?>><a href="<?php echo htmlspecialchars($tab['href']) ?>"><?php
										echo htmlspecialchars($tab['text']) ?></a></span>
						<?php			 } ?>

						</div>
						<div id="p-search" class="box">
							<h6><label for="searchInput"><?php $this->msg('search') ?></label></h6>
								<form action="<?php $this->text('searchaction') ?>" id="searchform"><div>
									<input id="searchInput" name="search" type="text" <?php
										if($this->haveMsg('accesskey-search')) {
											?>accesskey="<?php $this->msg('accesskey-search') ?>"<?php }
										if( isset( $this->data['search'] ) ) {
											?> value="<?php $this->text('search') ?>"<?php } ?> />
									<br /><br />
									<input type='submit' name="go" class="searchButton" id="searchGoButton"	value="<?php $this->msg('go') ?>" />&nbsp;
									<input type='submit' name="fulltext" class="searchButton" value="<?php $this->msg('search') ?>" />
								</div></form>
						</div>

						<div class="box" id="p-tb">
							<h6><?php $this->msg('toolbox') ?></h6>
					<?php
							if($this->data['notspecialpage']) { ?>
									<span id="t-whatlinkshere"><a href="<?php
									echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
									?>"><?php $this->msg('whatlinkshere') ?></a></span><br />
					<?php
								if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
									<span id="t-recentchangeslinked"><a href="<?php
									echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
									?>"><?php $this->msg('recentchangeslinked') ?></a></span><br />
					<?php 		}
							}
							if(isset($this->data['nav_urls']['trackbacklink'])) { ?>
								<span id="t-trackbacklink"><a href="<?php
									echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
									?>"><?php $this->msg('trackbacklink') ?></a></span><br />
					<?php 	}
							if($this->data['feeds']) { ?>
								<span id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
										?><span id="feed-<?php echo htmlspecialchars($key) ?>"><a href="<?php
										echo htmlspecialchars($feed['href']) ?>"><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
										<?php } ?></span><br /><?php
							}

							foreach( array('contributions', 'blockip', 'emailuser', 'upload', 'specialpages') as $special ) {

								if($this->data['nav_urls'][$special]) {
									?><span id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
									?>"><?php $this->msg($special) ?></a></span><br />
					<?php		}
							}

							if(!empty($this->data['nav_urls']['print']['href'])) { ?>
									<span id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
									?>"><?php $this->msg('printableversion') ?></a></span><br /><?php
							}

							if(!empty($this->data['nav_urls']['permalink']['href'])) { ?>
									<span id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
									?>"><?php $this->msg('permalink') ?></a></span><br /><?php
							} elseif ($this->data['nav_urls']['permalink']['href'] === '') { ?>
									<span id="t-ispermalink"><?php $this->msg('permalink') ?></span><br /><?php
							}

							wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
					?>
							<?php
									if( $this->data['language_urls'] ) { ?>
								<div id="p-lang" class="box">
									<h6><?php $this->msg('otherlanguages') ?></h6>
							<?php		foreach($this->data['language_urls'] as $langlink) { ?>
											<span class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
											?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></span><br />
							<?php		} ?>
								</div>
							<?php	} ?>
							</div>
						</div>
					</div>
					<div id="em" class="clear"></div>
				</div><!---.cols-wrapper--->
			</div><!---#columns--->
		</div><!---#main--->
		<div id="footer">
			<?php
					if($this->data['copyrightico']) { ?>
							<div id="f-copyrightico"><?php $this->html('copyrightico') ?></div>
			<?php	}

					// Generate additional footer links
			?>
			<?php
					$footerlinks = array(
						'lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright',
						'privacy', 'about', 'disclaimer', 'tagline',
					);
					foreach( $footerlinks as $aLink ) {
						if( $this->data[$aLink] ) {
			?>				<span id="<?php echo$aLink?>" style="padding: 5px;"><?php $this->html($aLink) ?></span>
			<?php 		}
						if($aLink == 'viewcount' || $aLink == 'numberofwatchingusers') echo '<br />';
					}
			?>

			<br />
			<br />
			&copy; 2006 - <?php echo date('Y', mktime())?> проект "Erlang по-русски"

			<br />
			<br />
			<?php
				if($this->data['poweredbyico']) { ?>
						<div id="f-poweredbyico"><?php $this->html('poweredbyico') ?></div>
			<?php }?>
		</div>



</body></html>
<?php
	wfRestoreWarnings();
	} // end of execute() method
} // end of class
?>
