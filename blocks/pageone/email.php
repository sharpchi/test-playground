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

    $id         = optional_param('id', -1, PARAM_INT);  // course ID
    $instanceid = optional_param('instanceid', 0, PARAM_INT);
    $action     = optional_param('action', '', PARAM_ALPHA);
    $editable   = optional_param('editable', true, PARAM_BOOL);
    $to		= optional_param('to', '-1', PARAM_INT);
    $sendtype	= optional_param('type', 0, PARAM_INT);
    $instance = new stdClass;

    /*****The request came in without a course ID, try to find one*****/
    if ($id==-1)
    {
        $id=pageone_find_course($to, $USER->id);
        if ($id==null)
            error (get_string('no_suitable_course', 'block_pageone'));
    }

    if (!$course = get_record('course', 'id', $id)) {
        error(get_string('course_id', 'block_pageone'));
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

    // set up some strings
    $readonly       = '';
    $strchooseafile = get_string('chooseafile', 'resource');
    $strpageone   = get_string('blockname', 'block_pageone');

/// Header setup
    if ($course->category) {
        $navigation = "<a href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    print_header($course->fullname.': '.$strpageone, $course->fullname, "$navigation $strpageone", '', '', true);

    // print the email form START
    print_heading($strpageone);

/// This block of code ensures that pageone will run 
///     whether it is in the course or not
    if (empty($instance)) {
        $groupmode = groupmode($course);

        if (has_capability('block/pageone:cansend', get_context_instance(CONTEXT_BLOCK, $instanceid))) {
            $haspermission = true;
        } else {
            $haspermission = false;
        }
    } else {
        // create a pageone block instance
        $pageone = block_instance('pageone', $instance);
        
        $groupmode     = $pageone->groupmode();
        $haspermission = $pageone->check_permission();
    }
    
    if (!$haspermission) {
        error(get_string('permission', 'block_pageone'));
    }

    $courseusers=array();
    if ($to>-1)
    {
        $u=get_record("user", "id", $to);
        $courseusers[$to]=$u;
    }
    else
    {
        $possibleroles = get_roles_with_capability('moodle/course:view', CAP_ALLOW, $context);
        foreach ($possibleroles as $p)
        {
            $nc=get_role_users($p->id, $context,false, 'u.*', 'u.lastname, u.firstname', false, '', '');
            if (!empty($nc))
            {
                foreach ($nc as $u)
                    $courseusers[$u->id]=$u;
            }   
        }
    }
    if (empty($courseusers)) {
        error(get_string('no_user_email', 'block_pageone'));
    }

    if ($action == 'view') {
        // viewing an old email.  Hitting the db and puting it into the object $form
        $emailid = required_param('emailid', PARAM_INT);
        $form = get_record('block_pageone_log', 'id', $emailid);
        $form->mailto = explode(',', $form->mailto); // convert mailto back to an array

    } else if ($form = data_submitted()) {   // data was submitted to be mailed
        confirm_sesskey();

        if (!empty($form->cancel)) {
            // cancel button was hit...
            redirect("$CFG->wwwroot/course/view.php?id=$course->id");
        }
        
        // prepare variables for email
        $form->subject = stripslashes($form->subject);
        $form->subject = clean_param(strip_tags($form->subject, '<lang><span>'), PARAM_RAW); // Strip all tags except multilang
        $form->message = clean_param($form->message, PARAM_CLEANHTML);
        

        // make sure the user didn't miss anything
        if (!isset($form->mailto)) {
            $form->error = get_string('toerror', 'block_pageone');
        } else if (!$form->subject) {
            $form->error = get_string('subjecterror', 'block_pageone');
        } else if (!$form->message) {
            $form->error = get_string('messageerror', 'block_pageone');
        }

        // process the attachment
        $attachment = $attachname = '';
        if (has_capability('moodle/course:managefiles', $context)) {
            if (isset($form->attachment))
            {
                $form->attachment = trim($form->attachment);
                if (!empty($form->attachment)) {
                    $form->attachment = clean_param($form->attachment, PARAM_PATH);
            
                    if (file_exists($CFG->dataroot.'/'.$course->id.'/'.$form->attachment)) {
                        $attachment = $course->id.'/'.$form->attachment;
            
                        $pathparts = pathinfo($form->attachment);
                        $attachname = $pathparts['basename'];
                    } else {
                        $form->error = get_string('attachmenterror', 'block_pageone', $form->attachment);
                    }
                }
            }
        } else {
            require_once($CFG->libdir.'/uploadlib.php');
        
            $um = new upload_manager('attachment', false, true, $course, false, 0, true);

            // process the student posted attachment if it exists
            if ($um->process_file_uploads('temp/block_pageone')) {
                
                // original name gets saved in the database
                $form->attachment = $um->get_original_filename();

                // check if file is there
                if (file_exists($um->get_new_filepath())) {
                    // get path to the file without $CFG->dataroot
                    $attachment = 'temp/block_pageone/'.$um->get_new_filename();
                
                    // get the new name (name may change due to filename collisions)
                    $attachname = $um->get_new_filename();
                } else {
                    $form->error = get_string("attachmenterror", "block_pageone", $form->attachment);
                }
            } else {
                $form->attachment = ''; // no attachment
            }
        }
           
        // no errors, then email
        if(!isset($form->error)) {
            $mailedto = array(); // holds all the userid of successful emails
            
            // get the correct formating for the emails
            $form->plaintxt = format_text_email($form->message, $form->format); // plain text
            $form->html = format_text($form->message, $form->format);        // html
            $form->messagetype=intval($form->messagetype);

            $users_to_text=array();

            // run through each user id and send a copy of the email to him/her
            // not sending 1 email with CC to all user ids because emails were required to be kept private

            foreach ($form->mailto as $userid) {  
                if (!$courseusers[$userid]->emailstop) {
                    // Send emails
                    if ($form->messagetype==TYPE_TEXT_EMAIL || $form->messagetype==TYPE_EMAIL) {
                        $mailresult = email_to_user($courseusers[$userid], $USER, $form->subject, $form->plaintxt, $form->html, $attachment, $attachname);
                        // checking for errors, if there is an error, store the name
                        if (!$mailresult || (string) $mailresult == 'emailstop') {
                            $form->error = get_string('emailfailerror', 'block_pageone');
                            $form->usersfail['emailfail'][] = $courseusers[$userid]->firstname.' '.$courseusers[$userid]->lastname;
                        } else {
                            // success
                            $mailedto[] = $userid;
                        }
                    }

                    if ($form->messagetype==TYPE_TEXT_MM || $form->messagetype==TYPE_MM)
                    {
                        message_post_message($USER, $courseusers[$userid], $form->html, FORMAT_HTML, 'direct');
                        $mailedto[] = $userid;
                    }

                    array_push($users_to_text, $courseusers[$userid]);

                } else {
                    // blocked email
                    $form->error = get_string('emailfailerror', 'block_pageone');
                    $form->usersfail['emailstop'][] = $courseusers[$userid]->firstname.' '.$courseusers[$userid]->lastname;
                }
            }

            $ovid=-1;
            if (count($users_to_text) && ($form->messagetype==TYPE_TEXT_EMAIL || $form->messagetype==TYPE_TEXT || $form->messagetype==TYPE_TEXT_MM))
            {
                 $textresult=pageone_send_text($users_to_text, $USER, $form->subject, $form->plaintxt, $form->includefrom);
                 if ($textresult->ok==false)
                 {
                    $form->texterror = get_string("textfail", "block_pageone");
                    if (isset($textresult->faultstring))
                        $form->texterror.=' '.$textresult->faultstring;
                    if (isset($textresult->failednumbers))
                        $form->failednumbers = $textresult->failednumbers;
                 }
                 else
                     $ovid=$textresult->id;

                 if ($form->messagetype==TYPE_TEXT)
                    foreach ($users_to_text as $user)
                        $mailedto[]=$user->id;
            }
            // cleanup - delete the uploaded file
            if (isset($um) and file_exists($um->get_new_filepath())) {
                unlink($um->get_new_filepath());
            }

            // prepare an object for the insert_record function
            $log = new stdClass;
            $log->ovid       = $ovid;
            $log->courseid   = $course->id;
            $log->userid     = $USER->id;
            $log->mailto     = implode(',', $mailedto);
            $log->subject    = addslashes($form->subject);
            $log->message    = addslashes($form->message);
            if (isset($form->attachment))
                $log->attachment = $form->attachment;
            else
                $log->attachment = '';
            $log->format     = $form->format;
            $log->timesent   = time();
            $log->messagetype = $form->messagetype;
            if (!isset($form->includefrom))
             $log->includefrom=false;
            else
             $log->includefrom=true;
            if (isset($form->error) || isset($form->texterror))
                $log->status=PAGEONE_ERRORS;
            else
                $log->status=PAGEONE_NO_ERRORS;
            if (isset($form->failednumbers))
                $log->failednumbers=$form->failednumbers;
            else
                $log->failednumbers="";

            if (!insert_record('block_pageone_log', $log)) {
                error('Message not logged.');
            }

            if(!isset($form->error) && !isset($form->texterror)) {  // if no emailing errors, we are done
                // inform of success and continue
                if (!IS_DEBUGGING)
                    redirect("$CFG->wwwroot/course/view.php?id=$course->id", get_string('successfulemail', 'block_pageone'));
            }
        }
        // so people can use quotes.  It will display correctly in the subject input text box
        $form->subject = s($form->subject);

    } else {
        // set them as blank
        $form->subject = $form->message = $form->format = $form->attachment = '';
    }

/// Create the table object for holding course users in the To section of email.html
    
    // table object used for printing the course users
    $table              = new stdClass;
    $table->cellpadding = '10px';    
    $table->width       = '100%';

    $t    = 1;    // keeps track of the number of users printed (used for javascript)
    $cols = 4;    // number of columns in the table

    if ($groupmode == NOGROUPS) { // no groups, basic view
        $table->head  = array();
        $table->align = array('left', 'left', 'left', 'left');
        $cells        = array();

        foreach($courseusers as $user) { 
            if ( (isset($form->mailto) && in_array($user->id, $form->mailto)) || $to==$user->id)
                $checked = 'checked="checked"';
              else
                $checked = '';

            $r=test_contacts($user, $context);
            
            $cells[] = "<input type=\"checkbox\" $checked id=\"mailto".$t."\" value=\"".$user->id."\" name=\"mailto[]\" ".$r->disabled." />".
                        "<label for=\"mailto".$t."\">".fullname($user, true)." ".$r->mobile."</label>";

            $t++;
        }
        $table->data = array_chunk($cells, $cols);
    } else {
        $groups      = new stdClass;    // holds the groups to be displayed
        $buttoncount = 1;               // counter for the buttons (used by javascript)
        $ingroup     = array();         // keeps track of the users that belong to groups
        
        // determine the group mode
        if (has_capability('moodle/site:accessallgroups', $context)) {
            // teachers/admins default to the more liberal group mode
            $groupmode = VISIBLEGROUPS;
        }
        
        if (!function_exists("groups_get_groups"))
            require_once("email-groups-compat.php");

        // set the groups variable
        switch ($groupmode) {
            case VISIBLEGROUPS:
                $groups = groups_get_groups($course->id);
                break;

            case SEPARATEGROUPS:
                $groups = groups_get_groups_for_current_user($course->id);
                break;
        }

        // Add a fake group for those who are not group members
        $groups[] = 0;

        $notingroup = array();
        if ($allgroups = groups_get_groups($course->id)) {
            foreach ($courseusers as $user) {
                $nomembership = true;
                foreach ($allgroups as $group) {
                    if (groups_is_member($group, $user->id)) {
                        $nomembership = false;
                        break;
                    }
                }
                if ($nomembership) {
                    $notingroup[] = $user->id;
                }
            }
        }
        else
        {
            //*****No groups found*****
            foreach ($courseusers as $user)
                    $notingroup[] = $user->id;
        }

        // set up the table
        $table->head        = array(get_string('groups'), get_string('groupmembers'));
        $table->align       = array('center', 'left');
        $table->size        = array('100px', '*');
        
        foreach($groups as $group) {            
            $start = $t;
            $cells = array();  // table cells (each is a check box next to a user name)
            foreach($courseusers as $user) { 
                if (groups_is_member($group, $user->id) or                    // is a member of the group or
                   ($group == 0 and in_array($user->id, $notingroup)) ) {     // this is our fake group and this user is not a member of another group
                                                    
                    if (isset($form->mailto) && in_array($user->id, $form->mailto)) {
                        $checked = 'checked="checked"';
                    } else {
                        $checked = '';
                    }

                    $r=test_contacts($user, $context);
                    $cells[] = "<input type=\"checkbox\" $checked id=\"mailto".$t."\" value=\"".$user->id."\" name=\"mailto[".$user->id."]\" ".$r->disabled."/>".
                                "<label for=\"mailto".$t."\">".fullname($user, true)." ".$r->mobile."</label>";
                    $t++;
                }
            }
            $end = $t;
            
            // cell1 has the group picture, name and check button
            $cell1 = '';
            if ($group) {
                $groupobj = groups_get_group($group);
                $cell1   .= print_group_picture($groupobj, $course->id, false, true).'<br />';
            }
            if ($group) {
                $cell1 .= groups_get_group_name($group);
            } else {
                $cell1 .= get_string('notingroup', 'block_pageone');
            }
            if (count($groups) > 1 and !empty($cells)) {
                $selectlinks = '<a href="javascript:void(0);" onclick="block_pageone_toggle(true, '.$start.', '.$end.');">'.get_string('selectall').'</a> / 
                                <a href="javascript:void(0);" onclick="block_pageone_toggle(false, '.$start.', '.$end.');">'.get_string('deselectall').'</a>';
            } else {
                $selectlinks = '';
            }
            $buttoncount++;
            
            // cell2 has the checkboxes and the user names inside of a table
            if (empty($cells) and !$group) {
                // there is no one that is not in a group, so no need to print our 'nogroup' group
                continue;
            } else if (empty($cells)) {
                // cells is empty, so there are no group members for that group
                $cell2 = get_string('nogroupmembers', 'block_pageone');
            } else {
                $cell2 = '<table cellpadding="5px">';
                $rows = array_chunk($cells, $cols);
                foreach ($rows as $row) {
                    $cell2 .= '<tr><td nowrap="nowrap">'.implode('</td><td nowrap="nowrap">', $row).'</td></tr>';
                }
                $cell2 .= '</table>';
            }
            // add the 2 cells to the table
            $table->data[] = array($cell1, $selectlinks.$cell2);
        }
    }

    // get the default format       
    if ($usehtmleditor = can_use_richtext_editor()) {
        $defaultformat = FORMAT_HTML;
    } else {
        $defaultformat = FORMAT_MOODLE;
    }

    // error printing
    if (isset($form->error)) {
        notify($form->error);
        if (isset($form->usersfail)) {
            $errorstring = '';

            if (isset($form->usersfail['emailfail'])) {
                $errorstring .= get_string('emailfail', 'block_pageone').'<br />';
                foreach($form->usersfail['emailfail'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }

            if (isset($form->usersfail['textfail'])) {
                $errorstring .= get_string('textfail', 'block_pageone').'<br />';
                foreach($form->usersfail['textfail'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }

            if (isset($form->usersfail['emailstop'])) {
                $errorstring .= get_string('emailstop', 'block_pageone').'<br />';
                foreach($form->usersfail['emailstop'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }
            notice($errorstring, "$CFG->wwwroot/course/view.php?id=$course->id", $course);
        }
    }

    if (isset($form->texterror))
    {
        notify($form->texterror);
        if (isset($form->failednumbers))
            notice(pageone_get_failed_numbers($form), "$CFG->wwwroot/course/view.php?id=$course->id", $course);
    }

    $currenttab = 'compose';
    include($CFG->dirroot.'/blocks/pageone/tabs.php');

    if (isset($form->status))
    {
        if ($form->status==PAGEONE_ERRORS)
        {
            notify('<p>'.get_string("errors", "block_pageone").'</p>');
            if (isset($form->failednumbers))
                echo pageone_get_failed_numbers($form);
        }
    }

    print_simple_box_start('center');
    //if ($editable)
        require($CFG->dirroot.'/blocks/pageone/email.html');
    //else
    //    require($CFG->dirroot.'/blocks/pageone/show-email.html');
    print_simple_box_end();
    
    if ($usehtmleditor) {
        echo use_html_editor('message');
    }

    print_footer($course);

    function pageone_get_failed_numbers($form)
    {
        $errorstring="";
        if (!has_capability('moodle/user:viewhiddendetails', $context))
            $errorstring.="<p>".get_string("no_view_priv", "block_pageone")."</p>";

        $errorstring.='<table style="margin-left:auto;margin-right:auto;" class="generaltable boxaligncenter">'.
            '<tr><th class="cell c0">'.get_string("user", "block_pageone").'</th>'.
            '<th class="cell c0">'.get_string("error").'</th></tr>';

        $tok=strtok($form->failednumbers, ",");
        $topup=false;
        while ($tok !== false)
        {
            $user=get_record("user", "id", $tok);
            if (isset($user->id))
                $errorstring.=' <tr><td class="cell c1"><a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'" '.
                                    'title="'.$user->username.'">'.$user->firstname.' '.$user->lastname.'</a></td>';
            else
                $errorstring.=' <tr><td class="cell c1">'.$tok.'</td>';

            $tok = strtok(",");
            if ($tok=="552" || $tok=="553" || $tok=="553")
                $topup=true;
            $errorstring.='<td class="cell c1">'.get_string("error_code_".$tok, "block_pageone").'</td></tr>';
            $tok = strtok(",");
        }

        $errorstring.='</table><br /><br />';

        if ($topup)
            notify(get_string("credit_message".$tok, "block_pageone"));
        return $errorstring;
    }

    /**
    * Tests a users contact details for validity
    * @param $user The user to test
    * @param $context The authorisation context of the user requesting this operation
    * @return Associative array ->mobile HTML showning the status icons ->disable true if the user has no valid contact details
    **/

    function test_contacts($user, $context)
    {
            global $CFG;
            $r=new stdClass();
            $emailok=true;
            $email="";
            if (has_capability('moodle/user:viewhiddendetails', $context))
                $email=$user->email;
            else
                $email=get_string('email_found', 'block_pageone');

            if (strlen($user->email)>0 && $user->email!="root@localhost")
                $r->mobile='<img src="'.$CFG->pixpath.'/t/email.gif" height="14" width="14" title="'.$email.'" alt="'.$email.'" />';
            else
            {
                $emailok=false;
                $r->mobile='<img src="'.$CFG->pixpath.'/t/emailno.gif" height="14" width="14" title="'.get_string('email_not_found', 'block_pageone').
                '" alt="'.get_string('email_not_found', 'block_pageone').'" />';
            }

            $mobileok=true;
            if (!pageone_has_valid_mobile_number($user))
            {
                $r->mobile.='<small><img src="nophone.gif" width="14" height="14" alt="'.get_string("no_mobile", "block_pageone").'"'.
                    ' title="'.get_string("no_mobile", "block_pageone").'"/></small>';
                $mobileok=false;
            }
            else
            {
                $number="";
                if (has_capability('moodle/user:viewhiddendetails', $context))
                    $number=pageone_find_mobile_number($user);
                else
                    $number=get_string("mobile_found", "block_pageone");
                $r->mobile.='<small><img src="phone.gif" width="14" height="14" alt="'.$number.'" title="'.$number.'"/></small>';
            }

            $r->disabled='';
            if ($emailok==false && $mobileok==false)
                $r->disabled='disabled="disabled"';
            return $r;
    }
?>