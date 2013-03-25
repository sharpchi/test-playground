<?php //Version: 2008090801

$settings->add(new admin_setting_configcheckbox('block_myCourses_withscrollbars',
               get_string('withscrollbars', 'block_myCourses'),
               get_string('configwithscrollbars', 'block_myCourses'), 1)); //Default: Yes

$settings->add(new admin_setting_configcheckbox('block_myCourses_showallcourseslink',
               get_string('showallcourseslink', 'block_myCourses'),
               get_string('configshowallcourseslink', 'block_myCourses'), 0)); //Default: No

$settings->add(new admin_setting_configcheckbox('block_myCourses_showlongnames',
               get_string('showlongnames', 'block_myCourses'),
               get_string('configshowlongnames', 'block_myCourses'), 0)); //Default: No

$settings->add(new admin_setting_configcheckbox('block_myCourses_showcategorylinks',
               get_string('showcategorylinks', 'block_myCourses'),
               get_string('configshowcategorylinks', 'block_myCourses'), 1)); //Default: Yes

$settings->add(new admin_setting_configcheckbox('block_myCourses_showflatlist',
               get_string('showflatlist', 'block_myCourses'),
               get_string('configshowflatlist', 'block_myCourses'), 1)); //Default: Yes

$settings->add(new admin_setting_configcheckbox('block_myCourses_showsearchbox',
               get_string('showsearchbox', 'block_myCourses'),
               get_string('configshowsearchbox', 'block_myCourses'), 0)); //Default: No



?>
