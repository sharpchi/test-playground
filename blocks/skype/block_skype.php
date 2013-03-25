<?php
// --------------------------------------------------------------
// Skype Presence Block v0.7 for Moodle 1.7
// --------------------------------------------------------------
// Created by Matt Crosslin for the University of Texas at 
// Arlington Center for Distance Education - December 2005.
// --------------------------------------------------------------
// This version currently works with any database that Moodle 
// supports and is written in English and Finnish .  To use in another  
// language, edit the lang file to any language you want and save
// it in the appropriate folder.  The buttons used in this are 
// from the SkypeWeb site and are in English.  If you want the 
// buttons in a different language, you may need to create new 
// buttons and change the code below to reflect the path to those
// new images.
// --------------------------------------------------------------
// Version History:
//
// 0.8 - Fixed problems with student Skype view
// 0.7 - Updated for Moodle 1.7 
// 0.6 - Added Finnish translation by Petri Niemi, Corrected bugs 
//		  discovered by Colin McQueen;
// 0.5 - Upgraded to work with SkypeWeb.  Also shows all teachers,
//        not just the primary.  Added a students page that will
//        show the Skype status of all students enrolled in the
//        also allows the block to be used on the front page of a
//        Moodle site (for admins).
// 0.4 - Made changes to image based on the new Jyve way
//        of doing things.  Also fixed some image links that 
//        were copied wrong.
// 0.3 - Repaired a glitch that was looking for the wrong teacher ID
// 0.2 - Updated the database search function to make this block
//        work within the Moodle structure.
//      - Removed language specific content to a lang file to
//        allow use with other languages.
// 0.1 - First version - only worked with MySQL and was written
//        in English.
// --------------------------------------------------------------
// The Skype contact text in the $this->content->text part below
// is based on code written by Tim Allen and Jamie Pratt.
// --------------------------------------------------------------

class block_skype extends block_base {
    function init() {
        $this->title = get_string('blocktitle', 'block_skype');
        $this->version = 2005121300;
    }

function get_content() {
        global $USER, $CFG, $COURSE;
    if ($this->content !== NULL) {
        return $this->content;
    }

    $this->content = new stdClass;
	$this->content->text = '';
	
// get content from language file
$amcurrently = get_string('currently', 'block_skype');
$callme = get_string('callme', 'block_skype');
$chat = get_string('chat', 'block_skype');
$addcontact = get_string('addcontact', 'block_skype');
$studentskype = get_string('studentskype', 'block_skype');
$skypefooter = get_string('skypefooter', 'block_skype');

// get the primary teacher's Skype id
$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);


if ($this->instance->pageid == '1')
{
$SQL = "SELECT userid FROM {$CFG->prefix}role_assignments WHERE roleid = '1'";
}
ELSE
{
$SQL = "SELECT userid FROM {$CFG->prefix}role_assignments WHERE (roleid = '3' OR roleid = '4') AND contextid = $context->id";
}

$teachers = get_records_sql($SQL);
if (!$teachers) {
}
else
{

foreach ($teachers as $teacher)
{
$profile = get_record('user','id',$teacher->userid);
$skypeID=$profile->skype;

// If there is no Skype ID, then don't print out the teacher
IF (!$skypeID) {
$this->content->text = $this->content->text.'';
}

// If there is a Skype ID, print the contact info from SkypeWeb
ELSE
{

// If you want to change the images used in this script, make the changes below
    $this->content->text = $this->content->text.'<div align="left">'.$profile->firstname.' '.$profile->lastname.': <img border="0" src="http://mystatus.skype.com/smallicon/'.$skypeID.'.png" alt="My Status" title="My Status" /></div><div class="logininfo"><a href="skype:'.$skypeID.'?call">'.$callme.'</a> | <a href="skype:'.$skypeID.'?chat">'.$chat.'</a> | <a href="skype:'.$skypeID.'?add">'.$addcontact.'</a></div><br />';
}
}
}
if ($this->instance->pageid == '1')
{
    $this->content->footer = '';
}

else {
    $this->content->text = $this->content->text.'<div align="left"><a href="../blocks/skype/students.php?id='.$this->instance->pageid.'">'.$studentskype.'</a></div><br />';
    $this->content->footer = $skypefooter;
}
    return $this->content;
}

}
?>