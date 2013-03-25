<?PHP

// --------------------------------------------------------------
// Skype Presence Block v0.7 for Moodle 1.7
// --------------------------------------------------------------
// Created by Matt Crosslin for the University of Texas at 
// Arlington Center for Distance Education - December 2005.
// --------------------------------------------------------------

// This page show the Skype status of students

    require_once("../../config.php");

    $id          = optional_param('id', 0, PARAM_INT);       // Course Module ID

        if (! $course = get_record("course", "id", $id)) {
            error("Forum is misconfigured - don't know what course it's from");
        }

    global $USER, $CFG, $COURSE;

    require_course_login($COURSE, true, $cm);

    $strstudentskype = get_string("studentskype", "block_skype");
    $strstudentheader = get_string("studentheader", "block_skype");
    $callme = get_string("callme", "block_skype");
    $chat = get_string("chat", "block_skype");
	$addcontact = get_string("addcontact", "block_skype");
	$strskypefooter = get_string("skypefooter", "block_skype");
	$strnostudents = get_string("nostudents", "block_skype");

/// Print the page header

    $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a>";
    print_header("$course->shortname: $strstudentskype", "$course->fullname",
                 "$navigation -> $strstudentskype", 
                  "", "", true);

/// Print the main part of the page

echo '<br /><br /><table cellpadding="3" cellspacing="0" border="0">';

$context = get_context_instance(CONTEXT_COURSE, $course->id);

$SQL = "SELECT userid FROM {$CFG->prefix}role_assignments WHERE roleid = '5' AND contextid = $context->id";

$students = get_records_sql($SQL);

// If there are no student users then print a message
if (!$students) {
echo '<tr><td align=center">'.$strnostudents.' '.$COURSE->students.'</td></tr>';
}
else
{

echo '<tr><td class="header" colspan="3">'.$strstudentheader.'</td></tr>';
foreach ($students as $student)
{
$profile = get_record('user','id',$student->userid);
$skypeID=$profile->skype;

// If there is no Skype ID, then don't print out the student
IF (!$skypeID) {
}

// If there is a Skype ID, print the contact info from SkypeWeb
ELSE
{

// If you want to change the images used in this script, make the changes below
echo '<tr><td align="left">'.$profile->firstname.' '.$profile->lastname.': </td><td align="left"><img border="0" src="http://mystatus.skype.com/smallclassic/'.$skypeID.'.png" alt="My Status" title="My Status" /></td><td><div class="logininfo"><a href="skype:'.$skypeID.'?call">'.$callme.'</a> | <a href="skype:'.$skypeID.'?chat">'.$chat.'</a> | <a href="skype:'.$skypeID.'?add">'.$addcontact.'</a></div></td></tr>';
}
}
}

echo "</table>";

echo '<br /><br /><div class="footer">'.$strskypefooter.'</div><br />';

/// Finish the page
    print_footer($course);

?>