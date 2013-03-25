<?PHP 

class block_activity_locking extends block_base {
    function init() {
        $this->title = get_string('activitylocking', 'format_locking');
        $this->version = 2009070400;
    }

    function get_content() {
        global $USER, $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        
        if (empty($this->instance)) {
            return $this->content;
        }
    
        $course = get_record('course', 'id', $this->instance->pageid);
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('moodle/course:manageactivities', $context)) {
       
        	$courselocks = count_records('course_module_locks', 'courseid', $course->id);
        
        	$this->content->text .= '<p>There are '.$courselocks.' locks within this course.</p>';
        }
        
        return $this->content;
    }
}

?>