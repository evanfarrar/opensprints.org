<?php

/*
 * Created on Sep 25, 2006
 *
 * API for MediaWiki 1.8+
 *
 * Copyright (C) 2006 Yuri Astrakhan <Firstname><Lastname>@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if( !defined('MEDIAWIKI') ) {
	// Eclipse helper - will be ignored in production
	require_once( 'ApiQueryBase.php' );
}

/**
 * A query action to return meta information about the wiki site.
 *
 * @ingroup API
 */
class ApiQuerySiteinfo extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent :: __construct( $query, $moduleName, 'si' );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		foreach( $params['prop'] as $p )
		{
			switch ( $p )
			{
				case 'general':
					$this->appendGeneralInfo( $p );
					break;
				case 'namespaces':
					$this->appendNamespaces( $p );
					break;
				case 'namespacealiases':
					$this->appendNamespaceAliases( $p );
					break;
				case 'specialpagealiases':
					$this->appendSpecialPageAliases( $p );
					break;
				case 'magicwords':
					$this->appendMagicWords( $p );
					break;
				case 'interwikimap':
					$filteriw = isset( $params['filteriw'] ) ? $params['filteriw'] : false;
					$this->appendInterwikiMap( $p, $filteriw );
					break;
				case 'dbrepllag':
					$this->appendDbReplLagInfo( $p, $params['showalldb'] );
					break;
				case 'statistics':
					$this->appendStatistics( $p );
					break;
				case 'usergroups':
					$this->appendUserGroups( $p );
					break;
				case 'extensions':
					$this->appendExtensions( $p );
					break;
				default :
					ApiBase :: dieDebug( __METHOD__, "Unknown prop=$p" );
			}
		}
	}

	protected function appendGeneralInfo( $property ) {
		global $wgSitename, $wgVersion, $wgCapitalLinks, $wgRightsCode, $wgRightsText, $wgContLang;
		global $wgLanguageCode, $IP, $wgEnableWriteAPI, $wgLang, $wgLocaltimezone, $wgLocalTZoffset;

		$data = array();
		$mainPage = Title :: newFromText(wfMsgForContent('mainpage'));
		$data['mainpage'] = $mainPage->getPrefixedText();
		$data['base'] = $mainPage->getFullUrl();
		$data['sitename'] = $wgSitename;
		$data['generator'] = "MediaWiki $wgVersion";

		$svn = SpecialVersion::getSvnRevision( $IP );
		if( $svn )
			$data['rev'] = $svn;

		$data['case'] = $wgCapitalLinks ? 'first-letter' : 'case-sensitive'; // 'case-insensitive' option is reserved for future

		if( isset( $wgRightsCode ) )
			$data['rightscode'] = $wgRightsCode;
		$data['rights'] = $wgRightsText;
		$data['lang'] = $wgLanguageCode;
		if( $wgContLang->isRTL() ) 
			$data['rtl'] = '';
		$data['fallback8bitEncoding'] = $wgLang->fallback8bitEncoding();
		
		if( wfReadOnly() )
			$data['readonly'] = '';
		if( $wgEnableWriteAPI )
			$data['writeapi'] = '';

		$tz = $wgLocaltimezone;
		$offset = $wgLocalTZoffset;
		if( is_null( $tz ) ) {
			$tz = 'UTC';
			$offset = 0;
		} elseif( is_null( $offset ) ) {
			$offset = 0;
		}
		$data['timezone'] = $tz;
		$data['timeoffset'] = $offset;

		$this->getResult()->addValue( 'query', $property, $data );
	}

	protected function appendNamespaces( $property ) {
		global $wgContLang;
		$data = array();
		foreach( $wgContLang->getFormattedNamespaces() as $ns => $title )
		{
			$data[$ns] = array(
				'id' => $ns
			);
			ApiResult :: setContent( $data[$ns], $title );
			$canonical = MWNamespace::getCanonicalName( $ns );
			
			if( MWNamespace::hasSubpages( $ns ) )
				$data[$ns]['subpages'] = '';
			
			if( $canonical ) 
				$data[$ns]['canonical'] = strtr($canonical, '_', ' ');
		}

		$this->getResult()->setIndexedTagName( $data, 'ns' );
		$this->getResult()->addValue( 'query', $property, $data );
	}

	protected function appendNamespaceAliases( $property ) {
		global $wgNamespaceAliases, $wgContLang;
		$wgContLang->load();
		$aliases = array_merge($wgNamespaceAliases, $wgContLang->namespaceAliases);
		$data = array();
		foreach( $aliases as $title => $ns ) {
			$item = array(
				'id' => $ns
			);
			ApiResult :: setContent( $item, strtr( $title, '_', ' ' ) );
			$data[] = $item;
		}

		$this->getResult()->setIndexedTagName( $data, 'ns' );
		$this->getResult()->addValue( 'query', $property, $data );
	}

	protected function appendSpecialPageAliases( $property ) {
		global $wgLang;
		$data = array();
		foreach( $wgLang->getSpecialPageAliases() as $specialpage => $aliases )
		{
			$arr = array( 'realname' => $specialpage, 'aliases' => $aliases );
			$this->getResult()->setIndexedTagName( $arr['aliases'], 'alias' );
			$data[] = $arr;
		}
		$this->getResult()->setIndexedTagName( $data, 'specialpage' );
		$this->getResult()->addValue( 'query', $property, $data );
	}
	
	protected function appendMagicWords( $property ) {
		global $wgContLang;
		$data = array();
		foreach($wgContLang->getMagicWords() as $magicword => $aliases)
		{
			$caseSensitive = array_shift($aliases);
			$arr = array('name' => $magicword, 'aliases' => $aliases);
			if($caseSensitive)
				$arr['case-sensitive'] = '';
			$this->getResult()->setIndexedTagName($arr['aliases'], 'alias');
			$data[] = $arr;
		}
		$this->getResult()->setIndexedTagName($data, 'magicword');
		$this->getResult()->addValue('query', $property, $data);
	}

	protected function appendInterwikiMap( $property, $filter ) {
		$this->resetQueryParams();
		$this->addTables( 'interwiki' );
		$this->addFields( array( 'iw_prefix', 'iw_local', 'iw_url' ) );

		if( $filter === 'local' )
			$this->addWhere( 'iw_local = 1' );
		elseif( $filter === '!local' )
			$this->addWhere( 'iw_local = 0' );
		elseif( $filter )
			ApiBase :: dieDebug( __METHOD__, "Unknown filter=$filter" );

		$this->addOption( 'ORDER BY', 'iw_prefix' );

		$db = $this->getDB();
		$res = $this->select( __METHOD__ );

		$data = array();
		$langNames = Language::getLanguageNames();
		while( $row = $db->fetchObject($res) )
		{
			$val = array();
			$val['prefix'] = $row->iw_prefix;
			if( $row->iw_local == '1' )
				$val['local'] = '';
//			$val['trans'] = intval($row->iw_trans);	// should this be exposed?
			if( isset( $langNames[$row->iw_prefix] ) )
				$val['language'] = $langNames[$row->iw_prefix];
			$val['url'] = $row->iw_url;

			$data[] = $val;
		}
		$db->freeResult( $res );

		$this->getResult()->setIndexedTagName( $data, 'iw' );
		$this->getResult()->addValue( 'query', $property, $data );
	}

	protected function appendDbReplLagInfo( $property, $includeAll ) {
		global $wgShowHostnames;
		$data = array();
		if( $includeAll ) {
			if ( !$wgShowHostnames )
				$this->dieUsage('Cannot view all servers info unless $wgShowHostnames is true', 'includeAllDenied');

			$lb = wfGetLB();
			$lags = $lb->getLagTimes();
			foreach( $lags as $i => $lag ) {
				$data[] = array(
					'host' => $lb->getServerName( $i ),
					'lag' => $lag
				);
			}
		} else {
			list( $host, $lag ) = wfGetLB()->getMaxLag();
			$data[] = array(
				'host' => $wgShowHostnames ? $host : '',
				'lag' => $lag
			);
		}

		$result = $this->getResult();
		$result->setIndexedTagName( $data, 'db' );
		$result->addValue( 'query', $property, $data );
	}

	protected function appendStatistics( $property ) {
		$data = array();
		$data['pages'] = intval( SiteStats::pages() );
		$data['articles'] = intval( SiteStats::articles() );
		$data['views'] = intval( SiteStats::views() );
		$data['edits'] = intval( SiteStats::edits() );
		$data['images'] = intval( SiteStats::images() );
		$data['users'] = intval( SiteStats::users() );
		$data['activeusers'] = intval( SiteStats::activeUsers() );
		$data['admins'] = intval( SiteStats::numberingroup('sysop') );
		$data['jobs'] = intval( SiteStats::jobs() );
		$this->getResult()->addValue( 'query', $property, $data );
	}

	protected function appendUserGroups( $property ) {
		global $wgGroupPermissions;
		$data = array();
		foreach( $wgGroupPermissions as $group => $permissions ) {
			$arr = array( 'name' => $group, 'rights' => array_keys( $permissions, true ) );
			$this->getResult()->setIndexedTagName( $arr['rights'], 'permission' );
			$data[] = $arr;
		}

		$this->getResult()->setIndexedTagName( $data, 'group' );
		$this->getResult()->addValue( 'query', $property, $data );
	}

	protected function appendExtensions( $property ) {
		global $wgExtensionCredits;
		$data = array();
		foreach ( $wgExtensionCredits as $type => $extensions ) {
			foreach ( $extensions as $ext ) {
				$ret = array();
				$ret['type'] = $type;
				if ( isset( $ext['name'] ) ) 
					$ret['name'] = $ext['name'];
				if ( isset( $ext['description'] ) ) 
					$ret['description'] = $ext['description'];
				if ( isset( $ext['descriptionmsg'] ) ) 
					$ret['descriptionmsg'] = $ext['descriptionmsg'];
				if ( isset( $ext['author'] ) ) {
					$ret['author'] = is_array( $ext['author'] ) ? 
						implode( ', ', $ext['author' ] ) : $ext['author'];
				}
				if ( isset( $ext['version'] ) ) {
						$ret['version'] = $ext['version'];
				} elseif ( isset( $ext['svn-revision'] ) && 
					preg_match( '/\$(?:Rev|LastChangedRevision|Revision): *(\d+)/', 
						$ext['svn-revision'], $m ) )  
				{
						$ret['version'] = 'r' . $m[1];
				}
				$data[] = $ret;
			}
		}

		$this->getResult()->setIndexedTagName( $data, 'ext' );
		$this->getResult()->addValue( 'query', $property, $data );
	}


	public function getAllowedParams() {
		return array(
			'prop' => array(
				ApiBase :: PARAM_DFLT => 'general',
				ApiBase :: PARAM_ISMULTI => true,
				ApiBase :: PARAM_TYPE => array(
					'general',
					'namespaces',
					'namespacealiases',
					'specialpagealiases',
					'magicwords',
					'interwikimap',
					'dbrepllag',
					'statistics',
					'usergroups',
					'extensions',
				)
			),
			'filteriw' => array(
				ApiBase :: PARAM_TYPE => array(
					'local',
					'!local',
				)
			),
			'showalldb' => false,
		);
	}

	public function getParamDescription() {
		return array(
			'prop' => array(
				'Which sysinfo properties to get:',
				' "general"      - Overall system information',
				' "namespaces"   - List of registered namespaces and their canonical names',
				' "namespacealiases" - List of registered namespace aliases',
				' "specialpagealiases" - List of special page aliases',
				' "magicwords"   - List of magic words and their aliases',
				' "statistics"   - Returns site statistics',
				' "interwikimap" - Returns interwiki map (optionally filtered)',
				' "dbrepllag"    - Returns database server with the highest replication lag',
				' "usergroups"   - Returns user groups and the associated permissions',
				' "extensions"   - Returns extensions installed on the wiki',
			),
			'filteriw' =>  'Return only local or only nonlocal entries of the interwiki map',
			'showalldb' => 'List all database servers, not just the one lagging the most',
		);
	}

	public function getDescription() {
		return 'Return general information about the site.';
	}

	protected function getExamples() {
		return array(
			'api.php?action=query&meta=siteinfo&siprop=general|namespaces|namespacealiases|statistics',
			'api.php?action=query&meta=siteinfo&siprop=interwikimap&sifilteriw=local',
			'api.php?action=query&meta=siteinfo&siprop=dbrepllag&sishowalldb',
			);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: ApiQuerySiteinfo.php 44862 2008-12-20 23:49:16Z catrope $';
	}
}
