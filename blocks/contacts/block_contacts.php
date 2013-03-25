<?php
//    MOODLE CONTACTS BLOCK v0.4 - Copyright (C) 2008
//    Display your list of contacts in a block.

//    Design & Coding:  Dale Davies (Liverpool Community College)
//    Email:  dale.davies@liv-coll.ac.uk

//    See README.txt (file) for more details & instructions.

//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.

//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>

//    See LICENSE.txt (file) for details of the GNU General Public License.

class block_contacts extends block_base {

    function init() {  // OK, lets get going!
        $this->title = get_string('blocktitle', 'block_contacts');
        $this->version = 2007101509;
    }
	
	function applicable_formats() {
        return array('all' => true);
    }
	
	function specialization() {  //  This allows the title to be changed on an instance-by-instance basis.
		global $CFG;
		if ($this->config->title == '') {
			$this->title = get_string('newblocktitle', 'block_contacts');
		} else {
			$this->title = $this->config->title;
		}
    }

	function has_config() {
      return true;
    } //has_config	

    function get_content() { // Main content function...
		global $CFG;
		require_once($CFG->dirroot.'/message/lib.php');
		
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
		$this->content->text = '';  //  Initialise text.
		$this->content->footer = '';  //  We dont need no footer!

		if (isloggedin()){ // Check if user is logged in...
				
			// Buffer output of message_print_contacts.  Place into $contacts_output ...
			ob_start();  // Start output buffer.
			message_print_contacts();
			$contacts_output = ob_get_contents();
			ob_clean(); // Clear output buffer.
		
			// Remove empty table rows for consistent layout...
			$contacts_cleaned = str_replace('<tr><td colspan="3">&nbsp;</td></tr>','',$contacts_output);
			
			$this->content->text .= $contacts_cleaned; // Put output of message_print_contacts into block.	
				
			$this->content->text .= '<div id="add_contacts"><a href="'.$CFG->wwwroot.'/message/index.php?tab=search" onclick="this.target=\'message\'; return openpopup(\'/message/index.php?tab=search\', \'message\', \'menubar=0,location=0,scrollbars,status,resizable,width=500,height=600\', 0);">'.get_string('addcontact', 'block_contacts').'</a></div>';			
		} else { // If user is not logged in, display a nice message...
			$this->content->text .= '<p class="notloggedin">'.get_string('notloggedin', 'block_contacts').'</p>';
		}
		
		//  Finally!  Spit it all out on the page...
        return $this->content;
    } 
}
?>