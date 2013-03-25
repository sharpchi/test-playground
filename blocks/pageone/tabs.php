<?php 
/**
 * Tabs for pageone
 *
 * @author Mark Nielsen
 * @package pageone
 **/

    if (empty($course)) {
        error('Programmer error: cannot call this script without $course set');
    }
    if (!isset($instanceid)) {
        $instanceid = 0;
    }
    if (empty($currenttab)) {
        $currenttab = 'compose';
    }

    $rows = array();
    $row = array();

    $row[] = new tabobject('compose', "$CFG->wwwroot/blocks/pageone/email.php?id=$course->id&amp;instanceid=$instanceid", get_string('compose', 'block_pageone'));
    $row[] = new tabobject('history', "$CFG->wwwroot/blocks/pageone/emaillog.php?id=$course->id&amp;instanceid=$instanceid", get_string('history', 'block_pageone'));
    $row[] = new tabobject('inhistory', "$CFG->wwwroot/blocks/pageone/emaillog.php?in=1&amp;id=$course->id&amp;instanceid=$instanceid", get_string('inhistory', 'block_pageone'));
    $rows[] = $row;

    print_tabs($rows, $currenttab);
?>