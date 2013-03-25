<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
class block_student_handbook extends block_base {
  function init() {

	$this->title   =    'Student Information Links'   ; //get_string('iip_ad', 'block_iip_ad');
	
	 
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
	

 	

	
	
	$this->content->text   .= '<p><a href="https://ex2.chi.ac.uk/services/handbooks/student/studenthandbook.cfm" target="_blank">Student Handbook</a><br /><br /><a href="https://ex2.chi.ac.uk/documents/uploads/CHI%20UNI%20INTERMISSION%20FORM.pdf" title="Intermission Form" target="_blank">Intermission form</a><br />
	<a href="https://ex2.chi.ac.uk/documents/uploads/CHI%20UNI%20CHANGE%20REGISTRATION%20FORM.pdf" title="Change in registration form" target="_blank">Change in Registration form</a><br /><a href="https://ex2.chi.ac.uk/documents/uploads/CHI%20UNI%20COUNCIL%20TAX%20FORM.pdf" target="_blank">Council Tax Exemption form</a><br /><br />
	<a href="http://staffmoodle.chi.ac.uk/course/view.php?id=62865" target="_blank">Module Selections</a>
	
	<br /> <br />
	
	<a href="http://www.chi.ac.uk/about-us/how-we-work/semester-dates" target="_blank">Semester Dates</a>
	</p>';
	
	
    $this->content->text   .= '<p><a href="http://www.chi.ac.uk/disabilitydyslexiaservice/staff.cfm" target="_blank">Disability/dyslexia for staff</a><br />'; 
	$this->content->text   .= '<a href="http://www.chi.ac.uk/disabilitydyslexiaservice/students.cfm" target="_blank">Disability/dyslexia for students</a></p>';
	
		$this->content->text   .= '<a href="https://ex2.chi.ac.uk/documents/uploads/EXTERNAL-ORGANISATION-LETTER.pdf" target="_blank">External Organisation Letter Form</a></p>';
	
	 

//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return false;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 