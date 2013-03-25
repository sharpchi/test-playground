<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head><title>Oventus Liquid Callback generator</title></head>
<body>

<?php

/**
* This is a test callback generator. It allows a developer to create callbacks to test callback server code.
* @author Tim Williams
* @package pageone
**/

    /*****Defines the WSDL file for the service. Only necessary if you are using a proxy class*****/
    define ("CALLBACK_WSDL", "http://dev.autotrain.org/moodle/blocks/pageone/callback.php?wsdl");

    /*****This must be set to point at your callback server*****/
    define ("CALLBACK_SERVER", "http://dev.autotrain.org/moodle/blocks/pageone/callback.php");

    /*****These can be set to provide some quick defaults for the test forms*****/
    $reply->source="";
    $reply->destination="";
    $reply->text="";

    $report->id="";
    $report->source="";
    $report->destination="";

    /*****End of defaults*****/

    /*****Load the nusoap classes*****/

    if (!class_exists("nusoap_base"))
         require_once("lib/nusoap.php");

    if ($_POST['action']=="reply")
    {
        echo "<p>Sending message</p>";

        $client=new nusoap_client(CALLBACK_WSDL, true);
        if (!check_error($client, "Constructor error"))
        {
            //$client->setEndpoint(CALLBACK_SERVER);

            /*****Create a proxy class so that we can make SOAP calls with simple PHP method calls*****/
            $proxy = $client->getProxy();

            $result=$proxy->onMessageReceived(
               array('source' => $_POST['source'], 'destination' => $_POST['destination'], 'messageTime' => get_time(), 'text' => $_POST['text']));

            check_result($proxy, $result);
        }
     }
    else
    if ($_POST['action']=="report")
    {
        echo "<p>Sending delivery report</p>";
        //The proxy class is incapable of handling the transactionID attribute correctly, so do this request by hand
        send_callback("onDeliveryReport", get_report_body($_POST['id'], $_POST['source'], $_POST['destination'], $_POST['result']));
    }

    /****Utility Methods*****/

    /**
    * Get a suitably formatted time string
    * @return UTC format time string
    **/

    function get_time()
    {
        //PHP 5
        $t = date_create(); 
        return $t->format("Y-m-d\TH:i:s.uP"); 
        //PHP 4
        //return date("Y-m-d\TH:i:s.uP"); 
    }

    /**
    * Result checking method
    * @param $client The SOAP client to check
    * @param $result The result object generated by the request (should only contain errors) 
    **/

    function check_result($client, $result)
    {
        echo '<h3>Sending</h3><textarea cols="100" rows="20">'.$client->request.'</textarea><br />'.
             '<h3>Response</h3><textarea cols="100" rows="20">'.$client->response.'</textarea><br />';

        if ($client->fault)
        {
            echo '<h2>Fault</h2><pre>';
            print_r($result);
            echo '</pre>';
        }
        else
            check_error($client, "Send error");

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

    /**
    * This gets around not being able to set the transactionID when using a proxy
    * @param $id Transaction id
    * @param $source The MSISDN to be used to send the message
    * @param $destination The destination MSISDN of the message
    * @param $result The Oventus result code to send
    **/

    function get_report_body($id, $source, $destination, $result)
    {
        return "<onDeliveryReport xmlns=\"http://jaxb.liquidcallback.pageone.com\" transactionID=\"".$id."\">\n"
            ."<source>".$source."</source>\n"
            ."<destination>".$destination."</destination>\n"
            ."<receiptTime>".get_time()."</receiptTime>\n"
            ."<resultCode>".$result."</resultCode>\n"
            ."</onDeliveryReport>\n";
    }

    /**
    * Sends a callback
    * @param $method The SOAP method to call
    * @param $body The SOAP message body
    **/

    function send_callback($method, $body)
    {
        echo "<h3>Performing '".$method."' callback test</h3>";
        $client=new nusoap_client(CALLBACK_SERVER);
        if (check_error($client, "Constructor error"))
            return;

        $soapxml = $client->serializeEnvelope($body, "", array(), 'document', 'literal');
        $result = $client->send($soapxml, $method);

        check_result($client, $result);
    }
?>

<hr />

<h1>Generate message reply</h1>
<form action="callback_test.php" method="post">
<input type="hidden" name="action" value="reply" />
<table>
 <tr>
  <td>Sender</td>
  <td><input type="text" name="source" value="<?php echo $reply->source ?>" /></td>
 </tr><tr>
  <td>Destination</td>
  <td><input type="text" name="destination" value="<?php echo $reply->destination ?>" /></td>
 </tr><tr>
  <td>Message</td>
  <td><textarea cols="60" rows="5" name="text"><?php echo $reply->text ?></textarea></td>
 </tr>
</table>
 <input type="submit" value="Send" />
</form>

<h1>Generate delivery report</h1>
<form action="callback_test.php" method="post">
<input type="hidden" name="action" value="report" />
<table>
 <tr>
  <td>Message ID</td>
  <td><input type="text" name="id" value="<?php echo $report->id ?>" /></td>
 </tr>
 <tr>
  <td>Sender</td>
  <td><input type="text" name="source" value="<?php echo $report->source ?>" /></td>
 </tr><tr>
  <td>Destination</td>
  <td><input type="text" name="destination" value="<?php echo $report->destination ?>" /></td>
 </tr
  <td>Result Code</td>
  <td>
   <select name="result">
    <option value="200">Sent (200)</option>
    <option value="201">Pending (201)</option>
    <option value="202">Group Pending (202)</option>
    <option value="203">Valid Login (203)</option>
    <option value="400">No access to this service (404)</option>
    <option value="401">Message not sent (401)</option>
    <option value="402">Service currently not available (402)</option>
    <option value="403">Could not send email (403)</option>
    <option value="501">Bad character (501)</option>
    <option value="502">Invalid Number(502)</option>
    <option value="503">Unknown called address (503)</option>
    <option value="504">Unknown pager (504)</option>
    <option value="551">Failed security (551)</option>
    <option value="552">Inactive subscriber (552)</option>
    <option value="553">Max limit reached (553)</option>
    <option value="554">Please contact service provider (554)</option>
    <option value="555">System error (555)</option>
    <option value="556">Unknown (556)</option>
    <option value="558">Process failure (558)</option>
   </select>
  </td>
 </tr>
</table>
 <input type="submit" value="Send" />
</form>
</body>
</html>
