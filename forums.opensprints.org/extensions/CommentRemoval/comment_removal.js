var g_com_id, g_is_discussion, g_baseurl;

function removecomment(baseurl, com_id, is_discussion)
{
	var dm = new DataManager();
	
	g_com_id = com_id;
	g_is_discussion = is_discussion;
	g_baseurl = baseurl;
	
	document.getElementById('RmComment_' + com_id).className = 'HideProgress';
	document.getElementById('RmComment_' + com_id).innerHTML = '&nbsp;';
	
	dm.RequestCompleteEvent = _removecomment;
	dm.RequestFailedEvent = _rmc_failure;
	dm.LoadData(baseurl + 'extensions/CommentRemoval/ajax.php?CommentID=' + com_id);
}

function _removecomment(request)
{
	//if(request.responseText == 'success')
	if(request.responseText.indexOf('itwasasuccess') != -1)
	{
		if(g_is_discussion) window.location = g_baseurl; //discussions
		else document.getElementById('Comment_' + g_com_id).style.display = 'none';
	}
	else
	{
		alert('Error: ' + request.responseText);
		_rmc_cleanup();
	}
}

function _rmc_failure(request)
{
	alert('Error ' + request.status + ': ' + request.statusText);
	_rmc_cleanup();
}

function _rmc_cleanup()
{
	var rmbutton = document.getElementById('RmComment_' + g_com_id);
	
	rmbutton.className = '';
	rmbutton.onclick = 'return false;';
	rmbutton.innerHTML = '(error)';
}