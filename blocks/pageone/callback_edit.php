<?php

/**
* Handles the manual editing of callbacks registrations on the PageOne server
* @author Tim Williams
* @package pageone
**/

    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('pageonelib.php');
    require_once('services/callback_url.php');

    $action = optional_param('action', '', PARAM_ALPHA);
    global $CFG;

    $adminroot = admin_get_root();
    if ($CFG->version>2007101522)
        admin_externalpage_setup('manageblocks');
    else
        admin_externalpage_setup('manageblocks', $adminroot);

    $strmanageblocks = get_string('edit_callback', 'block_pageone');
    $strdelete = get_string('delete');
    $strversion = get_string('version');
    $strhide = get_string('hide');
    $strshow = get_string('show');
    $strsettings = get_string('settings');
    $strcourses = get_string('blockinstances', 'admin');
    $strname = get_string('name');
    $strmultiple = get_string('blockmultiple', 'admin');
    $strshowblockcourse = get_string('showblockcourse');

    admin_externalpage_print_header($adminroot);

    print_heading($strmanageblocks);

    $context = get_context_instance(CONTEXT_SYSTEM);
    if (!has_capability('moodle/site:config', $context))
    {
        echo '<p>'.get_string("not_authorised", "block_pageone").'</p>';
        admin_externalpage_print_footer($adminroot);
        die();
    }

    if ($action=="addignore")
    {
        pageone_set_callback();
        echo '<p class="informationbox">'.get_string("callback_added", "block_pageone").'</p>';
    }
    else
    if ($action=="deletealladd")
    {
        pageone_remove_all_callbacks();
        pageone_set_callback();
        echo '<p class="informationbox">'.get_string("callback_added", "block_pageone").'</p>';
    }
    else
    if ($action=="delete")
    {
        pageone_remove_specific_callback(required_param('id', PARAM_INT));
    }
    else
    if ($action=="add")
    {
        pageone_set_callback(required_param('host', PARAM_URL), required_param('service', PARAM_INT));
    }


    $tablecolumns = array('host', 'service', '');
    $tableheaders = array(get_string('host', 'block_pageone'), get_string('service', 'block_pageone'), get_string('action', 'block_pageone'));

    $table = new flexible_table('blocks-pageone-editcallback');

/// define table columns, headers, and base url
    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($CFG->wwwroot.'/blocks/pageone/callback_edit.php');

/// table settings
    //$table->sortable(true, 'user', SORT_DESC);
    //$table->collapsible(true);
    $table->initialbars(false);
    $table->pageable(false);

/// set attributes in the table tag
    $table->set_attribute('cellpadding', '4');
    $table->set_attribute('id', 'editalpha');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('style', 'margin-left:auto; margin-right:auto;');
    $table->setup();

    $callbacks=pageone_get_callback_list();

    foreach ($callbacks as $cb)
    {
        $table->add_data(array(
            $cb->host,
            get_string("service_".$cb->service, "block_pageone"),
            "<a href=\"$CFG->wwwroot/blocks/pageone/callback_edit.php?action=delete&amp;id=".$cb->entryID."\">".
            "<img src=\"$CFG->pixpath/t/delete.gif\" height=\"11\" width=\"11\" alt=\"".get_string('delete').'" /></a>'));
    }

    $table->print_html();

    echo "<br /><form  action=\"".$CFG->wwwroot."/blocks/pageone/callback_edit.php\" method=\"post\">\n".
        "<table style=\"margin-left:auto;margin-right:auto;\">".
        "<tr>".
        "<td>".get_string("host", "block_pageone")."</td>".
        "<td><input type=\"hidden\" name=\"action\" value=\"add\" /><input type=\"text\" size=\"80\" name=\"host\" value=\"".$CALLBACK_URL."?wsdl\" /></td>".
        "</tr><tr>".
        "<td>".get_string("service", "block_pageone")."</td>".
        "<td><select name=\"service\">".
        "<option value=\"".CALLBACK_BOTH."\">".get_string("service_".CALLBACK_BOTH, "block_pageone")."</option>".
        "<option value=\"".CALLBACK_DELIVERY."\">".get_string("service_".CALLBACK_DELIVERY, "block_pageone")."</option>".
        "<option value=\"".CALLBACK_REPLY."\">".get_string("service_".CALLBACK_REPLY, "block_pageone")."</option>".
        "</select></td>".
        "</tr><tr>".
        "<td colspan=\"2\" align=\"right\"><input type=\"submit\" value=\"".get_string("add_callback", "block_pageone")."\" /></td>\n".
        "</tr></table></form>".
        "<p>".get_string("callback_delay", "block_pageone")."</p>";

    admin_externalpage_print_footer($adminroot);
?>
