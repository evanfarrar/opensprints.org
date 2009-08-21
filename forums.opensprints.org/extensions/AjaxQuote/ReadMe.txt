===================================
EXTENSION INSTALLATION INSTRUCTIONS
===================================

In order for Vanilla to recognize an extension, it must be contained within it's
own directory within the extensions directory. So, once you have downloaded and
unzipped the extension files, you can then place the folder containing the
default.php file into your installation of Vanilla. The path to your extension's
default.php file should look like this:

/path/to/vanilla/extensions/this_extension_name/default.php

Once this is complete, you can enable the extension through the "Manage
Extensions" form on the settings tab in Vanilla.


==========================
ABOUT AJAXQUOTE EXTENSION
==========================

Adds quote option to posts. Works with any format type without complex JS parsing (retrives original data from DB on the fly).
Redirects to last(CommentBox) page if needed.

This extension is compatible with BetterBBCode,BBInsertBar,Add Comments;

Change ajaxqoute_format in ajaxquote.js to define format type (Html or BBCode);
Set $Configuration["AJAXQUOTE_LOGINREQUIRED"] in default.php to false if u are using Add Comments Extension;