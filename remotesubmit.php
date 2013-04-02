<?php
    require_once("../../config.php");
    require_once($CFG->dirroot."/mod/sharedresource/lib.php");
    require_once($CFG->dirroot."/mod/sharedresource/sharedresource_plugin_base.class.php");
    $identifier = required_param('resid', PARAM_RAW);
    $navlinks[] = array('name' => get_string('remotesubmission', 'sharedresource'),
                        'url' => '',
                        'type' => 'title');
    $PAGE->set_title(get_string('remotesubmission', 'sharedresource'));
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('remotesubmission', 'sharedresource'));
    $id = required_param('id', PARAM_INT);
    $confirm = optional_param('confirm', '', PARAM_INT);
    $identifier = required_param('resid', PARAM_RAW);
    $repos = optional_param('repos', '', PARAM_TEXT);
    if ($confirm == 1){
        $sharedresource_entry =  $DB->get_record('sharedresource_entry', array('identifier'=> $identifier));
        $plugins = sharedresource_get_plugins();
        foreach ($plugins as $plugin) {
            $pluginclass = get_class($plugin);
            preg_match('/sharedresource_plugin_(.*)/', $pluginclass, $matches); 
            $pluginname = $matches[1];
            if (!empty($repos) && !preg_match("/\\b$pluginname\\b/", $repos)) continue;
            if ($plugin->remotesubmit($sharedresource_entry)){
                redirect($CFG->wwwroot."/course/view.php?id=$id&amp;action=remoteindex");
            } else {
                print_error('errornnoticecreation', 'sharedresource');
            }
        }
    } else {
        if ($confirm === 0){
            redirect($CFG->wwwroot."/course/view.php?id=$id&amp;action=remoteindex");
        } else {
            $options['id'] = $id;
            $options['resid'] = $identifier;
            $options['confirm'] = 1;
            $options['repos'] = $repos;
            echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/mod/sharedresource/remotesubmit.php', $options), get_string('confirm'), 'get');
            $canceloptions['id'] = $id;
            $canceloptions['action'] = 'remoteindex';
            echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/course/view.php', $canceloptions), get_string('cancel'), 'get');
        }
    }
?>