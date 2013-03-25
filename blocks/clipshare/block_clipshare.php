<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
	
	

class block_clipshare extends block_base {
  function init() {

	$this->title   =    'Your Videos'   ; //get_string('iip_ad', 'block_iip_ad');
	
	 
	$this->version = 20101110;
  }
  
  
  #function instance_allow_config() {  // allows editing - shows the 'edit' icon... (is obsolete if function instance_allow_multiple is defined )
  #return true;
#}
  

  
  function specialization() {   // allows us to edit the title.
  if(!empty($this->config->title)){
    $this->title = $this->config->title;   // set the title to the one in the editor
  }else{
    $this->config->title = 'Some title ...';
  }
  if(empty($this->config->text)){
    $this->config->text = 'Some text ...';
  }    
}
  
  /*
	
	function instance_allow_multiple() {  // allow any number of instances of the SimpleHTML block in any course.
										// method instance_allow_config() that was already defined became obsolete.					
	  return true;
	}
		*/
  function get_content() {
	   
	  global $CFG, $id,  $USER;  
	  
	$current_cs_vids = '';
	
	/* access clipshare DB  */
	
	$cs_conn = mysql_connect($CFG->CS_DBHOST, $CFG->CS_DBUSER, $CFG->CS_DBPASSWORD) or die  ('Error connecting to CS mysql. user .  '.$CFG->CS_DBUSER . ' pw ' .$CFG->CS_DBPASSWORD . ' '   . mysql_error() );
	
	mysql_select_db('clipshare') or die('cannot select CS db '. $CFG->CS_DBNAME. ' ' . mysql_error());
	  
	$sql = "select * from video v, signup s WHERE s.UID   = v.UID  AND s.email = '".$USER->email."' ORDER BY adddate DESC LIMIT 10";
	
	 
    if (!$res = mysql_query($sql)) echo 'error in query ' . mysql_error();
	  	
	
	while ($row = mysql_fetch_array($res) ) {
		
		$current_cs_vids .= '<img src="'. $CFG->wwwroot .'/pix/f/avi.gif"> <a href="'.$CFG->wwwroot .'/'.$CFG->CS_moodle_page .'?vkey='.$row['vkey'] .'">'. $row['title']. '</a><br>';
		
		 
	} // end while
	  
	  
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
	
	
    #$this->content->text   = 'The content of our SimpleHTML block!';  // hard-coded
	
	// $this->content->text   = $this->config->text; // taken from data entered in textarea box
 
  //$this->content->text   = '<p><img src="'. $CFG->httpsthemewww .'/'. current_theme() .'/pix/iip.jpg"></p>'; 
	
	// get the course id from the URL
	//$this->content->text   = '<p>Course ID = '.$id.'</p>'; 
	
	
	/*
	$sql = 'select id from mdl_context where instanceid =  ' . $id ;
	$res = mysql_query($sql);
	list ($contextid) = mysql_fetch_row($res);
		'
	$sql = 'select category from mdl_course where id =  ' . $id ;
	$res = mysql_query($sql);
	list ($cat) = mysql_fetch_row($res);
	*/
	 
	 
	 
		
	$this->content->text   .= '<p><a href="'.$CFG->wwwroot .'/'.$CFG->CS_moodle_page.'">Upload video</a></p>'; 
 	
	$this->content->text   .= '<h2>Your current videos</h2>'; 

	$this->content->text   .= '<p>'.$current_cs_vids.'</p>';
	
    
//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return false;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 