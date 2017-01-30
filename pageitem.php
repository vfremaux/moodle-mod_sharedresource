<?php

/**
* implements a hook for the page_module block to construct the
* access link to a sharedressource 
*
*
*/

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

function sharedresource_set_instance(&$block) {
    global $CFG, $DB, $COURSE, $PAGE;

    $modinfo = get_fast_modinfo($block->course);
    $renderer = $PAGE->get_renderer('format_page');

    if (empty($block->config)) {
        return;
    }

    if (!array_key_exists($block->config->cmid, $modinfo->cms)) {
        return;
    }

    $block->content->text = '<div class="block-page-module-view">'.$renderer->print_cm($COURSE, $modinfo->cms[$block->config->cmid], array()).'</div>';
 
    // call each plugin to add something
    $plugins = sharedresource_get_plugins();
    foreach ($plugins as $plugin) {
        if (method_exists($plugin, 'sharedresource_set_instance')){
            $cm = get_coursemodule_from_id('sharedresource', $block->cm->id);
            $sharedresource =  $DB->get_record('sharedresource', array('id' => $cm->instance));
            $plugin->sharedresource_set_instance($block, $sharedresource);
        }
    }

    return true;
}
