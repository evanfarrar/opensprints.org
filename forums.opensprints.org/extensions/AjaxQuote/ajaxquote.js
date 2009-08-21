// customize
var ajaxqoute_format = 'Html';  // 'Html' or 'BBCode' 
var ajaxqoute_errormessage = "Some Error occured while retriving qoute!\nIt's either server problem or comment doesn't exist anymore.";
//

var g_com_author, q_qoute, g_aq;

function ajaxquote(baseurl, com_id, com_author)
{
	if(!document.getElementById("CommentBox")) return true;
	
	g_com_author=com_author;
	var dm = new DataManager();
	
	if((g_aq=document.getElementById('AjaxQuote_' + com_id))){
	g_aq.className = 'HideProgress';	
	q_qoute = g_aq.innerHTML;
	g_aq.innerHTML = '&nbsp;';
	}
	
	if(document.getElementById("BBBar") && ajaxqoute_format == 'BBCode') document.getElementById("BBBar").style.display = '';
	if(document.getElementById("Radio_"+ajaxqoute_format)) document.getElementById("Radio_"+ajaxqoute_format).checked = true;
	
	dm.RequestCompleteEvent = _ajaxquote;
	dm.RequestFailedEvent = _ajaxquote_failure;
	dm.LoadData(baseurl + 'extensions/AjaxQuote/ajax.php?CommentID=' + com_id);

return false;
}

function _ajaxquote(request)
{
	var input = document.getElementById("CommentBox");
	
	if(g_aq){ 
  g_aq.className = '';	
	g_aq.innerHTML = q_qoute;
	}
	if(!request.responseText || request.responseText=='ERROR'){ _ajaxquote_failure(); return false;}
	
	if(ajaxqoute_format == 'BBCode')
	ajaxquote_insert('[quote][cite] '+g_com_author+':[/cite]'+ request.responseText+'[/quote]','');
	else
	ajaxquote_insert('<blockquote><cite> '+g_com_author+':</cite>'+ request.responseText+'</blockquote>','');	

return false;
}


function _ajaxquote_failure(){
document.getElementById("CommentBox").focus(); alert(ajaxqoute_errormessage);
return false;
}


function ajaxquote_insert(aTag, eTag) {
  var input = document.getElementById("CommentBox");
  input.focus();
  /* für Internet Explorer */
  if(typeof document.selection != 'undefined') {
    /* Einfügen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = aTag + insText + eTag;
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);      
    }
    range.select();
  }
  /* für neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length == 0) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* für die übrigen Browser */
  else
  {
    /* Abfrage der Einfügeposition */
    var pos;
    var re = new RegExp('^[0-9]{0,3}$');
    while(!re.test(pos)) {
      pos = prompt("Insert Code Position (0.." + input.value.length + "):", "0");
    }
    if(pos > input.value.length) {
      pos = input.value.length;
    }
    /* Einfügen des Formatierungscodes */
    var insText = prompt("Insert Code value:","");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
  }
}


