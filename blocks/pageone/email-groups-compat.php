<?php

/**
* Some of the methods in Moodle have been changed between versions, this page is
* included in circumstances where these methods have been found to be missing
**/

/**
* Gets all the groups which are part of the specified course
* @param $courseid The course ID
* @return and array of group ID's
**/

function groups_get_groups($courseid)
{
    $groups=groups_get_all_groups($courseid);
    $final=array();
    foreach ($groups as $a)
        $final[]=$a->id;
    return $final;
}

/**
* Gets all the groups the current user is a member of for the specified course
* @param $courseid The course ID
* @return and array of group ID's
**/

function groups_get_groups_for_current_user($courseid)
{
    global $USER;
    $groups=groups_get_all_groups($courseid, $USER->id);
    $final=array();
    foreach ($groups as $a)
        $final[]=$a->id;
    return $final;
}

?> 
