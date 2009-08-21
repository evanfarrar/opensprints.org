===================================
EXTENSION INSTALLATION INSTRUCTIONS
===================================

In order for Vanilla to recognize an extension, it must be contained within it's
own directory within the extensions directory. So, once you have downloaded and
unzipped the extension files, you can then place the folder containing the
default.php file into your installation of Vanilla. The path to your extension's
default.php file should look like this:

/path/to/vanilla/extensions/Gravatar/default.php

The extension won't work with Vanilla inferior to version 1.1.4 (inclusive).
If you are not running Vanilla 1.1.4, you should upgrade first.
If you are running Vanilla 1.1.4 you can install Gravatar/Vanilla-upgrade-1.1.4-to-1.1.4-a.zip

Once this is complete, you can enable the extension through the "Manage
Extensions" form on the settings tab in Vanilla.



SETTINGS:
---------

 * GRAVATAR_RATING
   Determines the highest rating (inclusive) of the returned Icon:
		$Configuration['GRAVATAR_RATING'] = 'PG';
   Valid values:	G | PG | R | X
   Default:		PG

 * GRAVATAR_SIZE
   Size of the icon to request:
		$Configuration['GRAVATAR_SIZE']	= '32';
   Valid values:	1 to 80 inclusive.
   Default:		32
   NB: The default vanilla style centers and crops the image to 32px.
	   This setting won't change that.

 * GRAVATAR_DEFAULT_ICON
   Default icon if the user email is not associated
   to an icon and the user didn't submit any default icon:
		$Configuration['GRAVATAR_DEFAULT_ICON']	= 'http://example.com/image.gif';
   Valid values:	Absolute Url | identicons | monsterids | wavatars
   Default:		empty.