<?php

/**
 * pageonelib - Contains PHP 4 dependent functions for pageonelib
 * 
 * This code is licenced under the GNU GPLv2 licence (please see gpl.txt for details) and is
 * copyright to PageOne.
 *
 * @author Tim Williams
 * @package pageone
 **/

/**
* Sets up the SOAP client and logins into pageone server
* @return The Session ID or null if the login failed
**/

function pageone_soap_login()
{
    global $CFG;
    if (IS_DEBUGGING)
        echo 'Start login<br />';

    $doc=domxml_new_doc("1.0");
    //$doc->formatOutput = IS_DEBUGGING;

    $loginRequest=$doc->create_element("loginRequest");
    $loginRequest->set_attribute("xmlns:m", SOAP_NS);
    $doc->append_child($loginRequest);

    $ovLogin=$doc->create_element("ovLogin");
    $loginRequest->append_child($ovLogin); 

    $request=$doc->create_element("request");
    $ovLogin->append_child($request); 

    $user_id=$doc->create_element("user-id");
    $user_id->set_content($CFG->block_pageone_account_num);
    $request->append_child($user_id);

    $pwd=$doc->create_element("pwd");
    $pwd->set_content($CFG->block_pageone_account_pass);
    $request->append_child($pwd);

    $result = pageone_send_soap('', $doc->dump_node($loginRequest), "login");
    if ($result==null)
        return null;
    else
    {
        $status=$result->get_elements_by_tagname("status");
        if ($status[0]->get_content()=="200")
        {
            $iddoc=$result->get_elements_by_tagname("session-id");
            return $iddoc[0]->get_content();
        }
        else
            return null;
    }
}

/**
* Logoff from the PageOne SOAP server
* @param $session The session ID
* @return true if the logout worked
**/

function pageone_soap_logout($session)
{
    $doc=domxml_new_doc("1.0");
    //$doc->formatOutput = IS_DEBUGGING;

    $logoffRequest=$doc->create_element("m:loginRequest");
    $logoffRequest->set_attribute("xmlns:m", SOAP_NS);
    $doc->append_child($logoffRequest);

    $ovLogoff=$doc->create_element("ovLogoff");
    $logoffRequest->append_child($ovLogoff); 

    $result = pageone_send_soap(pageone_get_session_header($session), $doc->dump_node($logoffRequest), "logoff");
    if ($result==null)
        return false;
    else
    {
        $status=$result->get_elements_by_tagname("status");
        if ($status[0]->get_content()=="200")
            return true;
        else
            return false;
    }
}

/**
* Sends a text message to the specified phone numbers
* @param $session The session ID
* @param $numbers An array of the phone number(s) to send to
* @param $messageText A string of the message to send
* @param $from The sending address
* @return The server response object
**/

function pageone_soap_send_message($session, $numbers, $messageText, $from, $user_map)
{
    global $CFG;
    $doc=domxml_new_doc("1.0");
    //$doc->formatOutput = IS_DEBUGGING;

    $sendMessageRequest=$doc->create_element("m:sendMessageRequest");
    $sendMessageRequest->set_attribute("xmlns:m", SOAP_NS);
    $doc->append_child($sendMessageRequest);

    $ovSend=$doc->create_element("ovSend");
    $sendMessageRequest->append_child($ovSend);

    $request=$doc->create_element("request");
    $ovSend->append_child($request); 

    foreach ($numbers as $number)
    {
        $address=$doc->create_element("address");
        $address->set_content($number);
        $request->append_child($address);
    }

    $message=$doc->create_element("message");
    $message->set_content(htmlspecialchars($messageText));
    $request->append_child($message);

    if (strlen($from)>0)
    {
        $sourceAddress=new DOMElement("sourceAddress");
        $sourceAddress->set_content($from);
        $request->append_child($sourceAddress);
    }
    else
    {
        if (isset($CFG->block_pageone_alpha_tag) && strlen($CFG->block_pageone_alpha_tag)>0)
        {
            $sourceAddress=new DOMElement("sourceAddress");
            $sourceAddress->set_content($CFG->block_pageone_alpha_tag);
            $request->append_child($sourceAddress);
        }
    }

    $result = pageone_send_soap(pageone_get_session_header($session), $doc->dump_node($sendMessageRequest), "sendMessage");
    $response->ok=false;
    if ($result==null)
        return $response;
    else
    {
        $faultcodes=$result->get_elements_by_tagname("faultcode");
        if (count($faultcodes)>0)
        {
            $response->faultcode=$faultcodes[0]->get_content();
            $faultstrings=$result->get_elements_by_tagname("faultstring");
            $response->faultstring=$faultstrings[0]->get_content();
            return $response;
        }

        $failednumbers="";
        $response->ok=true;
        $status_list=$result->get_elements_by_tagname("status");
        //If anything has a status higher than 399, then something went wrong
        foreach ($status_list as $status)
        {
            if (intval($status->get_content())>399)
            {
                $response->ok=false;
                $failednumbers.=$user_map[$status->get_attribute('address')].','.$status->get_content().',';
            }
        }

        $id_list=$result->get_elements_by_tagname("id");
        foreach ($id_list as $id)
            $response->id=$id->get_content();

        $response->failednumbers=$failednumbers;
        return $response;
    }
}

/**
* Gets the Session header XML
* @param $session The Session ID
* @return The session header XML as a string
**/

function pageone_get_session_header($session)
{
    $doc=domxml_new_doc("1.0");
    //$doc->formatOutput = IS_DEBUGGING;

    $ovHeader=$doc->create_element("ovHeader");
    $doc->append_child($ovHeader);

    $session_id=$doc->create_element("session-id");
    $session_id->set_content($session);
    $ovHeader->append_child($session_id); 

    return $doc->dump_node($ovHeader);
}

/**
* Gets the available account credit
* @return account credit
**/

function pageone_available_credit()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $doc=domxml_new_doc("1.0");
    //$doc->formatOutput = IS_DEBUGGING;

    $getCreditsRequest=$doc->create_element("m:creditsRequest");
    $getCreditsRequest->set_attribute("xmlns:m", SOAP_NS);
    $doc->append_child($getCreditsRequest);

    $result=pageone_send_soap(pageone_get_session_header($session), $doc->dump_node($getCreditsRequest), "getCredits");
    pageone_soap_logout($session);

    if ($result==null)
        return -1;
    else
    {
        $credits=$result->get_elements_by_tagname("creditsRemaining");
        return $credits[0]->get_content();
    }
}

/**
* Returns a list of the alphatags which are valid for this PageOne account
* @return Array of alphatags
**/

function pageone_get_alphatags()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $doc=domxml_new_doc("1.0");
    //$doc->formatOutput = IS_DEBUGGING;

    $msisdnRequest=$doc->create_element("m:getMsisdnRequest");
    $msisdnRequest->set_attribute("xmlns:m", SOAP_NS);
    $doc->append_child($msisdnRequest);

    $result=pageone_send_soap(pageone_get_session_header($session), $doc->dump_node($msisdnRequest), "getMsisdn");
    pageone_soap_logout($session);

    if ($result==null)
        return array();
    else
    {
        $msisdns=$result->get_elements_by_tagname("msisdn");
        $items=array();
        $i=0;
        foreach ($msisdns as $ms)
        {
           $items[$i]=$ms->get_content();
           $i++;
        }
        return $items;
    }
}

/**
* Creates an XML document object from the supplied data
* @param $xmldata The XML to process
* @return The XML document
**/

function pageone_get_xml_document($xmldata)
{
    $doc=domxml_open_mem($xmldata);
    return $doc;
}

/*****Callback methods*****/

/**
* Gets the Session header XML
* @param $session The Session ID
* @return The session header XML as a string
**/

function pageone_get_callback_session_header($session)
{
    $doc=domxml_new_doc("1.0");

    $pageoneHeader=$doc->create_element("m:pageoneHeader");
    $pageoneHeader->set_attribute("xmlns:m", SOAP_CALLBACK_NS);
    $doc->append_child($pageoneHeader);

    $session_id=$doc->create_element("session-id");
    $session_id->set_content($session);
    $pageoneHeader->append_child($session_id); 

    return $doc->dump_node($pageoneHeader);
}

/**
* Adds a call back service URL to the PageOne server
* @param $session The server session to use
* @param $host The host URL to add
* @param $request soap element name for the call back type should be "deliveryReportListenerRequest" or
*                 "receivedMessageListenerRequest"
* @param $method soap method to call. Should be "registerDeliveryReportListener" or "registerReceivedMessageListener"
* @return $object->value=pageone response code $object->text=Textual description of the response
**/

function pageone_add_callback_service($session, $host, $request, $method)
{
    $doc=domxml_new_doc("1.0");

    $addCallBackRequest=$doc->create_element("m:".$request);
    $addCallBackRequest->set_attribute("xmlns:m", SOAP_CALLBACK_NS);
    $doc->append_child($addCallBackRequest);

    $wsdlUrl=$doc->create_element("wsdlUrl");
    $wsdlUrl->set_content($host); 
    $addCallBackRequest->append_child($wsdlUrl);

    $result = pageone_send_soap(pageone_get_callback_session_header($session), $doc->dump_node($addCallBackRequest), $method, SOAP_CALLBACK_URL, SOAP_CALLBACK_NS);

    return pageone_check_callback_response($result);
}

/**
* Removes a call back service URL to the PageOne server
* @param $session The server session to use
* @param $entryID The id of the callback to remove
* @return $object->value=pageone response code $object->text=Textual description of the response
**/

function pageone_remove_callback($session, $entryID)
{
    global $CFG;
    if (IS_DEBUGGING)
        echo "Removing callback ".$entryID."<br />";

    $doc=domxml_new_doc("1.0");

    $removeCallBackRequest=$doc->create_element("m:unregisterListenerRequest");
    $removeCallBackRequest->set_attribute("xmlns:m", SOAP_CALLBACK_NS);
    $doc->append_child($removeCallBackRequest);

    $id=$doc->create_element("id");
    $id->set_content($entryID); 
    $removeCallBackRequest->append_child($id);

    $result = pageone_send_soap(pageone_get_callback_session_header($session),
        $doc->dump_node($removeCallBackRequest), "unregisterListener", SOAP_CALLBACK_URL, SOAP_CALLBACK_NS);

    return pageone_check_callback_response($result);
}

/**
* Checks the callback add/remove call response
* @param $result The callback response
* @param $entryID The id of the callback to remove
* @return $object->value=pageone response code $object->text=Textual description of the response
**/

function pageone_check_callback_response($result)
{
    if ($result==null)
    {
        $data->value=-1;
        $data->text=get_string("server_fail", "block_pageone");
        return $data;
    }
    else
    {
        $status=$result->get_elements_by_tagname("status");
        $data->value=intval($status[0]->get_content());
        $data->text=pageone_get_xml_attribute("description", $status[0]->attributes());
        return $data;
    }
}

/**
* Lists the callback services registered on the PageOne server
* @param $session The server session to use
* @return An Array of values in the following format $a[$index]->entryID=The callback ID
*         $a[$index]->service=The callback type, should be CALLBACK_REPLY or CALLBACK_DELIVERY
*         $a[$index]->host=The registered URL
**/

function pageone_list_callbacks($session)
{
    if (IS_DEBUGGING)
        echo "Listing callbacks<br />";

    $doc=domxml_new_doc("1.0");

    $listRequest=$doc->create_element("m:listListenersRequest");
    $listRequest->set_attribute("xmlns:m", SOAP_CALLBACK_NS);
    $doc->append_child($listRequest);

    $result = pageone_send_soap(pageone_get_callback_session_header($session), $doc->dump_node($listRequest),
        "listListeners", SOAP_CALLBACK_URL, SOAP_CALLBACK_NS);

    if ($result==null)
        return array();
    else
    {
        $items=array();
        $services=$result->get_elements_by_tagname("listenerWsdlUrl");
        $loop=0;
        foreach ($services as $service)
        {
            $items[$loop]->entryID=pageone_get_xml_attribute("id", $service->attributes());
            $type=pageone_get_xml_attribute("type", $service->attributes());
            if ($type=="InboundListener")
                $items[$loop]->service=CALLBACK_REPLY;
            else
            if ($type=="DeliveryRecieptListener")
                $items[$loop]->service=CALLBACK_DELIVERY;

            $items[$loop]->host=$service->get_content();
                $loop++;
        }
        return $items;
    }

}

/**
* Finds an attribute in the list of attributes
* @param $name The name of the attribute
* @param $att The attribute list
* @return The attribute value or null if not found
**/

function pageone_get_xml_attribute($name, $att)
 {
     foreach($att as $i)
     {
         if($i->name()==$name)
             return $i->value();        
     }

     return null;
 }


?>
