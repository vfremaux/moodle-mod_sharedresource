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

    $cm = get_coursemodule_from_id('sharedresource', $block->cm->id);
    require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_base.class.php');
    $resourceinstance = new sharedresource_base($cmid, null);
    $resourceinstance->embedded
    $block->content->text = '<div class="block-page-module-view">'.$sharedresource->display().'</div>';
 
    // call each plugin to add something
    $plugins = sharedresource_get_plugins();
    foreach ($plugins as $plugin) {
        if (method_exists($plugin, 'sharedresource_set_instance')){
            $sharedresource =  $DB->get_record('sharedresource', array('id' => $cm->instance));
            $plugin->sharedresource_set_instance($block, $sharedresource);
        }
    }

    return true;
}
