<?php // disable on the live server!!!!


 
	
	
	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
 	
	*/
class block_sits_import extends block_base {
  function init() {

	$this->title   = get_string('sits_import', 'block_sits_import');
	
	$this->title = 'Sits import';
	
	$this->version = 2004111200;
	
	
	
	
	
	
	
  }
  
  
  #function instance_allow_config() {  // allows editing - shows the 'edit' icon... (is obsolete if function instance_allow_multiple is defined )
  #return true;
#}
  
  
  
  function specialization() {   // allows us to edit the title.
  if(!empty($this->config->title)){
    $this->title = $this->config->title;   // set the title to the one in the editor
  }else{
    $this->config->title = 'Sits import';
  }
  if(empty($this->config->text)){
    $this->config->text = 'Sits import';
  }    
}
  
	
	
	function instance_allow_multiple() {  // allow any number of instances of the SimpleHTML block in any course.
										// method instance_allow_config() that was already defined became obsolete.					
	  return true;
	}
	
  function get_content() {
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    #$this->content->text   = 'The content of our SimpleHTML block!';  // hard-coded
	
	
	$sql = 'select  time from import_log WHERE import_type= "users" order by date desc LIMIT 1 ';
	$res = mysql_query($sql);
	list ($this->user_last_import) = mysql_fetch_array($res);
	
	$sql = 'select  time from import_log WHERE import_type= "courses" order by date desc LIMIT 1 ';
	$res = mysql_query($sql);
	list ($this->course_last_import) = mysql_fetch_array($res);
	
	$sql = 'select  time from import_log WHERE import_type= "enrol" order by date desc LIMIT 1 ';
	$res = mysql_query($sql);
	list ($this->enrol_last_import) = mysql_fetch_array($res);
	
	$sql = 'select  time from import_log WHERE import_type= "tutors" order by date desc LIMIT 1 ';
	$res = mysql_query($sql);
	list ($this->tutor_last_import) = mysql_fetch_array($res);
	
	
	
	
	
	
	// $this->content->text   = $this->config->text; // taken from data entered in textarea box
	
	 
	 $this->content->text   = '<p>These must be run in order!</p><p><a href="'.$CFG->root.'/admin/import.php?type=user&outputtype=browser">Import Users</a> (last run '.$this->user_last_import.')<br />';  
	 
	  $this->content->text   .= '<a href="'.$CFG->root.'/admin/import.php?type=course&outputtype=browser">Import courses</a> (last run '.$this->course_last_import.')<br />';  
	  	  $this->content->text   .= '<a href="'.$CFG->root.'/admin/import.php?type=students&outputtype=browser">Enrol students</a> (last run '.$this->enrol_last_import.')<br />';  

	  $this->content->text   .= '<a href="'.$CFG->root.'/admin/import.php?type=tutors&outputtype=browser">Enrol tutors</a> (last run '.$this->tutor_last_import.')<br />';  

	 
	   
	$this->content->text   .= '</p>';
	
	
	##########  this code below is essential - stops it running on live servers  ############
	
	/* make sure it's updated should domain names change etc  */
	
	if ( $_SERVER['SERVER_NAME'] == 'moodle.chi.ac.uk'){
		
	$this->content->text   = '<p>Sorry, this block cannot run on live servers for security reasons.</p>';	
		
	}
	$this->content->text   .= '<hr>';  
	  $this->content->text   .= '<a href="'.$CFG->root.'/admin/manual_enrolements.php">Manual Enrolements</a><br />';  
	
	 $this->content->text   .= '<a href="'.$CFG->root.'/admin/import_report.php">Reports</a><br />';  
	
	
	
	
    
//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return TRUE;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 