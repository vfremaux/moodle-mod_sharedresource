<?php

/**
* implements a hook for the page_module block to construct the
* access link to a sharedressource
*
*
*/

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

function sharedresource_set_instance(&$block){
    global $CFG, $DB;

    $modinfo = get_fast_modinfo($block->course);

    // Get module icon
    if (!empty($modinfo->cms[$block->cm->id]->icon)) {
        $icon = $CFG->pixpath.'/'.urldecode($modinfo->cms[$block->cm->id]->icon);
    } else {
        $icon = "$CFG->modpixpath/{$block->module->name}/icon.gif";
    }

    $name = format_string($block->moduleinstance->name);
    $alt  = get_string('modulename', $block->module->name);
    $alt  = s($alt);

    $block->content->text  = "<img src=\"$icon\" alt=\"$alt\" class=\"icon\" />";
    $block->content->text .= "<a title=\"$alt\" href=\"$CFG->wwwroot/mod/{$block->module->name}/view.php?id={$block->cm->id}\">$name</a>";

    // call each plugin to add something
    $plugins = sharedresource_get_plugins();
    foreach ($plugins as $plugin) {
        if (method_exists($plugin, 'sharedresource_set_instance')){
            $cm = get_coursemodule_from_id('sharedresource', $block->cm->id);
            $sharedresource =  $DB->get_record('sharedresource', array('id'=> $cm->instance));
            $plugin->sharedresource_set_instance($block, $sharedresource);
        }
    }

    return true;

}
