<?php
    /**
    * Editing page for MSISDN assignments (formerly know as alphatags)
    * @author T.M. Williams
    * @package pageone
    **/

    require_once('../../config.php');
    require_once($CFG->libdir.'/adminlib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('pageonelib.php');

    $action = optional_param('action', '', PARAM_ALPHA);

    $adminroot = admin_get_root();
    if ($CFG->version>2007101522)
        admin_externalpage_setup('manageblocks');
    else
        admin_externalpage_setup('manageblocks', $adminroot);

    $strmanageblocks = get_string('edit_alpha', 'block_pageone');
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
    //$currenttab = 'custom';
    //include($CFG->dirroot.'/blocks/pageone/block-tabs.php');

    $context = get_context_instance(CONTEXT_SYSTEM);
    if (!has_capability('moodle/site:config', $context))
    {
        echo '<p>'.get_string("not_authorised", "block_pageone").'</p>';
        admin_externalpage_print_footer($adminroot);
        die();
    }

    if ($action=="add")
    {
        if (pageone_add_user_alphatag(required_param('user', PARAM_INT), required_param('alphatag', PARAM_TEXT), required_param('rec', PARAM_INT))==false)
            echo '<p class="warning" style="text-align:center;">'
                 .get_string("already_set", "block_pageone").'</p>';
    }
    else
    if ($action=="delete")
        pageone_delete_user_alphatag(required_param('id', PARAM_INT));
    else
    if ($action=="update")
    {
        $num=required_param('num', PARAM_INT);
        for ($loop=0; $loop<$num; $loop++)
        {
            pageone_update_user_alphatag(required_param('id_'.$loop, PARAM_INT), required_param('val_'.$loop, PARAM_TEXT), optional_param('rec_'.$loop, 0, PARAM_INT));
        }
    }
?>

<form action="<?php echo $CFG->wwwroot; ?>/blocks/pageone/editalpha.php" method="post">
<table style="margin-left:auto; margin-right:auto;"><tr><td>
<?php

    $tablecolumns = array('username', 'firstname', 'lastname', 'email', 'alphatag', 'receive', '');
    $tableheaders = array(get_string('user', 'block_pageone'), get_string('firstname'), get_string('lastname'), get_string('email'),
     get_string('alphatag', 'block_pageone'), get_string('receive', 'block_pageone'), get_string('action', 'block_pageone'));

    $table = new flexible_table('blocks-pageone-editalpha');

/// define table columns, headers, and base url
    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($CFG->wwwroot.'/blocks/pageone/editalpha.php');

/// table settings
    $table->sortable(true, 'user', SORT_DESC);
    //$table->collapsible(true);
    $table->initialbars(false);
    $table->pageable(true);

/// set attributes in the table tag
    $table->set_attribute('cellpadding', '4');
    $table->set_attribute('id', 'editalpha');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_attribute('style', 'margin-left:auto; margin-right:auto;');
    $table->setup();

/// SQL
    $sql = "SELECT * FROM {$CFG->prefix}user ";

    if ($table->get_sql_where()) {
        $sql .= "AND ".$table->get_sql_where();
    }
    $sql .= " JOIN {$CFG->prefix}block_pageone_alphatags ON {$CFG->prefix}block_pageone_alphatags.userid = {$CFG->prefix}user.id";

    if (strlen($table->get_sql_sort())>0)
        $sql .= " ORDER BY ". $table->get_sql_sort();

    $rec_list=get_records_sql($sql, $table->get_page_start(), $table->get_page_size());
    $table->pagesize(10, count($rec_list));

    $count=0;
    foreach ($rec_list as $r)
    {
        if ($r->receive==1)
         $r->receive=" checked=\"checked\"";
        else
         $r->receive="";

        $table->add_data(array(
            "<a href=\"".$CFG->wwwroot."/user/view.php?id=".$r->userid."\">".$r->username."</a>",
            $r->firstname,
            $r->lastname,
            $r->email,
            "\n<input type=\"hidden\" name=\"id_".$count."\" value=\"".$r->id."\" />".
            pageone_get_alphatagoptions("val_".$count, "val_".$count."_label", $r->alphatag)."\n",
            "<input type=\"checkbox\" name=\"rec_".$count."\" value=\"1\"".$r->receive." />\n",
            "<a href=\"$CFG->wwwroot/blocks/pageone/editalpha.php?action=delete&amp;id=".$r->id."\">".
            "<img src=\"$CFG->pixpath/t/delete.gif\" height=\"11\" width=\"11\" alt=\"".get_string('delete').'" /></a>'));
        $count++;
    }
    $table->print_html();
?>
</td></tr><tr><td align="right">
    <input type="hidden" name="num" value="<?php echo count($rec_list) ?>" />
    <input type="hidden" name="action" value="update" />
    <input type="submit" value="<?php print_string("update_alphas", "block_pageone") ?>" />
</td></tr></table>
</form>

<br /><br />

<?php
    $capable_users=get_users_by_capability(get_context_instance(CONTEXT_SYSTEM), "block/pageone:cansend");

    $count=0;
    $data="";
    foreach ($capable_users as $user)
       if (!pageone_has_custom_alphatag($user->id))
       {
           $count++;
           $data .= '<option value="'.$user->id.'">'.$user->firstname.' '.$user->lastname.', '.$user->email.'</option>';
       }

       if ($count==0)
           echo '<p style="text-align:center;">'.get_string("no_available_users", "block_pageone").'</p>';
       else
       {
?>
<form action="<?php echo $CFG->wwwroot; ?>/blocks/pageone/editalpha.php" method="post">
<table style="margin-right:auto;margin-left:auto;">
    <tr>
        <td style="text-align:right;"><label for="block_pageone_selectuser_label"><?php print_string("selectuser", "block_pageone"); ?></label></td>
        <td>
            <select name="user" id="block_pageone_selectuser_label">
            <?php echo $data; ?>
            </select>
        </td>
    </tr>
    <tr>
        <td style="text-align:right;"><label for="block_pageone_alphatag"><?php print_string("alphatag", "block_pageone"); ?></label></td>
        <td>
            <?php echo pageone_get_alphatagoptions("alphatag", "block_pageone_alphatag").' '; print_string('alphatag_help', 'block_pageone'); ?>
        </td>
    </tr>
    <tr>
        <td style="text-align:right;"><label for="block_pageone_alphatag"><?php print_string("receive", "block_pageone"); ?></label></td>
        <td>
            <input type="checkbox" name="rec" value="1" />
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:right;">
            <input type="hidden" name="action" value="add" /><br />
            <input type="submit" value="<?php print_string("add_alpha_tag", "block_pageone") ?>" />
        </td>
    </tr>
</table>
</form>

<?php
        }
    admin_externalpage_print_footer($adminroot);
?>
