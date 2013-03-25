<?php

function pageone_get_locale_name_starts_m() 
{
    return get_string('config_mobile_find_starts_m', 'block_pageone');
}

function pageone_get_mobile_number_starts_m($user)
{
   return "Not Implemented";
}

function pageone_process_mobile_number_starts_m($num)
{
    $num=preg_replace("/[\s|-]/", "", $num);
    global $CFG;
    if (ereg("^(".$CFG->block_pageone_country_code.")", $num))
        return "0".substr($num, strlen($CFG->block_pageone_country_code));

    return $num;
}

?>