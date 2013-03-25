<?php
/**
* If for some reason you have an unusual network setup where the callback URL used by the PageOne server
* dosn't match up with the normal URLs and hostnames used for normal access to your Moodle server, this
* file should be edited to include the correct URL of the callback server
**/

$CALLBACK_URL=$CFG->wwwroot."/blocks/pageone/callback.php";

?>