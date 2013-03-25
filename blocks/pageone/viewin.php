<?php
/**
 * email.php - Used by pageone for sending emails to users enrolled in a specific course.
 *      Calls email.hmtl at the end.
 *
 * @author Mark Nielsen. Modified for PageOne by Tim Williams
 * @package pageone
 **/
    
    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/message/lib.php');
    require_once('pageonelib.php');

    $id = required_param('id', PARAM_INT);  // course ID
    $instanceid = optional_param('instanceid', 0, PARAM_INT);
    $inid = required_param('inid', PARAM_INT);
    $show = optional_param('show', "own", PARAM_ALPHA);
    $instance = new stdClass;

    if (!$course = get_record('course', 'id', $id)) {
        error('Course ID was incorrect');
    }

    require_login($course->id);
    $context = get_context_instance(CONTEXT_COURSE, $course->id);

    if ($instanceid) {
        $instance = get_record('block_instance', 'id', $instanceid);
    } else {
        if ($pageoneblock = get_record('block', 'name', 'pageone')) {
            $instance = get_record('block_instance', 'blockid', $pageoneblock->id, 'pageid', $course->id);
        }
    }

    $strpageone = "<a href=\"emaillog.php?in=1&amp;id=$id&amp;instanceid=$instanceid&amp;show=$show\">"
                  .get_string('blockname', 'block_pageone')."</a> -> ".get_string("view_message", "block_pageone");
    /// Header setup
    if ($course->category) {
        $navigation = "<a href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    print_header($course->fullname.": ".get_string('blockname', 'block_pageone'), $course->fullname, "$navigation $strpageone", '', '', true, '');

    if (!has_capability('block/pageone:cansend', $context))
    {
        print_string("permission", "block_pageone");
        print_footer($course);
        die();
    }

    $message=get_record("block_pageone_inlog", "id", $inid);
    $userfrom=get_record("user", "id", $message->mailfrom);
    ?>
    <br /><br />
    <table>
     <tr>
      <td><b><?php print_string("from", "block_pageone"); ?></b></td>
      <td><b>:</b></td>
      <td><?php echo "<a href='".$CFG->wwwroot."/user/view.php?id=".$message->mailfrom."' ".
                               "title='".$userfrom->username."'>".$userfrom->firstname." ".$userfrom->lastname."</a>";?></td>
     </tr>
     <tr>
      <td><b><?php print_string("date", "block_pageone"); ?></b></td>
      <td><b>:</b></td>
      <td><?php echo userdate($message->timesent, "%H:%M, %d %b %y");?></td>
     </tr>
    </table>

    <p class="generalbox"><?php echo $message->message;?></p>
    <?php

    print_footer($course);

?>