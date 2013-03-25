<?php

function pageone_get_locale_name_first_valid() 
{
    return get_string('config_mobile_find_first_valid', 'block_pageone');
}

function pageone_get_mobile_number_first_valid($user)
{
   if (strlen($user->phone1)>0)
       return $user->phone1;

   if (strlen($user->phone2)>0)
       return $user->phone2;

   return "";
}

function pageone_process_mobile_number_first_valid($num)
{
    $num=preg_replace("/[\s|-]/", "", $num);
    global $CFG;
    if (ereg("^(".$CFG->block_pageone_country_code.")", $num))
        return "0".substr($num, strlen($CFG->block_pageone_country_code));

    return $num;
}

?>