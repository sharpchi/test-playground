<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
class block_email_category extends block_base {
  function init() {

	$this->title   =    'Email Category'   ; //get_string('iip_ad', 'block_iip_ad');
	
	 
	$this->version = 2010060410;
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
  
	
	
	function instance_allow_multiple() {  // allow any number of instances of the SimpleHTML block in any course.
										// method instance_allow_config() that was already defined became obsolete.					
	  return true;
	}
	
  function get_content() {
	  
	  global $CFG, $id;
	  
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    #$this->content->text   = 'The content of our SimpleHTML block!';  // hard-coded
	
	// $this->content->text   = $this->config->text; // taken from data entered in textarea box
 
  //$this->content->text   = '<p><img src="'. $CFG->httpsthemewww .'/'. current_theme() .'/pix/iip.jpg"></p>'; 
	
	// get the course id from the URL
	//$this->content->text   = '<p>Course ID = '.$id.'</p>'; 
	
	
	// then get the context ID to use below
		
	$sql = 'select id from mdl_context where instanceid =  ' . $id ;
	$res = mysql_query($sql);
	list ($contextid) = mysql_fetch_row($res);
	
	// get the category for the course from course ID 
		
	$sql = 'select category from mdl_course where id =  ' . $id ;
	$res = mysql_query($sql);
	list ($cat) = mysql_fetch_row($res);
	

	$this->content->text   .= '<p><a href="'.$CFG->wwwroot .'/user/index.php?contextid='.$contextid.'&get_all_catgegory=yes&cat='.$cat.'&perpage=5000">Email category</a></p>'; 
	
	$this->content->text   .= '<p><a href="'.$CFG->wwwroot .'/user/index.php?contextid='.$contextid.'&filter_by_year=yes&get_all_catgegory=yes&cat='.$cat.'&perpage=5000">Email year group</a></p>'; 

	
	
    
//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return false;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 