<?PHP //$Id: block_participants.php,v 1.33.2.2 2008/03/03 11:41:03 moodler Exp $

class block_participants extends block_list {
    function init() {
        $this->title = get_string('people');
        $this->version = 2007101509;
    }

    function get_content() {

        global $CFG, $COURSE, $id;  // $id added RH 5/5/10 

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        // the following 3 lines is need to pass _self_test();
        if (empty($this->instance->pageid)) {
            return '';
        }
        
        $this->content = new object();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        /// MDL-13252 Always get the course context or else the context may be incorrect in the user/index.php
        if (!$currentcontext = get_context_instance(CONTEXT_COURSE, $COURSE->id)) {
            $this->content = '';
            return $this->content;
        }
        
        if ($COURSE->id == SITEID) {
            if (!has_capability('moodle/site:viewparticipants', get_context_instance(CONTEXT_SYSTEM))) {
                $this->content = '';
                return $this->content;
            }
        } else {
            if (!has_capability('moodle/course:viewparticipants', $currentcontext)) {
                $this->content = '';
                return $this->content;
            }
        }

        $this->content->items[] = '<a title="'.get_string('listofallpeople').'" href="'.
                                  $CFG->wwwroot.'/user/index.php?contextid='.$currentcontext->id.'&perpage=5000">'.get_string('participants').'</a>';
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" />';


			/*  email all in category */
			
	 
 
	 

		
		
		$id_obj =  get_record_sql('select id from mdl_context where instanceid =  ' . $id);	
		
		$contextid = $id_obj->id;
		 
		 
	//	  $this->content->items[] = 'select id from mdl_context where instanceid =  ' . $id;
		 
		unset($id_obj);
		
		$id_obj =  get_record_sql('select category from mdl_course where id =  ' . $id);	
				
				$cat = $id_obj->category;
				
				unset($id_obj);
				
				
        $this->content->items[] = '<a title="'.get_string('listofallpeople').'" href="'.$CFG->wwwroot .'/user/index.php?contextid='.$contextid.'&get_all_catgegory=yes&cat='.$cat.'&perpage=5000">'.get_string('emailallparticipants').'</a>';
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" />';
		
		
		
		/*  email all in year group within category */
		
	 
	
	
		

        $this->content->items[] = '<a title="'.get_string('emailallyrgroup1_alt').'" href="'.$CFG->wwwroot .'/user/index.php?contextid='.$contextid.'&filter_by_year=yes&get_all_catgegory=yes&cat='.$cat.'&perpage=5000&year_group=1">'.get_string('emailallyrgroup1').'</a>';
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" />';
		
	 
      $this->content->items[] = '<a title="'.get_string('emailallyrgroup2_alt').'" href="'.$CFG->wwwroot .'/user/index.php?contextid='.$contextid.'&filter_by_year=yes&get_all_catgegory=yes&cat='.$cat.'&perpage=5000&year_group=2">'.get_string('emailallyrgroup2').'</a>';
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" />';
		
		
		
		     $this->content->items[] = '<a title="'.get_string('emailallyrgroup3_alt').'" href="'.$CFG->wwwroot .'/user/index.php?contextid='.$contextid.'&filter_by_year=yes&get_all_catgegory=yes&cat='.$cat.'&perpage=5000&year_group=3">'.get_string('emailallyrgroup3').'</a>';
        $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/users.gif" class="icon" alt="" />';



        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

}

?>
