<?php



class mod_sharedresource_renderer extends plugin_renderer_base {


    public function add_instance_form($section, $return) {
        global $COURSE;

        if ($COURSE->id == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($COURSE->id);
        }

        $template = new StdClass;

        $libraryurl = new moodle_url('/local/sharedresources/index.php', array('course' => $COURSE->id, 'section' => $section, 'return' => $return));
        $template->searchbutton = $this->output->single_button($libraryurl, get_string('searchinlibrary', 'sharedresource'));
        $template->searchdesc = get_string('addinstance_search_desc', 'sharedresource');

        if (has_capability('repository/sharedresources:create', $context)) {
            $template->cancreate = true;
            $params = array('course' => $COURSE->id, 'section' => $section, 'return' => $return, 'add' => 'sharedresource', 'mode' => 'add');
            $editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
            $template->createbutton = $this->output->single_button($editurl, get_string('addsharedresource', 'sharedresource'));
            $template->createdesc = get_string('addinstance_create_desc', 'sharedresource');
        }

        return $this->output->render_from_template('mod_sharedresource/addinstance', $template);
    }

}