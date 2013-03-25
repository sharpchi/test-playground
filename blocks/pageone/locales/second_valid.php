<?php

function pageone_get_locale_name_second_valid() 
{
    return get_string('config_mobile_find_second_valid', 'block_pageone');
}

function pageone_get_mobile_number_second_valid($user)
{
   if (strlen($user->phone2)>0)
       return $user->phone2;

   if (strlen($user->phone1)>0)
       return $user->phone1;

   return "";
}

function pageone_process_mobile_number_second_valid($num)
{
    $num=preg_replace("/[\s|-]/", "", $num);
    global $CFG;
    if (ereg("^(".$CFG->block_pageone_country_code.")", $num))
        return "0".substr($num, strlen($CFG->block_pageone_country_code));

    return $num;
}

?>