MoodleMobile block
------------------

This block has been written for use with the Moodle VLE. If you are not running Moodle,
then this download is not for you.

Licence
-------

This code is licenced under the GPL v2 licence (see gpl.txt) and is copyright to PageOne. If you have
any further questions on the licencing of this block, please contact us on info@pageone.co.uk.


Requirements
------------

Moodle 1.8+
PHP 4 or 5
DOMXML and Curl extensions

The extensions should be included with most binary builds of PHP on windows systems, linux users might
need to install additional package(s). eg on CentOS 4 the php-domxml rpm will need to be installed, for
CentOS 5, use the php-xml package.

If you are installing PHP from source, make sure you use the --with-dom --with-zlib --with-curl build
options.

Installation or Upgrade
-----------------------

1) Copy the directory containing this readme file (or unzip the original download) into the moodle/blocks
directory of your moodle installation.
2) Open your web browser and go to /admin on your moodle server or click the 'notifications' link in the
site admin block, if necessary login using an account with admin privileges. The block should now
automatically install or update itself.
3) Assuming there were no error messages from step 2, go to modules>blocks from the site administration
menu. Find 'MoodleMobile' and click associated the settings link. If upgrading skip straight to step 8.
4) Choose a method for identifying the mobile phone number from the Moodle user database and fill in your
page one account details.
5) Ignore the MSISDN option for now.
6) Click Save changes.
7) Return to the MoodleMobile settings page
8) Choose a system default MSISDN (remember to save the change) and check that that SMS Callback service
is listed as registered
9) You can now add MoodleMobile blocks to courses in the normal way and tutors should be able to send
text messages.


Patches for enhanced functionality
----------------------------------

The patches directory in this block contains some modifications to the standard Moodle code which can
integrate some MoodleMobile funtions into other parts of the Moodle system. These patches add the following
functionality to Moodle :

1) A 'Contact with MoodleMobile' button on the user profile page
2) 'Send with MoodleMobile' option within the MoodleMessaging system

These patches have been created against Moodle version 1.8.6+ and 1.9.2+, however they should be applicable
to any Moodle 1.8.x or 1.9.x installation. The files in your moodle installation which need to be modified
are :

message/discussion.php
message/lib.php
message/send.php
user/view.php

The modifications to these files can be applied in one of two ways (we reccomend backing up your moodle
installation before doing this) :


Copy the content of the patches/moodle-186 or patches/moodle-192 directory into the root of your moodle
installation. This is the reccomended method for most normal Moodle installations.

--- or ---

Use the .diff files with the unix patch command to add the modifications to your existing server. If you
have made other patches to your moodle system which affect files listed above, then this should be the
prefered method, since copying the files we have supplied directly into your Moodle installation will
remove your existing changes. These patch files may also be used for other later versions of Moodle, though
we cannot guarantee that they will work correctly. In order to apply these patches with the unix patch command,
please use the following procedure :

1) Open a shell or command prompt on your server
2) Switch to the top level directory of your moodle installation
3) Execute the following commands :

For Moodle 1.9.x or later

   patch -p1 < blocks/pageone/patches/moodle-192-user.diff
   patch -p1 < blocks/pageone/patches/moodle-192-message.diff

For Moodle 1.8.x

   patch -p1 < blocks/pageone/patches/moodle-186-user.diff
   patch -p1 < blocks/pageone/patches/moodle-186-message.diff

4) Check the output of the commands to make sure that no parts of the patch failed. If there were any failures
you will need to manually edit the code to insert the patches at the correct point.

Users with windows servers can download a suitable patch command as part of the UnxUtils package available from
http://unxutils.sourceforge.net/, or as part of the cygwin environment available from http://www.cygwin.com/ .
