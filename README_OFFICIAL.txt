Cimy Counter

This plug-in let you to count how many times a file has been downloaded or how many times a page has been visited.
To add a download counter you need to add a new counter, copy/paste in your post/page the download link provided and add filename.

BEFORE writing to me read carefully ALL the documentation AND the FAQ. Missing this step means you are wasting my time!
Bugs or suggestions can be mailed at: cimmino.marco@gmail.com


REQUIREMENTS:
PHP >= 5.0.0
WORDPRESS >= 3.0.x
MYSQL >= 4.1.2

INSTALLATION:
- just copy whole cimy-counter subdir into your plug-in directory and activate it

UPDATE FROM A PREVIOUS VERSION:
- always deactivate the plug-in and reactivate after the update


Example of use:
<a href="http://www.yourblogurl.net/wp-content/plugins/Cimy_Counter/cc_redirect.php?cc=a_new_counter&fn=">Link</a>

Modify to:
<a href="http://www.yourblogurl.net/wp-content/plugins/Cimy_Counter/cc_redirect.php?cc=a_new_counter&fn=http://www.yourblogurl.net/wp-content/uploads/2009/01/my_app.zip">Download my application</a>

To add a page visit counter you just need to copy/paste the views link provided; page visit counter will be a total visit counter NOT unique visitor.

Example:
<img src="http://www.yourblogurl.net/wp-content/plugins/Cimy_Counter/cc_redirect.php?cc=a_new_counter" />

To display a counter in your posts/pages use one of the following code:
[cc_counter#counter_name] - This will display only the counter (int) of the given counter_name
[cc_since#counter_name]   - This will display counter beginning date of the given counter_name
[cc_string#counter_name]  - This will display formatted string of the given counter_name


FUNCTIONS USEFUL FOR YOUR THEMES OR TEMPLATES:

[Function cc_add_one_to_the_counter]
Add one to a counter

PARAMETERS: pass counter_name as first parameter
RETURNED VALUE: none

GENERIC:
cc_add_one_to_the_counter(<counter_name>);

EXAMPLE:
cc_add_one_to_the_counter("my_app");


[Function cc_get_counter]
Read state of a counter in a given moment

PARAMETERS: pass counter_name as first parameter
RETURNED VALUE: the function will return the integer value of the counter; in case of error will return -1

GENERIC:
cc_get_counter(<counter_name>);

EXAMPLE:
$myapp_downloads = cc_get_counter("my_app");


[Function cc_get_since]
Get the date of a counter

PARAMETERS: pass counter_name as first parameter; as second argument (optional) pass date's formatting string [default: blog's date format]
RETURNED VALUE: the function will return the string representing the date of the counter; in case of error will return false

GENERIC:
cc_get_since(<counter_name>, <date_format>);

EXAMPLE:
$myapp_date = cc_get_since("my_app", "j F Y");


[Function cc_get_display_str]
Get the string to be displayed of a counter

PARAMETERS: pass counter_name as first parameter; as second argument (optional) pass date's formatting string [default: blog's date format]
RETURNED VALUE: the function will return the display string of the counter; in case of error will return false

GENERIC:
cc_get_display_str(<counter_name>, <date_format>);

EXAMPLE:
$myapp_display_str = cc_get_display_str("my_app", "j F Y");


KNOWN ISSUES:
- Plug-in doesn't provide a widget (will come in a future release)
- If your 'wp-content' directory is not in the default location you have to edit cc_redirect.php and read WARNING comment inside


FAQ:
Q: Which is the maximum number of one counter?

A: For now maximum number that can be reached by a counter without issues is 2.147.483.647 do you need more?


Q: Why since v1.0.0 I cannot redirect anymore to external links?

A: Because it is considered a security issue, so now first you have to add to the white list these domains.


Q: Why counter increases by 2 every time instead of 1?

A: Because probably you added the same counter twice.


Q: When feature XYZ will be added?

A: I don't know, remember that this is a 100% free project so answer is "When I have time and/or when someone help me with a donation".


Q: Can I help with a donation?

A: Sure, visit the donation page or contact me via e-mail.


Q: Can I hack this plug-in and hope to see my code in the next release?

A: For sure, this is just happened and can happen again if you write useful new features and good code. Try to see how I maintain the code and try to do the same (or even better of course), I have rules on how I write it, don't want "spaghetti code", I'm Italian and I want spaghetti only on my plate.
There is no guarantee that your patch will hit an official upcoming release of the plug-in, but feel free to do a fork of this project and distribute it, this is GPL!


Q1: I have found a bug what can I do?
Q2: Something does not work as expected, why?

A: The first thing is to download the latest version of the plug-in and see if you still have the same issue.
If yes please write me an email or write a comment but give as more details as you can, like:
- Plug-in version
- WordPress version
- MYSQL version
- PHP version
- exact error that is returned (if any)

after describe what you did, what you expected and what instead the plug-in did :)
Then the MOST important thing is: DO NOT DISAPPEAR!
A lot of times I cannot reproduce the problem and I need more details, so if you don't check my answer then 80% of the times bug (if any) will NOT BE FIXED!


CHANGELOG:
v1.1.1 - 30/01/2012
- Fixed use of a deprecated function on plug-in activation, fixes relative warning (thanks to David Anderson)
- Fixed use of an obsolete method's parameter for the localization (thanks to Tom Broad)

v1.1.0 - 17/11/2011
- Added external white list URLs so is again possible to redirect outside current domain
- Fixed version mismatch was not possible to get rid of (again)

v1.0.0 - 11/10/2011
- Added support for WordPress 3.x
- Fixed some security issues
- Fixed css/js inclusions are now https aware and they are not conflicting with other plugins
- Code cleanup

v0.9.6 - 06/05/2010
- Fixed version mismatch was not possible to get rid of.

v0.9.5 - 05/05/2010
- Fixed JavaScript code execution sending base64 encoded data in the filename (thanks to MustLive for discovering it)
- Fixed full path disclosure accessing the script directly (thanks to MustLive for discovering it)
- Fixed full path disclosure sending a new line in the filename (thanks to MustLive for discovering it)

v0.9.4 - 24/02/2009
- Added versioning check
- Added counters order saving
- Fixed counters/links didn't work anymore when upgrading automatically a version downloaded from the blog
- Renamed plug-in directory due to WordPress Plugin Directory rules

v0.9.3 - 13/02/2009
- Fixed counters could not be deleted
- Fixed do not return/print garbage when trying to retrieve a non existent counter
- Fixed spaces at start/end of the counter's name were giving issues, now are removed
- Fixed tags used in the same line weren't correctly recognized
- Renamed README file to README_OFFICIAL.txt due to WordPress Plugin Directory rules

v0.9.2 - 01/01/2009
- Changed JS/CSS inclusions: now WP standard APIs are used
- Dropped use of "level_10" role check since is deprecated
- Updated README file

v0.9.1 - 26/12/2008
- Added possibility to set counters' date
- General cosmetic fixes
- Fixed table messed up when ordering columns
- Fixed plug-in url
- Updated Italian translation
- Updated README file

v0.9.0 - 25/12/2008
- Added possibility to add counters into posts/pages
- Added display string support
- Added since [date] support
- Added cc_get_display_str function
- Added cc_get_since function
- Added column order
- Added possibility to translate the plug-in
- Added Italian translation
- Added a proper README file with initial documentation
- Moved invert selection javascript to a stand-alone file
- Fixed all HTML code, now it's XHTML 1.0 Transitional compliant
- Fixed problems with special characters

v0.4.0 - 16/12/2008
- Added support for WordPress >=2.5

v0.3.0 - 16/10/2007
- First release
