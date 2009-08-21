<?php
/*
Extension Name: Html Formatter
Extension Url: http://lussumo.com/docs/
Description: Allows html to be used in strings, but breaks out all "script" related activities.
Version: 2.4
Author: SirNotAppearingOnThisForum
Author Url: N/A
*/


//SETTINGS, EDIT WHAT YOU WANT HERE

//if you want lots of control over what users can put, set this to 1
//array $Html_AllowedTags describes what tags and attributes are allowed
define('HTML_USE_WHITELIST',			0);

//index = node name
//value = array with allowed attributes; 0 = none; non-zero = all; array can be passed with allowed attributes
if(HTML_USE_WHITELIST)
	$Html_AllowedTags = array(
		'a' => array('href', 'title'), 
		'img' => array('src', 'align', 'alt'), 
		'font' => array('family', 'color', 'size'), 
		'b' => 0, 
		'i' => 0, 
		'u' => 0, 
		's' => 0, 
		'strong' => 0, 
		'code' => 0, 
		'li' => 0, 
		'ul' => 0, 
		'ol' => 0, 
		'center' => 0, 
		'abbr' => array('title'), 
		//etc...
	);

//self explanatory really; boolean value telling HtmlFormatter whether or not inline styling is allowed
//(this must also be set to 1 if you're using a whitelist and allow style attributes somewhere)
define('HTML_ALLOW_INLINE_STYLING',		1);

//should we convert newlines (ie. \n;\r;\r\n) to line-breaks (<br />) or leave them as they are?
define('HTML_CONVERT_NEWLINES',			1);

//are html comments allowed?
define('HTML_ALLOW_COMMENTS', 			1);

//this option tells the parser to make sure there are no stray opening or closing tags anywhere
//provides more protection against forgetful/poor markup and/or malicious users, but will take slightly longer
define('HTML_POLICE_TAGS',				1);

//allow youtube and google videos to be posted, tags are:
//<video type="google">docid</video> (google video) -or-
//<video type="youtube">video id</video> (youtube) -or-
//<video type="myspace">video id</video> (myspace)
define('HTML_VIDEO_TAG',				0);

//index = type (what you specify in the tag)
//value = replacement html; VIDEO_ID = video id (duh) (can only contain numbers, letters, - and _)
if(HTML_VIDEO_TAG)
	$Html_VideoLinks = array(
		'google' => '<embed style="width: 400px; height: 326px;" id="VideoPlayback" '.
					'type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=VIDEO_ID"></embed>', 
		
		'youtube' => '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/VIDEO_ID"></param>'.
					'<embed src="http://www.youtube.com/v/VIDEO_ID" type="application/x-shockwave-flash" width="425" height="350"></embed>'.
					'</object>', 
		
		'myspace' => '<embed src="http://lads.myspace.com/videos/vplayer.swf" flashvars="m=VIDEO_ID&type=video" '.
					'type="application/x-shockwave-flash" width="430" height="346"></embed>'
	);

//which tags should simply be removed (be warned, most of these are here because they are not safe 
//enough to allow/clean, remove at your own risk)
$Html_DisallowedTags = array('link', 'iframe', 'frame', 'frameset', 'object', 'param', 'embed', 'style', 
	'applet', 'meta', 'layer', 'import', 'xml', 'script', 'body', 'html', 'head', 'title', 'ilayer');

//don't parse anything in these tags
//(as this is geared towards vanilla, pre is not included by default)
$Html_Literals = array('code', 'samp');

//which url protocols are allowed
$Html_AllowedProtocols = array('http', 'https', 'ftp', 'news', 'nntp', 'feed', 'gopher', 'mailto');

//protocol to replace invalid protocols with
$Html_DefaultProtocol = 'http://';

//END SETTINGS


//unclosed or orphaned tags
$Html_TagArray = array();

//entites and their equivelents
$Html_EntityTable = array_flip(get_html_translation_table(HTML_ENTITIES));
unset($Html_EntityTable['&amp;'], $Html_EntityTable['&lt;'], $Html_EntityTable['&gt;']);

class HtmlFormatter extends StringFormatter
{
	var $AllowedProtocols;
	var $DefaultProtocol;
	var $FreestandingLoose = array('li', 'option', 'dt', 'dd', 'td', 'tfoot', 'th', 'tbody', 'thead', 'tr', 'colgroup');
	var $Freestanding = array('area', 'base', 'basefont', 'br', 'col', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param');
	var $TagArray;
	
	function HtmlFormatter()
	{
		$this->AllowedProtocols = &$GLOBALS['Html_AllowedProtocols'];
		$this->DefaultProtocol = &$GLOBALS['Html_DefaultProtocol'];
		$this->TagArray = &$GLOBALS['Html_TagArray'];
	}
	
	function Execute($String)
	{
		$this->TagArray = array('normal' => array(), 'extraclosing' => array());
		$String = str_replace(chr(0), ' ', $String);
		
		//comments
		$String = preg_replace_callback(
			'/<!--(.*?)(-->|$)/s', 
			create_function(
				'$m', 
				HTML_ALLOW_COMMENTS ? 
					'if($m[2]==\'-->\')return \'<!--\'.str_replace(\'--\',\' - - \',htmlspecialchars($m[1])).\'-->\';else return \'\';'
				:
					'return \'\';'
			), 
			$String
		);
		
		//handle literals
		$String = preg_replace_callback(
			'/<('.implode('|', $GLOBALS['Html_Literals']).')((?>[^>A-Za-z\d][^>]*)|)>(.+?)<\/\1((?>[^>A-Za-z\d][^>]*)|)>/si', 
			create_function(
				'$m', 
				'return \'<\'.$m[1].$m[2].\'>\'.htmlspecialchars($m[3]).\'</\'.$m[1].\'>\';'
			), 
			$String
		);
		
		//clean up any stray '<'
		$String = preg_replace(
			'/<(?![A-Za-z\/'.(HTML_ALLOW_COMMENTS?'!':'').'])/i', 
			'&lt;', 
			$String
		);
		
		//go through and check attributes of each tag
		$sReturn = preg_replace_callback('/<((?>[^>]+))(>|$)/', array($this, 'RemoveEvilAttribs'), $String);
		
		if(HTML_POLICE_TAGS)
		{
			$this->TagArray['normal'] = array_reverse($this->TagArray['normal'], 1);
			while(list($i, $v) = each($this->TagArray['normal']))
			{
				if(in_array($i, $this->FreestandingLoose)) continue;
				if($v > 0) $sReturn .= str_repeat('</'.$i.'>', $v);
			}
			
			//now we manage orphaned closing tags
			while(list($i, $v) = each($this->TagArray['extraclosing']))
			{
				if($v > 0) $sReturn = str_repeat('<'.$i.'>', $v) . $sReturn;
			}
		}
		
		if(HTML_VIDEO_TAG) 
			$sReturn = preg_replace_callback(
				'/<video(?>\s+)type=(["\'`])((?>\w+))\1(?>\s*)>((?>[A-Za-z\-_\d]+))<\/video>/is', 
				array($this, 'VideoLink'), 
				$sReturn
			);
		
		if(HTML_CONVERT_NEWLINES) 
			$sReturn = str_replace(
				array("\r\n", "\r", "\n"), 
				'<br />', 
				$sReturn
			);
		
		//return '<samp>'.nl2br(htmlspecialchars($sReturn)).'</samp>';
		return $sReturn;
	}
	
	function VideoLink($Matches)
	{
		$Type = strtolower(trim($Matches[2]));
		$ID = $Matches[3];
		
		if(isset($GLOBALS['Html_VideoLinks'][$Type])) return str_replace('VIDEO_ID', $ID, $GLOBALS['Html_VideoLinks'][$Type]);
		else return $Matches[0];
	}
	
	function RemoveQuoteSlashes($String)
	{
		return str_replace("\\\"", '"', $String);
	}
	
	function DecodeEntities($String)
	{
		$String = preg_replace_callback(
			'/&#(x([\dA-Fa-f]+)|(\d+));?/i', 
			create_function(
				'$m', 
				'$t=(($m[1][0]==\'x\'||$m[1][0]==\'X\')?hexdec($m[2]):$m[1]);return $t>0?chr($t):\'\';'
			), 
			$String
		);
		$String = strtr($String, $GLOBALS['Html_EntityTable']);
		
		return $String;
	}
	
	function ItWillBeClosed($i)
	{
		//these will be closed automagically by the browser
		return (
			(@$this->TagArray['normal']['ul'] || @$this->TagArray['normal']['ol'] || @$this->TagArray['normal']['menu'] || 
				@$this->TagArray['normal']['dir']) && $i == 'li') || 
			(@$this->TagArray['normal']['select'] && $i == 'option') || 
			(@$this->TagArray['normal']['dl'] && ($i == 'dt' || $i == 'dd')) || 
			(@$this->TagArray['normal']['table'] && in_array($i, array('td', 'tfoot', 'th', 'tbody', 'thead', 'tr', 'colgroup'))
		);
	}
	
	function EscapeQuotes($Str)
	{
		//we replace quotes with these in attributes so a style= in a href (eg) won't be interpreted as a style attribute
		$sReturn = str_replace(
			array('"', '\'', '`'), 
			array('&quot;', '&#39;', '&#96;'), 
			$Str
		);
		
		return $sReturn;
	}
	
	function ParseProtocol($Url, $AllowPageJumps = 0)
	{
		$Url = trim($Url);
		if($AllowPageJumps && $Url[0] == '#') return $Url;
		
		$sReturn = $GLOBALS['Html_DefaultProtocol'];
		$UrlParts = explode(':', $Url);
		
		if(count($UrlParts) > 1)
		{
			if(in_array(strtolower($UrlParts[0]), $GLOBALS['Html_AllowedProtocols'])) $sReturn = $Url;
			else $sReturn .= implode('', array_slice($UrlParts, 1));
		}
		else $sReturn .= $Url;
		
		return $sReturn;
	}
	
	function HandleAttribute($Node, $Matches)
	{
		if(isset($Matches[6])) $Value = $Matches[6];
		else $Value = $Matches[5];
		
		$Quot = $Matches[4];
		$Name = strtolower($Matches[1]);
		
		//if whitelist
		if(HTML_USE_WHITELIST)
		{
			if(is_array($GLOBALS['Html_AllowedTags'][$Node])) {if(!in_array($Name, $GLOBALS['Html_AllowedTags'][$Node])) return '';}
			else {if(!$GLOBALS['Html_AllowedTags'][$Node]) return '';}
		}
		
		//url of some kind
		if(in_array($Name, array('href', 'src', 'background', 'url', 'dynsrc', 'lowsrc')) && !empty($Value))
			$Value = HtmlFormatter::ParseProtocol(HtmlFormatter::DecodeEntities($Value), $Name == 'href');
		else if($Name == 'style') //styling
		{
			if(HTML_ALLOW_INLINE_STYLING) $Value = HtmlFormatter::ParseCSS($Value);
			else return '';
		}
		else if(substr($Name, 0, 2) == 'on') //event
			return '';
		
		$Value = str_replace('&{', '&amp;{', $Value);
		if(empty($Quot)) $Quot = '"';
		
		return $Name.'='.$Quot.HtmlFormatter::EscapeQuotes($Value).$Quot.' ';
	}
	
	function RemoveEvilAttribs($Matches)
	{
		if(HTML_ALLOW_COMMENTS && substr($Matches[1], 0, 3) == '!--') return $Matches[0];
		
		if($Matches[1][0] == '/')
		{
			$Open = 0;
			
			$Matches[1] = substr($Matches[1], 1);
		}
		else $Open = 1;
		
		$Node = preg_split('/(?>[^A-Za-z\d]+)/', $Matches[1], 2);
		if(empty($Node)) return '';
		
		$Node = strtolower($Node[0]);
		if(empty($Node)) return '';
		
		//check if allowed
		if(HTML_USE_WHITELIST && !isset($GLOBALS['Html_AllowedTags'][$Node])) return '';
		
		$Free = in_array($Node, $this->Freestanding);
		
		if(in_array($Node, $GLOBALS['Html_DisallowedTags'])) return '';
		if($Open)
		{
			$c = preg_match_all(
				'/(?<=[\s"\'`\/])((?>[\w\-]+))(?>[^A-Za-z\d\'"=]*)=(?>\s*)(((["\'`])(.*?)\4)|((?>[^\s]+)))(?>\s*)/si', 
				$Matches[1], 
				$Attribs, 
				PREG_SET_ORDER
			);
			
			$Inside = '';
			for($i = 0; $i < $c; $i++)
				$Inside .= HtmlFormatter::HandleAttribute($Node, $Attribs[$i]);
			
			$sReturn = $Node.' '.$Inside;
			if($Free) $sReturn .= '/';
		}
		else
		{
			if($Free) return '';
			
			$sReturn = '/'.$Node;
		}
		
		if(HTML_POLICE_TAGS && !$Free)
		{
			$t = $this->ItWillBeClosed($Node);
			if(!$t && in_array($Node, $this->FreestandingLoose)) $sReturn = '';
			else
			{
				//set default array value if not already set
				if(!isset($this->TagArray['normal'][$Node])) $this->TagArray['normal'][$Node] = 0;
				
				//check if we're one too many open tags
				if(!$Open)
				{
					//we seem to have an orphaned closing tag
					if(!$this->TagArray['normal'][$Node])
					{
						if($t) $sReturn = '';
						else
						{
							if(!isset($this->TagArray['extraclosing'][$Node])) $this->TagArray['extraclosing'][$Node] = 0;
							$this->TagArray['extraclosing'][$Node]++;
						}
					}
					else $this->TagArray['normal'][$Node]--;
				}
				else $this->TagArray['normal'][$Node]++;
			}
		}
		
		if($sReturn) return '<'.$sReturn.'>';
		else return '';
	}
	
	function ParseCSS($String)
	{
		do
		{
			$sReturn = $String;
			$String = str_replace('\\', '', HtmlFormatter::DecodeEntities($String));
			$String = preg_replace(array('%/\*(.*?)\*/%si', '/expression\(/i'), array('', '('), $String);
			$String = preg_replace_callback(
				'/((behavior|-moz-binding)(?>\s*):(?>\s*))?url\((?>\s*)(((["\'`])(.*?)\5)|((?>[^)]+)))/si', 
				create_function(
					'$m', 
					'if(isset($m[7]))$t=7;else $t=6;'.
					'return (\'url(\'.$m[5].HtmlFormatter::EscapeQuotes(HtmlFormatter::ParseProtocol($m[$t])).$m[5]);'
				), 
				$String
			);
		}
		while($sReturn != $String);
		
		return $sReturn;
	}
	
	function Parse($String, $Object, $FormatPurpose)
	{
		if($FormatPurpose == FORMAT_STRING_FOR_DISPLAY) $sReturn = $this->Execute($String);
		else $sReturn = $String;
		
		return $this->ParseChildren($sReturn, $Object, $FormatPurpose);
	}
}

$HtmlFormatter = new HtmlFormatter();

$HtmlFormatter = $Context->ObjectFactory->NewContextObject($Context, 'HtmlFormatter');
$Context->StringManipulator->AddManipulator('Html', $HtmlFormatter);

?>