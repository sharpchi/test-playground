<?php
class block_sent_emails extends block_base {
  function init() {
    $this->title   = "Your Sent Emails";
    $this->version = 2004111200;
  }
  
  function get_content() {
    if ($this->content !== NULL) {
      return $this->content;
    }
    global $USER;
    $limit = 5; //change number of emails to display
    
    
    //get email recordset
    $strsql = "SELECT id, emaildate, emailsubject from mdl_email WHERE emailfrom=$USER->id ORDER BY emaildate DESC LIMIT $limit";
    $emails = get_records_sql($strsql);
    

    $this->content         =  new stdClass;
    
    //print email recordset to table
    
    if (!empty($emails)) {
        $strTable = '<table border="1" width="100%"><tr><th>Date</th><th>Subject</th></tr>';
        foreach ($emails as $email){
            $emaildate = date('d/m/y', strtotime($email->emaildate));
            $emailsubject = $email->emailsubject;
            
            $strTable .= "<tr><td>$emaildate</td><td>$emailsubject</td></tr>"; //add in session variable to pass to email.php page.
        }
        $strTable .="</table>";
        $this->content->text   =  $strTable;
    }
    else{
        $this->content->text = "";
    }
    
    
    
    $this->content->footer = '<a href="'.$CFG->wwwroot.'/email.php">More emails</a>';
 
    return $this->content;
  }
  
}   
?>

 