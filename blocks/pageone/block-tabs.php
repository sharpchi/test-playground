<?php 
/**
 * Tabs for pageone
 *
 * @author Mark Nielsen
 * @package pageone
 **/

    if (empty($currenttab)) {
        $currenttab = 'config';
    }

    $rows = array();
    $row = array();

    $row[] = new tabobject('config', "$CFG->wwwroot/admin/block.php", get_string('tab_config', 'block_pageone'));
    $row[] = new tabobject('custom', "$CFG->wwwroot/blocks/pageone/editalpha.php", get_string('edit_alpha', 'block_pageone'));
    $rows[] = $row;

    print_tabs($rows, $currenttab);
?>