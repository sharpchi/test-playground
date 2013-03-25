<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
class block_library_courses extends block_base {
  function init() {

	$this->title   =    'Library Resources'   ; //get_string('iip_ad', 'block_iip_ad');
	
	 
	$this->version = 2010080610;
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
	  
	  global $CFG;
	  
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content   =  new stdClass;
    
	
	
	// get all courses in cat 86
	
 
 
 	$sql = 'select id, fullname , summary from mdl_course where category = 86 order by fullname';
	
	$lib_course_array = get_records_sql($sql, $limitfrom='', $limitnum='');
	
 	//echo '<pre>'; 	print_r ($lib_course_array ); 	echo '</pre>';
	 
	
	foreach ($lib_course_array as $lib_course_id ){
		
		 
		
 			if ( !empty(  $lib_course_id->summary ) ) $link_title =  strip_tags( $lib_course_id->summary);
		    else  $link_title = $lib_course_id->fullname;		 
		
			$this->content->text   .= '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$lib_course_id->id.'" target="_blank">'. $link_title. '</a><br>'; 
		
		
	} // end foreach
	
//	$this->content->text   .= '<hr><a href="'.$CFG->wwwroot.'/course/category.php?id=86">All...</a><br>'; 
	
	
	 
	
	
    return $this->content;
  }
  
  
  function has_config() {
  return false;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 