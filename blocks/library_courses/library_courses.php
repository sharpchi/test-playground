<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
class block_library_courses extends block_base {
  function init() {

	$this->title   = get_string('library_courses', 'block_library_courses');
	
	$this->title = 'Library Courses';
	
	$this->version = 2004111200;
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
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    #$this->content->text   = 'The content of our SimpleHTML block!';  // hard-coded
	
	// $this->content->text   = $this->config->text; // taken from data entered in textarea box
	 $this->content->text  = 'test';
		 
//	 $this->content->text   = '<img src="'. $CFG->httpsthemewww .'/'. current_theme() .'/pix/iip.jpg">'; 
	 
	 
    
//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return TRUE;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 