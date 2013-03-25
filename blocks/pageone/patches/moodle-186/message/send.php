<?php // $Id: send.php,v 1.18.2.4 2008/05/01 05:21:11 scyrma Exp $

require('../config.php');
require('lib.php');

require_login();

if (isguest()) {
    redirect($CFG->wwwroot);
}

if (empty($CFG->messaging)) {
    error("Messaging is disabled on this site");
}

if (has_capability('moodle/site:sendmessage', get_context_instance(CONTEXT_SYSTEM))) {


/// Don't use print_header, for more speed
    $stylesheetshtml = '';
    foreach ($CFG->stylesheets as $stylesheet) {
        $stylesheetshtml .= '<link rel="stylesheet" type="text/css" href="'.$stylesheet.'" />';
    }

/// Select direction
    if ( get_string('thisdirection') == 'rtl' ) {
        $direction = ' dir="rtl"';
    } else {
        $direction = ' dir="ltr"';
    }

    @header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
    echo "<html $direction xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n";
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
    echo $stylesheetshtml;
    include($CFG->javascript);

/// Script parameters
    $userid   = required_param('id', PARAM_INT);
    $message  = optional_param('message', '', PARAM_CLEANHTML);
    $format   = optional_param('format', FORMAT_MOODLE, PARAM_INT);
    $send_by_mm = optional_param('send_by_mm', 0, PARAM_INT);

/// Check the user we are talking to is valid
    if (! $user = get_record('user', 'id', $userid)) {
        error("User ID was incorrect");
    }

/// Check that the user is not blocking us!!
    if ($contact = get_record('message_contacts', 'userid', $user->id, 'contactid', $USER->id)) {
        if ($contact->blocked and !has_capability('moodle/site:readallmessages', get_context_instance(CONTEXT_SYSTEM, SITEID))) {
            print_heading(get_string('userisblockingyou', 'message'));
            exit;
        }
    }
    $userpreferences = get_user_preferences(NULL, NULL, $user->id);

    if (!empty($userpreferences['message_blocknoncontacts'])) {  // User is blocking non-contacts
        if (empty($contact)) {   // We are not a contact!
            print_heading(get_string('userisblockingyounoncontact', 'message'));
            exit;
        }
    }


    if ($message!='' and confirm_sesskey()) {   /// Current user has just sent a message

        $send_text=false;
        if (pageone_find_course($user->id, $USER->id)!=null && $send_by_mm==true)
            $send_text=true;

    /// Save it to the database...
        $messageid = message_post_message($USER, $user, addslashes($message), $format, 'direct', $send_text);

    /// Format the message as HTML
        $options = NULL;
        $options->para = false;
        $options->newlines = true;
        $message = format_text($message, $format, $options);

        $time = userdate(time(), get_string('strftimedaytime'));
        $message = '<div class="message me"><span class="author">'.fullname($USER).'</span> '.
                   '<span class="time">['.$time.']</span>: '.
                   '<span class="content">'.$message.'</span></div>';
        $message = addslashes_js($message);  // So Javascript can write it

    /// Then write it to our own message screen immediately
        echo "\n<script type=\"text/javascript\">\n<!--\n";
        echo 'parent.messages.document.write(\''.$message."\\n');\n";
        echo 'parent.messages.scroll(1,5000000);';
        echo "\n-->\n</script>\n\n";

        add_to_log(SITEID, 'message', 'write', 'history.php?user1='.$user->id.'&amp;user2='.$USER->id.'#m'.$messageid, $user->id);
    }

    echo '<title></title></head>';


    echo '<body class="message course-1" id="message-send">';
    echo '<center>';
    echo '<form id="editing" method="post" action="send.php" name="theform">';
    echo '<input type="hidden" name="id" value="'.$user->id.'" />';
    echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';

    if (pageone_find_course($user->id, $USER->id)!=null && $CFG->block_pageone_mtype==MTYPE_MM)
    {
     ?>
        <table style="margin-top:5px;margin-bottom:0px;"><tr>
         <td style="font-size:small;"><b><?php print_string('credit_usage', 'block_pageone'); ?>:</b></td>
         <td style="font-size:small;" class="informationbox"><div id="creditusage"></div><div id="warningCell" style="font-size:x-small;"></div></td>
        </tr><tr>
         <td style="font-size:small;" colspan="2"><input type="checkbox" value="1" name="send_by_mm" /><?php print_string('send_with_mm', 'block_pageone');?></td>
        </tr><tr>
         <td style="font-size:small;" colspan="2"><input type="checkbox" name="includefrom" checked="checked" /> <?php print_string('includefrom', 'block_pageone'); ?></td>
        </tr></table>

        <script type="text/javascript" src="../blocks/pageone/script/prototype.js"></script>
        <script type="text/javascript" src="../blocks/pageone/script/fieldCounter.js"></script>
        <script type="text/javascript">

         var smsCounter = new PageoneCounter.PageoneSMSCounter(2000);

         var fieldMonitor = new PageoneCounter.PageoneCharMonitor();
         var fromString = "<?php echo get_string("from", "block_pageone").":".$USER->firstname." ".$USER->lastname."\\n"; ?>";

         function checkField()
         {
             var aField=document.theform.message.value;

             var extChars = fieldMonitor.getExtendedChars(aField);

             if(extChars.length>0)
             {
                 smsCounter.setSMSSize(70);
                 $('warningCell').innerHTML = "<br /><?php print_string("messagewarn_1", "block_pageone")?> ("+extChars+") <?php print_string("messagewarn_2", "block_pageone")?>";
             }
             else
             {
                 smsCounter.setSMSSize(160);
                 $('warningCell').innerHTML = "";
              }
             smsCounter.displayCounter(aField, $('creditusage'));
         }

         var monitor=setInterval('checkField()', 1000);

         </script>
        <?php
    }

    $usehtmleditor = (can_use_html_editor() && get_user_preferences('message_usehtmleditor', 0));
    if ($usehtmleditor) {
        echo '<table align="center"><tr><td align="center">';
        print_textarea($usehtmleditor, 8, 34, 0, 0, 'message', '');
        echo '</td></tr></table>';
        use_html_editor('message', 'formatblock subscript superscript copy cut paste clean undo redo justifyleft justifycenter justifyright justifyfull lefttoright righttoleft insertorderedlist insertunorderedlist outdent indent inserthorizontalrule createanchor nolink inserttable');
        echo '<input type="hidden" name="format" value="'.FORMAT_HTML.'" />';
    } else {
        print_textarea(false, 5, 34, 0, 0, 'message', '');
        echo '<input type="hidden" name="format" value="'.FORMAT_MOODLE.'" />';
    }
    echo '<br /><input type="submit" value="'.get_string('sendmessage', 'message').'" />';
    echo '</form>';
    echo '<div class="noframesjslink"><a target="_parent" href="discussion.php?id='.$userid.'&amp;noframesjs=1">'.get_string('noframesjs', 'message').'</a></div>';
    echo '</center>';

    echo "\n<script type=\"text/javascript\">\n<!--\n";                  /// Focus on the textarea
    echo 'document.getElementById("edit-message").focus();'."\n";
    echo "\n-->\n</script>\n\n";

    echo '</body></html>';
}
?>
