<?php

/**
 * pageonelib - Contains PageOnelibrary for sending text messages and associated functions
 * 
 * This code is licenced under the GNU GPLv2 licence (please see gpl.txt for details) and
 * is copyright to PageOne
 *
 * @author Tim Williams
 * @package pageone
 **/

/*****Some help with turning debug on/off*****/

define ("IS_DEBUGGING", false);

/*****Some constants to use in the system*****/

define ("TYPE_TEXT_EMAIL", 0);
define ("TYPE_TEXT", 1);
define ("TYPE_EMAIL", 2);
define ("TYPE_TEXT_MM", 3);
define ("TYPE_MM", 4);

define ("MTYPE_EMAIL", 0);
define ("MTYPE_MM",1);

define ("RECEIVE_IGNORE", 0);
define ("RECEIVE_EMAIL", 1);
define ("RECEIVE_DEFAULT_MBOX", 2);
define ("RECEIVE_REJECT", 3);

define ("CALLBACK_BOTH", 0);
define ("CALLBACK_DELIVERY", 1);
define ("CALLBACK_REPLY", 2);

define ("SOAP_NS", "http://schemas.oventus.com");
define ("SOAP_CALLBACK_NS", "http://jaxb.liquidcallbackregister.pageone.com");

define ("SOAP_SERVER", "https://soap.oventus.com");
//define ("SOAP_SERVER", "https://sandbox.oventus.com");

define ("SOAP_WEBSERICES_URL", "/webservices/soap");
define ("SOAP_CALLBACK_URL", "/LiquidWS/CallbackRegisterService");

/*****Some error response codes for externally called methods*****/

/**
* Returned when the login fails
**/
define ("PAGEONE_LOGIN_FAILED", 0);

/**
* Returned when everything has worked
**/
define ("PAGEONE_SUCESS", 1);

/**
* For log, message sent with no errors
**/

define("PAGEONE_NO_ERRORS", 0);

/**
* For log, some errors during message sending
**/

define("PAGEONE_ERRORS", 1);

/**
* For log, callback was recieved and everything is good
**/

define("PAGEONE_CONFIRMED", 2);

/**
* For log, callback was recieved but there were some errors, either in the original send or the callback
**/

define("PAGEONE_CONFIRMED_ERRORS", 3);

//The Moodle nusoap library has broken proxy support, so include our alternative version here
//However, moodle includes it's version of nusoap and this lib on some admin pages and that causes a fatal error
//so we must test for nusoap classes to ensure this doesn't happen

if (!class_exists("nusoap_base"))
 require_once("lib/nusoap.php");

/**Check which version of PHP we have**/
//if (class_exists("DOMNodeList"))
if (substr(PHP_VERSION,0,strpos(PHP_VERSION, '.'))>=5)
 require_once("pageonelib_5.php");
else
 require_once("pageonelib_4.php");

/*****SOAP connection stuff*****/

/**
* This method will send a SOAP request and return it's result
* @param $header The SOAP header XML as a String
* @param $body The SOAP body XML as a String
* @param $action The SOAP method to call
* @param $soapurl The SOAP url to send the method too on the server. Defaults to SOAP_WEBSERICES_URL
* @return A DOMDocument containing the SOAP response
**/

function pageone_send_soap($header, $body, $action, $soapurl=SOAP_WEBSERICES_URL)
{
    global $CFG;

    if (IS_DEBUGGING)
        echo 'Start soap send <br />';

    $client=new nusoap_client(SOAP_SERVER.$soapurl);
    if (!empty($CFG->proxyhost))
    {
        $pp=false;
        if (!empty($CFG->proxyport))
            $pp=$CFG->proxyport;

        $pu=false;
        $ps=false;
        if (!empty($CFG->proxyuser) && !empty($CFG->proxypassword))
        {
            $pu=$CFG->proxyuser;
            $ps=$CFG->proxypassword;
        }
        if (IS_DEBUGGING)
            echo "proxy host:".$CFG->proxyhost." port:".$pp." user:".$pu." pass:".$ps;
        $client->setHTTPProxy($CFG->proxyhost, $pp, $pu, $ps);
    }
    $err = $client->getError();

    if (IS_DEBUGGING)
        echo 'Done client<br />';

    if ($err)
    {
	pageone_show_error("SOAP Constructor error", $err."<br /><br />client->response='".$client->response."'");
        return null;
    }


    $soapxml = $client->serializeEnvelope($body, $header, array(), 'document', 'literal');
    $result = $client->send($soapxml, $action);
    if (IS_DEBUGGING)
    {
        echo 'Sending <br /><textarea cols="100" rows="20">'.$client->request.'</textarea><br />';
        echo 'Response <br /><textarea cols="100" rows="20">'.$client->response.'</textarea><br />';
    }

    if ($client->fault)
    {
	pageone_show_error(get_string('soap_fault', 'block_pageone'), get_r($result), get_string('soap_fault_help', 'block_pageone'));
        return null;
    }
    else
    {
	$err = $client->getError();
	if ($err)
        {
            pageone_show_error(get_string('send_error', 'block_pageone'), $err.'<br/><br/>'.$client->response, get_string('send_error_help', 'block_pageone'));
            return null;
        }
    }

    return pageone_get_xml_document($client->responseData);
}

/**
* Shows an error on the page
* @param $title The error title
* @param $err The error message
**/

function pageone_show_error($title, $err, $help="")
{
    echo '<h2>'.$title.'</h2><pre>'.$err.'</pre>';
    if (strlen($help)>0)
     echo '<p>'.$help.'</p>';
}

/*****Methods which are intended for external use*****/

/**
* Call this to see if the config details have been entered
* @return true if the PageOne username & password has been entered
**/

function pageone_is_configured()
{
    global $CFG;
    if (isset($CFG->block_pageone_account_num) && strlen($CFG->block_pageone_account_num)>0 &&
        isset($CFG->block_pageone_account_pass) && strlen($CFG->block_pageone_account_pass)>0)
        return true;

    return false;
}

/**
* This method tests the pageone account details
* @return error response code
**/

function pageone_test_account()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;
    pageone_soap_logout($session);
    return PAGEONE_SUCESS;
}

/**
* Sends a text message to a group of users
* @param $userstotext An array of Users who should be texted
* @param $sender The message sender User
* @param $subject The message subject
* @param $message The message text
* @param $includefrom true if the message sender is to be included in the text message body
* @return a result object containing any any errors
**/

function pageone_send_text($userstotext, $sender, $subject, $message, $includefrom)
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $messageText=$subject."\n".$message;
    if ($includefrom)
     $messageText=get_string("from", "block_pageone").":".$sender->firstname." ".$sender->lastname."\n".$messageText;

    $numbers=array();
    $user_map=array();

    foreach($userstotext as $user)
    {
        $num=pageone_find_mobile_number($user, true);
        if (strlen($num)>0)
        {
            if (IS_DEBUGGING)
                echo 'sending to '.$user->username.' '.$num.'<br />';
            array_push($numbers, $num);
            $user_map[$num]=$user->id;
        }
    }

    $result=pageone_soap_send_message($session, $numbers, $messageText, pageone_get_custom_alphatag($sender->id), $user_map);
    pageone_soap_logout($session);

    return $result;
}

/**
* Sends a text message to a specific number from the system administrator
* @param $number The number to text
* @param $message The message text
* @return a result object containing any any errors
**/

function pageone_send_text_to_number($number, $message)
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $numbers=array();
    array_push($numbers, $number);

    $result=pageone_soap_send_message($session, $numbers, $message, "", array());
    pageone_soap_logout($session);

    return $result;
}

//*****Mobile phone number methods*****

/**
* Checks to see if the user has a valid phone number
* @param $user The use to test
* @return true if a valid number has been found
**/

function pageone_has_valid_mobile_number($user)
{
    if (strlen(pageone_find_mobile_number($user))>0)
        return true;

    return false;
}

/**
* Finds the mobile phone number of the supplied user
* @param $user The user to test
* @param $process If the number should be processed according to locale rules to remove internationalisation
* @return The users phone number (or an empty string if it's not found
**/

function pageone_find_mobile_number($user, $process=false)
{
    $locale=pageone_include_number_locale();
    $num=eval('return pageone_get_mobile_number_'.$locale.'($user);');
    if ($process)
        $num=eval('return pageone_process_mobile_number_'.$locale.'($num);');

    return $num;
}

/**
* Includes the phone number processing locale
**/

function pageone_include_number_locale()
{
    global $CFG;

    $locale=$CFG->block_pageone_locale;
    if (!file_exists('locales/'.$locale.'.php'))
        $locale="first_valid";

    require_once('locales/'.$locale.'.php');
    return $locale;
}

/**
* Gets the available locales for detecting mobile phone numbers
* @return An array of the locales
**/

function pageone_get_mobile_locales()
{
    global $CFG;
    $handle=opendir($CFG->dirroot.'/blocks/pageone/locales');
    $files=array();

    while (false !== ($file = readdir($handle)))
        if ($file!=".." && $file!=".")
            array_push($files, substr($file, 0, strlen($file)-4));

    closedir($handle);

    return $files;
}

/**
* Gets the name of the specified locale
* @param $locale The required locale
* @return The locale name
**/

function pageone_get_locale_name($locale)
{
    require_once('locales/'.$locale.'.php');
    return eval('return pageone_get_locale_name_'.$locale.'();');
}

//*****Alphatag editing functions*****

/**
* Adds a custom alphatag
* @param $userid The id of the user
* @param $alphatag The custom alphatag to set
* @return true if the tag was successfully set
**/

function pageone_add_user_alphatag($userid, $alphatag, $receive)
{
    if (pageone_has_custom_alphatag($userid))
        return false;
    else
    {
        $data->userid=$userid;
        $data->alphatag=$alphatag;
        $data->receive=$receive;
        insert_record("block_pageone_alphatags", $data);
        return true;
    }
}

/**
* Updates the custom alphatag of the specified user
* @param $userid The id of the user
* @param $alphatag The custom alphatag to set
**/

function pageone_update_user_alphatag($id, $alphatag, $receive)
{
    $data->id=$id;
    $data->alphatag=$alphatag;
    $data->receive=$receive;
    update_record("block_pageone_alphatags", $data);
}

/**
* Tests to see if the users has a custom alphatag
* @param $userid The id of the user to test
* @return true if the user has a custom alphatag
**/

function pageone_has_custom_alphatag($userid)
{
    $r=get_record("block_pageone_alphatags", "userid", $userid);
    return isset($r->id);
}

/**
* Tests to see if the users has a custom alphatag
* @param $userid The id of the user to test
* @return true if the user has a custom alphatag
**/

function pageone_get_custom_alphatag($userid)
{
    $r=get_record("block_pageone_alphatags", "userid", $userid);
    if (isset($r->id))
    {
        return $r->alphatag;
    }
    else
        return "";
}

/**
* Delete the custom alpha tag of a user
* @param $id The id of the tag to delete
**/

function pageone_delete_user_alphatag($id)
{
    delete_records("block_pageone_alphatags", "id", $id);
}

/**
* Returns an HTML drop down box of the currently valid alphatags for this account.
* This will return a textbox if a list of the alpha tags cannot be obtained.
* @param $name The HTML tag name to use for the box
* @param $id The HTML tag id to use
* @param $set The currently selected alhpatag (optional)
* @return An HTML formatted string
**/

function pageone_get_alphatagoptions($name, $id, $set="")
{
    global $CFG;
    //*****If the module isn't configured, return the current value*****
    if (!pageone_is_configured())
        return "<i>".get_string("no_list", "block_pageone")."</i><input type=\"hidden\" name=\"".$name."\" id=\"".$id."\" value=\"\" />";

    //*****Pick up the alphatags. If the list is blank, send back a text box*****
    $tags=pageone_get_alphatags();
    if ($tags==PAGEONE_LOGIN_FAILED || count($tags)==0)
        return "<i>".get_string("no_list", "block_pageone")."</i><input type=\"hidden\" name=\"".$name."\" id=\"".$id."\" value=\"\" />";

    $data='<select name="'.$name.'" id="'.$id.'">';
    if ($set=="")
     $data.='<option value="" selected="selected">'.get_string("please_select", "block_pageone").'</option>';

    foreach ($tags as $tag)
    {
        $inuse='';
        if (pageone_alphatag_user($tag)>-1)
            $inuse=' *';
        if ($tag==$CFG->block_pageone_alpha_tag)
            $inuse=' #';

        if ($tag==$set)
            $data .= '<option value="'.$tag.'" selected="selected">'.$tag.$inuse.'</option>';
        else
            $data .= '<option value="'.$tag.'">'.$tag.$inuse.'</option>';
    }
    return $data.'</select>';

}

function pageone_alphatag_user($tag)
{
    $r=get_record("block_pageone_alphatags", "alphatag", $tag);
    if (isset($r->id))
        return $r->id;

    return -1;
}

/***Callback code***/

/**
* This method sets callbacks on the PageOne server
* @param $host The callback host URL to add (defaults to current server)
* @param $serv The type of callback to add. Defaults to both. Should be CALLBACK_BOTH, CALLBACK_DELIVERY or CALLBACK_REPLY
**/

function pageone_set_callback($host="", $serv=CALLBACK_BOTH)
{
    global $CFG;
    if ($host=="")
       $host=$CFG->wwwroot."/blocks/pageone/callback.php?wsdl";

    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    pageone_add_callback($session, $host, $serv);

    pageone_soap_logout($session);
}

/**
* Sets the specificed callback on the pageone server
* @param $session The server session to use
* @param $host The callback URL to add
* @param $serv The type of callback to add. Defaults to both. Should be CALLBACK_BOTH, CALLBACK_DELIVERY or CALLBACK_REPLY
**/

function pageone_add_callback($session, $host, $serv)
{
    global $CFG;
    if (IS_DEBUGGING)
        echo "Adding callback host=".$host."<br />";

    if ($serv==CALLBACK_DELIVERY || $serv==CALLBACK_BOTH)
    {
        $r=pageone_add_callback_service($session, $host, "deliveryReportListenerRequest", "registerDeliveryReportListener");
        if ($r->value!=200)
            pageone_show_error(get_string('callback_reg_error_delivery', 'block_pageone'), $r->text." (".$r->value.")");
    }

    if ($serv==CALLBACK_REPLY || $serv==CALLBACK_BOTH)
    {
        $r=pageone_add_callback_service($session, $host, "receivedMessageListenerRequest", "registerReceivedMessageListener");
        if ($r->value!=200)
            pageone_show_error(get_string('callback_reg_error_reply', 'block_pageone'), $r->text." (".$r->value.")");
    }
}

/**
* This method removes all of the callbacks currently registered on the PageOne server
**/

function pageone_remove_all_callbacks()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $callbacks=pageone_list_callbacks($session);
    foreach ($callbacks as $cb)
        pageone_remove_callback($session, $cb->entryID);

    pageone_soap_logout($session);
}

/**
* This method removes a specific callback on the PageOne server
* @param $entryID The id of the callback to remove
**/

function pageone_remove_specific_callback($entryID)
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    pageone_remove_callback($session, $entryID);

    pageone_soap_logout($session);
}

/**
* This method gets a list of the currently registered callbacks
* @return An Array of values in the following format $a[$index]->entryID=The callback ID
*         $a[$index]->service=The callback type, should be CALLBACK_REPLY or CALLBACK_DELIVERY
*         $a[$index]->host=The registered URL
**/

function pageone_get_callback_list()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $callbacks=pageone_list_callbacks($session);

    pageone_soap_logout($session);
    return $callbacks;
}

/**
* This method checks to see if the currently registered callbacks are likely to work
* @return true or false
**/

function pageone_check_callbacks()
{
    global $CFG;
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $callbacks=pageone_list_callbacks($session);
    $okreply=false;
    $okdelivery=false;
    require_once("services/callback_url.php");
    $callbackURL=$CALLBACK_URL."?wsdl";
   
    foreach ($callbacks as $cb)
    {
        if ($cb->host==$callbackURL) 
        {
            if ($cb->service==CALLBACK_DELIVERY)
                $okdelivery=true;
            else
            if ($cb->service==CALLBACK_REPLY)
                $okreply=true;
        }
    }

    pageone_soap_logout($session);
    if ($okreply && $okdelivery)
        return true;
    else
        return false;
}

/****Debug helper methods****/

/**
* Result checking method
* @param $client The SOAP client proxy to check
* @param $result The result object generated by the request (should only contain errors) 
**/

function check_result($proxy, $result)
{
   if (!IS_DEBUGGING)
       return;

   echo '<h3>Sending</h3><textarea cols="100" rows="20">'.$proxy->request.'</textarea><br />'.
        '<h3>Response</h3><textarea cols="100" rows="20">'.$proxy->response.'</textarea><br />';

   if ($proxy->fault)
   {
       echo '<h2>Fault</h2><pre>';
       print_r($result);
       echo '</pre>';
   }
   else
       check_error($proxy, "Send error");

   echo "<p>Sending done</p>";
}

/**
* Checks client for errors
* @param $client The SOAP client to check
* @param $message A message to print when an error is found
**/

function check_error($client, $message)
{
    $err = $client->getError();
    if ($err)
    {
        echo '<h2>'.$message.'</h2><pre>'.$err.'</pre>';
        return true;
    }
    return false;
}

/*****Misc helper methods*****/

/**
* This function tries to find a course in which this user is allowed to use MoodleMobile and of which the recipient is a member
* @param $to The id of the user the message is to
* @param $from The id of the user the message is from
**/

function pageone_find_course($to, $from, $noerror=false)
{
    $courses=get_user_capability_course('block/pageone:cansend', $from);
    if (count($courses)==0 && $noerror==false)
        error(get_string('no_permission', 'block_pageone'));

    foreach ($courses as $course)
    {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('moodle/course:view', $context, $to))
            return $course->id;
    }
    return 1;
}

?>