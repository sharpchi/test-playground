<?PHP


/**
 * Main block class for myCourses block
 *
 * @package myCourses
 * @category blocks
 * @author Rosario Carco, Fachhochschule Nordwestschweiz
 * @version 1.9.06, 2009052201
 * @uses $CFG->dirroot . '/blocks/myCourses/HTML_TreeMenu-1.2.0/TreeMenu.php'
 *
 * I took the myCourses Block which was originally released as part of the FN 
 * Moodle.
 *
 * This block was partially revised and enhanced by Julian Ridden and Mario 
 * Schellenberger
 *
 * Nevertheless I decided to reuse the original FN-Code which was labeled as 
 * follows, the file's date being 2006-oct-04, but integrating the mentioned
 * enhancements and revisions:
 *
 * $Id: block_FN_my_courses.php,v 1.1.2.1 2006/06/23 14:59:40 mchurch Exp $
 *
 * See the following thread: http://moodle.org/mod/forum/discuss.php?d=67494
 *
 * I modified the code to address the following issues:
 *
 * Development history:
 *
 * Date      | Author | Text (newest on top, please!)
 ************+********+******************************
 *
 * 2009/05/14 Cr - Started to implement new features based on the 06 Beta
 *                 version:
 *                 - new option to toggle short or long names in the display
 *                   line or tool tip help
 *                 - new option to show/hide active links for folders to
 *                   navigate to corresponding Moodle category page
 *                 - new option to show a flat list of courses instead of a
 *                   hierarchical tree-structure with folders
 *                 - @todo convert some or all of these options to individual
 *                         user options. As this block is a user block, I feel
 *                         that the user should be able to configure it to his
 *                         taste. On the other hand, if this block is combined
 *                         with the siteNavigation block, it still makes sense
 *                         that the site-Admin chooses the best combination of
 *                         options for everybody.
 *                 - new option to show/hide a course search box
 *                 - checked all indentations by 4 spaces and split comment
 *                   lines to not exceed 80 chars horizontally
 *                 - changed all string- and other variables to lowercase
 *                 
 * 
 * 2009/01/30 Cr - Non final release 06 Beta, trying to get back the
 *                 functionality of admins being able to toggle on and off the
 *                 display of their OWN  courses, ie. courses they are enrolled
 *                 in as STUDENTS and TEACHERS
 * 2008/09/26 Cr - changed default options for Admins to not see any course
 *                 which is better if the myCourses Block is placed on the
 *                 front page and there are really many courses
 * 2008/09/08 Cr - option added to show/hide "all courses" link in the footer
 *               - option added to show/hide scroll bars in the block
 * 2008/08/19 Cr - minor fixes and spanish language added.
 * 2008/08/08 Cr - I have been using this myCourses Block now for a couple of
 *                 months and also in the moodle-thread, where I uploaded 
 *                 Version 1.9.02, 2008052601, no one complained. So it seems 
 *                 to be stable and I removed all old FN Code, which previously
 *                 was only commented out. I will put this version as first
 *                 stable release in the thread
 *                 http://moodle.org/mod/forum/discuss.php?d=67494
 *                 and then start working on the new block called
 *                 siteNavigation.
 *
 * Starting Works and Design:
 *
 * 2008/05/09 Cr - last tests with code of Julian Ridden
 * 2008/05/13 Cr - decided to use original FN code. Adapted code to run fine
 *                 in 1.9
 * 2008/05/14 Cr - adapted to reflect Moodle 1.9 programming of blocks
 *                 according to http://docs.moodle.org/en/Development:Blocks
 *                 - removed all code relicts of FN and all code not strictly
 *                   necessary
 *                 - removed also all errors displayed with DEBUGGING on
 *                 - added language support for GE, FR, IT (spanish and 
 *                   portuguese, still open)
 *
 * Note: most of the original points/questions in the next section have been
 *       resolved. See development history above.
 *
 *                 - made TWO different blocks, so that they can be used
 *                   concurrently in different courses and the Front Page:
 *
 *                 a) the original block name and purpose remain the same:
 *                    myCourses
 *                     - show only courses of logged in user
 *                       - What should be displayed if user is the 
 *                         site-administrator?
 *                         - actually admins can choose to show/hide active
 *                           and inactive courses, which makes the most sense
 *                           imho, no myCourses in which they could be
 *                           enrolled. Maybe the original code's intent was 
 *                           another one, which I could not deduce/reconstruct
 *                           from the code. Let me know if there is something I
 *                           missed or if I should restore the previous logic.
 *                         - actually teachers can choose to show/hide 
 *                           myCourses, i.e. only courses in which they teach.
 *                           Part of those courses may be set to "not 
 *                           available to students", i.e. those courses are not
 *                           visible = hidden = inactive. Teachers are not
 *                           allowed to see all courses, so in the block's GUI
 *                           I only show two option buttons: show/hide
 *                           myCourses and show/hide inactive courses.
 *                           [Has been extended on user's demand: site-admins
 *                            get three buttons now to toggle also their own
 *                            courses, i.e. courses they are enrolled as
 *                            teachers or students.]
 *                         - actually normal users can see only the courses 
 *                           they are enrolled in. There is no GUI to allow
 *                           them to set according options.
 *
 *
 *                 b) changed name of second block to better reflect its
 *                    purpose to: siteNavigation this block offers three
 *                    params/settings:
 *
 *                    - siteNavigationByCategory 
 *                      -> if YES, show only categories, no courses for a fast
 *                         display of the whole category-tree (loading 500
 *                         courses and more takes 7 seconds and longer)
 *                      -> if NO, show all categories AND all courses (only 
 *                         appropriate for small sites with few courses, maybe
 *                         less than 100)
 *                      - @todo I am looking at the code of the built in 
 *                              block_admin_tree which also uses open/closed
 *                              folders in a hierarchical tree. I guess that
 *                              this code is faster to render the whole tree
 *                              than the HTML_TreeMenu-1.2.0 actually used.
 *
 *                    - siteNavigation_loginRequired
 *                      -> if YES, only logged in users may use it
 *                      -> if NO, everyone may use it to navigate, login prompt
 *                         appears only when entering a course.
 *
 *
 */

require_once ($CFG->dirroot . '/blocks/myCourses/HTML_TreeMenu-1.2.0/TreeMenu.php');

class block_myCourses extends block_base {

/**
 * Standard block API function for initializing block instance
 * @return void
 */

function init() {
    $this->title = get_string('blocktitle', 'block_myCourses');//Cr: must be 
//initialized here and can be dynamically changed in the specialization
//function, see MoodleDocs:Development:Blocks

    $this->version = 2009052201;//Cr: original FN_my_courses was: 2005061000;
}

/**
 * Standard block API function for initializing/preparing/changing block settings/variables
 * See block_course_summary.php for a good example
 *
 * @uses $COURSE
 * @uses $SITE
 * @uses $CFG
 * @return void
 */

function specialization() {
//At this point, $this->instance and $this->config are available for use. We
//can now change the title, etc. to whatever we want. E.g.
//$this->title = $this->config->variable_holding_the_title

    global $CFG, $COURSE, $SITE;//Cr: $course replaced with $COURSE

//Cr: check and initialize needed block config option variables
    if (!isset ($CFG->block_myCourses_withscrollbars)) {
        $CFG->block_myCourses_withscrollbars = true; //Cr: show scrollbars
    }

    if (!isset ($CFG->block_myCourses_showallcourseslink)) {
        $CFG->block_myCourses_showallcourseslink = false;//Cr: don't display it
    }

    if (!isset ($CFG->block_myCourses_showlongnames)) {
        $CFG->block_myCourses_showlongnames = false;//Cr: display only short
//names and long names in the tool tip help
    }

    if (!isset ($CFG->block_myCourses_showcategorylinks)) {
        $CFG->block_myCourses_showcategorylinks = true;//Cr: display links
    }


    if (!isset ($CFG->block_myCourses_showflatlist)) {
        $CFG->block_myCourses_showflatlist = true;//Cr: display no tree/folders
    }


    if ($this->instance->pageid == SITEID) {//Cr: if this block appears on the
//front page which is course id # 1 and category # 0 in the course table,
        $this->course = $SITE;//start with $SITE which is initialized by
                              //get_site() in lib/setup.php
                              //which normally returns 1
    } else {
//Cr: else block appears on a course page, so start with this course
        $this->course = $COURSE;//Cr: $course replaced with $COURSE
//Cr: According to MoodleDocs:Development:Blocks you could also use
//$this->course = get_record('course', 'id', $this->instance->pageid);
    }

    $this->admin = false;//Cr: initialize local variables
    $this->teacher = false;
    $this->mycact = false;//Cr: do not show active courses, only for admins
    $this->mycinact = false;//Cr: do not show inactive courses, only for admins
                            //    and teachers
    $this->mycmyc = true;//Cr: show myCourses [originally only for teachers
                         //    and students], now also for admins

    if (isadmin()) {//Cr: admins see ALL courses and may choose to show/hide
                    //    them in the header of the block
        $this->set_admin_options();//Cr: to show/hide given courses for admins
        $this->admin = true;
    }

//The next line tests whether the logged in user is also teacher in any course
//From the cross-ref: Determines if a user is a teacher in any course, or an
//                    admin
//bool isteacherinanycourse ([int $userid = 0], [bool $includeadmin = true])
//Set this to 0 if you would just like to test against the currently logged in
//user

    if (isteacherinanycourse()) {//Cr: teachers see ONLY courses they teach in
                                 //    and may choose to show/hide them in the 
                                 //    header of the block
        $this->set_teacher_options();//Cr: to show/hide given courses for 
                                     //    teachers
        $this->teacher = true;//Cr: changed name of member variable 
                              //    $this->marker to $this->teacher to better
                              //    reflect admin and teacher options
    }

//Cr: we no longer use the next line because it is recommended to use only one 
//    title for the block. We load it already in the init() function. If you
//    need a more dynamic title it could be set here.
//    $this->title = get_string('displaytitle', 'block_myCourses');

}

/**
 * Standard block API function to return in which formats this block may be used
 *
 * @return array formats
 */

function applicable_formats() {
   // The block can be used in all course types, but not in modules/activities
    return array (
        'all' => true,
        'site' => true,//Cr: this is probably already included in 'all'
        'mod' => false
    );
}

/**
 * Standard block API function to return the block's preferred width
 *
 * @return integer width
 */

function preferred_width() {//Cr: has to be coordinated with Mario's pix width setting in TreeMenu.php
    return 210;//The default is 180, but the admin_tree on the front page is 210
}

/**
 * Standard block API function for telling if block has configuration
 * @return boolean
 */

function has_config() {//Cr: settings.php is ready to use 
    return true;
}

/**
 * Standard block API function for printing/composing/returning block content
 *
 * @return object $this->content
 */

function get_content() {
    global $CFG;//Cr: use as needed $USER, $THEME, $SESSION

    $enrol = get_string('enrol', 'block_myCourses');

    if ($this->content !== NULL) {//Cr: if there is content, return it for
                                  //    display
        return $this->content;
    }

    $this->content = new stdClass;//Cr: according to 
                                  //    MoodleDocs:Development:Blocks
//Maybe there are still older versions using new object() instead

    $this->content->text = '';//Cr: initialize to empty string in which case
                              //    the block is not displayed
    $this->content->footer = '';//according to MoodleDocs:Development:Blocks

    if (empty ($this->instance)) {//Cr: most blocks have this code, but I can
                                  //    not imagine a block not having been 
        return $this->content;    //    instantiated/added on the front or
    }                             //    any other course page

//Cr: otherwise 'compute' and fill in the content to be displayed in the block

    $this->title = $this->get_header();//Cr: we use member functions here to 
                                       //    avoid cluttering up this one here
    $this->content->text = $this->build_navtree();//Cr: return a  hierarchical
                                                  //    tree or flat list
//Cr: show/hide the "all courses" link
    if ($CFG->block_myCourses_showallcourseslink) {
        $this->content->footer = 
            '<center><a href="' . $CFG->wwwroot . '/course">' . $enrol . '</a></center>';
    } else {
        $this->content->footer = '';
    }
    if ($CFG->block_myCourses_showsearchbox) {//Cr: added option to display
                                              //    moodle course search box
//Cr: simply add output of moodle api function to footer:
//    print_course_search($value="sampleSearchString", //Cr: string in box
//    $return=false, //Cr: whether to echo or return text
//    $format="plain")//Cr: plain, short or navbar format
        $this->content->footer .= print_course_search("",true,"short");
    }

    return $this->content;
}

/**
 *
 * set_admin_options()
 *
 * Cr: extract toggling options to show/hide given courses in the $_GET response
 * for admins
 *
 * @uses $SESSION
 * @var  boolean $this->mycact   to toggle display of active courses
 * @var  boolean $this->mycinact to toggle dispaly of inactive courses
 * @var  boolean $this->mycmyc   to toggle display of myCourses
 *
 */

function set_admin_options() {
    global $SESSION;

    if (isset ($_GET['mycinact'])) {//Cr: show/hide all inactive courses
        $SESSION->mycinact = ($_GET['mycinact'] == 'on') ? true : false;
    } else
        if (!isset ($SESSION->mycinact)) {
            $SESSION->mycinact = false;//Cr: do not show them as default
        }

    if (isset ($_GET['mycact'])) {//Cr: show/hide all active courses
        $SESSION->mycact = ($_GET['mycact'] == 'on') ? true : false;
    } else
        if (!isset ($SESSION->mycact)) {
            $SESSION->mycact = false;//Cr: do not show them as default
        }

//Cr: for debugging
//print_r(array('set admin_options','mycinact',$SESSION->mycinact,'mycact',$SESSION->mycact));

    $this->mycinact = $SESSION->mycinact;//Cr: store settings in local variables for later display
    $this->mycact = $SESSION->mycact;
}

/**
 *
 * set_teachers_options()
 *
 * Cr: changed name of next function from set_marker_options to set_teacher_options to better reflect display options
 * of teachers in analogy with set_admin_options
 *
 * Cr: extract toggling options to show/hide given courses in the $_GET response
 * for teachers. Changed variable name mycmem to mycmyc to better reflect myCourses of teachers, i.e.
 * the courses they teach in
 *
 * @uses $SESSION
 * @var  boolean $this->mycact   to toggle display of active courses
 * @var  boolean $this->mycinact to toggle display of inactive courses
 * @var  boolean $this->mycmyc   to toggle display of myCourses
 *
 */

function set_teacher_options() {
    global $SESSION;

    if (isset ($_GET['mycmyc'])) {//Cr: show/hide all courses the teacher is teaching in
        $SESSION->mycmyc = ($_GET['mycmyc'] == 'on') ? true : false;
    } else
        if (!isset ($SESSION->mycmyc)) {
            $SESSION->mycmyc = true;
        }

    if (isset ($_GET['mycinact'])) {//Cr: show/hide all inactive courses
        $SESSION->mycinact = ($_GET['mycinact'] == 'on') ? true : false;
    } else
        if (!isset ($SESSION->mycinact)) {
            $SESSION->mycinact = true;
        }

    $this->mycmyc = $SESSION->mycmyc;//Cr: store settings in member variables for later display
    $this->mycinact = $SESSION->mycinact;
}

/**
 *
 * get_header()
 *
 * Cr: removed custom title hack, which was not clear any more. But see
 * the comments I made in the code if you want this functionality back:
 * http://moodle.org/mod/forum/discuss.php?d=16076, post of Daryl Hawes.
 *
 * The function composes the title of the block and returns what is needed
 * for admins, teachers, and normal users, i.e. the simple title string or
 * a table containing two icons and links to toggle the display of
 * active, inactive and my courses for admins and teachers.
 *
 * @note This function toggles the display sending a GET-Request which causes the
 *       whole page to be rebuilt by the server, which is very inefficient. A
 *       better approach would probably involve some javascript programming
 *       to simply toggle what the TreeMenu should display.
 *
 * @uses $CFG
 * @uses $FULLME
 *
 * @var string iurl to store url of inactive courses
 * @var string aurl to store url of active courses
 * @var string murl to store url of myCourses of teachers/admins
 * @var string iimg to store icon of inactive courses
 * @var string aimg to store icon of active courses
 * @var string mimg to store icon of myCourses of teachers/admins
 *
 * @return object HTML-Text/Table to be displayed as Title-Bar of the block
 */

function get_header() {
    global $CFG, $FULLME;//Cr: not needed any more, $SESSION;

    $iurl = preg_replace('/([\&\?]mycact=(on|off)|[\&\?]mycinact=(on|off)|[\&\?]mycmyc=(on|off))/', '', $FULLME);
    $aurl = $iurl;
    $murl = $iurl;

    $iimg = '';
    $aimg = '';
    $mimg = '';

    if ($this->admin || $this->teacher) {//Cr: compose table with icons and urls to toggle display of given courses
        if ($this->mycinact) {
            $iimg = $CFG->wwwroot . '/blocks/myCourses/pix/inacton.gif';
            $title = ' title="' . get_string('hideinactivecourses', 'block_myCourses') . '."';
            if (strpos($iurl, '?')) {
                $iurl .= '&mycinact=off';
            } else {
                $iurl .= '?mycinact=off';
            }
        } else {
            $iimg = $CFG->wwwroot . '/blocks/myCourses/pix/inactoff.gif';
            $title = ' title="' . get_string('showinactivecourses', 'block_myCourses') . '."';
            if (strpos($iurl, '?')) {
                $iurl .= '&mycinact=on';
            } else {
                $iurl .= '?mycinact=on';
            }
        }

       //Cr: next section only for admins, because only admins can see all courses
        if ($this->admin) {
            if ($this->mycact) {
                $aimg = $CFG->wwwroot . '/blocks/myCourses/pix/acton.gif';
                $atitle = ' title="' . get_string('hideactivecourses', 'block_myCourses') . '."';
                if (strpos($aurl, '?')) {
                    $aurl .= '&mycact=off';
                } else {
                    $aurl .= '?mycact=off';
                }
            } else {
                $aimg = $CFG->wwwroot . '/blocks/myCourses/pix/actoff.gif';
                $atitle = ' title="' . get_string('showactivecourses', 'block_myCourses') . '."';
                if (strpos($aurl, '?')) {
                    $aurl .= '&mycact=on';
                } else {
                    $aurl .= '?mycact=on';
                }
            }

        }
       // else { to be removed at cleanup 
       //Cr: only teachers have myCourses, but now we include also admins to restore original functionality

        if ($this->mycmyc) {
            $mimg = $CFG->wwwroot . '/blocks/myCourses/pix/mycon.gif';
            $mtitle = ' title="' . get_string('hidemycourses', 'block_myCourses') . '."';
            if (strpos($murl, '?')) {
                $murl .= '&mycmyc=off';
            } else {
                $murl .= '?mycmyc=off';
            }
        } else {
            $mimg = $CFG->wwwroot . '/blocks/myCourses/pix/mycoff.gif';
            $mtitle = ' title="' . get_string('showmycourses', 'block_myCourses') . '."';
            if (strpos($murl, '?')) {
                $murl .= '&mycmyc=on';
            } else {
                $murl .= '?mycmyc=on';
            }
           // } to be removed at cleanup
        }

       //Cr: return title embedded in a table with GUI to toggle show/hide teachers's and admin's courses

        return '<table cellpadding="0" cellspacing="0" border="0"><tr>' .
        '<td width="100%">' . $this->title . '</td>' .
        '<td align="right" nowrap>' .
         (!empty ($mimg) ? '<a href="' . $murl . '"' . $mtitle . '><img src="' . $mimg . '" /></a>' : '') .
         (!empty ($aimg) ? '<a href="' . $aurl . '"' . $atitle . '><img src="' . $aimg . '" /></a>' : '') .
         (!empty ($iimg) ? '<a href="' . $iurl . '"' . $title . '><img src="' . $iimg . '" /></a>' : '') .
        '</td></tr></table>';
    } else {
        return $this->title;//Cr: return title string as is for normal users
    }
}

/**
 *
 * build_navtree() constructs a hierarchical navigation tree with icons of open/closed folders
 * and returns it
 *
 * Cr: changed function name from build_menu to build_navtree to better reflect what the
 * function does
 *
 * @uses $CFG
 * @uses $USER
 * @uses $SITE
 *
 * @var object $this->navTree the whole navigation tree
 * @var boolean $adminseesall can be used to show own or all courses for admins
 * @var array $categories for the cats returned by get_categories()
 * @var array $this->cattree to hold the tree of categories returned by $this->load_cattree($categories)
 * @var array $courses to hold courses returned by get_my_courses or get_records('course')
 *
 * @return object $output
 */

function build_navtree() {
    global $CFG, $USER, $SITE;

    $this->navTree = new HTML_TreeMenu();//Cr: changed name of member variable
//from $this->menu to $this->navTree to better reflect what is stored here.

/// Build a tree of categories for later use.
    $categories = get_categories(0);

//print_r($categories);//Cr: for debugging

    $this->cattree = $this->load_cattree($categories);//Cr: search recursively
//through all cats and save a reference to that tree

//Cr: in next line we have adjusted the logic to display teacher's and admin's
//active, inactive and myCourses

    if (isset ($USER->id) && ((isadmin() && $this->mycmyc) || !isadmin())) {
//Cr: get myCourses of normal users and of teachers if they want to

//Cr: for debugging
//print_r(array('load only myCourses',$USER->id,'myc',$this->mycmyc,'mycact',$this->mycact));

        if (!($courses = get_my_courses($USER->id))) {
            $courses = array ();
        }
    } else {// print all courses
        if (!($courses = get_records('course'))) {
            $courses = array ();
        }
    }

//print_r($courses);//Cr: for debugging

/// First, load all of the courses into an array of categories.
    foreach ($courses as $course) {
         
		 if (!$course->category) {//Cr: the only course belonging to category 0
                                 //    is the front page, so skip it
            continue;
        } // end if

        $this->has_courses($course);//Cr: mark all categories having courses if
                                    //the according courses are to be displayed
    } // end foreach

    if ($this->course->id == $SITE->id) {//Cr: if we are on the front page,
                                         //    show node text in bold
//Cr: title= will show up a tool tip text on mouse hover
    /*
	  	$url = $CFG->wwwroot . '" title="' . $SITE->fullname;
        
		if ($CFG->block_myCourses_showlongnames) {
            $text = ' <span style="font-weight: bold;" title="' .  $SITE->shortname . '">' .  $SITE->fullname . '</span>';
        } else {
            $text = ' <span style="font-weight: bold;" title="' .  $SITE->fullname. '">' .  $SITE->shortname . '</span>';
        }
        $cssclass = 'treeMenuBold';
        $icon = 'home.gif';
        $expandedicon = 'home.gif';*/
    } 
	
	
	else {//Cr: else show it in plain
	
	   
	/*
        $url = $CFG->wwwroot . '" title="' . $SITE->fullname;
        if ($CFG->block_myCourses_showlongnames) {
            $text = ' <span style="font-weight: plain;" title="' . $SITE->shortname . '">' . $SITE->fullname . '</span>';
        } else {
            $text = ' <span style="font-weight: plain;" title="' . $SITE->fullname . '">' . $SITE->shortname . '</span>';
        }
        $cssclass = 'treeMenuDefault';
        $icon = 'home.gif';
        $expandedicon = 'home.gif';*/
    }




    $mnode = new HTML_TreeNode(array (
        'text' => $text,
        'link' => $url,
        'icon' => $icon,
        'cssClass' => $cssclass,
        'expandedIcon' => $expandedicon,
        'expanded' => true
    ));
    $this->create_tree_menu($this->cattree, $mnode);
    $this->navTree->addItem($mnode);
    $treemenu = & new HTML_TreeMenu_DHTML($this->navTree, array (
        'images' => $CFG->wwwroot . '/blocks/myCourses/HTML_TreeMenu-1.2.0/images',
        'defaultClass' => 'treeMenuDefault'
    ));

    ob_start();
    $treemenu->printMenu();
    $output = '
        <style type="text/css">
            .treeMenuDefault {
                font-size: 90%;
                font-style: normal;
            }
    
            .treeMenuBold {
                font-size: 90%;
                font-weight: bold;
            }
        </style>
            ';
    $output .= '<script src="' . $CFG->wwwroot . '/blocks/myCourses/HTML_TreeMenu-1.2.0/TreeMenu.js" language="JavaScript" type="text/javascript"></script>';
    $output .= ob_get_contents();
    ob_end_clean();

    return $output;
}

/**
 * Compose indented tree recursively and return it
 *
 *Cr: It seems, this function is not used any more, I guess the HTMLtreeMenu
 *    does the needed indentation. I will remove that code on next cleanup.
 * 
 * @param array $branches
 * @param object $output
 * @param integer $indent default is 0
 * @return object $output
 */

function print_menu($branches, $output = '', $indent = 0) {

    foreach ($branches as $node) {
        if (!empty ($output)) {
            $output .= '<br />';
        }
        if ($indent) {
            for ($i = 0; $i < $indent; $i++) {
                $output .= '&nbsp;';
            }
        }
        $output .= $node->node->name;
        if ($node->branches) {
            $output = $this->print_menu($node->branches, $output, $indent +2);
        }
    }
    return $output;
}

/**
 * Compose Tree of categories recursively and return it
 *
 * @param array &$categories
 * @return array $categories
 */

function load_cattree(& $categories) {

//print_r($categories);          //Cr: for debugging
//print_r("next recursive call");//Cr: for debugging

    foreach ($categories as $category) {
        if ($cats = get_categories($category->id)) {
            $categories[$category->id]->categories = $this->load_cattree($cats);
        } else {
            $categories[$category->id]->categories = array ();
        }
        $categories[$category->id]->hascourses = false;
        $this->categories[(int) $category->id] = & $categories[$category->id];
    }

//print_r($categories);//Cr: for debugging

    return $categories;
}

/**
 * Function that loads the courses applicable to the current selected filter settings.
 *
 * @param object $course The course to check against.
 * @return boolean
 */

function has_courses($course) {

/// Load this course if its not filtered.

/// If it's a visible course, and the active courses filter is off, don't show it.
//Cr: this case only applies for admins. Teachers are not able to see all active courses.

    if ($course->visible && isadmin() && !$this->mycact) {

//Cr: for debugging
//print_r(array($course, 'do not show active courses of admins'));
        return;

/// If it's an inactive course, and the inactive course filter is off, don't show it.
    } else
        if (!$course->visible && (isadmin() || isteacheredit($course->id)) && !$this->mycinact) {

//Cr: for debugging
//print_r(array($course, 'do not show inactive courses of teachers or admins'));
            return;

/// If it's a mycourse, and the mycourse filter is off and the course is active
/// (inactivity takes precedence), don't show it.

//Cr: only teachers AND ADMINS can toggle myCourses on and off. But the logic may still be buggy here
//    if the admin has teacher AND admin role in one course. In the best case he is admin which
//    takes precedence here, and all courses are displayed, i.e. none is skipped. The other way round
//    it should work fine because an editing teacher should normally NOT be an admin and hence no
//    courses should be skipped here.

        } else
            if ($course->visible && (isteacheredit($course->id) XOR isadmin()) && !$this->mycmyc) {

//Cr: for debugging
//print_r(array($course, 'do not show myCourses of teachers'));
                return;
            }

    $this->categories[$course->category]->hascourses = true;
    $this->categories[$course->category]->courses[$course->sortorder] = $course;

/// Make sure the courses are in the order specified.
    ksort($this->categories[$course->category]->courses);

    $catid = $course->category;
    while ($this->categories[$catid]->parent > 0) {
        $catid = $this->categories[$catid]->parent;
        $this->categories[$catid]->hascourses = true;
    }
}

/**
 * Create whole hierarchical Tree
 *
 * @uses $CFG
 * @param array $categories
 * @param object &$pnode
 * @return void
 */

function create_tree_menu($categories, & $pnode) {
    global $CFG;

    $ficon = 'folder.gif';//Cr: changed name of variable from nicon to ficon
                          //    to better reflect its content
    $feicon = 'folder-expanded.gif';//Cr: changed name of variable from eicon to
                                    //    feicon to better reflect its content
    $cicon = 'course.gif';
    $cacticon = 'courseact.gif';
    $cinacticon = 'courseinact.gif';
    $cmycicon = 'coursemyc.gif';

    foreach ($categories as $catid => $catnode) {
        if (!$catnode->hascourses)
            continue;

        $linkcss = '';
        $cssclass = 'treeMenuDefault';
        
        if (!$CFG->block_myCourses_showflatlist) {//Cr: skip folders/categories
//to show only a flat list of courses
            if ($CFG->block_myCourses_showcategorylinks) { //Cr: show/hide link
                $url = $CFG->wwwroot . '/course/category.php?id=' . $catnode->id .
                '" title="' . htmlspecialchars($catnode->name, ENT_QUOTES) . $linkcss;//categories have no fullname field
            } else {
                $url = '';//Cr: empty url, i.e. no link to Moodle category page
            }

            if (!empty ($this->selectedcat) && ($this->selectedcat == $catnode->id)) {
                $cssclass = 'treeMenuDefault highlight';
            }
            $node = & $pnode->addItem(new HTML_TreeNode(array (
                'text' => ' ' . $catnode->name,
                'link' => $url,
                'icon' => $ficon,
                'expandedIcon' => $feicon,
                'cssClass' => $cssclass
            )));
        } else {             //Cr: add an empty node
            $node = & $pnode;//Cr: this seems to be a little hack, as we do not
                             //    create a new node here with ->addItem but
                             //    simply pass the same node's address,
        }                    //    nevertheless this should be ok, as we add
                             //    several entries to the same node

        if (!empty ($catnode->categories)) {
            $this->create_tree_menu($catnode->categories, $node);
        }
        if (!empty ($catnode->courses)) {
            foreach ($catnode->courses as $course) {

                $linkcss = '';//Cr: I am not sure here, but I guess that a
                              //    specific css for links could be specified
                              //    here or below

                if ($this->mycmyc) {//Cr: if admin or teacher wants to see his
                                    //    myCourses
                    if ($course->visible) {
                        $icon = $cmycicon;
                    } else {
                        $icon = $cinacticon;
                    }
                } else
                    if (!$this->admin && !$this->teacher) {
                        $icon = $cicon;
                    } else
                        if ($course->visible) {
                            $icon = $cacticon;
                        } else {
                            $linkcss .= '';//Cr: dito for this particular case
                                           //    I guess, like $cssclass
                            $icon = $cinacticon;
                        }
                if ($this->course->id == $course->id) {
                    $url = '';
                    $text = $course->shortname;
                    $cssclass = 'treeMenuBold';
                } else {
                    if ($CFG->block_myCourses_showlongnames) {
                        $url = $CFG->wwwroot . '/course/view.php?id=' . $course->id .
                       '" title="' . htmlspecialchars($course->shortname, ENT_QUOTES) . $linkcss;
                       $text = $course->fullname;
                    } else {
                        $url = $CFG->wwwroot . '/course/view.php?id=' . $course->id .
                       '" title="' . htmlspecialchars($course->fullname, ENT_QUOTES) . $linkcss;
                       $text = $course->shortname;
                    }
                    $cssclass = 'treeMenuDefault';
                }
                $node->addItem(new HTML_TreeNode(array (
                    'text' => $text,
                    'link' => $url,
                    'icon' => $icon,
                    'cssClass' => $cssclass
                )));
            }
        }
    }
}


/** Experimental, has to be recoded to return a text-string instead of modifying a global
 *
 * get_remote_courses() returns a flat list of courses line by line
 *
 * Cr: added to original code to better complete the myCourses list
 *
 * @uses $CFG
 * @uses $USER
 * @uses $THEME
 *
 * @var array $remotecourses to hold eventual mnet courses
 * @var array $courses to hold courses returned by get_my_courses
 *
 * @return object $output
 */

function get_remote_courses() {
    global $THEME, $CFG, $USER;

    if (!is_enabled_auth('mnet')) {
        // no need to query anything remote related
        return;
    }

    $icon  = '<img src="'.$CFG->pixpath.'/i/mnethost.gif" class="icon" alt="'.get_string('course').'" />';

    // only for logged in users!
    if (!isloggedin() || isguest()) {
        return false;
    }

    if ($courses = get_my_remotecourses()) {
        $this->content->items[] = get_string('remotecourses','mnet');
        $this->content->icons[] = '';
        foreach ($courses as $course) {
            $this->content->items[]="<a title=\"" . format_string($course->shortname) . "\" ".
                "href=\"{$CFG->wwwroot}/auth/mnet/jump.php?hostid={$course->hostid}&amp;wantsurl=/course/view.php?id={$course->remoteid}\">" 
                . format_string($course->fullname) . "</a>";
            $this->content->icons[]=$icon;
        }
        // if we listed courses, we are done
        return true;
    }

    if ($hosts = get_my_remotehosts()) {
        $this->content->items[] = get_string('remotemoodles','mnet'); 
        $this->content->icons[] = '';
        foreach($USER->mnet_foreign_host_array as $somehost) {
            $this->content->items[] = $somehost['count'].get_string('courseson','mnet').'<a title="'.$somehost['name'].'" href="'.$somehost['url'].'">'.$somehost['name'].'</a>';
            $this->content->icons[] = $icon;
        }
        // if we listed hosts, done
        return true;
    }

    return false;
}

}

?>
