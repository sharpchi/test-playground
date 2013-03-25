<?php

function pageone_get_locale_name_always_second() 
{
    return get_string('config_mobile_find_always_second', 'block_pageone');
}

function pageone_get_mobile_number_always_second($user)
{
    return $user->phone2;
}

function pageone_process_mobile_number_always_second($num)
{
    $num=preg_replace("/[\s|-]/", "", $num);
    global $CFG;
    if (ereg("^(".$CFG->block_pageone_country_code.")", $num))
        return "0".substr($num, strlen($CFG->block_pageone_country_code));

    return $num;
}

?>