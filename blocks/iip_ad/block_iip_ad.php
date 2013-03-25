<?php // this is just a test 

	/*
	
	following instructions at : http://docs.moodle.org/en/Blocks_Howto
	
	this can be deleted along with its folder 'simplehtml'
	
	*/
class block_iip_ad extends block_base {
  function init() {

	$this->title   =    'Library Search'   ; //get_string('iip_ad', 'block_iip_ad');
	
	 
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
	  
	  global $CFG;
	  
    if ($this->content !== NULL) {
      return $this->content;
    }
 
    $this->content         =  new stdClass;
    #$this->content->text   = 'The content of our SimpleHTML block!';  // hard-coded
	
	// $this->content->text   = $this->config->text; // taken from data entered in textarea box
 
  //$this->content->text   = '<p><img src="'. $CFG->httpsthemewww .'/'. current_theme() .'/pix/iip.jpg"></p>'; 

	$this->content->text   = '<form name="searchForm" method="get" action="http://prism.talis.com/chi-ac/items"  target="_blank" >
<input type="hidden" name="searchType" value="briefSearch">
 	    <table width="100%" border="0">
	      <tr>
	        <td valign="top"><label for="StandardSearchTextBox&lt;B&gt;Search&lt;/B&gt;2"><b>Search:</b></label><br />
	              <input type="text" name="query" value="" style="width: 100%;" />
	             </td>
          </tr>
	      <tr>
	        <td><input class="SearchButton" id="StandardSearchButtonSubmit" name="submit" type="submit" value="Search" /></td>
	      </tr>
		    <tr>
	        <td><a href="http://prism.talis.com/chi-ac/advancedsearch" title="More search options" target="_blank">More search options</a></td>
	      </tr>
  		   <tr>
	        <td><a href="https://prism.talis.com/chi-ac/borrowerservices?messageNlsid=borrowerservices_notloggedin&referer=%2Fchi-ac%2Faccount" title="Your account" target="_blank">Your account</a></td>
	      </tr>
 		</table> 
</form>'; 

    
//	$this->content->footer = 'This is the footer.';
 
    return $this->content;
  }
  
  
  function has_config() {
  return false;
}
  
 



}   // Here's the closing curly bracket for the class definition

 // and here's the closing PHP tag from the section above.
?> 