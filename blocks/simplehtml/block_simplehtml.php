<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
class block_simplehtml extends block_base {
  function init() {

	$this->title   = get_string('simplehtml', 'block_simplehtml');
	
	$this->title = 'Select background';
	
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
	
	 
	 $this->content->text   = '<p><a href="index.php?style=1">Prospectus Swish - 1000 pixels (no repeat)</a><br />'; // bg1.jpg
	 
	  $this->content->text   = '<p><a href="index.php?style=1_m">Prospectus Swish - 1000 pixels (reflected)</a><br />'; // bg1.jpg
	 
	 
	 
	  $this->content->text   .= '<p><a href="index.php?style=5">Prospectus Swish - 1800 pixels</a><br /><hr>'; // bg1.jpg
	 
	 $this->content->text   .= '<a href="index.php?style=2">Prospectus Turquoise - 1800 pixels</a><br /><hr>'; 
	 
	  $this->content->text   .= '<a href="index.php?style=6">Prospectus Cover - 1000 pixels</a><br />'; 
	    $this->content->text   .= '<a href="index.php?style=7">Prospectus Cover - 1800 pixels</a><br /><hr>';
	 //6ac1bc
	 
	 
	 $this->content->text   .=  '<a href="index.php?style=3">White</a><br />';
	 $this->content->text   .= '<a href="index.php?style=4">Cream</a><br /><hr>';
	 
	  $this->content->text   .= '<a href="index.php?style=8">chi.ac.uk</a><br /><hr>';
	  
	  
	   $this->content->text   .= '<a href="index.php?style=9">Swoosh (Matt)</a><br />';
	  $this->content->text   .= '<a href="index.php?style=10">Swoosh dark(Matt)</a><br />';
	  	  $this->content->text   .= '<hr><a href="index.php?style=none">turn off style</a><br />';
	 
	
	$this->content->text   .= '</p>';
    
//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return TRUE;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 