



<form method="post" name="update_comment_form" style="margin-top: .5em; font-size: 1em; width: 100%;">
<input type="hidden" name="update_status_id" value="' + statusId + '"/>
<input type="hidden" name="issue_id" value="' + issueId + '" />
<div style="float:left;"><label class="lib-inline" for="id="update-when">Time:</label>&nbsp;<input type="text" id="update-when" name="update-when" class="lib-inline" value="' + oldTime + '" /></div><div style="margin-left:2em;float:left"><label for="update_status_type_id">Issue Type:</label>' + selectMenu + '</div><br /><textarea name="update_status_text" style="height:5em;width:94%;margin: 1em 0;">' + statusText + '</textarea><br /><button class="btn btn-default" style="color: red !important;" id="status_delete" name="status_delete" type="submit" value="1">Delete Entry</button> <input type="submit" name="update_status" class="btn btn-primary" style="display:inline-block; float: right;margin-right:4%;" /></form>');