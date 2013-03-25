<?php

/**
* Callback server for MoodleMobile. This allows the block to deal with incoming text messages and receive
* delivery reports from the PageOne servers
* @author Tim Williams
* @package pageone
**/

require_once('../../config.php');
require_once('../../lib/moodlelib.php');
require_once('../../lib/phpmailer/class.phpmailer.php');
require_once('../../message/lib.php');

//****If message/lib.php has been patched, then this is unnecessary.*****
if (!function_exists("pageone_send_soap"))
    require_once('pageonelib.php');

/**
* If you need to record and debug what's happening in the callback server set this to true and define a 
* log file
**/

define("DEBUG_LOG", false);
define("DEBUG_LOG_FILE", "/tmp/callback-log.txt");

/*****Include the IP Address security*****/
global $CFG;

require_once('callback_ips.php');

/*****Setup the SOAP server*****/

$server=new nusoap_server($CFG->wwwroot."/blocks/pageone/services/liquidCallback.php");

/**
* This method received notifications of delivery reports
**/

function onDeliveryReport($source, $destination, $recieptTime, $resultCode, $transactionID)
{
    logData(array($source, $destination, $recieptTime, $resultCode, $transactionID));
    if ($transactionID==null)
    {
        logData("Transaction ID is missing, I can't process this delivery report. Aborted.");
        return;
    }

    $logentry=get_record("block_pageone_log", "ovid", $transactionID);
    if (!isset($logentry->id))
    {
        logData("Transaction ID doesn't exist in the database, I can't process this delivery report. Aborted.");
        return;
    }

    if ($logentry->status==PAGEONE_NO_ERRORS)
        $logentry->status=PAGEONE_CONFIRMED;
    else
    if ($logentry->status==PAGEONE_ERRORS)
        $logentry->status=PAGEONE_CONFIRMED_ERRORS;

    $errors=array();

    /*****Read any pre-existing errors in the log so they can be updated*****/
    $tok=strtok($logentry->failednumbers, ",");
    while ($tok !== false)
    {
        $num=$tok;
        $tok = strtok(",");
        $errors[$num]=$tok;
        $tok = strtok(",");
    }

    /*****Read errors from callback*****/
    if ($resultCode>299)
    {
        $logentry->status=PAGEONE_CONFIRMED_ERRORS;
        $user=getUserByPhoneNumber($destination);
        if ($user==null)
            $errors[$destination]=$resultCode;
        else
            $errors[$user->id]=$resultCode;
    }

    /*****Reconstruct the errors for the log entry*****/
    $logentry->failednumbers="";
    $keys=array_keys($errors);
    foreach ($keys as $key)
        $logentry->failednumbers.=$key.",".$errors[$key].",";

    /*****Update the db log*****/
    update_record("block_pageone_log", $logentry);
    logData(array($transactionID, $logentry->id));
}

/**
* Called when a message is recieved from the PageOne server
* @param $sender The message senders MSISDN
* @param $destination The MSISDN the message was sent to
* @param $messageTime The message time in UTC date format
* @param $message The message text
**/

function onMessageReceived($sender, $destination, $messageTime, $message)
{
    logData(array($sender, $destination, $messageTime, $message));
    global $CFG;

    $default_sender=get_record('user', 'username', 'admin');
    $send_user=getUserByPhoneNumber($sender);
    $user_found=true;
    $subject="";

    if ($send_user==null)
    {
        $user_found=false;
        $send_user=$default_sender;
        $subject=get_string('message_from', 'block_pageone')." ".$sender;
    }
    else
        $subject=get_string('message_from', 'block_pageone')." ".$send_user->firstname." ".$send_user->lastname;

    $textmessage=get_string('message_time', 'block_pageone')." : ".$messageTime."\n\n".$message.
        "\n\n--------------------------------------------------------------\n\n"
        .get_string('reply_with', 'block_pageone')."\n\n"
        .$CFG->wwwroot."/blocks/pageone/email.php?to=".$send_user->id;

    $htmlmessage="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\""
        ."\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
        ."<head><title>".get_string("blockname", "block_pageone")."</title></head>\n"
        ."<body>\n"
        ."<p>".get_string('message_time', 'block_pageone')." : ".$messageTime."</p>\n"
        ."<p>".$message."</p>\n"
        ."<hr />\n"
        ."<p>".get_string('reply_with', 'block_pageone')."</p>\n"
        ."<p><a href=\"".$CFG->wwwroot."/blocks/pageone/email.php?type=".TYPE_TEXT_EMAIL."to=".$send_user->id."\">\n"
        .$CFG->wwwroot."/blocks/pageone/email.php?to=".$send_user->id."</a></p>\n"
        ."</body>\n"
        ."</html>";

    $mm_message="<i>".get_string("via", "block_pageone")."</i> ".$message;
    if (!function_exists('message_moodle_mobile_patch'))
        $mm_message="<i>".get_string("via", "block_pageone")." "
            ."<a target=\"_blank\" href=\"".$CFG->wwwroot."/blocks/pageone/email.php?type=".TYPE_TEXT_MM."&to=".$send_user->id."\">"
            .get_string("reply_mm", "block_pageone")."</a>.</i> ".$message;

    $receivers=get_records("block_pageone_alphatags", "alphatag", $destination); //, "receive", true);
    if (count($receivers)>0)
    {
        foreach($receivers as $receiver)
        {
            $receive_user=get_record('user', 'id', $receiver->userid);
            //logData($CFG->block_pageone_mtype);

            //*****Send the message*****
            if ($CFG->block_pageone_mtype==MTYPE_MM && $CFG->messaging==true)
            {
                logData("Sending by MM to ".$receive_user->username." (".$receiver->userid.")");
                message_post_message($send_user, $receive_user, $mm_message, FORMAT_HTML, 'direct');
            }
            else
            {
                logData("Emailing message to ".$receive_user->username." (".$receiver->userid.")");
                email_to_user($receive_user, $send_user, $subject, $textmessage, $htmlmessage);
            }
            //*****Add to the message log*****
            $log = new stdClass;
            $log->userid=$receive_user->id;
            if ($user_found)
            {
                $log->courseid=pageone_find_course($receive_user->id, $send_user->id, true);
                $log->mailfrom=$send_user->id;
            }
            else
            {
                $log->courseid=1;
                $log->mailfrom=$sender;
            }
            $t=strtotime($messageTime);
            //PHP 4 sometimes has trouble parsing the dateformat used, so check it here
            if ($t>0)
                $log->timesent=$t;
            else
                $log->timesent=time();
            $log->message=$message;
            insert_record('block_pageone_inlog', $log);
        }
        return;
    }

    //*****If we have got this far, then the receipient could not be identified*****

    if ($CFG->block_pageone_receive==RECEIVE_IGNORE)
    {
        logData("Ignoring message ".$CFG->block_pageone_receive.".");
        return;
    }
    else
    if ($CFG->block_pageone_receive==RECEIVE_REJECT)
    {
        global $SITE;
        $locale=pageone_include_number_locale();
        $sender=eval('return pageone_process_mobile_number_'.$locale.'($sender);');

        logData("rejecting message to ".$sender);
        pageone_send_text_to_number($sender, $SITE->fullname."\n\n".get_string('messagefail', 'block_pageone'));
        return;
    }
    else
    if ($CFG->block_pageone_receive==RECEIVE_EMAIL && $user_found)
    {
        logData("emailing fail message to sender ".$send_user->email);
        $message=get_string('messagefail', 'block_pageone')." ".get_string('messagefail_extra', 'block_pageone')."\n\n".
            get_string('to', 'block_pageone')." : +".$destination."\n\n".$message;

        email_to_user($send_user, $default_sender, get_string('messagefail_subject','block_pageone'), $message); 
        return;
    }
    else
    if ($CFG->block_pageone_receive==RECEIVE_DEFAULT_MBOX || $CFG->block_pageone_receive==RECEIVE_EMAIL)
    {
        $message=get_string('messagefail_extra', 'block_pageone')."\n\n"
            .get_string('from', 'block_pageone').": +".$sender;

        if ($user_found)
            $message.=" (".$send_user->firstname." ".$send_user->lastname." - ".$send_user->username.")\n";
        else
            $message.="\n";

        $message.=get_string('to', 'block_pageone').": +".$destination
             ."\n\n".$message."\n";

        logData("sending to default mailbox\n\n".$message);

        $mail=getMailer();
        $mail->Sender=$send_user->email;
        $mail->From=$send_user->email;
        $mail->FromName=fullname($send_user);
        $mail->Subject=get_string('messagefail_subject_default', 'block_pageone')." ".$destination;
        $mail->AddAddress($CFG->block_pageone_default_mbox, get_string('blockname', 'block_pageone'));
        $mail->WordWrap=79;
        $mail->IsHTML(false);
        $mail->Body=$message;
        $mail->Send();

        //email_to_user($send_user, $default_sender, get_string('messagefail_subject','block_pageone'), $message); 
        return;
    }

    //*****Should not be reached*****
}

/**
* This method gets a phpmailer object, pre-configured with the correct parameters for this instance of Moodle
* @return a phpmailer object
**/

function getMailer()
{
    global $CFG;
    $mail = new phpmailer;

    $mail->Version = 'Moodle '. $CFG->version;           // mailer version
    $mail->PluginDir = $CFG->libdir .'/phpmailer/';      // plugin directory (eg smtp plugin)

    //$mail->CharSet = 'UTF-8';

    if ($CFG->smtphosts == 'qmail') {
        $mail->IsQmail();                              // use Qmail system

    } else if (empty($CFG->smtphosts)) {
        $mail->IsMail();                               // use PHP mail() = sendmail

    } else {
        $mail->IsSMTP();                               // use SMTP directly
        if (!empty($CFG->debugsmtp)) {
            echo '<pre>' . "\n";
            $mail->SMTPDebug = true;
        }
        $mail->Host = $CFG->smtphosts;               // specify main and backup servers

        if ($CFG->smtpuser) {                          // Use SMTP authentication
            $mail->SMTPAuth = true;
            $mail->Username = $CFG->smtpuser;
            $mail->Password = $CFG->smtppass;
        }
    }
    return $mail;
}

/**
* This method attempts to identify a user from their MSISDN.
* Only the first user found with the specified phone number will be returned
* @param $num The MSISDN to idenfiy
* @return A user record or null if not found
**/

function getUserByPhoneNumber($num)
{
    global $CFG;
    //*****Create a searchable number****
    if (ereg("^(".$CFG->block_pageone_country_code.")", $num))
        $num=substr($num, strlen($CFG->block_pageone_country_code));

    $newnum="%";
    for($loop=0; $loop<strlen($num); $loop++)
        $newnum.=substr($num, $loop, 1)."%";

    logData("Looking for ".$newnum);

    //*****Check both phone fields*****
    $users=get_records_sql("SELECT * FROM ".$CFG->prefix."user WHERE `phone1` LIKE '".$newnum."';");
    if ($users)
        foreach ($users as $user)
            return $user;

    $users=get_records_sql("SELECT * FROM ".$CFG->prefix."user WHERE `phone2` LIKE '".$newnum."';");
    if ($users)
        foreach ($users as $user)
            return $user;

    logData("Can't find phone number ".$num." message will be from admin");

    return null;
}


/****Debug helper methods*****/

/**
* Logs the supplied data in a nice easy to read format
* $data a single item or array of data
**/

function logData($data)
{
    if (!DEBUG_LOG)
        return;

    $fhandle=fopen(DEBUG_LOG_FILE, "a");

    fwrite($fhandle, "\n*****".date("F j, Y, g:i a")."*****\n");

    if (gettype($data)=="array")
    {
     fwrite($fhandle, "Data array detected\n");
     printArray($fhandle, $data);
    }
    else
     fwrite($fhandle, $data."\n");

    fwrite($fhandle, "**********************************\n");
    fclose($fhandle);
}

/**
* Prints out the specified array, one item per line
* @param $fhandler The file handle of the file to print the data to
* @param $data The data array to print
* @param $recurse Information to be prepended at the start of the line.
*                 Should not be used when calling externally, only exists
*                 to give a nice output when recursing through nested arrays.
**/

function printArray($fhandle, $data, $recurse="")
{
    $keys=array_keys($data);
    foreach ($keys as $key)
    {
        if (gettype($data[$key])=="array")
            printArray($fhandle, $data[$key], $recurse.$key."/");
        else
            fwrite($fhandle, $recurse.$key.": ".$data[$key]."\n");
    }
}

/****Send data to Soap Server*****/

foreach($ALLOWED_IPS as $ip)
    if ($_SERVER['REMOTE_ADDR']==$ip)
    {
        $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';

        if (array_key_exists('HTTP_SOAPACTION', $_SERVER))
        {
            logData(array("Callback triggered", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_SOAPACTION'],
                $_SERVER['REQUEST_URI'], $HTTP_RAW_POST_DATA));
        }
        else
            logData(array("Callback triggered", $_SERVER['REMOTE_ADDR'], "No Soap action specified",
                $_SERVER['REQUEST_URI'], $HTTP_RAW_POST_DATA));

        $server->service($HTTP_RAW_POST_DATA);

        die();
    }

logdata(array("Callback rejected, unknown IP", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_SOAPACTION'],
    $_SERVER['REQUEST_URI'], $HTTP_RAW_POST_DATA));
?>