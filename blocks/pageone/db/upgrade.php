<?php

// This file keeps track of upgrades to this block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_pageone_upgrade($oldversion=0) {
    $result = true;

    if ($oldversion < 2008050803)
    {
        $table = new XMLDBTable('block_pageone_log');
        $field = new XMLDBField('includefrom');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', true, true, false, false, null, "0", 'failednumbers');
        $result=add_field($table, $field);
    }

    if ($oldversion < 2008082601)
    {
        $table = new XMLDBTable('block_pageone_alphatags');
        $field = new XMLDBField('receive');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '1', true, true, false, false, null, "0", 'alphatag');
        $result=add_field($table, $field);
    }

    if ($oldversion < 2008082901)
    {
        $table = new XMLDBTable('block_pageone_log');
        $field = new XMLDBField('ovid');
        $field->setAttributes(XMLDB_TYPE_INTEGER, '15', true, true, false, false, null, "0", 'includefrom');
        $result=add_field($table, $field);
    }

    if ($oldversion < 2009011309)
    {
        $table = new XMLDBTable('block_pageone_inlog');
        $id = new XMLDBField('id');
        $id->setAttributes(XMLDB_TYPE_INTEGER, '10', true, true, true, false, null);
        $table->addField($id);

        $courseid = new XMLDBField('courseid');
        $courseid->setAttributes(XMLDB_TYPE_INTEGER, '10', true, true, false, false, null, "0", 'id');
        $table->addField($courseid);

        $userid = new XMLDBField('userid');
        $userid->setAttributes(XMLDB_TYPE_INTEGER, '10', true, true, false, false, null, "0", 'courseid');
        $table->addField($userid);

        $mailfrom = new XMLDBField('mailfrom');
        $mailfrom->setAttributes(XMLDB_TYPE_INTEGER, '16', true, true, false, false, null, "0", 'userid');
        $table->addField($mailfrom);

        $timesent = new XMLDBField('timesent');
        $timesent->setAttributes(XMLDB_TYPE_INTEGER, '10', true, true, false, false, null, "0", 'mailfrom');
        $table->addField($timesent);

        $message = new XMLDBField('message');
        $message->setAttributes(XMLDB_TYPE_TEXT, 'small', true, true, false, false, null, "", 'timesent');
        $table->addField($message);

        $dbkey=new XMLDBKey('id');
        $dbkey->setAttributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($dbkey);

        $coursekey=new XMLDBKey('courseid');
        $coursekey->setAttributes(XMLDB_KEY_FOREIGN, array('courseid'));
        $table->addKey($coursekey);

        $userkey=new XMLDBKey('userid');
        $userkey->setAttributes(XMLDB_KEY_FOREIGN, array('userid'));
        $table->addKey($userkey);

        if (!create_table($table))
            return false;
    }

    return $result;
}

?>