<?php

function pageone_get_locale_name_uk() 
{
    return get_string('config_mobile_find_uk_auto', 'block_pageone');
}

function pageone_get_mobile_number_uk($user)
{
    if (pageone_is_valid_mobile_number_uk($user->phone1))
        return $user->phone1;

    if (pageone_is_valid_mobile_number_uk($user->phone2))
        return $user->phone2;

    return "";
}

function pageone_is_valid_mobile_number_uk($num)
{
    //Do some processing to remove spaces/hyphens and make life simpler
    $num=preg_replace("/[\s|-]/", "", $num);

    //Regular expression to match all of the possible starting sequences for a UK mobile number
    return ereg("^(07|00447|447|\+447|\(07|\+44\(0\)7)", $num);
}

function pageone_process_mobile_number_uk($num)
{
    $num=preg_replace("/[\s|-]/", "", $num);

    if (ereg("^(07)", $num))
        return $num;

    if (ereg("^(00447)", $num))
        return "0".substr($num, 4);

    if (ereg("^(\+447)", $num))
        return "0".substr($num, 3);

    if (ereg("^(447)", $num))
        return "0".substr($num, 2);

    if (ereg("^(\(07)", $num))
        return preg_replace("/[\(|\)]/", "", $num);

    if (ereg("^(\+44\(0\)7)", $num))
        return "0".substr($num, 6);

    return $num;
}


?>