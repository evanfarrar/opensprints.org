<?php
/**
 * See docs/skin.txt
 *
 * @todo document
 * @file
 * @ingroup Skins
 */

if( !defined( 'MEDIAWIKI' ) )
	die( -1 );

/** */
require_once( dirname(__FILE__) . '/MonoBook.php' );

/**
 * @todo document
 * @ingroup Skins
 */
class SkinOpensprints extends SkinTemplate {
	function initPage( OutputPage $out ) {
		SkinTemplate::initPage( $out );
		$this->skinname  = 'opensprints';
		$this->stylename = 'opensprints';
		$this->template  = 'MonoBookTemplate';
	}

	function setupSkinUserCss( OutputPage $out ){
		parent::setupSkinUserCss( $out );
		// Append to the default screen common & print styles...
		$out->addStyle( 'http://opensprints.org/stylesheets/screen.css', 'screen,handheld' );
		$out->addStyle( 'http://opensprints.org/stylesheets/application.css', 'screen,handheld' );
		$out->addStyle( 'opensprints/main.css', 'screen,handheld' );
		$out->addStyle( 'opensprints/IE50Fixes.css', 'screen,handheld', 'lt IE 5.5000' );
		$out->addStyle( 'opensprints/IE55Fixes.css', 'screen,handheld', 'IE 5.5000' );
		$out->addStyle( 'opensprints/IE60Fixes.css', 'screen,handheld', 'IE 6' );
	}
}


