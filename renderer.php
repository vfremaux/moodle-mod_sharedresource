<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package sharedresource
 * @category mod
 * @author  Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 */
defined('MOODLE_INTERNAL') || die();

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